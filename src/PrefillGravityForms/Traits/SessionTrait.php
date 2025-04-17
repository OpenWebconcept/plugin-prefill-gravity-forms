<?php

namespace OWC\PrefillGravityForms\Traits;

use function OWC\PrefillGravityForms\Foundation\Helpers\resolve;
use OWC\PrefillGravityForms\Foundation\TeamsLogger;

trait SessionTrait
{
    protected function getBSN(): string
    {
        if ($bsn = $this->idpDigiD()) {
            return $this->validateBSN($bsn);
        }

        if ($bsn = $this->samlDigiD()) {
            return $this->validateBSN($bsn);
        }

        return '';
    }

    private function idpDigiD(): string
    {
        if (! class_exists('\OWC\IdpUserData\DigiDSession')) {
            return '';
        }

        if (! \OWC\IdpUserData\DigiDSession::isLoggedIn() || is_null(\OWC\IdpUserData\DigiDSession::getUserData())) {
            return '';
        }

        return \OWC\IdpUserData\DigiDSession::getUserData()->getBsn();
    }

    private function samlDigiD(): string
    {
        if (! function_exists('\\Yard\\DigiD\\Foundation\\Helpers\\resolve')) {
            return '';
        }

        if (! function_exists('\\Yard\\DigiD\\Foundation\\Helpers\\decrypt')) {
            return '';
        }

        $bsn = \Yard\DigiD\Foundation\Helpers\resolve('session')->getSegment('digid')->get('bsn');

        return ! empty($bsn) && is_string($bsn) ? \Yard\DigiD\Foundation\Helpers\decrypt($bsn) : '';
    }

    private function validateBSN(string $bsn)
    {
        $bsn = $this->supplementBSN($bsn);

        if (strlen($bsn) !== 9) {
            TeamsLogger::make(resolve('teams'))->addRecord('error', 'BSN', [
                'message' => 'BSN does not meet the required length of 9.',
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

        if (1 > $difference || $difference > $requiredLength) {
            return $bsn;
        }

        return sprintf("%'.0" . $requiredLength . "d", $bsn);
    }
}
