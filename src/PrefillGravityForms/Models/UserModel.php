<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Models;

use Exception;
use OWC\PrefillGravityForms\Abstracts\PostController;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Traits\ControllerTrait;
use OWC\PrefillGravityForms\Traits\Logger;

class UserModel
{
    use ControllerTrait;
    use Logger;

    protected string $supplier;
    protected ?BaseController $controller;
    protected array $data;
    private ?int $residentsCount = null;
    private bool $residentsCountResolved = false;

    public function __construct()
    {
        $this->supplier = GravityFormsSettings::make()->getSupplier();
        $this->controller = $this->handleController();
        $this->data = $this->controller?->get(GravityFormsSettings::make()->getGoalBinding(), GravityFormsSettings::make()->getProcessing()) ?? [];
    }

    private function handleController(): ?BaseController
    {
        if (! GravityFormsSettings::make()->isUserModelEnabled()) {
            return null;
        }

        try {
            return $this->getController($this->supplier);
        } catch (Exception $e) {
            $this->logException($e);

            return null;
        }
    }

    /**
     * Use this method to determine whether the user is logged in or not before using any of the class methods.
     * A DigiD login is required to retrieve user data.
     */
    public function isLoggedIn(): bool
    {
        $bsn = $this->bsn();

        return 7 < strlen($bsn) && 10 > strlen($bsn);
    }

    public function bsn(): string
    {
        return (string) ($this->data['burgerservicenummer'] ?? '');
    }

    public function age(): int
    {
        return (int) ($this->data['leeftijd'] ?? 0);
    }

    public function initials(): string
    {
        return (string) ($this->data['naam']['voorletters'] ?? '');
    }

    public function firstNames(): string
    {
        return (string) ($this->data['naam']['voornamen'] ?? '');
    }

    public function lastName(): string
    {
        return (string) ($this->data['naam']['geslachtsnaam'] ?? '');
    }

    public function lastNamePrefix(): string
    {
        return (string) ($this->data['naam']['voorvoegsel'] ?? '');
    }

    public function fullName(bool $withInitials = false): string
    {
        $nameParts = [
            $withInitials ? $this->initials() : $this->firstNames(),
            $this->lastNamePrefix(),
            $this->lastName(),
        ];

        return implode(' ', array_filter($nameParts));
    }

    public function zipcode(): string
    {
        return $this->stringValue('verblijfplaats.verblijfadres.postcode', 'verblijfplaats.postcode');
    }

    public function houseNumber(): string
    {
        return $this->stringValue('verblijfplaats.verblijfadres.huisnummer', 'verblijfplaats.huisnummer');
    }

    public function houseLetter(): string
    {
        return $this->stringValue('verblijfplaats.verblijfadres.huisletter', 'verblijfplaats.huisletter');
    }

    public function houseNumberAddition(): string
    {
        return $this->stringValue('verblijfplaats.verblijfadres.huisnummertoevoeging', 'verblijfplaats.huisnummertoevoeging');
    }

    public function street(): string
    {
        return $this->stringValue(
            'verblijfplaats.verblijfadres.officieleStraatnaam',
            'verblijfplaats.straat',
            'verblijfplaats.straatnaam'
        );
    }

    public function city(): string
    {
        return $this->stringValue(
            'verblijfplaats.verblijfadres.woonplaats',
            'verblijfplaats.woonplaats',
            'verblijfplaats.woonplaatsnaam'
        );
    }

    /**
     * BAG identification (16 digits) of the address the person is registered at, or an empty string.
     */
    public function nummeraanduidingIdentificatie(): string
    {
        $value = $this->stringValue(
            'verblijfplaats.nummeraanduidingIdentificatie',
            'verblijfplaats.identificatiecodeNummeraanduiding'
        );

        return 1 === preg_match('/^\d{16}$/', $value) ? $value : '';
    }

    /**
     * Number of persons registered at the residential address of the logged-in user.
     * Only available for BRP API v2 suppliers; null when it cannot be determined.
     */
    public function residentsCount(): ?int
    {
        if ($this->residentsCountResolved) {
            return $this->residentsCount;
        }

        $this->residentsCountResolved = true;
        $controller = $this->controller ?? null;
        $nummeraanduidingIdentificatie = $this->nummeraanduidingIdentificatie();

        if (! $controller instanceof PostController || ! $this->isLoggedIn() || '' === $nummeraanduidingIdentificatie) {
            return $this->residentsCount = null;
        }

        $settings = GravityFormsSettings::make();

        return $this->residentsCount = $controller->countResidents(
            $nummeraanduidingIdentificatie,
            $settings->getGoalBinding(),
            $settings->getProcessing()
        );
    }

    /**
     * Returns the first non-empty scalar found at one of the given dot-notated paths, as a string.
     */
    private function stringValue(string ...$paths): string
    {
        foreach ($paths as $path) {
            $value = $this->data;

            foreach (explode('.', $path) as $segment) {
                if (! is_array($value) || ! array_key_exists($segment, $value)) {
                    $value = null;

                    break;
                }

                $value = $value[$segment];
            }

            if (is_bool($value) || ! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ('' !== $value) {
                return $value;
            }
        }

        return '';
    }
}
