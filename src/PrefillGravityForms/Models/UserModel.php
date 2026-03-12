<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Models;

use Exception;
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

    public function __construct()
    {
        $this->supplier = GravityFormsSettings::make()->getSupplier();
        $this->controller = $this->handleController();
        $this->data = $this->controller?->get() ?? [];
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
        $bsn = (string) $this->bsn();

        return 7 < strlen($bsn) && 10 > strlen($bsn);
    }

    public function bsn(): int
    {
        return (int) ($this->data['burgerservicenummer'] ?? 0);
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
        return (string) ($this->data['verblijfplaats']['postcode'] ?? '');
    }

    public function houseNumber(): string
    {
        return (string) ($this->data['verblijfplaats']['huisnummer'] ?? '');
    }

    public function houseLetter(): string
    {
        return (string) ($this->data['verblijfplaats']['huisletter'] ?? '');
    }
}
