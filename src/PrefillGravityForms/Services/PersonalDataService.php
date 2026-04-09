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

    public function get(string $key, string $goalBinding = '', string $processing = ''): string
    {
        if (! $this->controller instanceof BaseController || 1 > strlen($key)) {
            return '';
        }

        $data = $this->controller->get($goalBinding, $processing);

        if ($this->controller->getApiVersion() === '1') {
            $key = $this->keyVersionOne($key);
            $value = $this->getValueFromNestedArray($key, $data);
        } elseif ($this->controller->getApiVersion() === '2') {
            $key = $this->keyVersionTwo($key);
            $value = $this->getValueFromNestedArray($key, $data);
        } else {
            return '';
        }

        return $this->format($key, $value);
    }

    /**
     * In API version 1, some keys had different names or were structured differently. This method maps the old
     * keys to their new counterparts for suppliers that haven't updated to the new structure.
     */
    private function keyVersionOne(string $key): string
    {
        if ('naam.voornaam' === $key) {
            return 'naam.voornamen';
        }

        $prefillSuppliersWithKeyExceptions = [
            'vrijbrp'
        ];

        if (! in_array(strtolower($this->supplier), $prefillSuppliersWithKeyExceptions)) {
            return $key;
        }

        $mapping = [
            'verblijfplaats.woonplaats' => 'verblijfplaats.woonplaatsnaam',
            'verblijfplaats.straat' => 'verblijfplaats.straatnaam',
        ];

        return $mapping[$key] ?? $key;
    }

    /**
     * In API version 2, some keys had different names or were structured differently. This method maps the old
     * keys to their new counterparts for suppliers that haven't updated to the new structure.
     */
    private function keyVersionTwo(string $key): string
    {
        if ('naam.voornaam' === $key) {
            return 'naam.voornamen';
        }

        $mapping = [
            'geslachtsaanduiding' => 'geslacht.omschrijving',
            'verblijfplaats.straat' => 'verblijfplaats.verblijfadres.officieleStraatnaam',
            'verblijfplaats.huisnummer' => 'verblijfplaats.verblijfadres.huisnummer',
            'verblijfplaats.huisletter' => 'verblijfplaats.verblijfadres.huisletter',
            'verblijfplaats.postcode' => 'verblijfplaats.verblijfadres.postcode',
            'verblijfplaats.woonplaats' => 'verblijfplaats.verblijfadres.woonplaats'
        ];

        return $mapping[$key] ?? $key;
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
            'geslacht.omschrijving' => fn ($value) => ucfirst($value),
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
