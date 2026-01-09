<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Abstracts;

use OWC\PrefillGravityForms\Controllers\BaseController;

abstract class GetController extends BaseController
{
    /**
     * Filters deceased persons from the given embedded relation collection.
     *
     * For each embedded item containing a BSN, additional person data is requested
     * and the relation is removed when the person is marked as deceased
     * ('opschortingBijhouding.reden.omschrijving' === 'overlijden').
     */
    protected function filterDeceasedFromEmbeddedRelations(array $apiResponse, string $embedType = '', string $goalBinding = ''): array
    {
        if (! isset($apiResponse['_embedded'][$embedType])) {
            return $apiResponse;
        }

        foreach ($apiResponse['_embedded'][$embedType] as $key => $embeddedItem) {
            if (! isset($embeddedItem['_links']['ingeschrevenPersoon']['href'])) {
                continue;
            }

            $response = $this->requestEmbedded($this->normalizeCommonGroundUrl($embeddedItem['_links']['ingeschrevenPersoon']['href']), $goalBinding);

            if (true === ($response['overlijden']['indicatieOverleden'] ?? false)) {
                unset($apiResponse['_embedded'][$embedType][$key]);
            }
        }

        $apiResponse['_embedded'][$embedType] = array_values($apiResponse['_embedded'][$embedType]);

        return $apiResponse;
    }

    /**
     * Normalizes incorrectly embedded HaalCentraal BRP endpoints originating from a supplier.
     *
     * Certain Common Ground VrijBRP environments return URLs without the required `/api`
     * prefix (e.g. `/haal-centraal-brp-bevragen/...` instead of
     * `/api/haalcentraal-brp-bevragen/...`). As a result, follow-up requests would fail.
     *
     * This method transparently corrects the malformed path for commonground.nu domains
     * until the supplier resolves the issue in their endpoint generation.
     *
     * IMPORTANT:
     * This is a temporary compatibility workaround and should be removed once the
     * supplier provides correctly structured URLs.
     */
    private function normalizeCommonGroundUrl(string $url): string
    {
        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['host'], $parts['path'])) {
            return $url;
        }

        $host = $parts['host'];
        $path = $parts['path'];

        // Only for commonground.nu domains (incl. subdomains).
        if (substr($host, -strlen('commonground.nu')) !== 'commonground.nu') {
            return $url;
        }

        // Only correct if the path starts incorrectly.
        if (strpos($path, '/haal-centraal-brp-bevragen') !== 0) {
            return $url;
        }

        // Replace only the leading segment (safer than blind str_replace).
        $path = preg_replace(
            '#^/haal-centraal-brp-bevragen#',
            '/api/haalcentraal-brp-bevragen',
            $path
        );

        // Rebuild URL safely
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'https';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . '://' . $host . $port . $path . $query . $fragment;
    }

    abstract protected function requestEmbedded(string $url, string $goalBinding): array;
}
