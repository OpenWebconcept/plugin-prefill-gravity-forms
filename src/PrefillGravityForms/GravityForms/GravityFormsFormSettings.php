<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsFormSettings
{
    public function addFormSettings(array $fields): array
    {
        $fields[] = [
            'title' => esc_html__('OWC Prefill', 'prefill-gravity-forms'),
            'fields' => $this->preparedFields(),
        ];

        return $fields;
    }

    protected function preparedFields(): array
    {
        $fields = [
            [
                'name' => 'owc-form-setting-supplier',
                'default_value' => 'owc-form-setting-supplier-none',
                'tooltip' => '<h6>' . esc_html__('Selecteer een leverancier', 'prefill-gravity-forms') . '</h6>' . esc_html__('Kies een leverancier. Let op dat je de inloggegevens ook moet configureren in de algemene instellingen van Gravity Forms.', 'prefill-gravity-forms'),
                'type' => 'select',
                'label' => esc_html__('Selecteer een leverancier', 'prefill-gravity-forms'),
                'choices' => [
                    [
                        'name' => 'owc-form-setting-supplier-none',
                        'label' => esc_html__('Selecteer een leverancier', 'prefill-gravity-forms'),
                        'value' => 'none',
                    ],
                    [
                        'name' => 'owc-form-setting-supplier-openzaak',
                        'label' => esc_html__('OpenZaak', 'prefill-gravity-forms'),
                        'value' => 'openzaak',
                    ],
                    [
                        'name' => 'owc-form-setting-supplier-enable-u',
                        'label' => esc_html__('EnableU', 'prefill-gravity-forms'),
                        'value' => 'enable-u',
                    ],
                    [
                        'name' => 'owc-form-setting-supplier-pink-roccade',
                        'label' => esc_html__('PinkRoccade', 'prefill-gravity-forms'),
                        'value' => 'pink-roccade',
                    ],
                    [
                        'name' => 'owc-form-setting-supplier-pink-roccade-v2',
                        'label' => esc_html__('PinkRoccadeV2', 'prefill-gravity-forms'),
                        'value' => 'pink-roccade-v2',
                    ],
                    [
                        'name' => 'owc-form-setting-supplier-vrij-brp',
                        'label' => esc_html__('VrijBRP', 'prefill-gravity-forms'),
                        'value' => 'vrij-brp',
                    ],
                    [
                        'name' => 'owc-form-setting-supplier-we-are-frank',
                        'label' => esc_html__('WeAreFrank!', 'prefill-gravity-forms'),
                        'value' => 'we-are-frank',
                    ],
                ],
            ],
            [
                'name' => 'owc-iconnect-processing',
                'label' => esc_html__('Verwerking (V2)', 'prefill-gravity-forms'),
                'description' => esc_html__('Schrijf de globale instelling over op formulier niveau.', 'prefill-gravity-forms'),
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'owc-iconnect-doelbinding',
                'label' => __('Doelbinding', 'prefill-gravity-forms'),
                'type' => 'text',
            ],
            $this->themeMappingOptionsField(),
            [
                'name' => 'owc-iconnect-expand',
                'label' => esc_html__('Uitbreiden (V1)', 'prefill-gravity-forms'),
                'type' => 'text',
                'description' => esc_html__('Breidt de resultaten uit met andere entiteiten. Kommagescheiden waardes in vullen. Bijvoorbeeld: \'ouders,partners,kinderen\'. Alleen gebruiken wanneer de doelbinding niet verantwoordelijk is voor het ophalen van extra velden. (vaak alleen voor versie 1 van de HaalCentraal)', 'prefill-gravity-forms'),
            ],
        ];

        return array_filter($fields, function ($field) {
            return is_array($field) && isset($field['name']);
        });
    }

    /**
     * Get the theme mapping options field when there are options available.
     */
    private function themeMappingOptionsField(): array
    {
        $options = $this->getThemeMappingOptions();

        if (is_null($options)) {
            return [];
        }

        return [
            'name' => 'owc-iconnect-theme-mapping-options-file',
            'label' => esc_html__('Selecteer een mappingbestand uit het thema', 'prefill-gravity-forms'),
            'description' => esc_html__('Gebruik een eigen bestand vanuit bijv. een thema om formuliervelden te kunnen mappen.', 'prefill-gravity-forms'),
            'type' => 'select',
            'choices' => $options,
            'required' => true,
        ];
    }

    /**
     * Retrieve theme mapping options (which are file paths) if a directory is provided via the filter.
     * The options are derived from the files within the specified directory.
     */
    protected function getThemeMappingOptions(): ?array
    {
        $themeDir = apply_filters('owc_prefill_gravity_forms_theme_dir_mapping_options', null);

        if (is_null($themeDir) || ! is_dir($themeDir)) {
            return null;
        }

        $mappingOptions = glob(trailingslashit($themeDir) . '*.php');

        if (! is_array($mappingOptions) || ! count($mappingOptions)) {
            return null;
        }

        $mappingOptions = array_map(function ($file) {
            return [
                'label' => basename($file, '.php'),
                'value' => $file,
            ];
        }, $mappingOptions);

        return array_merge(
            [['label' => esc_html__('Selecteer een bestand', 'prefill-gravity-forms'), 'value' => '0']],
            $mappingOptions
        );
    }
}
