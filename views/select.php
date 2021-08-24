<?php
/**
 * Select element for custom field
 **/
?>
<li style="display: list-item;">
    <label for="linkedField" class="section_label">
        <?php _e('Automatisch invullen', config('app.text_domain')); ?>
        <?php gform_tooltip('caseCategoryToolTip'); ?>
    </label>
    <select id="linkedField" onchange="SetFieldProperty('linkedFieldValue', this.value);">
        <option value=""><?php _e('Kies veldnaam', config('app.text_domain')); ?></option>
        <option value="leeftijd"><?php _e('Leeftijd', config('app.text_domain')); ?></option>
        <option value="geslachtsaanduiding"><?php _e('Geslachtsaanduiding', config('app.text_domain')); ?></option>
        <option value="leeftijd"><?php _e('Leeftijd', config('app.text_domain')); ?></option>
        <optgroup label="Naam">
            <option value="naam.aNummer"><?php _e('aNummer', config('app.text_domain')); ?></option>
            <option value="naam.voorletters"><?php _e('Voorletters', config('app.text_domain')); ?></option>
            <option value="naam.voornamen"><?php _e('Voornamen', config('app.text_domain')); ?></option>
            <option value="naam.aanschrijfwijze"><?php _e('Aanschrijfwijze', config('app.text_domain')); ?></option>
            <option value="naam.aanduidingNaamgebruik"><?php _e('AanduidingNaamgebruik', config('app.text_domain')); ?></option>
        </optgroup>
        <optgroup label="Nationaliteiten">
            <option value="nationaliteiten.datumIngangGeldigheid.datum"><?php _e('Datum', config('app.text_domain')); ?></option>
            <option value="nationaliteiten.datumIngangGeldigheid.jaar"><?php _e('Jaar', config('app.text_domain')); ?></option>
            <option value="nationaliteiten.datumIngangGeldigheid.maand"><?php _e('Maand', config('app.text_domain')); ?></option>
            <option value="nationaliteiten.datumIngangGeldigheid.dag"><?php _e('Dag', config('app.text_domain')); ?></option>
            <option value="nationaliteiten.nationaliteit.omschrijving"><?php _e('Omschrijving', config('app.text_domain')); ?></option>
            <option value="nationaliteiten.nationaliteit.code"><?php _e('Code', config('app.text_domain')); ?></option>
        </optgroup>
    </select>
</li>