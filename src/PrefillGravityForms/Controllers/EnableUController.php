<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use OWC\PrefillGravityForms\Abstracts\GetController;
use Exception;
use OWC\PrefillGravityForms\Services\CacheService;

class EnableUController extends GetController
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

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (! is_string($doelBinding)) {
            $doelBinding = (string) $doelBinding;
        }

        $excludeDeceased = (bool) rgar($form, 'owc-iconnect-exclude-deceased', false);
        $apiResponse = $this->fetchApiResponse($bsn, $doelBinding, $expand, $excludeDeceased);

        if (empty($apiResponse)) {
            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $apiResponse);
    }

    protected function makeRequest(): array
    {
        $bsn = $this->getBSN();

        if ('' === $bsn) {
            return [];
        }

        return $this->fetchApiResponse($bsn);
    }

    protected function fetchApiResponse(string $bsn, string $doelBinding = '', string $expand = '', bool $excludeDeceased = false): array
    {
        $apiResponse = $this->request($bsn, $doelBinding, $expand);

        if (isset($apiResponse['status'])) {
            $message = 'Retrieving prefill data failed';

            if (isset($apiResponse['message'])) {
                $message = sprintf('%s: %s', $message, $apiResponse['message']);
            }

            $this->logException(new Exception($message, (int) ($response['status'] ?? 500)));

            return [];
        }

        if ($excludeDeceased) {
            foreach (array_filter(explode(',', $expand)) as $expandItem) {
                $apiResponse = $this->supplementEmbeddedByLinks($apiResponse, trim($expandItem), $doelBinding);
            }
        }

        return $apiResponse;
    }

    protected function request(string $bsn = '', string $doelBinding = '', string $expand = ''): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->getRequestURL($bsn, $expand),
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($doelBinding),
        ];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
    }

    protected function requestEmbedded(string $url, string $doelBinding): array
    {
        $curlArgs = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($doelBinding),
        ];

        $urlParts = explode('/', $url);
        $bsn = is_array($urlParts) && 0 < count($urlParts) ? end($urlParts) : '';

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
    }
}
