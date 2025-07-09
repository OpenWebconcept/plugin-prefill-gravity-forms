<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use DateTime;
use Exception;
use GF_Field;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Traits\Logger;
use OWC\PrefillGravityForms\Traits\SessionTrait;
use TypeError;

abstract class BaseController
{
    use Logger;
    use SessionTrait;

    protected const CUSTOM_FIELDS_TYPES = [
        'owc_pg_age_check',
        'owc_pg_municipality_check',
    ];

    protected GravityFormsSettings $settings;
    protected array $prefilledChildrenMappingOptions = [];

    public function __construct()
    {
        $this->settings = GravityFormsSettings::make();
    }

    abstract public function handle(array $form): array;

    public function get(): array
    {
        return static::makeRequest();
    }

    abstract protected function makeRequest(): array;

    protected function preFillFields(array $form, array $response): array
    {
        foreach ($form['fields'] as $field) {
            $linkedMappingOption = $field->linkedFieldValue ?? '';

            if (! is_string($linkedMappingOption) || 1 > strlen($linkedMappingOption)) {
                continue;
            }

            $linkedMappingOption = $this->replaceChildIndexPlaceholder($linkedMappingOption);
            $foundValue = $this->findValueOfMappedOption($linkedMappingOption, $response);

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

    /**
     * Replaces the child index placeholder (*) in the linked field reference.
     *
     * In the prefill options, child-related fields use an asterisk (*) as a placeholder
     * for the child index (e.g., "kinderen.*"). This method replaces the
     * asterisk with an incremented number to ensure unique identifiers for each child.
     *
     * Example:
     * Input:  "kinderen.*.burgerservicenummer"
     * Output: "kinderen.0.burgerservicenummer" (for the first child)
     *         "kinderen.1.burgerservicenummer" (for the second child)
     */
    protected function replaceChildIndexPlaceholder(string $linkedMappingOption): string
    {
        if (strpos($linkedMappingOption, 'kinderen.*') === false) {
            return $linkedMappingOption;
        }

        // Store the children mapping option used to keep track of the number of times a mapping option is used.
        $this->prefilledChildrenMappingOptions[] = $linkedMappingOption;

        $timesMappingOptionIsUsed = count(array_filter($this->prefilledChildrenMappingOptions, function ($field) use ($linkedMappingOption) {
            return $field === $linkedMappingOption;
        }));

        $linkedMappingOption = str_replace(
            'kinderen.*',
            sprintf('kinderen.%d', $timesMappingOptionIsUsed ? $timesMappingOptionIsUsed - 1 : 0),
            $linkedMappingOption
        );

        return $linkedMappingOption;
    }

    protected function findValueOfMappedOption(string $linkedMappingOption = '', array $response = []): string
    {
        if (1 > strlen($linkedMappingOption) || ! count($response)) {
            return $linkedMappingOption;
        }

        return $this->explodeDotNotationValue($linkedMappingOption, $response);
    }

    /**
     * Explode dot notation string into array items.
     * Use these array items to retrieve nested array values from the response.
     */
    protected function explodeDotNotationValue(string $dotNotationString, array $response): string
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
        return isset($array[0]) && ! isset($array[1]) && is_array($array[0]);
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

    protected function getRequestURL(string $identifier = '', string $expand = ''): string
    {
        $baseURL = $this->settings->getBaseURL();

        if (1 > strlen($baseURL) || 1 > strlen($identifier)) {
            return '';
        }

        $url = sprintf('%s/%s', $baseURL, $identifier);

        if (0 < strlen($expand)) {
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
                $headers[] = sprintf('%s: %s', $this->settings->getAPIKeyHeaderName(), $this->settings->getAPIKey());
            } elseif (! empty($this->settings->getAPITokenUsername()) && ! empty($this->settings->getAPITokenPassword())) {
                $bearerToken = base64_encode(sprintf('%s:%s', $this->settings->getAPITokenUsername(), $this->settings->getAPITokenPassword()));
                $headers[] = sprintf('Authorization: Basic %s', $bearerToken);
            }
        }

        return array_filter($headers);
    }

    protected function handleCurl(array $args): array
    {
        $curl = curl_init();

        try {
            curl_setopt_array($curl, $this->getDefaultCurlArgs() + $args);

            if (! empty($this->settings->getPassphrase())) {
                curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->settings->getPassphrase());
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeoutOptionCURL());

            $output = curl_exec($curl);

            if (curl_error($curl)) {
                throw new Exception(curl_error($curl));
            }

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (200 !== $httpStatus) {
                throw new Exception('Request failed', is_int($httpStatus) ? $httpStatus : 500);
            }

            $decoded = json_decode($output, true);

            if (! $decoded || json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Something went wrong with decoding of the JSON output.', 500);
            }

            return $decoded;
        } catch (Exception $e) {
            return [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
            ];
        } finally {
            curl_close($curl);
        }
    }

    protected function timeoutOptionCURL(): int
    {
        $timeout = apply_filters('owc_prefill_gravity_forms_curl_timeout', 10);

        return is_int($timeout) && 0 < $timeout ? $timeout : 10;
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
