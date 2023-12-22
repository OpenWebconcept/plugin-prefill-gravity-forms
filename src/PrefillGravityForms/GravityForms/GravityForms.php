<?php

namespace OWC\PrefillGravityForms\GravityForms;

use GF_Field;
use GFAPI;
use function OWC\PrefillGravityForms\Foundation\Helpers\get_supplier;
use function Yard\DigiD\Foundation\Helpers\decrypt;
use function Yard\DigiD\Foundation\Helpers\encrypt;

class GravityForms
{
    protected string $supplier;
    protected bool $shouldDecrypt;

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
        $this->supplier = get_supplier($form);
    }

    /**
     * Compose method name based on supplier and execute.
     */
    protected function handleSupplier(array $form): array
    {
        try {
            $instance = $this->getController();
        } catch(\Exception $e) {
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
            throw new \Exception(sprintf('Class %s does not exists', $controller));
        }

        return new $controller();
    }

    /**
     * For security reasons, when populating/prefilling a field with a BSN number, the value is encrypted and securely stored.
     */
    public function saveFieldValue(string $value, $lead, GF_Field $field, array $form): string
    {
        if ('burgerservicenummer' !== ($field->linkedFieldValue ?? '')) {
            return $value;
        }

        if (empty($value) || ! is_string($value)) {
            return $value;
        }

        return encrypt($value);
    }

    /**
     * Decrypts the value for display on the Entry list page, only for prepopulated fields containing a BSN number.
     */
    public function modifyEntryValue(string $value, int $formID, int $fieldID): string
    {
        $field = GFAPI::get_field($formID, $fieldID);

        if (empty($field->linkedFieldValue) || 'burgerservicenummer' !== ($field->linkedFieldValue ?? '')) {
            return $value;
        }

        $shouldDecrypt = apply_filters('owc_prefill_gravityforms_use_value_bsn_decrypted', false);

        if ($shouldDecrypt) {
            $value = $this->decryptEncryptedBSN($value);
        }

        return esc_html($value);
    }

    /**
     * Decrypts the value for display on the Entry detail page, only for prepopulated fields containing a BSN number.
     */
    public function modifyEntryValueDetail($value, $field, $lead, $form): string
    {
        if (empty($field->linkedFieldValue) || 'burgerservicenummer' !== ($field->linkedFieldValue ?? '')) {
            return $value;
        }

        $shouldDecrypt = apply_filters('owc_prefill_gravityforms_use_value_bsn_decrypted', false);

        if ($shouldDecrypt) {
            $value = $this->decryptEncryptedBSN($value);
        }

        return esc_html($value);
    }

    private function decryptEncryptedBSN(string $value): string
    {
        $decrypted = decrypt($value);

        return $decrypted && is_string($decrypted) ? $decrypted : $value;
    }
}
