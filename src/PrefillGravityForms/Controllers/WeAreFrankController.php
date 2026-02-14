<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use Exception;
use OWC\PrefillGravityForms\Abstracts\PostController;
use OWC\PrefillGravityForms\Services\CacheService;

class WeAreFrankController extends PostController
{
    public function handle(array $form): array
    {
        if ($this->isBlockEditor()) {
            return $form;
        }

        $bsn = $this->getBSN();

        if ('' === $bsn) {
            return $form;
        }

        $expand = rgar($form, 'owc-iconnect-expand', '');
        $firstPerson = $this->fetchPersonData($bsn, $expand);

        if (empty($firstPerson)) {
            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $firstPerson);
    }

    protected function makeRequest(): array
    {
        $bsn = $this->getBSN();

        if ('' === $bsn) {
            return [];
        }

        $preparedData = $this->prepareData($bsn);

        return $this->fetchPersonData($bsn);
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

    /**
     * @inheritDoc
     */
    protected function extractBSN(array $response): string
    {
        if (! isset($response['personen'][0]['burgerservicenummer'])) {
            throw new Exception('Burgerservicenummer not found in response.', 404);
        }

        $bsn = $response['personen'][0]['burgerservicenummer'];

        if (! is_numeric($bsn)) {
            throw new Exception('Invalid burgerservicenummer format, value is not numeric.', 500);
        }

        return (string) $bsn;
    }

    protected function fetchPersonData(string $bsn, string $expand = ''): array
    {
        $apiResponse = $this->request($bsn, $expand);
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

        foreach (array_filter(explode(',', $expand)) as $expandItem) {
            $firstPerson = $this->supplementEmbeddedByLinks($firstPerson, trim($expandItem));
        }

        return $firstPerson;
    }

    protected function request(string $bsn = '', string $expand = ''): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->settings->getBaseURL(),
            CURLOPT_POSTFIELDS => json_encode($this->prepareData($bsn, $expand)),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                sprintf('%s: %s', $this->settings->getAPITokenUsername(), $this->settings->getAPITokenPassword()),
            ],
        ];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
    }

    /**
     * This one breaks the contract, fix later.
     */
    protected function requestEmbedded(string $bsn = ''): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->settings->getBaseURL(),
            CURLOPT_POSTFIELDS => json_encode($this->prepareData($bsn)),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                sprintf('%s: %s', $this->settings->getAPITokenUsername(), $this->settings->getAPITokenPassword()),
            ],
        ];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
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
