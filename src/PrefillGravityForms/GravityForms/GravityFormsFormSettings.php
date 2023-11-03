<?php

namespace OWC\PrefillGravityForms\GravityForms;

use function OWC\PrefillGravityForms\Foundation\Helpers\config;

class GravityFormsFormSettings
{
    public function addFormSettingsCSS(): void
    {
        wp_enqueue_style('gf-custom-admin', \plugins_url(PG_DIR . '/resources/css/admin.css'));
    }

    public function addFormSettings(array $fields): array
    {
        $fields[] = [
            'title' => esc_html__('iConnect', config('core.text_domain')),
            'fields' => [
                [
                    'name' => 'owc-iconnect-doelbinding',
                    'label' => 'Doelbinding',
                    'type' => 'text'
                ],
                [
                    'name' => 'owc-iconnect-expand',
                    'label' => 'Breidt uit',
                    'type' => 'text',
                    'description' => 'Breid de resultaten uit met andere entiteiten. Kommagescheiden waardes in vullen. Bijvoorbeeld: \'ouders,partners,kinderen\''
                ],
                [
                    'name' => "owc-form-setting-supplier",
                    'default_value' => "owc-form-setting-supplier-none",
                    'tooltip' => '<h6>' . __('Select a supplier', config('core.text_domain')) . '</h6>' . __('Choose the Zaaksysteem supplier. Please note that you\'ll also need to configure the credentials in the Gravity Forms main settings.', config('core.text_domain')),
                    'type' => 'select',
                    'label' => esc_html__('Select a supplier', config('core.text_domain')),
                    'choices' => [
                        [
                            'name' => "owc-form-setting-supplier-none",
                            'label' => __('Select supplier', config('core.text_domain')),
                            'value' => 'none',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-openzaak",
                            'label' => __('OpenZaak', config('core.text_domain')),
                            'value' => 'openzaak',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-enable-u",
                            'label' => __('EnableU', config('core.text_domain')),
                            'value' => 'enable-u',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-pink-roccade",
                            'label' => __('PinkRoccade', config('core.text_domain')),
                            'value' => 'pink-roccade',
                        ]
                    ],
                ],
            ]
        ];

        return $fields;
    }
}
