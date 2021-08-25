<?php

/**
 * Select element for custom field
 **/
?>
<li style="display: list-item;">
    <label for="linkedField" class="section_label">
        <?php _e('Automatisch invullen', 'prefill-gravity-forms'); ?>
        <?php gform_tooltip('caseCategoryToolTip'); ?>
    </label>
    <select id="linkedField" onchange="SetFieldProperty('linkedFieldValue', this.value);">
        <option value=""><?php _e('Kies veldnaam', 'prefill-gravity-forms'); ?></option>
        <option value="leeftijd"><?php _e('Leeftijd', 'prefill-gravity-forms'); ?></option>
        <option value="geslachtsaanduiding"><?php _e('Geslachtsaanduiding', 'prefill-gravity-forms'); ?></option>
        <option value="leeftijd"><?php _e('Leeftijd', 'prefill-gravity-forms'); ?></option>
        <optgroup label="Naam">
            <option value="naam.aNummer"><?php _e('aNummer', 'prefill-gravity-forms'); ?></option>
            <option value="naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
            <option value="naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
            <option value="naam.aanschrijfwijze"><?php _e('Aanschrijfwijze', 'prefill-gravity-forms'); ?></option>
            <option value="naam.aanduidingNaamgebruik"><?php _e('AanduidingNaamgebruik', 'prefill-gravity-forms'); ?></option>
        </optgroup>
        <optgroup label="Nationaliteiten">
            <option value="nationaliteiten.datumIngangGeldigheid.datum"><?php _e('Datum', 'prefill-gravity-forms'); ?></option>
            <option value="nationaliteiten.datumIngangGeldigheid.jaar"><?php _e('Jaar', 'prefill-gravity-forms'); ?></option>
            <option value="nationaliteiten.datumIngangGeldigheid.maand"><?php _e('Maand', 'prefill-gravity-forms'); ?></option>
            <option value="nationaliteiten.datumIngangGeldigheid.dag"><?php _e('Dag', 'prefill-gravity-forms'); ?></option>
            <option value="nationaliteiten.nationaliteit.omschrijving"><?php _e('Omschrijving', 'prefill-gravity-forms'); ?></option>
            <option value="nationaliteiten.nationaliteit.code"><?php _e('Code', 'prefill-gravity-forms'); ?></option>
        </optgroup>
    </select>
</li>