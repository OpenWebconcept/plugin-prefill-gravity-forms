<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use function OWC\PrefillGravityForms\Foundation\Helpers\get_supplier;

class GravityFormsFieldSettings
{
    public function addSelectScript()
    {
        echo view('scriptSelectPrefill.php');
    }

    /**
     * Add custom select to Gravity Form fields.
     * Used for mapping a field to a supplier setting.
     */
    public function addSelect($position, $formId): void
    {
        if (! class_exists('\GFAPI')) {
            return;
        }

        $form = \GFAPI::get_form($formId);
        $supplier = get_supplier($form, true);

        if ($position !== 0 || empty($supplier)) {
            return;
        }
        // Render the supplier based options.
        $mappingOptions = sprintf('partials/gf-field-options-%s.php', $supplier);

        echo view($mappingOptions);
    }
}
