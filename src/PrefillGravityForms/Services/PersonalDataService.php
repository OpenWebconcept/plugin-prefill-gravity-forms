<?php

namespace OWC\PrefillGravityForms\Services;

use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Traits\ControllerTrait;

class PersonalDataService
{
    use ControllerTrait;

    private string $supplier;
    private ?BaseController $controller;

    public function __construct(string $supplier)
    {
        $this->supplier = $supplier;
        $this->controller = $this->getController($this->supplier);
    }

    public function get(string $key): string
    {
        if (! $this->controller instanceof BaseController || 1 > strlen($key)) {
            return '';
        }

        $data = $this->controller->get();
        $value = $this->getValueFromNestedArray($this->key($key), $data);

        return $this->format($key, $value);
    }

    private function key(string $key): string
    {
        if ('naam.voornaam' === $key) {
            return 'naam.voornamen';
        }

        return $key;
    }

    private function getValueFromNestedArray(string $keyString, array $array): string
    {
        $keys = explode('.', $keyString);

        foreach ($keys as $key) {
            if (is_array($array) && isset($array[$key])) {
                $array = $array[$key];
            } else {
                return '';
            }
        }

        return $array;
    }

    private function format(string $key, string $value): string
    {
        $keyFormatMapping = [
            'geslachtsaanduiding' => fn ($value) => ucfirst($value),
            'naam.voornaam' => fn ($value) => explode(' ', $value)[0],
            'geboorte.datum.datum' => fn ($value) => date_i18n(get_option('date_format', 'j F Y'), strtotime($value)),
        ];

        if (isset($keyFormatMapping[$key])) {
            return $keyFormatMapping[$key]($value);
        }

        return $value;
    }

}
