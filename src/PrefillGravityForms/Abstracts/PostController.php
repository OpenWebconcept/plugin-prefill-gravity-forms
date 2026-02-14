<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Abstracts;

use OWC\PrefillGravityForms\Controllers\BaseController;

abstract class PostController extends BaseController
{
    /**
    * Supplements the '_embedded' array with additional data retrieved via the '_links' array,
    * using the configured expand arguments to fetch and merge related resources.
     */
    protected function supplementEmbeddedByLinks(array $apiResponse, string $embedType = ''): array
    {
        if (! isset($apiResponse[$embedType])) {
            return $apiResponse;
        }

        foreach ($apiResponse[$embedType] as $key => $embeddedItem) {
            if (! isset($embeddedItem['burgerservicenummer']) || ! is_numeric($embeddedItem['burgerservicenummer'])) {
                continue;
            }

            $response = $this->requestEmbedded((string) $embeddedItem['burgerservicenummer']);
            $personData = $response['personen'] ?? [];
            $firstPerson = reset($personData); // Response is in a multidimensional array which differs from other suppliers.

            if (! is_array($firstPerson) || 0 === count($firstPerson)) {
                continue;
            }

            // @todo: do this conditionally based on setting.
            if ('overlijden' === ($firstPerson['opschortingBijhouding']['reden']['omschrijving'] ?? '')) {
                unset($apiResponse[$embedType][$key]);
            }
        }

        $apiResponse[$embedType] = array_values($apiResponse[$embedType]);

        return $apiResponse;
    }

    abstract protected function requestEmbedded(string $bsn): array;
}
