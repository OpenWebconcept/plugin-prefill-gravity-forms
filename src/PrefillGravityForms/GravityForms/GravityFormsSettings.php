<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsSettings
{
    protected string $prefix = 'owc-iconnect-';
    protected string $name = 'gravityformsaddon_owc-gravityforms-iconnect_settings';
    protected array $options = [];

    final private function __construct()
    {
        $this->options = \get_option($this->name, []);
    }

    /**
     * Static constructor
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * Get the value from the database.
     */
    public function get(string $key): string
    {
        $key = $this->prefix . $key;

        return $this->options[$key] ?? '';
    }

    public function getBaseURL(): string
    {
        return $this->options[$this->prefix . 'base-url'] ?? '';
    }

    public function getNumberOIN(): string
    {
        return $this->options[$this->prefix . 'oin-number'] ?? '';
    }

    public function useAPIAuthentication(): bool
    {
        $value = $this->options[$this->prefix . 'api-use-authentication'] ?? '0';

        return boolval($value);
    }

    public function getAPIKey(): string
    {
        return $this->options[$this->prefix . 'api-key'] ?? '';
    }

    public function getBearerTokenUsername(): string
    {
        return $this->options[$this->prefix . 'api-basic-token-username'] ?? '';
    }

    public function getBearerTokenPassword(): string
    {
        return $this->options[$this->prefix . 'api-basic-token-password'] ?? '';
    }

    public function getPublicCertificate(): string
    {
        return $this->options[$this->prefix . 'public-certificate'] ?? '';
    }

    public function getPrivateCertificate(): string
    {
        return $this->options[$this->prefix . 'private-certificate'] ?? '';
    }

    public function getPassphrase(): string
    {
        return $this->options[$this->prefix . 'passphrase'] ?? '';
    }
}
