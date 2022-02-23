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
    protected $_title = 'iConnect';

    /**
     * The short title of the Add-On to be used in limited spaces.
     *
     * @var string
     */
    protected $_short_title = 'iConnect';

    /**
     * Instance object
     *
     * @var self
     */
    private static $_instance = null;

    /**
     * Singleton loader.
     *
     * @return self
     */
    public static function get_instance(): self
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
     *
     * @return array
     */
    public function plugin_settings_fields()
    {
        $prefix = "owc-iconnect-";

        return [
            [
                'title' => __('General', 'prefill-gravity-forms'),
                'fields' => [
                    [
                        'label'             => __('OIN number', 'prefill-gravity-forms'),
                        'type'              => 'text',
                        'class'             => 'medium',
                        'name'              => "{$prefix}oin-number",
                        'required'          => true
                    ],
                    [
                        'label'             => __('Base URL', 'prefill-gravity-forms'),
                        'type'              => 'text',
                        'class'             => 'medium',
                        'name'              => "{$prefix}base-url",
                        'required'          => true
                    ]
                ]
            ],
            [
                'title' => __('Certificates', 'prefill-gravity-forms'),
                'fields' => [
                    [
                        'label'                => esc_html__('Certificates root location', 'prefill-gravity-forms'),
                        'type'                 => 'text',
                        'class'                => 'medium',
                        'name'                 => "{$prefix}location-root-path-certificates",
                        'default_value'        => $this->getRootPathToCertificates(),
                        'required'             => true
                    ],
                    [
                        'label'                => esc_html__('Public certificate location', 'prefill-gravity-forms'),
                        'type'                 => 'select',
                        'name'                 => "{$prefix}public-certificate",
                        'choices'              => $this->getPublicCertificates(),
                        'required'             => true
                    ],
                    [
                        'label'                => esc_html__('Private certificate location', 'prefill-gravity-forms'),
                        'type'                 => 'select',
                        'name'                 => "{$prefix}private-certificate",
                        'choices'              => $this->getPrivateCertificates(),
                        'required'             => true,
                    ]
                ]
            ]
        ];
    }

    /**
     * Format the list of certificates for the selectbox.
     *
     * @param array $certificates
     * @return array
     */
    private function formatListOfCertificates(array $certificates): array
    {
        $noCertificate = [
            [
                'label' => esc_html__('No certificate selected', 'prefill-gravity-forms'),
                'value' => 'no-certificate'
            ]
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
     *
     * @return array
     */
    private function getPublicCertificates(): array
    {
        return $this->formatListOfCertificates(glob($this->getCertificateLocation() . '/*.{crt,cer}', GLOB_BRACE));
    }

    /**
     * Get all the private certificates from the storage map.
     *
     * @return array
     */
    private function getPrivateCertificates(): array
    {
        return $this->formatListOfCertificates(glob($this->getCertificateLocation() . '/*.{key}', GLOB_BRACE));
    }

    /**
     * Get the correct path for the certificates of the current site.
     *
     * @return string
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
     *
     * @return string
     */
    private function getRootPathToCertificates(): string
    {
        return (!empty(GravityFormsSettings::make()->get('location-root-path-certificates'))) ? GravityFormsSettings::make()->get('location-root-path-certificates') : storage_path('certificates');
    }
}
