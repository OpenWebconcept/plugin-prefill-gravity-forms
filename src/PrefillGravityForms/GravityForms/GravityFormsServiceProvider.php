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

        $this->plugin->loader->addFilter('gform_pre_render', new GravityForms(), 'preRender');
        $this->plugin->loader->addAction('gform_field_standard_settings', $gravityFormsFieldSettings, 'addSelect', 10, 2);
        $this->plugin->loader->addAction('gform_editor_js', $gravityFormsFieldSettings, 'addSelectScript', 10, 0);
        $this->plugin->loader->addFilter('gform_form_settings_fields', $gravityFormsFormSettings, 'addFormSettings', 9999, 2);
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
