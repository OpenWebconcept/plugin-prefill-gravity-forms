<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

class WeAreFrankController extends BaseController
{
    public function handle(array $form): array
    {
        if ($this->isBlockEditor()) {
            return $form;
        }

        $bsn = $this->getBSN();

        if (empty($bsn)) {
            return $form;
        }

        $expand = rgar($form, 'owc-iconnect-expand', '');
        $preparedData = $this->prepareData($bsn, $expand);

        $firstPerson = $this->fetchPersonData($preparedData);

        if (empty($firstPerson)) {
            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $firstPerson);
    }

    protected function makeRequest(): array
    {
        $bsn = $this->getBSN();

        if (empty($bsn)) {
            return [];
        }

        $preparedData = $this->prepareData($bsn);

        return $this->fetchPersonData($preparedData) ?? [];
    }

    /**
     * Prepares the data payload for querying a citizen's information using their BSN (Burgerservicenummer).
     *
     * This method constructs a data array containing the necessary fields for a query.
     * Additional fields can be included by passing a comma-separated string to the $expand parameter.
     *
     * @param string $bsn The citizen's BSN (Burgerservicenummer), a unique identification number in the Netherlands.
     * @param string $expand Comma-separated list of additional fields to include in the query. Possible values: 'ouders', 'kinderen', 'partners'.
     *
     * @return array The prepared data payload for the query, including the base fields and any additional expanded fields.
     */
    protected function prepareData(string $bsn, string $expand = ''): array
    {
        $fields = [
            'aNummer',
            'adressering',
            'burgerservicenummer',
            'datumEersteInschrijvingGBA',
            'datumInschrijvingInGemeente',
            'geboorte',
            'gemeenteVanInschrijving',
            'geslacht',
            'immigratie',
            'leeftijd',
            'naam',
            'nationaliteiten',
            'verblijfplaats',
            'verblijfstitel',
        ];

        if (! empty($expand)) {
            $expandFields = $this->getExpandFields($expand);
            $fields = array_merge($fields, $expandFields);
        }

        return [
            'type' => 'RaadpleegMetBurgerservicenummer',
            'fields' => $fields,
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

    protected function fetchPersonData(array $preparedData): array
    {
        $apiResponse = $this->request($preparedData);
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

    protected function request(array $data = []): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->settings->getBaseURL(),
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                sprintf('%s: %s', $this->settings->getAPITokenUsername(), $this->settings->getAPITokenPassword()),
            ],
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
