<?php

namespace OWC\PrefillGravityForms\GravityForms;

use GFAddOn;

use function OWC\PrefillGravityForms\Foundation\Helpers\storage_path;

class GravityFormsAddon extends GFAddOn
{
    /**
     * Subview slug.
     *
     * @var string
     */
    protected $_slug = 'owc-gravityforms-iconnect';

    /**
     * The complete title of the Add-On.
     *
     * @var string
     */
    protected $_title = 'OWC Prefill';

    /**
     * The short title of the Add-On to be used in limited spaces.
     *
     * @var string
     */
    protected $_short_title = 'OWC Prefill';

    /**
     * Instance object
     *
     * @var self
     */
    private static $_instance = null;

    /**
     * Singleton loader.
     */
    public static function get_instance(): self
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Return the plugin's icon for the plugin/form settings menu.
     *
     * @since 2.5
     *
     * @return string
     */
    public function get_menu_icon()
    {
        return 'dashicons-yard-y';
    }

    /**
     * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
     */
    public function plugin_settings_fields(): array
    {
        $prefix = 'owc-iconnect-';

        return [
            [
                'title' => __('Algemeen', 'prefill-gravity-forms'),
                'fields' => [
                    [
                        'label' => __('OIN number', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}oin-number",
                        'required' => true,
                    ],
                    [
                        'label' => __('Basis URL', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}base-url",
                        'required' => true,
                    ],
                    [
                        'label' => __('API sleutel', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}api-key",
                        'description' => __('API sleutel is niet altijd vereist. Dit hangt af van de leverancier.', 'prefill-gravity-forms'),
                    ],
                ],
            ],
            [
                'title' => __('Certificaten', 'prefill-gravity-forms'),
                'fields' => [
                    [
                        'label' => esc_html__('Certificaten hoofd locatie', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}location-root-path-certificates",
                        'default_value' => $this->getRootPathToCertificates(),
                        'required' => true,
                    ],
                    [
                        'label' => esc_html__('Publieke locatie certificaten', 'prefill-gravity-forms'),
                        'type' => 'select',
                        'name' => "{$prefix}public-certificate",
                        'choices' => $this->getPublicCertificates(),
                        'required' => true,
                    ],
                    [
                        'label' => esc_html__('Privé locatie certificaten', 'prefill-gravity-forms'),
                        'type' => 'select',
                        'name' => "{$prefix}private-certificate",
                        'choices' => $this->getPrivateCertificates(),
                        'required' => true,
                    ],
                    [
                        'label' => __('Wachtwoord', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}passphrase",
                        'required' => false,
                        'tooltip' => esc_html__('Dit veld mag leeg gelaten worden als er geen wachtwoord vereist is voor het maken van de verzoeken naar de "Haalcentraal" API.', 'prefill-gravity-forms'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Format the list of certificates for the selectbox.
     */
    private function formatListOfCertificates(array $certificates): array
    {
        $noCertificate = [
            [
                'label' => esc_html__('Geen certificaat geselecteerd', 'prefill-gravity-forms'),
                'value' => 'no-certificate',
            ],
        ];

        $certificates = array_values(array_map(function ($certificate) {
            return [
                'label' => basename($certificate),
                'value' => $certificate,
            ];
        }, $certificates));

        return array_merge($noCertificate, $certificates);
    }

    /**
     * Get all the public certificates from the storage map.
     */
    private function getPublicCertificates(): array
    {
        return $this->formatListOfCertificates(glob($this->getCertificateLocation() . '/*.{crt,cer}', GLOB_BRACE));
    }

    /**
     * Get all the private certificates from the storage map.
     */
    private function getPrivateCertificates(): array
    {
        return $this->formatListOfCertificates(glob($this->getCertificateLocation() . '/*.{key}', GLOB_BRACE));
    }

    /**
     * Get the correct path for the certificates of the current site.
     */
    private function getCertificateLocation(): string
    {
        if (is_multisite()) {
            return sprintf('%s/%s', $this->getRootPathToCertificates(), get_current_blog_id() ?? '1');
        }

        return sprintf('%s', $this->getRootPathToCertificates());
    }

    /**
     * Get root path to certificates.
     */
    private function getRootPathToCertificates(): string
    {
        return (! empty(GravityFormsSettings::make()->get('location-root-path-certificates'))) ? GravityFormsSettings::make()->get('location-root-path-certificates') : storage_path('certificates');
    }
}
