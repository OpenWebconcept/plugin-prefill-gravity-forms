<?php

namespace OWC\PrefillGravityForms\GravityForms;

use Exception;
use function OWC\PrefillGravityForms\Foundation\Helpers\get_supplier;

class GravityForms
{
    protected string $supplier;

    public function preRender(array $form): array
    {
        $this->setSupplier($form);

        if (empty($this->supplier)) {
            return $form;
        }

        return $this->handleSupplier($form);
    }

    protected function setSupplier(array $form)
    {
        $supplier = get_supplier($form);

        /**
         * OpenZaak is deprecated. Some applications may still use 'OpenZaak'
         * as configured supplier. We'll use PinkRoccade instead.
         */
        if ('OpenZaak' === $supplier) {
            $supplier = 'PinkRoccade';
        }

        $this->supplier = $supplier;
    }

    /**
     * Compose method name based on supplier and execute.
     */
    protected function handleSupplier(array $form): array
    {
        try {
            $instance = $this->getController();
        } catch(Exception $e) {
            return $form;
        }

        if (! method_exists($instance, 'handle')) {
            return $form;
        }

        return $instance->handle($form);
    }

    /**
     * Get controller class based on supplier.
     */
    protected function getController(): object
    {
        $controller = sprintf('OWC\PrefillGravityForms\Controllers\%sController', $this->supplier);

        if (! class_exists($controller)) {
            throw new Exception(sprintf('Class %s does not exists', $controller));
        }

        return new $controller();
    }
}
