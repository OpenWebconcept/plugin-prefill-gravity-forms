<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Abstracts;

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Services\CacheService;

abstract class PostController extends BaseController
{
    protected const BRP_API_VERSION = '2';

    /**
     * Mapping key of the virtual "number of residents" field.
     */
    public const RESIDENTS_COUNT_KEY = 'aantalBewoners';

    /**
     * Counts the number of persons registered at the given address (BAG nummeraanduiding)
     * using the BRP search query 'ZoekMetNummeraanduidingIdentificatie'.
     *
     * Only the count is kept: the persons in the response are discarded and never cached.
     *
     * @return int|null Null when the count could not be determined.
     */
    public function countResidents(string $nummeraanduidingIdentificatie, string $goalBinding = '', string $processing = ''): ?int
    {
        if (1 !== preg_match('/^\d{16}$/', $nummeraanduidingIdentificatie)) {
            return null;
        }

        if ($this->settings->requestsDisabled()) {
            return null;
        }

        // Without a specific goal binding (e.g. a block where none is filled in) the general one from the settings is used;
        // PinkRoccade refuses this search without x-doelbinding (HTTP 400).
        $goalBinding = '' !== $goalBinding ? $goalBinding : $this->settings->getGoalBinding();
        $processing = '' !== $processing ? $processing : $this->settings->getProcessing();
        $transientKey = CacheService::formatTransientKey('residents_count_' . $nummeraanduidingIdentificatie);

        try {
            $cached = CacheService::getArrayFromTransient($transientKey);

            if (isset($cached['count']) && is_int($cached['count'])) {
                return $cached['count'];
            }
        } catch (Exception $e) {
            $this->logException($e);
        }

        try {
            $response = $this->executeCurlRequest([
                CURLOPT_URL => $this->settings->getBaseURL(),
                CURLOPT_POSTFIELDS => json_encode($this->prepareResidentsSearchData($nummeraanduidingIdentificatie)),
                CURLOPT_HTTPHEADER => $this->getCurlHeaders($goalBinding, $processing),
            ]);
        } catch (Exception $e) {
            $this->logException($e);

            return null;
        }

        $persons = $response['body']['personen'] ?? null;

        if (200 !== $response['status'] || ! is_array($persons)) {
            $this->logException(new Exception(
                sprintf('Residents count request failed with HTTP status %d.', $response['status']),
                0 !== $response['status'] ? $response['status'] : 500
            ));

            return null;
        }

        $count = count($persons);

        try {
            CacheService::setTransient($transientKey, ['count' => $count]);
        } catch (Exception $e) {
            $this->logException($e);
        }

        return $count;
    }

    /**
     * Adds the number of residents to the API response, but only when one of the form fields is mapped to it.
     * The count is added after the (BSN-cached) person response has been retrieved, so it is never stored in that cache.
     *
     * @param array<string, mixed> $form
     * @param array<mixed> $apiResponse
     *
     * @return array<mixed>
     */
    protected function addResidentsCountToResponse(array $form, array $apiResponse, string $goalBinding = '', string $processing = ''): array
    {
        if (! $this->formHasResidentsCountField($form)) {
            return $apiResponse;
        }

        $verblijfplaats = $apiResponse['verblijfplaats'] ?? null;
        $nummeraanduidingIdentificatie = is_array($verblijfplaats) ? ($verblijfplaats['nummeraanduidingIdentificatie'] ?? null) : null;

        if (! is_string($nummeraanduidingIdentificatie)) {
            return $apiResponse;
        }

        $count = $this->countResidents($nummeraanduidingIdentificatie, $goalBinding, $processing);

        if (null !== $count) {
            $apiResponse[self::RESIDENTS_COUNT_KEY] = (string) $count;
        }

        return $apiResponse;
    }

    /**
     * @param array<string, mixed> $form
     */
    private function formHasResidentsCountField(array $form): bool
    {
        $fields = $form['fields'] ?? [];

        if (! is_iterable($fields)) {
            return false;
        }

        foreach ($fields as $field) {
            if (is_object($field) && self::RESIDENTS_COUNT_KEY === ($field->linkedFieldValue ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Request body for counting the residents of an address; only the BSN field is requested to minimise the data returned.
     *
     * @return array{type: string, nummeraanduidingIdentificatie: string, fields: list<string>}
     */
    protected function prepareResidentsSearchData(string $nummeraanduidingIdentificatie): array
    {
        return [
            'type' => 'ZoekMetNummeraanduidingIdentificatie',
            'nummeraanduidingIdentificatie' => $nummeraanduidingIdentificatie,
            'fields' => ['burgerservicenummer'],
        ];
    }

    /**
     * Returns the goal binding of the form, falls back to the general goal binding of the plugin settings.
     */
    protected function getGoalBinding(array $form): string
    {
        return rgar($form, 'owc-iconnect-doelbinding', '') ?: $this->settings->getGoalBinding();
    }

    /**
     * Filters deceased persons from the given embedded relation collection.
     *
     * For each embedded item containing a BSN, additional person data is requested
     * and the relation is removed when the person is marked as deceased
     * ('opschortingBijhouding.reden.omschrijving' === 'overlijden').
     */
    protected function filterDeceasedFromEmbeddedRelations(array $apiResponse, string $embedType = '', string $goalBinding = '', string $processing = ''): array
    {
        if (! isset($apiResponse[$embedType])) {
            return $apiResponse;
        }

        foreach ($apiResponse[$embedType] as $key => $embeddedItem) {
            if (! isset($embeddedItem['burgerservicenummer']) || ! is_numeric($embeddedItem['burgerservicenummer'])) {
                continue;
            }

            $response = $this->requestEmbedded((string) $embeddedItem['burgerservicenummer'], $goalBinding, $processing);
            $personData = $response['personen'] ?? [];
            $firstPerson = reset($personData); // Response is in a multidimensional array which differs from other suppliers.

            if (! is_array($firstPerson) || 0 === count($firstPerson)) {
                continue;
            }

            if ('overlijden' === ($firstPerson['opschortingBijhouding']['reden']['omschrijving'] ?? '')) {
                unset($apiResponse[$embedType][$key]);
            }
        }

        $apiResponse[$embedType] = array_values($apiResponse[$embedType]);

        return $apiResponse;
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

    protected function getDefaultCurlArgs(): array
    {
        $args = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ];

        if ($this->settings->useSSLCertificates()) {
            $publicCertificate = $this->settings->getPublicCertificate();
            $privateCertificate = $this->settings->getPrivateCertificate();

            if ('' !== $publicCertificate && '' !== $privateCertificate) {
                $args[CURLOPT_SSLCERT] = $publicCertificate;
                $args[CURLOPT_SSLKEY] = $privateCertificate;
            }
        }

        return $args;
    }

    /**
     * Prepares the data payload for querying a citizen's information using their BSN (Burgerservicenummer).
     *
     * This method constructs a data array containing the necessary fields for a query.
     * Additional fields can be included by passing a comma-separated string to the $expand parameter.
     */
    abstract protected function prepareData(string $bsn, string $expand = ''): array;

    /**
     * Sends a request to retrieve embedded data for a given BSN.
     * Embedded data include related entities such as family members, addresses, etc.
     */
    abstract protected function requestEmbedded(string $bsn, string $goalBinding = '', string $processing = ''): array;
}
