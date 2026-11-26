<?php

namespace OWC\PrefillGravityForms\GravityForms\Fields\Traits;

use OWC\PrefillGravityForms\Helpers;

trait CheckBSN
{
    protected function check_bsn_value_from_session(): string
    {
        if ($this->is_second_applicant_field()) {
            return Helpers::secondApplicantBSN();
        }

        $bsn = Helpers::currentUserHasBSN();

        if (empty($bsn) && ! empty($_ENV['DIGID_FAKE_SESSION'])) {
            $bsn = $_ENV['DIGID_FAKE_SESSION'];
        }

        return $bsn;
    }

    /**
     * Whether this field is configured to be prefilled with the data of the second applicant (co-applicant).
     *
     * @since NEXT
     */
    protected function is_second_applicant_field(): bool
    {
        return true === filter_var($this->linkedFieldSecondApplicant ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}
