<?php

namespace OWC\PrefillGravityForms\Traits;

use OWC\PrefillGravityForms\Controllers\BaseController;

trait ControllerTrait
{
    private function getController($supplier): ?BaseController
    {
        $controller = sprintf('OWC\PrefillGravityForms\Controllers\%sController', $supplier);

        if (! class_exists($controller)) {
            return null;
        }

        return new $controller();
    }
}
