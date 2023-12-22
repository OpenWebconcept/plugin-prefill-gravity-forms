<?php

namespace OWC\PrefillGravityForms\GravityForms;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;

class GravityFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerSettingsAddon();
    }

    public function boot(): void
    {
        $this->loadHooks();
    }

    protected function loadHooks(): void
    {
        $gravityFormsFieldSettings = new GravityFormsFieldSettings();
        $gravityFormsFormSettings = new GravityFormsFormSettings();
        $gravityForms = new GravityForms();

        $this->plugin->loader->addFilter('gform_pre_render', $gravityForms, 'preRender');
        $this->plugin->loader->addAction('gform_field_standard_settings', $gravityFormsFieldSettings, 'addSelect', 10, 2);
        $this->plugin->loader->addAction('gform_editor_js', $gravityFormsFieldSettings, 'addSelectScript', 10, 0);
        $this->plugin->loader->addFilter('gform_form_settings_fields', $gravityFormsFormSettings, 'addFormSettings', 9999, 2);
        $this->plugin->loader->addAction('gform_save_field_value', $gravityForms, 'saveFieldValue', 10, 4);
        $this->plugin->loader->addFilter('gform_entries_field_value', $gravityForms, 'modifyEntryValue', 10, 3);
        $this->plugin->loader->addFilter('gform_entry_field_value', $gravityForms, 'modifyEntryValueDetail', 10, 4);
    }

    private function registerSettingsAddon(): void
    {
        if (! method_exists('\GFForms', 'include_addon_framework')) {
            return;
        }

        \GFForms::include_addon_framework();
        \GFAddOn::register(GravityFormsAddon::class);
        GravityFormsAddon::get_instance();
    }
}
