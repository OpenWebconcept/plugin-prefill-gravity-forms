<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Abstracts;

use OWC\PrefillGravityForms\Controllers\BaseController;

abstract class GetController extends BaseController
{
    /**
    * Supplements the '_embedded' array with additional data retrieved via the '_links' array,
    * using the configured expand arguments to fetch and merge related resources.
     */
    protected function supplementEmbeddedByLinks(array $apiResponse, string $embedType = '', string $doelBinding = ''): array
    {
        if (! isset($apiResponse['_embedded'][$embedType])) {
            return $apiResponse;
        }

        foreach ($apiResponse['_embedded'][$embedType] as $key => $embeddedItem) {
            if (! isset($embeddedItem['_links']['ingeschrevenPersoon']['href'])) {
                continue;
            }

            $response = $this->requestEmbedded(str_replace('https://api.acc-vrijbrp-hoeksche-waard.commonground.nu/haal-centraal-brp-bevragen/api/v1.3/ingeschrevenpersonen', 'https://api.acc-vrijbrp-hoeksche-waard.commonground.nu/api/haalcentraal-brp-bevragen/api/v1.3/ingeschrevenpersonen', $embeddedItem['_links']['ingeschrevenPersoon']['href']), $doelBinding);

            // @todo: do this conditionally based on setting.
            if (true === ($response['overlijden']['indicatieOverleden'] ?? false)) {
                unset($apiResponse['_embedded'][$embedType][$key]);
            }
        }

        $apiResponse['_embedded'][$embedType] = array_values($apiResponse['_embedded'][$embedType]);

        return $apiResponse;
    }

    abstract protected function requestEmbedded(string $url, string $doelBinding): array;
}
