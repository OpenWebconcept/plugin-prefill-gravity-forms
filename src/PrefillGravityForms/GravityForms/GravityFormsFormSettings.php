<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsFormSettings
{
    public function addFormSettings(array $fields): array
    {
        $fields[] = [
            'title' => esc_html__('OWC Prefill', 'prefill-gravity-forms'),
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
                    'tooltip' => '<h6>' . __('Selecteer een leverancier', 'prefill-gravity-forms') . '</h6>' . __('Kies een Zaaksysteem leverancier. Let op dat je de inloggegevens ook moet configureren in de algemene instellingen van Gravity Forms.', 'prefill-gravity-forms'),
                    'type' => 'select',
                    'label' => esc_html__('Selecteer een leverancier', 'prefill-gravity-forms'),
                    'choices' => [
                        [
                            'name' => "owc-form-setting-supplier-none",
                            'label' => __('Selecteer een leverancier', 'prefill-gravity-forms'),
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
                        [
                            'name' => "owc-form-setting-supplier-vrij-brp",
                            'label' => __('VrijBRP', 'prefill-gravity-forms'),
                            'value' => 'vrij-brp',
                        ],
                        [
                            'name' => "owc-form-setting-supplier-we-are-frank",
                            'label' => __('WeAreFrank!', 'prefill-gravity-forms'),
                            'value' => 'we-are-frank',
                        ],
                    ],
                ],
            ],
        ];

        return $fields;
    }
}
