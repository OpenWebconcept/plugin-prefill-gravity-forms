<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function Yard\DigiD\Foundation\Helpers\decrypt;

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

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding');

        if (empty($doelBinding) || !is_string($doelBinding)) {
            return $form;
        }

        $response = $this->request($bsn, $doelBinding);

        if (empty($response) || isset($response['status'])) {
            return $form;
        }

        return $this->preFillFields($form, $response);
    }

    protected function getBSN(array $form): string
    {
        $bsn = '';

        foreach ($form['fields'] as $field) {
            if ($field->type !== 'digid' || empty($_POST['input_' . $field->id . '_1'])) {
                continue;
            }

            $bsn = decrypt(\sanitize_text_field($_POST['input_' . $field->id . '_1']));
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

            $field->defaultValue = $foundValue;
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

    public function getRequestURL(string $identifier = ''): string
    {
        $baseURL = $this->settings->getBaseURL();

        if (empty($baseURL) || empty($identifier)) {
            return '';
        }

        return \trailingslashit($baseURL) . $identifier;
    }

    protected function request(string $bsn = '159859037', string $doelBinding = '')
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->getRequestURL($bsn),
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
