<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use Exception;
use OWC\PrefillGravityForms\Abstracts\GetController;
use OWC\PrefillGravityForms\Services\CacheService;

class VrijBRPController extends GetController
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
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (! is_string($goalBinding)) {
            $goalBinding = (string) $goalBinding;
        }

        $excludeDeceased = (bool) rgar($form, 'owc-iconnect-exclude-deceased', false);
        $apiResponse = $this->fetchApiResponse($bsn, $goalBinding, $expand, $excludeDeceased);

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

        return $this->fetchApiResponse($bsn, $goalBinding, '');
    }

    protected function fetchApiResponse(string $bsn, string $goalBinding = '', string $expand = '', bool $excludeDeceased = false): array
    {
        $apiResponse = $this->request($bsn, $goalBinding, $expand);

        if (isset($apiResponse['status'])) {
            $message = 'Retrieving prefill data failed';

            if (isset($apiResponse['message'])) {
                $message = sprintf('%s: %s', $message, $apiResponse['message']);
            }

            $this->logException(new Exception($message, (int) ($apiResponse['status'] ?? 500)));

            return [];
        }

        if ($excludeDeceased) {
            foreach (array_filter(explode(',', $expand)) as $expandItem) {
                $apiResponse = $this->filterDeceasedFromEmbeddedRelations($apiResponse, trim($expandItem), $goalBinding);
            }
        }

        return $apiResponse;
    }

    protected function request(string $bsn = '', string $goalBinding = '', string $expand = ''): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->getRequestURL($bsn, $expand),
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($goalBinding)
        ];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
    }

    protected function requestEmbedded(string $url, string $goalBinding): array
    {
        $curlArgs = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($goalBinding)
        ];

        $urlParts = explode('/', $url);
        $bsn = is_array($urlParts) && 0 < count($urlParts) ? end($urlParts) : '';

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
    }
}
