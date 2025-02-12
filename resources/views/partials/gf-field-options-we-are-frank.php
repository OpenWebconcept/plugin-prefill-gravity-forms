<li class="owc_pg_prefill_setting field_setting">
    <label for="linkedField" class="section_label">
        <?php _e('Automatisch invullen', 'prefill-gravity-forms'); ?>
    </label>
    <select id="linkedField" onchange="SetFieldProperty('linkedFieldValue', this.value);">
        <option value=""><?php _e('Kies veldnaam', 'prefill-gravity-forms'); ?></option>
		<option value="burgerservicenummer"><?php _e('Burgerservicenummer', 'prefill-gravity-forms'); ?></option>
		<option value="aNummer"><?php _e('aNummer', 'prefill-gravity-forms'); ?></option>
		<option value="leeftijd"><?php _e('Leeftijd', 'prefill-gravity-forms'); ?></option>
        <optgroup label="Naam">
			<option value="naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
			<option value="naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
			<option value="naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
			<option value="naam.voorvoegsel"><?php _e('Voorvoegsel', 'prefill-gravity-forms'); ?></option>
			<option value="naam.aanduidingNaamgebruik.omschrijving"><?php _e('Aanduiding naamgebruik', 'prefill-gravity-forms'); ?></option>
			<option value="naam.adellijkeTitelPredicaat.omschrijving"><?php _e('Adellijke Titel', 'prefill-gravity-forms'); ?></option>
			<option value="naam.volledigeNaam"><?php _e('Volledige naam', 'prefill-gravity-forms'); ?></option>
    	</optgroup>
		<optgroup label="Geslacht">
        	<option value="geslacht.code"><?php _e('Geslacht code', 'prefill-gravity-forms'); ?></option>
        	<option value="geslacht.omschrijving"><?php _e('Geslacht omschrijving', 'prefill-gravity-forms'); ?></option>
    	</optgroup>
		<optgroup label="Nationaliteiten">
        	<option value="nationaliteiten.0.nationaliteit.omschrijving"><?php _e('Nationaliteit omschrijving', 'prefill-gravity-forms'); ?></option>
        	<option value="nationaliteiten.0.nationaliteit.code"><?php _e('Nationaliteit code', 'prefill-gravity-forms'); ?></option>
    	</optgroup>
		<optgroup label="Geboorte">
        	<option value="geboorte.datum.datum"><?php _e('Geboortedatum', 'prefill-gravity-forms'); ?></option>
        	<option value="geboorte.plaats.omschrijving"><?php _e('Geboorteplaats omschrijving', 'prefill-gravity-forms'); ?></option>
        	<option value="geboorte.plaats.code"><?php _e('Geboorteplaats code', 'prefill-gravity-forms'); ?></option>
        	<option value="geboorte.land.omschrijving"><?php _e('Geboorteland omschrijving', 'prefill-gravity-forms'); ?></option>
        	<option value="geboorte.land.code"><?php _e('Geboorteland code', 'prefill-gravity-forms'); ?></option>
    	</optgroup>
        <optgroup label="Verblijfplaats">
            <option value="verblijfplaats.verblijfadres.officieleStraatnaam"><?php _e('Straat', 'prefill-gravity-forms'); ?></option>
			<option value="verblijfplaats.verblijfadres.huisnummer"><?php _e('Huisnummer', 'prefill-gravity-forms'); ?></option>
			<option value="verblijfplaats.verblijfadres.huisletter"><?php _e('Huisletter', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.verblijfadres.postcode"><?php _e('Postcode', 'prefill-gravity-forms'); ?></option>
            <option value="verblijfplaats.verblijfadres.woonplaats"><?php _e('Woonplaats', 'prefill-gravity-forms'); ?></option>
            <option value="gemeenteVanInschrijving.omschrijving"><?php _e('Gemeente', 'prefill-gravity-forms'); ?></option>
            <option value="gemeenteVanInschrijving.code"><?php _e('Gemeentecode', 'prefill-gravity-forms'); ?></option>
        </optgroup>
        <optgroup label="Huwelijk/Partnerschap-gegevens">
			<option value="partners.0.burgerservicenummer"><?php _e('Burgerservicenummer', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
            <option value="partners.0.naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.naam.voorvoegsel"><?php _e('Voorvoegsel', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.datum.datum"><?php _e('Geboortedatum', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.datum.dag"><?php _e('Geboortedag', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.datum.maand"><?php _e('Geboortemaand', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.plaats.code"><?php _e('Geboorteplaats code', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.plaats.omschrijving"><?php _e('Geboorteplaats', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.land.code"><?php _e('Geboorteland code', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.geboorte.land.omschrijving"><?php _e('Geboorteland', 'prefill-gravity-forms'); ?></option>
            <option value="partners.0.soortVerbintenis"><?php _e('Soort verbintenis', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.datum.datum"><?php _e('Verbintenis datum', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.datum.dag"><?php _e('Verbintenis dag', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.datum.maand"><?php _e('Verbintenis maand', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.datum.jaar"><?php _e('Verbintenis jaar', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.land.code"><?php _e('Verbintenis land code', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.land.omschrijving"><?php _e('Verbintenis land', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.plaats.code"><?php _e('Verbintenis plaats code', 'prefill-gravity-forms'); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.plaats.omschrijving"><?php _e('Verbintenis plaats', 'prefill-gravity-forms'); ?></option>
        </optgroup>
		<optgroup label="Ouder 1 (doorgaans de moeder)">
			<option value="ouders.0.burgerservicenummer"><?php _e('Burgerservicenummer', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.geslacht.omschrijving"><?php _e('Geslachtsaanduiding', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
            <option value="ouders.0.naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.naam.voorvoegsel"><?php _e('Voorvoegsel', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.geboorte.datum.datum"><?php _e('Geboortedatum', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.geboorte.plaats.omschrijving"><?php _e('Geboorteplaats', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.geboorte.land.code"><?php _e('Geboorteland code', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.0.geboorte.land.omschrijving"><?php _e('Geboorteland', 'prefill-gravity-forms'); ?></option>
		</optgroup>
		<optgroup label="Ouder 2 (doorgaans de vader)">
			<option value="ouders.1.burgerservicenummer"><?php _e('Burgerservicenummer', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.geslacht.omschrijving"><?php _e('Geslachtsaanduiding', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
            <option value="ouders.1.naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.naam.voorvoegsel"><?php _e('Voorvoegsel', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.geboorte.datum.datum"><?php _e('Geboortedatum', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.geboorte.plaats.omschrijving"><?php _e('Geboorteplaats', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.geboorte.land.code"><?php _e('Geboorteland code', 'prefill-gravity-forms'); ?></option>
			<option value="ouders.1.geboorte.land.omschrijving"><?php _e('Geboorteland', 'prefill-gravity-forms'); ?></option>
		</optgroup>
		<optgroup label="Kinderen (gebruik vaker voor meerdere kinderen)">
			<option value="kinderen.*.burgerservicenummer"><?php _e('Burgerservicenummer', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geslachtsaanduiding"><?php _e('Geslachtsaanduiding', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.naam.geslachtsnaam"><?php _e('Geslachtsnaam', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.naam.voorletters"><?php _e('Voorletters', 'prefill-gravity-forms'); ?></option>
            <option value="kinderen.*.naam.voornamen"><?php _e('Voornamen', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.naam.voorvoegsel"><?php _e('Voorvoegsel', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.datum.datum"><?php _e('Geboortedatum', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.datum.dag"><?php _e('Geboortedag', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.datum.maand"><?php _e('Geboortemaand', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.plaats.code"><?php _e('Geboorteplaats code', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.plaats.omschrijving"><?php _e('Geboorteplaats', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.land.code"><?php _e('Geboorteland code', 'prefill-gravity-forms'); ?></option>
			<option value="kinderen.*.geboorte.land.omschrijving"><?php _e('Geboorteland', 'prefill-gravity-forms'); ?></option>
		</optgroup>
    </select>
</li>
