<?php

namespace OWC\PrefillGravityForms\Controllers;

class EnableUController extends BaseController
{
    public function handle(array $form)
    {
        $bsn = $this->getBSN();

        if (empty($bsn)) {
            return $form;
        }

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (! is_string($doelBinding)) {
            $doelBinding = (string) $doelBinding;
        }

        $apiResponse = $this->request($bsn, $doelBinding, $expand);

        if (isset($apiResponse['status'])) {
            $this->logError('Retrieving prefill data failed.', $apiResponse['status'] ?? 500);

            return $form;
        }

        echo $this->disableFormFields();

        return $this->preFillFields($form, $apiResponse);
    }

    protected function request(string $bsn = '', string $doelBinding = '', string $expand = ''): array
    {
        $curlArgs = [
            CURLOPT_URL => $this->getRequestURL($bsn, $expand),
            CURLOPT_HTTPHEADER => $this->getCurlHeaders($doelBinding),
        ];

        return $this->handleCurl($curlArgs);
    }
}
