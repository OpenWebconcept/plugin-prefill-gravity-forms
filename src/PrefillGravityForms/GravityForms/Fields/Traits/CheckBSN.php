<?php

namespace OWC\PrefillGravityForms\GravityForms\Fields\Traits;

use OWC\PrefillGravityForms\Helpers;

trait CheckBSN
{
    protected function check_bsn_value_from_session(): string
    {
        $bsn = Helpers::currentUserHasBSN();

        if (empty($bsn) && ! empty($_ENV['DIGID_FAKE_SESSION'])) {
            $bsn = $_ENV['DIGID_FAKE_SESSION'];
        }

        return $bsn;
    }
}
