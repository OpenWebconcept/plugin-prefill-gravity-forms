<?php

namespace OWC\PrefillGravityForms\GravityForms;

class GravityFormsFormSettings
{
    public function addFormSettings($settings, $form)
    {
        $settings['iConnect']['my_custom_setting'] = '
        <tr>
            <td><label class="gform-settings-label" for="my_custom_setting">Doelbinding</label></td>
        </tr>
        <tr>
            <td><input type="text" class="gform-settings-input__container" value="' . rgar($form, 'my_custom_setting') . '" name="my_custom_setting"></td>
        </tr>';

        return $settings;
    }

    public function saveFormSettings($form)
    {
        $form['my_custom_setting'] = rgpost('my_custom_setting');
        return $form;
    }

    /**
     * TO-DO: 
     * 
     * Opmaak van het veld in het formulier.
     * Ophalen van opgeslagen data en toepassen in request.
     */
}
