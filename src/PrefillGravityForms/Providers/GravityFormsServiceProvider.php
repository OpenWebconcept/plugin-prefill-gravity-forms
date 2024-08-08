<?php

namespace OWC\PrefillGravityForms\Providers;

use GFAddOn;
use GFForms;
use GF_Fields;
use OWC\PrefillGravityForms\Foundation\ServiceProvider;
use OWC\PrefillGravityForms\GravityForms\GravityForms;
use OWC\PrefillGravityForms\GravityForms\GravityFormsAddon;
use OWC\PrefillGravityForms\GravityForms\GravityFormsFieldSettings;
use OWC\PrefillGravityForms\GravityForms\GravityFormsFormSettings;
use function OWC\PrefillGravityForms\Foundation\Helpers\config;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;

class GravityFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerHooks();
        $this->registerFieldsWithTabs();
        $this->registerSettingsAddon();
    }

    protected function registerHooks(): void
    {
        add_filter('gform_pre_render', [new GravityForms(), 'preRender']);
        add_filter('gform_form_settings_fields', [new GravityFormsFormSettings(), 'addFormSettings'], 9999, 2);
        add_filter('gform_field_groups_form_editor', [$this, 'fieldGroupsFormEditor'], 999, 1);
        add_action('gform_field_standard_settings', [GravityFormsFieldSettings::class, 'addSupplierPrefillOptionsSelect'], 10, 2);
        add_action('gform_editor_js', [GravityFormsFieldSettings::class, 'addSelectScript'], 10, 0);
    }

    /**
     * Registers the custom fields and corrensponding editor settings tabs with their content.
     *
     * Since this plug-in is loaded on the 'plugins_loaded' hook,
     * it is not necessary to register the fields inside the 'gform_loaded' hook.
     */
    public function registerFieldsWithTabs(): void
    {
        $fields = config('gf-custom-fields');

        if (! is_array($fields) || count($fields) == 0) {
            return;
        }

        foreach ($fields as $field) {
            $field = new $field();

            GF_Fields::register($field);

            add_filter('gform_field_settings_tabs', function ($tabs, $form) use ($field) {
                $tabs[] = [
                    'id' => $field->type,
                    'title' => $field->get_form_editor_field_title(),
                ];

                return $tabs;
            }, 10, 2);

            add_action('gform_field_settings_tab_content', function ($form, $tabID) use ($field) {
                if ($field->type !== $tabID) {
                    return;
                }

                echo view($field->get_field_tab_content_template_path());
            }, 10, 2);
        }
    }

    private function registerSettingsAddon(): void
    {
        if (! method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        GFForms::include_addon_framework();
        GFAddOn::register(GravityFormsAddon::class);
        GravityFormsAddon::get_instance();
    }

    /**
     * Adds a custom field group to the form editor.
     */
    public function fieldGroupsFormEditor(array $fieldGroups): array
    {
        $customGroup = [
            'name' => 'owc_pg',
            'label' => __('BRP Prefill velden', 'prefill-gravity-forms'),
            'fields' => [],
        ];

        array_unshift($fieldGroups, $customGroup);

        return $fieldGroups;
    }
}
