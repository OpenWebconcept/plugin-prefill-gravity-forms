<?php

namespace OWC\PrefillGravityForms;

use OWC\PrefillGravityForms\Traits\SessionTrait;

class Helpers
{
    use SessionTrait;

    /** This method is publicly available for usage outside of this plugin */
    public static function currentUserHasBSN()
    {
        return (new self())->getBSN();
    }
}
