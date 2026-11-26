<?php

namespace OWC\PrefillGravityForms\GravityForms;

use GFAPI;
use function OWC\PrefillGravityForms\Foundation\Helpers\get_supplier;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use OWC\PrefillGravityForms\Foundation\View;

class GravityFormsFieldSettings
{
    public static function addSelectScript(): void
    {
        echo view('gf-script-custom-field-settings.php');
    }

    /**
     * Add custom select to Gravity Form fields.
     * Used for mapping a form field to a supplier setting.
     */
    public static function addSupplierPrefillOptionsSelect($position, $formId): void
    {
        if (! class_exists('GFAPI')) {
            return;
        }

        $form = GFAPI::get_form($formId);
        $supplier = get_supplier($form, true);

        if (0 !== $position || empty($supplier)) {
            return;
        }

        $themeMappingOptions = (string) ($form['owc-iconnect-theme-mapping-options-file'] ?? '0');

        // Check if the theme mapping options file exists.
        if ('0' !== $themeMappingOptions && is_file($themeMappingOptions)) {
            echo (new View())->renderFullPath($themeMappingOptions);
        } else {
            // Render the supplier based options.
            echo view(sprintf('partials/gf-field-options-%s.php', $supplier));
        }

        self::addSecondApplicantSetting();
    }

    /**
     * Allows a field to be prefilled with the data of the second applicant (co-applicant)
     * who logged in on the second login slot, e.g. via the Signicat OpenID plugin.
     *
     * @since NEXT
     */
    protected static function addSecondApplicantSetting(): void
    {
        if (! class_exists('\OWC\IdpUserData\DigiDPartnerSession')) {
            return;
        }

        echo view('partials/gf-field-second-applicant-setting.php');
    }
}
