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

        $bsn = $this->supplementBSN($bsn);

        if (strlen($bsn) !== 9) {
            $this->teams->addRecord('error', 'BSN', [
                'message' => 'BSN does not meet the required length of 9.'
            ]);

            return $form;
        }

        $doelBinding = rgar($form, 'owc-iconnect-doelbinding', '');
        $expand = rgar($form, 'owc-iconnect-expand', '');

        if (! is_string($doelBinding)) {
            $doelBinding = (string) $doelBinding;
        }

        $response = $this->request($bsn, $doelBinding, $expand);

        if (isset($response['status'])) {
            $this->teams->addRecord('error', 'Prefill data', [
                'message' => 'Retrieving prefill data failed.',
                'status' => $response['status']
            ]);
            return $form;
        }
        
        echo $this->disableFormFields();

        return $this->preFillFields($form, $response);
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
