<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use Exception;
use OWC\PrefillGravityForms\Abstracts\PostController;
use OWC\PrefillGravityForms\Services\CacheService;

class PinkRoccadeV2Controller extends PostController
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

        $goalBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $processing = rgar($form, 'owc-iconnect-processing', '') ?: $this->settings->getProcessing();
        $expand = rgar($form, 'owc-iconnect-expand', '');
        $excludeDeceased = (bool) rgar($form, 'owc-iconnect-exclude-deceased', false);
        $apiResponse = $this->fetchApiResponse($bsn, $expand, $goalBinding, $processing, $excludeDeceased);

        if (empty($apiResponse)) {
            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $apiResponse);
    }

    protected function makeRequest(string $goalBinding = '', string $processing = ''): array
    {
        $bsn = $this->getBSN();

        if ('' === $bsn) {
            return [];
        }

        return $this->fetchApiResponse($bsn, '', $goalBinding, $processing);
    }

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
            'overlijden',
            'verblijfplaats',
            'verblijfstitel',
            'verblijfplaatsBinnenland',
            'adresseringBinnenland',
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

    protected function fetchApiResponse(string $bsn, string $expand = '', string $goalBinding = '', string $processing = '', bool $excludeDeceased = false): array
    {
        $apiResponse = $this->request($bsn, $expand, $goalBinding, $processing);
        $personData = $apiResponse['personen'] ?? [];
        $firstPerson = reset($personData); // Response is in a multidimensional array which differs from other suppliers.

        if (isset($apiResponse['status']) || ! is_array($firstPerson) || ! count($firstPerson)) {
            $message = 'Retrieving prefill data failed';

            if (isset($apiResponse['message'])) {
                $message = sprintf('%s: %s', $message, $apiResponse['message']);
            }

            $this->logException(new Exception($message, (int) ($apiResponse['status'] ?? 500)));

            return [];
        }

        if ($excludeDeceased) {
            foreach (array_filter(explode(',', $expand)) as $embedType) {
                $firstPerson = $this->filterDeceasedFromEmbeddedRelations($firstPerson, trim($embedType), $goalBinding, $processing);
            }
        }

        return $firstPerson;
    }

    protected function request(string $bsn, string $expand = '', string $goalBinding = '', string $processing = ''): array
    {
        $processing = 0 < strlen($processing) ? $processing : $this->settings->getProcessing();

        $curlArgs = [
            CURLOPT_URL => $this->settings->getBaseURL(),
            CURLOPT_POSTFIELDS => json_encode($this->prepareData($bsn, $expand)),
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($goalBinding, $processing),
        ];

        $locationBsnInResponse = ['personen.0.burgerservicenummer'];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn), $locationBsnInResponse);
    }

    protected function requestEmbedded(string $bsn, string $goalBinding = '', string $processing = ''): array
    {
        $processing = 0 < strlen($processing) ? $processing : $this->settings->getProcessing();

        $curlArgs = [
            CURLOPT_URL => $this->settings->getBaseURL(),
            CURLOPT_POSTFIELDS => json_encode($this->prepareData($bsn)),
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($goalBinding, $processing),
        ];

        $locationBsnInResponse = ['personen.0.burgerservicenummer'];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn), $locationBsnInResponse);
    }
}
