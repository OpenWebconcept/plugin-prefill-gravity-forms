<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use DateTime;
use Exception;
use GF_Field;
use OWC\PrefillGravityForms\Foundation\TeamsLogger;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Traits\SessionTrait;
use TypeError;

use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use function Yard\DigiD\Foundation\Helpers\resolve;

abstract class BaseController
{
    use SessionTrait;

    protected const CUSTOM_FIELDS_TYPES = [
        'owc_pg_age_check',
        'owc_pg_municipality_check',
    ];

    protected GravityFormsSettings $settings;
    protected TeamsLogger $teams;
    protected string $supplier;

    public function __construct()
    {
        $this->settings = GravityFormsSettings::make();
        $this->teams = $this->resolveTeams();
    }

    public function resolveTeams(): TeamsLogger
    {
        try {
            if (! function_exists('Yard\DigiD\Foundation\Helpers\resolve')) {
                throw new Exception();
            }

            return TeamsLogger::make(resolve('teams'));
        } catch (Exception $e) {
            return TeamsLogger::make(new \Psr\Log\NullLogger());
        }
    }

    protected function preFillFields(array $form, array $response): array
    {
        foreach ($form['fields'] as $field) {
            $linkedValue = $field->linkedFieldValue ?? '';

            if (empty($linkedValue)) {
                continue;
            }

            $foundValue = $this->findLinkedValue($linkedValue, $response);

            if (empty($foundValue)) {
                $field->cssClass = 'owc_prefilled'; // When field has mapping but there is no value found, set to read-only.

                continue;
            }

            if ('text' === $field->type) {
                $this->handleFieldText($field, $foundValue);

                continue;
            }

            if ('date' === $field->type) {
                $this->handleFieldDate($field, $foundValue);

                continue;
            }

            if (in_array($field->type, self::CUSTOM_FIELDS_TYPES)) {
                $field->defaultValue = $foundValue;

                continue;
            }
        }

        return $form;
    }

    public function findLinkedValue(string $linkedValue = '', array $response = []): string
    {
        if (empty($linkedValue) || empty($response)) {
            return $linkedValue;
        }

        return $this->explodeDotNotationValue($linkedValue, $response);
    }

    /**
     * Explode dot notation string into array items.
     * Use these array items to retrieve nested array values from the response.
     */
    public function explodeDotNotationValue(string $dotNotationString, array $response): string
    {
        $exploded = explode('.', $dotNotationString);

        // Initialize the holder with the first part of the response array.
        $holder = $response[$exploded[0]] ?? '';

        foreach (array_slice($exploded, 1) as $item) {
            if (empty($holder)) {
                break;
            }

            // Flatten if the holder is a single multidimensional array and the item is not numeric
            if (is_array($holder) && $this->isSingleMultidimensionalArray($holder) && ! is_numeric($item)) {
                $holder = $this->flattenMultidimensionalArray($holder);
            }

            // Move deeper into the nested array.
            $holder = $holder[$item] ?? '';
        }

        // Return the result, ensuring it's a string or numeric value.
        return is_string($holder) || is_numeric($holder) ? (string) $holder : '';
    }

    /**
     * Checks if the array contains only one element, and that element is itself an array.
     */
    protected function isSingleMultidimensionalArray(array $array): bool
    {
        return count($array) === 1 && is_array(reset($array));
    }

    /**
     * Flatten a multidimensional array with identical keys into a single array where the values of the last array remain.
     */
    protected function flattenMultidimensionalArray(array $array): array
    {
        $holder = [];

        foreach ($array as $part) {
            $holder = array_merge($holder, $part);
        }

        return $holder;
    }

    protected function handleFieldText(GF_Field $field, string $foundValue): void
    {
        if ($this->isPossibleDate($foundValue)) {
            $field->defaultValue = (new DateTime($foundValue))->format('d-m-Y');
        } else {
            $field->defaultValue = ucfirst($foundValue);
        }

        $field->cssClass = 'owc_prefilled';
    }

    public function isPossibleDate(string $value): bool
    {
        try {
            return (date('Y-m-d', strtotime($value)) == $value);
        } catch (Exception | TypeError $e) {
            return false;
        }
    }

    /**
     * Handles prefilling of date fields based on their date type.
     *
     * This method processes date fields, specifically handling different date input types.
     * The 'datefield' and 'datedropdown' types require a unique approach for pre-populating
     * their inputs as they consist of multiple parts (month, day, year). The 'datepicker' type,
     * which consists of a single input, is handled differently.
     */
    protected function handleFieldDate(GF_Field $field, string $foundValue): void
    {
        try {
            $date = new DateTime($foundValue);
        } catch (Exception $e) {
            return;
        }

        // Field consists of 1 part.
        if (empty($field->inputs) || 'datepicker' === $field->dateType) {
            $field->defaultValue = $date->format('d-m-Y');
            $field->displayOnly = true;
            $field->cssClass = 'owc_prefilled';

            return;
        }

        // Field consists of 3 parts which are represented by the input attribute.
        if (! empty($field->inputs) && ('datefield' === $field->dateType || 'datedropdown' === $field->dateType)) {
            $field->inputs[0]['defaultValue'] = $date->format('m');
            $field->inputs[1]['defaultValue'] = $date->format('d');
            $field->inputs[2]['defaultValue'] = $date->format('Y');
            $field->cssClass = 'owc_prefilled';
        }
    }

    public function getRequestURL(string $identifier = '', string $expand = ''): string
    {
        $baseURL = $this->settings->getBaseURL();

        if (empty($baseURL) || empty($identifier)) {
            return '';
        }

        $url = sprintf('%s/%s', $baseURL, $identifier);

        if (! empty($expand)) {
            $url = sprintf('%s?%s', $url, $this->createExpandArguments($expand));
        }

        return $url;
    }

    protected function createExpandArguments(string $expand): string
    {
        $exploded = explode(',', $expand);
        $filtered = array_filter($exploded);
        $new = array_map('trim', $filtered);
        $imploded = implode(',', $new);

        return urldecode(http_build_query(['expand' => $imploded], '', ','));
    }

    protected function getCurlHeaders(string $doelBinding = ''): array
    {
        $headers = [
            'x-doelbinding: ' . $doelBinding,
            'x-origin-oin: ' . $this->settings->getNumberOIN(),
        ];

        if ($this->settings->useAPIAuthentication()) {
            if (! empty($this->settings->getAPIKey())) {
                $headers[] = 'x-opentunnel-api-key: ' . $this->settings->getAPIKey();
            } elseif (! empty($this->settings->getBearerTokenUsername()) && ! empty($this->settings->getBearerTokenPassword())) {
                $headers[] = 'Authorization: Basic ' . base64_encode($this->settings->getBearerTokenUsername() . ':' . $this->settings->getBearerTokenPassword());
            }
        }

        return array_filter($headers);
    }

    protected function handleCurl(array $args): array
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, $this->getDefaultCurlArgs() + $args);

            if (! empty($this->settings->getPassphrase())) {
                curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->settings->getPassphrase());
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $output = curl_exec($curl);

            if (curl_error($curl)) {
                throw new Exception(curl_error($curl));
            }

            $decoded = json_decode($output, true);

            if (! $decoded || json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Something went wrong with decoding of the JSON output.');
            }

            return $decoded;
        } catch (Exception $e) {
            return [
                'status' => $e->getMessage(),
            ];
        }
    }

    protected function getDefaultCurlArgs(): array
    {
        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ];
    }

    /**
     * Prefilled fields have a custom css class
     * Based on this custom class fields are disabled.
     */
    protected function disableFormFields(): string
    {
        return view('disabledFormFields.php');
    }
}
