<?php
/**
 * Select element for custom field
 */
?>
<li class="label_setting field_setting">
    <label for="linkedField" class="section_label">
        <?php _e('Automatisch invullen', 'prefill-gravity-forms'); ?>
    </label>
    <select id="linkedField" onchange="SetFieldProperty('linkedFieldValue', this.value);">
        <option value=""><?php _e('Kies veldnaam', 'prefill-gravity-forms'); ?></option>
        <option value="burgerservicenummer"><?php _e('Burgerservicenummer', 'prefill-gravity-forms'); ?></option>
        <option value="aNummer"><?php _e('aNummer', 'prefill-gravity-forms'); ?></option>
        <option value="geslachtsaanduiding"><?php _e('Geslachtsaanduiding', 'prefill-gravity-forms'); ?></option>
        <option value="leeftijd"><?php _e('Leeftijd', 'prefill-gravity-forms'); ?></option>
        <optgroup label="Naam">
            <option value="naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
            <option value="naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
            <option value="naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
            <option value="naam.voorvoegsel"><?php _e('Voorvoegsel', 'prefill-gravity-forms'); ?></option>
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
        <optgroup label="Geboorte">
            <option value="geboorte.datum.datum"><?php _e('Geboortedatum', 'prefill-gravity-forms'); ?></option>
            <option value="geboorte.land.omschrijving"><?php _e('Geboorteland', 'prefill-gravity-forms'); ?></option>
            <option value="geboorte.plaats.omschrijving"><?php _e('Geboorteplaats', 'prefill-gravity-forms'); ?></option>
        </optgroup>
        <optgroup label="Verblijftplaats">
            <option value="verblijfplaats.straat"><?php _e('Straat', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.huisnummer"><?php _e('Huisnummer', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.huisletter"><?php _e('Huisletter', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.postcode"><?php _e('Postcode', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.woonplaats"><?php _e('Woonplaats', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.adresregel1"><?php _e('Adres', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.adresregel2"><?php _e('Postcode + plaats', 'prefill-gravity-forms'); ?></option>
        </optgroup>
        <optgroup label="Huwelijk/Partnerschap-gegevens">
            <option value="_embedded.partners.soortVerbintenis"><?php _e('Soort verbintenis', 'prefill-gravity-forms'); ?></option>
            <option value="_embedded.partners.naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
            <option value="_embedded.partners.naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
        </optgroup>
    </select>
</li>
