<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Services;

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Traits\ControllerTrait;
use OWC\PrefillGravityForms\Traits\Logger;

class PersonalDataService
{
    use ControllerTrait;
    use Logger;

    private string $supplier;
    private ?BaseController $controller;

    public function __construct(string $supplier)
    {
        $this->supplier = $supplier;
        $this->controller = $this->handleController();
    }

    private function handleController(): ?BaseController
    {
        try {
            return $this->getController($this->supplier);
        } catch (Exception $e) {
            $this->logException($e);

            return null;
        }
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

        if ('enableu' === strtolower($this->supplier) && 'verblijfplaats.woonplaats' === $key) {
            return 'verblijfplaats.woonplaatsnaam';
        }

        if ('enableu' === strtolower($this->supplier) && 'verblijfplaats.straat' === $key) {
            return 'verblijfplaats.straatnaam';
        }

        return $key;
    }

    private function getValueFromNestedArray(string $keyString, array $data): string
    {
        $keys = explode('.', $keyString);

        foreach ($keys as $key) {
            if (is_array($data) && isset($data[$key])) {
                $data = $data[$key];
            } else {
                return '';
            }
        }

        return (string) $data;
    }

    private function format(string $key, string $value): string
    {
        $keyFormatMapping = [
            'geslachtsaanduiding' => fn ($value) => ucfirst($value),
            'naam.voornaam' => fn ($value) => explode(' ', $value)[0],
            'geboorte.datum.datum' => fn ($value) => '' !== $value ? date_i18n(get_option('date_format', 'j F Y'), strtotime($value)) : '',
        ];

        if (isset($keyFormatMapping[$key])) {
            return $keyFormatMapping[$key]($value);
        }

        return $value;
    }
}
