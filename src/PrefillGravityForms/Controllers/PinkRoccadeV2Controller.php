<?php

namespace OWC\PrefillGravityForms\Controllers;

class PinkRoccadeV2Controller extends BaseController
{
    public function handle(array $form): array
    {
        $bsn = $this->getBSN();

        if (empty($bsn)) {
            return $form;
        }

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $processing = rgar($form, 'owc-iconnect-processing', '');
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (! is_string($doelBinding)) {
            $doelBinding = (string) $doelBinding;
        }

        $preparedData = $this->prepareData($bsn, $expand);
        $apiResponse = $this->fetchApiResponse($preparedData, $doelBinding, $processing);

        if (empty($apiResponse)) {
            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $apiResponse);
    }

    protected function makeRequest(string $doelBinding = ''): array
    {
        $bsn = $this->getBSN();

        if (empty($bsn)) {
            return [];
        }

        $preparedData = $this->prepareData($bsn);

        return $this->fetchApiResponse($preparedData, $doelBinding);
    }

    /**
     * Prepares the data payload for querying a citizen's information using their BSN (Burgerservicenummer).
     *
     * This method constructs a data array containing the necessary fields for a query.
     */
    protected function prepareData(string $bsn): array
    {
        return [
            'type' => 'RaadpleegMetBurgerservicenummer',
            'fields' => ['burgerservicenummer'],
            'burgerservicenummer' => [$bsn],
        ];
    }

    /**
     * Splits the expand parameter into an array of fields.
     *
     * @param string $expand Comma-separated list of additional fields.
     *
     * @return array An array of expanded fields.
     */
    protected function getExpandFields(string $expand): array
    {
        return array_filter(explode(',', $expand));
    }

    protected function fetchApiResponse(array $preparedData, string $doelBinding, $processing = ''): array
    {
        $apiResponse = $this->request($preparedData, $doelBinding, $processing);
        $personData = $apiResponse['personen'] ?? [];
        $firstPerson = reset($personData); // Response is in a multidimensional array which differs from other suppliers.

        if (isset($apiResponse['status']) || ! is_array($firstPerson) || ! count($firstPerson)) {
            $message = 'Retrieving prefill data failed';

            if (isset($apiResponse['message'])) {
                $message = sprintf('%s: %s', $message, $apiResponse['message']);
            }

            $this->logError($message, $apiResponse['status'] ?? 500);

            return [];
        }

        return $firstPerson;
    }

    protected function request(array $data, string $doelBinding, string $processing = ''): array
    {
        $processing = 0 < strlen($processing) ? $processing : $this->settings->getProcessing();

        $curlArgs = [
            CURLOPT_URL => $this->settings->getBaseURL(),
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'x-doelbinding: ' . $doelBinding,
                'x-origin-oin: ' . $this->settings->getNumberOIN(),
                'x-verwerking: ' . $processing,
                'x-gebruiker: ' . $this->settings->getUser(),
            ],
            CURLOPT_SSLCERT => $this->settings->getPublicCertificate(),
            CURLOPT_SSLKEY => $this->settings->getPrivateCertificate(),
        ];

        return $this->handleCurl($curlArgs);
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
            CURLOPT_CUSTOMREQUEST => 'POST',
        ];
    }
}
