<?php

namespace OWC\PrefillGravityForms\GravityForms;

use Exception;
use function OWC\PrefillGravityForms\Foundation\Helpers\decrypt;
use function Yard\DigiD\Foundation\Helpers\resolve;
use OWC\PrefillGravityForms\Foundation\TeamsLogger;

class GravityForms
{
    protected TeamsLogger $teams;
    protected GravityFormsSettings $settings;
    
    public function __construct()
    {
        $this->teams = $this->resolveTeams();
        $this->settings = GravityFormsSettings::make();
    }

    public function resolveTeams(): TeamsLogger
    {
        try {
            if (! function_exists('Yard\DigiD\Foundation\Helpers\resolve')) {
                throw new \Exception;
            }

            return TeamsLogger::make(resolve('teams'));
        } catch (\Exception $e) {
            return TeamsLogger::make(new \Psr\Log\NullLogger());
        }
    }

    public function preRender(array $form): array
    {
        $bsn = $this->getBSN();

        if (empty($bsn)) {
            return $form;
        }

        $bsn = $this->supplementBSN($bsn);

        if (strlen($bsn) !== 9) {
            $this->teams->addRecord('error', 'BSN', [
                'message' => 'BSN does not meet the required length of 9.',
            ]);

            return $form;
        }

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (! is_string($doelBinding)) {
            $doelBinding = (string) $doelBinding;
        }

        $response = $this->request($bsn, $doelBinding, $expand);

        if (isset($response['status'])) {
            $this->teams->addRecord('error', 'Prefill data', [
                'message' => 'Retrieving prefill data failed.',
                'status' => $response['status'],
            ]);

            return $form;
        }

        return $this->preFillFields($form, $response);
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
    public function supplementBSN(string $bsn): string
    {
        $bsnLength = strlen($bsn);
        $requiredLength = 9;
        $difference = $requiredLength - $bsnLength;

        if (1 > $difference || $difference > $requiredLength) {
            return $bsn;
        }

        return sprintf("%'.0" . $requiredLength . 'd', $bsn);
    }

    protected function preFillFields(array $form, array $response): array
    {
        foreach ($form['fields'] as $field) {
            $linkedValue = $field->linkedFieldValue ?? '';
            $foundValue = $this->findLinkedValue($linkedValue, $response);

            if (empty($foundValue)) {
                continue;
            }

            if ('text' === $field->type) {
                $field->defaultValue = ucfirst($foundValue);
            }

            if ('date' === $field->type) {
                $field->defaultValue = (new \DateTime($foundValue))->format('d-m-Y');
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

            // If holder is a multidimensional array, unflatten.
            if (! empty($holder[0]) && is_array($holder[0])) {
                $holder = $this->unflattenHolderArray($holder);
            }

            // Place the nested part of the response in $holder.
            $holder = $holder[$item] ?? '';
        }

        return is_string($holder) || is_numeric($holder) ? $holder : '';
    }

    protected function unflattenHolderArray(array $holder): array
    {
        $backupHolder = [];

        foreach ($holder as $part) {
            $backupHolder = array_merge($backupHolder, $part);
        }

        return $backupHolder;
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

        return array_filter($headers);
    }

    protected function request(string $bsn = '', string $doelBinding = '', string $expand = ''): array
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->getRequestURL($bsn, $expand),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => $this->getCurlHeaders($doelBinding),
                CURLOPT_SSLCERT => $this->settings->getPublicCertificate(),
                CURLOPT_SSLKEY => $this->settings->getPrivateCertificate(),
            ]);

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

            if (! $decoded && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Something went wrong with decoding of the JSON output.');
            }

            return $decoded;
        } catch (\Exception $e) {
            return [
                'status' => $e->getMessage(),
            ];
        }
    }
}
