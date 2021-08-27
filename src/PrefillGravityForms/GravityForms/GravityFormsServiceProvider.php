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

    protected function loadHooks()
    {
        $this->plugin->loader->addFilter('gform_pre_render', new GravityForms, 'preRender');
        $this->plugin->loader->addAction('gform_field_standard_settings', new GravityFormsFieldSettings, 'addSelect', 10, 2);
        $this->plugin->loader->addAction('gform_editor_js', new GravityFormsFieldSettings, 'addSelectScript', 10, 2);
        $this->plugin->loader->addFilter('gform_form_settings', new GravityFormsFormSettings, 'addFormSettings', 10, 2);
        $this->plugin->loader->addFilter('gform_pre_form_settings_save', new GravityFormsFormSettings, 'saveFormSettings', 10, 1);
    }

    private function registerSettingsAddon(): void
    {
        if (!method_exists('\GFForms', 'include_addon_framework')) {
            return;
        }

        \GFForms::include_addon_framework();
        \GFAddOn::register(GravityFormsAddon::class);
        GravityFormsAddon::get_instance();
    }
}
