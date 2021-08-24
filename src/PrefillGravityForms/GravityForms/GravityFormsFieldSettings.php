<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use function OWC\PrefillGravityForms\Foundation\Helpers\resolve;

class GravityFormsFieldSettings
{
    public function addSelectScript()
    {
        echo view('scriptSelect.php');
    }

    public function addSelect($position, $formId)
    {
        if ($position == 0) {
            echo view('select.php');
        }
    }
}
