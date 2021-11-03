<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function Yard\DigiD\Foundation\Helpers\decrypt;
use function Yard\DigiD\Foundation\Helpers\resolve;

class GravityForms
{
    public function __construct()
    {
        $this->settings = GravityFormsSettings::make();
    }

    public function preRender(array $form): array
    {
        $bsn = $this->getBSN($form);

        if (empty($bsn)) {
            return $form;
        }

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (empty($doelBinding) || !is_string($doelBinding)) {
            return $form;
        }

        $response = $this->request($bsn, $doelBinding, $expand);

        if (empty($response) || isset($response['status'])) {
            return $form;
        }

        return $this->preFillFields($form, $response);
    }

    protected function getBSN(array $form): string
    {
        $bsn = '';

        foreach ($form['fields'] as $field) {
            // DigiD field is required in form.
            if ($field->type !== 'digid') {
                continue;
            }

            $resolvedBSN = resolve('session')->getSegment('digid')->get('bsn');

            if (empty($resolvedBSN)) {
                continue;
            }

            $bsn = decrypt($resolvedBSN);

            break;
        }

        return $bsn;
    }

    protected function preFillFields(array $form, array $response): array
    {
        foreach ($form['fields'] as $field) {
            $linkedValue = $field->linkedFieldValue ?? '';
            $foundValue  = $this->findLinkedValue($linkedValue, $response);

            if (empty($foundValue)) {
                continue;
            }

            if ($field->type === 'text') {
                $field->defaultValue = ucfirst($foundValue);
            }

            if ($field->type === 'date') {
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
        $holder   = [];

        foreach ($exploded as $key => $item) {
            if ($key === 0) {
                // Place the wanted part of the response in $holder.
                $holder = $response[$item] ?? '';
                continue;
            }

            // If $holder is empty there is no need to proceed.
            if (empty($holder)) {
                break;
            }

            // Place the nested part of the response in $holder.
            $holder = $holder[$item] ?? '';
        }

        return is_string($holder) || is_numeric($holder) ? $holder : '';
    }

    public function getRequestURL(string $identifier = '', string $expand = ''): string
    {
        $baseURL = $this->settings->getBaseURL();

        if (empty($baseURL) || empty($identifier)) {
            return '';
        }

        $url = sprintf('%s/%s', $baseURL, $identifier);

        if (!empty($expand)) {
            $url = sprintf('%s?%s', $url, $this->createExpandArguments($expand));
        }

        return $url;
    }

    protected function createExpandArguments(string $expand): string
    {
        $exploded = explode(',', $expand);
        $filtered = array_filter($exploded);
        $new = array_map("trim", $filtered);
        $imploded = implode(',', $new);

        return urldecode(http_build_query(['expand' => $imploded], '', ','));
    }

    protected function request(string $bsn = '100251663', string $doelBinding = '', string $expand = ''): array
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
                CURLOPT_HTTPHEADER => [
                    'x-doelbinding: ' . $doelBinding,
                    'x-origin-oin: ' . $this->settings->getNumberOIN()
                ],
                CURLOPT_SSLCERT => $this->settings->getPublicCertificate(),
                CURLOPT_SSLKEY => $this->settings->getPrivateCertificate()
            ]);

            curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->settings->getPassphrase());
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $output = curl_exec($curl);

            if (curl_error($curl)) {
                throw new \Exception(curl_error($curl));
            }

            $decoded = json_decode($output, true);

            if (!$decoded && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Something went wrong with decoding of the JSON output.');
            }

            return $decoded;
        } catch (\Exception $e) {
            return [];
        }
    }
}
