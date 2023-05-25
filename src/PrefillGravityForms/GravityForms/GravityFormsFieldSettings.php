<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function OWC\PrefillGravityForms\Foundation\Helpers\view;

class GravityFormsFieldSettings
{
    public function addSelectScript()
    {
        echo view('scriptSelect.php');
    }

    public function addSelect($position, $formId)
    {
        if (0 !== $position) {
            return;
        }

        echo view('select.php');
    }
}
