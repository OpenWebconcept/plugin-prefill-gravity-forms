<li class="owc_pg_prefill_setting field_setting">
	<label for="linkedField" class="section_label">
		<?php _e( 'Automatisch invullen', 'prefill-gravity-forms' ); ?>
	</label>
	<select id="linkedField" onchange="SetFieldProperty('linkedFieldValue', this.value);">
		<option value=""><?php _e( 'Kies veldnaam', 'prefill-gravity-forms' ); ?></option>
		<option value="burgerservicenummer"><?php _e( 'Burgerservicenummer', 'prefill-gravity-forms' ); ?></option>
		<option value="aNummer"><?php _e( 'aNummer', 'prefill-gravity-forms' ); ?></option>
		<option value="geslacht.code"><?php _e( 'Geslacht code', 'prefill-gravity-forms' ); ?></option>
		<option value="geslacht.omschrijving"><?php _e( 'Geslacht omschrijving', 'prefill-gravity-forms' ); ?></option>
		<option value="leeftijd"><?php _e( 'Leeftijd', 'prefill-gravity-forms' ); ?></option>
		<optgroup label="Naam">
			<option value="naam.geslachtsnaam"><?php _e( 'Geslachtsnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.voorletters"><?php _e( 'Voorletters', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.volledigeNaam"><?php _e( 'Volledige naam', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.voornamen"><?php _e( 'Voornamen', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.voorvoegsel"><?php _e( 'Voorvoegsel', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.aanschrijfwijze"><?php _e( 'Aanschrijfwijze', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.aanduidingNaamgebruik.code"><?php _e( 'AanduidingNaamgebruik code', 'prefill-gravity-forms' ); ?></option>
			<option value="naam.aanduidingNaamgebruik.omschrijving"><?php _e( 'AanduidingNaamgebruik omschrijving', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Nationaliteiten">
			<option value="nationaliteiten.0.datumIngangGeldigheid.datum"><?php _e( 'Datum', 'prefill-gravity-forms' ); ?></option>
			<option value="nationaliteiten.0.datumIngangGeldigheid.type"><?php _e( 'Type', 'prefill-gravity-forms' ); ?></option>
			<option value="nationaliteiten.0.datumIngangGeldigheid.langFormaat"><?php _e( 'Lang formaat', 'prefill-gravity-forms' ); ?></option>
			<option value="nationaliteiten.0.nationaliteit.omschrijving"><?php _e( 'Omschrijving', 'prefill-gravity-forms' ); ?></option>
			<option value="nationaliteiten.0.nationaliteit.code"><?php _e( 'Code', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Geboorte">
			<option value="geboorte.datum.datum"><?php _e( 'Geboortedatum', 'prefill-gravity-forms' ); ?></option>
			<option value="geboorte.land.omschrijving"><?php _e( 'Geboorteland', 'prefill-gravity-forms' ); ?></option>
			<option value="geboorte.plaats.omschrijving"><?php _e( 'Geboorteplaats', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Verblijfplaats">
			<option value="verblijfplaats.verblijfadres.officieleStraatnaam"><?php _e( 'Officiële straatnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="verblijfplaats.verblijfadres.korteStraatnaam"><?php _e( 'Korte straatnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="verblijfplaats.verblijfadres.huisnummer"><?php _e( 'Huisnummer', 'prefill-gravity-forms' ); ?></option>
			<option value="verblijfplaats.verblijfadres.huisletter"><?php _e( 'Huisletter', 'prefill-gravity-forms' ); ?></option>
			<option value="verblijfplaats.verblijfadres.postcode"><?php _e( 'Postcode', 'prefill-gravity-forms' ); ?></option>
			<option value="verblijfplaats.verblijfadres.woonplaats"><?php _e( 'Woonplaats', 'prefill-gravity-forms' ); ?></option>
			<option value="adressering.adresregel1"><?php _e( 'Adres', 'prefill-gravity-forms' ); ?></option>
			<option value="adressering.adresregel2"><?php _e( 'Postcode + plaats', 'prefill-gravity-forms' ); ?></option>
			<option value="gemeenteVanInschrijving.omschrijving"><?php _e( 'Gemeente', 'prefill-gravity-forms' ); ?></option>
			<option value="gemeenteVanInschrijving.code"><?php _e( 'Gemeentecode', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Huwelijk/Partnerschap-gegevens">
			<option value="partners.0.soortVerbintenis.omschrijving"><?php _e( 'Soort verbintenis', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.naam.voornamen"><?php _e( 'Voornamen', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.naam.voorvoegsel"><?php _e( 'Voorvoegsel', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.naam.voorletters"><?php _e( 'Voorletters', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.naam.geslachtsnaam"><?php _e( 'Geslachtsnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.geslacht.omschrijving"><?php _e( 'Geslacht', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.geboorte.datum.datum"><?php _e( 'Geboortedatum', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.geboorte.datum.langFormaat"><?php _e( 'Geboortedatum (lang)', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.geboorte.plaats.omschrijving"><?php _e( 'Geboorteplaats', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.geboorte.land.omschrijving"><?php _e( 'Geboorteland', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.datum.datum"><?php _e( 'Datum huwelijk/partnerschap', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.datum.langFormaat"><?php _e( 'Datum huwelijk/partnerschap (lang)', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.plaats.omschrijving"><?php _e( 'Plaats huwelijk/partnerschap', 'prefill-gravity-forms' ); ?></option>
			<option value="partners.0.aangaanHuwelijkPartnerschap.land.omschrijving"><?php _e( 'Land huwelijk/partnerschap', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Ouder 1 (doorgaans de moeder)">
			<option value="ouders.0.geslacht.omschrijving"><?php _e( 'Geslacht', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.naam.voornamen"><?php _e( 'Voornamen', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.naam.voorletters"><?php _e( 'Voorletters', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.naam.voorvoegsel"><?php _e( 'Voorvoegsel', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.naam.geslachtsnaam"><?php _e( 'Geslachtsnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.geboorte.datum.datum"><?php _e( 'Geboortedatum', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.geboorte.datum.langFormaat"><?php _e( 'Geboortedatum (lang)', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.geboorte.plaats.omschrijving"><?php _e( 'Geboorteplaats', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.geboorte.land.omschrijving"><?php _e( 'Geboorteland', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.datumIngangFamilierechtelijkeBetrekking.datum"><?php _e( 'Datum ingang familierechtelijke betrekking', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.0.datumIngangFamilierechtelijkeBetrekking.langFormaat"><?php _e( 'Datum ingang familierechtelijke betrekking (lang)', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Ouder 2 (doorgaans de vader)">
			<option value="ouders.1.geslacht.omschrijving"><?php _e( 'Geslacht', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.naam.voornamen"><?php _e( 'Voornamen', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.naam.voorletters"><?php _e( 'Voorletters', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.naam.voorvoegsel"><?php _e( 'Voorvoegsel', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.naam.geslachtsnaam"><?php _e( 'Geslachtsnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.geboorte.datum.datum"><?php _e( 'Geboortedatum', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.geboorte.datum.langFormaat"><?php _e( 'Geboortedatum (lang)', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.geboorte.plaats.omschrijving"><?php _e( 'Geboorteplaats', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.geboorte.land.omschrijving"><?php _e( 'Geboorteland', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.datumIngangFamilierechtelijkeBetrekking.datum"><?php _e( 'Datum ingang familierechtelijke betrekking', 'prefill-gravity-forms' ); ?></option>
			<option value="ouders.1.datumIngangFamilierechtelijkeBetrekking.langFormaat"><?php _e( 'Datum ingang familierechtelijke betrekking (lang)', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
		<optgroup label="Kinderen (gebruik vaker voor meerdere kinderen)">
			<option value="kinderen.*.naam.voornamen"><?php _e( 'Voornamen', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.naam.voorletters"><?php _e( 'Voorletters', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.naam.voorvoegsel"><?php _e( 'Voorvoegsel', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.naam.geslachtsnaam"><?php _e( 'Geslachtsnaam', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.geboorte.datum.datum"><?php _e( 'Geboortedatum', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.geboorte.datum.langFormaat"><?php _e( 'Geboortedatum (lang)', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.geboorte.plaats.omschrijving"><?php _e( 'Geboorteplaats', 'prefill-gravity-forms' ); ?></option>
			<option value="kinderen.*.geboorte.land.omschrijving"><?php _e( 'Geboorteland', 'prefill-gravity-forms' ); ?></option>
		</optgroup>
	</select>
</li>
