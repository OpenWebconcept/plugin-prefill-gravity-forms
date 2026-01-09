<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Controllers;

use Exception;
use OWC\PrefillGravityForms\Services\CacheService;

class VrijBRPController extends BaseController
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

        $apiResponse = $this->fetchApiResponse($bsn, $doelBinding, $expand);

        if (empty($apiResponse)) {
            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $apiResponse);
    }

    protected function makeRequest(string $doelBinding = ''): array
    {
        $bsn = $this->getBSN();

        if ('' === $bsn) {
            return [];
        }

        return $this->fetchApiResponse($bsn, $doelBinding);
    }

    protected function fetchApiResponse(string $bsn, string $doelBinding = '', string $expand = ''): array
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

        return $apiResponse;
    }

    protected function request(string $bsn = '', string $doelBinding = '', string $expand = ''): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->getRequestURL($bsn, $expand),
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($doelBinding)
        ];

        return $this->handleCurl($curlArgs, CacheService::formatTransientKey($bsn));
    }
}
