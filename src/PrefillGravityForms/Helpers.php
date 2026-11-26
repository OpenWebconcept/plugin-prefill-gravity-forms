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

    /**
     * Returns the BSN of the second applicant (co-applicant) logged in on the second login slot.
     * This method is publicly available for usage outside of this plugin.
     *
     * @since NEXT
     */
    public static function secondApplicantBSN(): string
    {
        return (new self())->getSecondApplicantBSN();
    }
}
