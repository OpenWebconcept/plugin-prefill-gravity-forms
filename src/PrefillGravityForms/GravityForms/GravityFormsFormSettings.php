<?php

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsFormSettings
{
    public function addFormSettings(array $fields): array
    {
        $fields[] = [
            'title' => esc_html__('iConnect', 'prefill-gravity-forms'),
            'fields' => [
                [
                    'name' => 'owc-iconnect-doelbinding',
                    'label' => 'Doelbinding',
                    'type' => 'text',
                ],
                [
                    'name' => 'owc-iconnect-expand',
                    'label' => 'Breidt uit',
                    'type' => 'text',
                    'description' => __('Breid de resultaten uit met andere entiteiten. Kommagescheiden waardes in vullen. Bijvoorbeeld: \'ouders,partners,kinderen\'', 'prefill-gravity-forms'),
                ],
                [
                    'name' => "owc-form-setting-supplier",
                    'default_value' => "owc-form-setting-supplier-none",
                    'tooltip' => '<h6>' . __('Select a supplier', 'prefill-gravity-forms') . '</h6>' . __('Choose the Zaaksysteem supplier. Please note that you\'ll also need to configure the credentials in the Gravity Forms main settings.', 'prefill-gravity-forms'),
                    'type' => 'select',
                    'label' => esc_html__('Select a supplier', 'prefill-gravity-forms'),
                    'choices' => [
                        [
                            'name' => "owc-form-setting-supplier-none",
                            'label' => __('Select supplier', 'prefill-gravity-forms'),
                            'value' => 'none',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-openzaak",
                            'label' => __('OpenZaak', 'prefill-gravity-forms'),
                            'value' => 'openzaak',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-enable-u",
                            'label' => __('EnableU', 'prefill-gravity-forms'),
                            'value' => 'enable-u',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-pink-roccade",
                            'label' => __('PinkRoccade', 'prefill-gravity-forms'),
                            'value' => 'pink-roccade',
                        ],
                    ],
                ],
            ],
        ];

        return $fields;
    }
}
