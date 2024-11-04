<?php

namespace OWC\PrefillGravityForms\Traits;

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;

trait ControllerTrait
{
    private function getController($supplier): BaseController
    {
        $controller = sprintf('OWC\PrefillGravityForms\Controllers\%sController', $supplier);

        if (! class_exists($controller)) {
            throw new Exception(sprintf('Class %s does not exists', $controller));
        }

        return new $controller();
    }
}
