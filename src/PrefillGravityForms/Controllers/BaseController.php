<?php

namespace OWC\PrefillGravityForms\Controllers;

use DateTime;
use Exception;
use GF_Field;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use OWC\PrefillGravityForms\Foundation\TeamsLogger;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use function Yard\DigiD\Foundation\Helpers\decrypt;
use function Yard\DigiD\Foundation\Helpers\resolve;

abstract class BaseController
{
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

    protected function getBSN(): string
    {
        try {
            $bsn = resolve('session')->getSegment('digid')->get('bsn');
        } catch(Exception $e) {
            $bsn = '';
        }

        return is_string($bsn) && ! empty($bsn) ? decrypt($bsn) : '';
    }

    /**
     * BSN numbers could start with one or more zero's at the beginning.
     * The zero's are not returned by DigiD so the required length of 9 characters is not met.
     * Supplement the value so it meets the required length of 9.
     */
    protected function supplementBSN(string $bsn): string
    {
        $bsnLength = strlen($bsn);
        $requiredLength = 9;
        $difference = $requiredLength - $bsnLength;

        if (1 > $difference || $difference > $requiredLength) {
            return $bsn;
        }

        return sprintf("%'.0" . $requiredLength . "d", $bsn);
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
     * Explode string in to array items.
     * Use these array items to retrieve nested array values from the response.
     */
    public function explodeDotNotationValue(string $dotNotationString, array $response): string
    {
        $exploded = explode('.', $dotNotationString);
        $holder = [];

        foreach ($exploded as $key => $item) {
            if (0 === $key) {
                // Place the wanted part of the response in $holder.
                $holder = $response[$item] ?? '';

                continue;
            }

            // If $holder is empty there is no need to proceed.
            if (empty($holder)) {
                break;
            }

            // If holder is a multidimensional array, flatten.
            if (! empty($holder[0]) && is_array($holder[0])) {
                $holder = $this->flattenMultidimensionalArray($holder);
            }

            // Place the nested part of the response in $holder.
            $holder = $holder[$item] ?? '';
        }

        return is_string($holder) || is_numeric($holder) ? $holder : '';
    }

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
            $field->defaultValue = (new \DateTime($foundValue))->format('d-m-Y');
        } else {
            $field->defaultValue = ucfirst($foundValue);
        }

        $field->cssClass = 'owc_prefilled';
    }

    public function isPossibleDate(string $value): bool
    {
        return (date('Y-m-d', strtotime($value)) == $value);
    }

    protected function handleFieldDate(GF_Field $field, string $foundValue): void
    {
        try {
            $date = new DateTime($foundValue);
        } catch(Exception $e) {
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

        if (! empty($this->settings->getAPIKey())) {
            $headers[] = 'x-opentunnel-api-key: ' . $this->settings->getAPIKey();
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
                throw new \Exception(curl_error($curl));
            }

            $decoded = json_decode($output, true);

            if (! $decoded || json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Something went wrong with decoding of the JSON output.');
            }

            return $decoded;
        } catch (\Exception $e) {
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
