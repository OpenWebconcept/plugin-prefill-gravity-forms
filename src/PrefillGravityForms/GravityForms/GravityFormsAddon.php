<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function OWC\PrefillGravityForms\Foundation\Helpers\storage_path;
use GFAddOn;

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
        return 'dashicons-admin-tools';
    }

    /**
     * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
     */
    public function plugin_settings_fields(): array
    {
        $prefix = 'owc-iconnect-';

        return [
            [
                'title' => __('General', 'prefill-gravity-forms'),
                'fields' => [
                    [
                        'label' => __('OIN number', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}oin-number",
                        'required' => true,
                    ],
                    [
                        'label' => __('Base URL', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}base-url",
                        'required' => true,
                    ],
                ],
            ],
            [
                'title' => __('Certificates', 'prefill-gravity-forms'),
                'fields' => [
                    [
                        'label' => esc_html__('Certificates root location', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}location-root-path-certificates",
                        'default_value' => $this->getRootPathToCertificates(),
                        'required' => true,
                    ],
                    [
                        'label' => esc_html__('Public certificate location', 'prefill-gravity-forms'),
                        'type' => 'select',
                        'name' => "{$prefix}public-certificate",
                        'choices' => $this->getPublicCertificates(),
                        'required' => true,
                    ],
                    [
                        'label' => esc_html__('Private certificate location', 'prefill-gravity-forms'),
                        'type' => 'select',
                        'name' => "{$prefix}private-certificate",
                        'choices' => $this->getPrivateCertificates(),
                        'required' => true,
                    ],
                    [
                        'label' => __('Passphrase', 'prefill-gravity-forms'),
                        'type' => 'text',
                        'class' => 'medium',
                        'name' => "{$prefix}passphrase",
                        'required' => false,
                        'tooltip' => esc_html__('Leave empty when a password is not required for the requests to the "Haalcentraal" API.', 'prefill-gravity-forms'),
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
                'label' => esc_html__('No certificate selected', 'prefill-gravity-forms'),
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
