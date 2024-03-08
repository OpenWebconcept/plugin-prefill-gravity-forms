<?php

namespace OWC\PrefillGravityForms\Traits;

use function Yard\DigiD\Foundation\Helpers\resolve;
use function OWC\PrefillGravityForms\Foundation\Helpers\decrypt;

trait SessionTrait
{
    protected function getBSN(): string
    {
        try {
			$session = resolve('session');
            $bsn = $session->getSegment('digid')->get('bsn') ?: $session->getSegment('eidas')->get('bsn');
        } catch(\Exception $e) {
            $bsn = '';
        }

        $bsn = is_string($bsn) && !empty($bsn) ? decrypt($bsn) : '';

        if (empty($bsn)) {
            return '';
        }

        return $this->validateBSN($bsn);
    }

    private function validateBSN(string $bsn)
    {
        $bsn = $this->supplementBSN($bsn);

        if (strlen($bsn) !== 9) {
            $this->teams->addRecord('error', 'BSN', [
                'message' => 'BSN does not meet the required length of 9.'
            ]);

            return '';
        }

        return $bsn;
    }

    /**
     * BSN numbers could start with one or more zero's at the beginning.
     * The zero's are not returned by DigiD so the required length of 9 characters is not met.
     * Supplement the value so it meets the required length of 9.
     */
    private function supplementBSN(string $bsn): string
    {
        $bsnLength = strlen($bsn);
        $requiredLength = 9;
        $difference = $requiredLength - $bsnLength;

        if ($difference < 1 || $difference > $requiredLength) {
            return $bsn;
        }

        return sprintf("%'.0" . $requiredLength . "d", $bsn);
    }
}
