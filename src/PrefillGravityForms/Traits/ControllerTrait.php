<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Traits;

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;

trait ControllerTrait
{
    /**
     * Get the controller instance for the given supplier.
     *
     * @throws Exception
     */
    private function getController($supplier): BaseController
    {
        $controller = sprintf('OWC\PrefillGravityForms\Controllers\%sController', $supplier);

        if (! class_exists($controller)) {
            throw new Exception(sprintf('Controller class %s does not exist.', $controller), 500);
        }

        return new $controller();
    }
}
