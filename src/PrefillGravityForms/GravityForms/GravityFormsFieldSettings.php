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

            return;
        }

        // Render the supplier based options.
        $mappingOptions = sprintf('partials/gf-field-options-%s.php', $supplier);

        echo view($mappingOptions);
    }
}
