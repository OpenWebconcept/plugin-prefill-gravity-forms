<?php

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsSettings
{
    /** @var string */
    protected $prefix = 'owc-iconnect-';

    /** @var string */
    protected $name = 'gravityformsaddon_owc-gravityforms-iconnect_settings';

    /** @var array */
    protected $options = [];

    final private function __construct()
    {
        $this->options = \get_option($this->name, []);
    }

    /**
     * Static constructor
     *
     * @return self
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * Get the value from the database.
     *
     * @param string $key
     * @return string
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
