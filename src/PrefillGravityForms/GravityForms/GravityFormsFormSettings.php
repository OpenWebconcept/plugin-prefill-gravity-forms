<?php

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsFormSettings
{
    public function addFormSettingsCSS(): void
    {
        wp_enqueue_style('gf-custom-admin', \plugins_url(PG_DIR . '/resources/css/admin.css'));
    }

    public function addFormSettings(array $settings, array $form): array
    {
        $settings['iConnect']['owc-iconnect-doelbinding'] = '
        <tr>
            <td><label class="gform-settings-label" for="owc-iconnect-doelbinding">Doelbinding</label></td>
        </tr>
        <tr>
            <td><input type="text" class="gform-settings-input__container" value="' . rgar($form, 'owc-iconnect-doelbinding') . '" name="owc-iconnect-doelbinding"></td>
        </tr>';

        return $settings;
    }

    public function saveFormSettings(array $form): array
    {
        $form['owc-iconnect-doelbinding'] = rgpost('owc-iconnect-doelbinding');

        return $form;
    }
}
