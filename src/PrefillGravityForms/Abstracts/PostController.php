<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Abstracts;

use OWC\PrefillGravityForms\Controllers\BaseController;

abstract class PostController extends BaseController
{
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
            $args[CURLOPT_SSLCERT] = $this->settings->getPublicCertificate();
            $args[CURLOPT_SSLKEY] = $this->settings->getPrivateCertificate();
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
