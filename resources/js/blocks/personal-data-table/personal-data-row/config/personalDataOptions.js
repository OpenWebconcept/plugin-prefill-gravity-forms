/**
 * Options without a version are available for every supplier, these are translated on runtime for V2 suppliers
 * by the PersonalDataService. Options with a version are only available for suppliers of that API version.
 */
const personalDataOptions = [
	{
		value: '',
		label: 'Selecteer persoonsgegevens',
		disabled: true,
	},
	{
		label: 'Burgerservicenummer (BSN)',
		value: 'burgerservicenummer',
	},
	{
		label: 'Geslacht',
		value: 'geslachtsaanduiding',
	},
	{
		label: 'Leeftijd',
		value: 'leeftijd',
	},
	{
		label: 'Achternaam',
		value: 'naam.geslachtsnaam',
	},
	{
		label: 'Voorvoegsel (tussenvoegsel)',
		value: 'naam.voorvoegsel',
	},
	{
		label: 'Voorletters',
		value: 'naam.voorletters',
	},
	{
		label: 'Voornaam',
		value: 'naam.voornaam',
	},
	{
		label: 'Voornamen',
		value: 'naam.voornamen',
	},
	{
		label: 'Aanduiding Naamgebruik',
		value: 'naam.aanduidingNaamgebruik',
		version: '1',
	},
	{
		label: 'Aanduiding Naamgebruik',
		value: 'naam.aanduidingNaamgebruik.omschrijving',
		version: '2',
	},
	{
		label: 'Aanschrijfwijze',
		value: 'naam.aanschrijfwijze',
	},
	{
		label: 'Gebruik in lopende tekst',
		value: 'naam.gebruikInLopendeTekst',
	},
	{
		label: 'Geboortedatum',
		value: 'geboorte.datum.datum',
	},
	{
		label: 'Straat',
		value: 'verblijfplaats.straat',
		version: '1',
	},
	{
		label: 'Straatnaam',
		value: 'verblijfplaats.straatnaam',
		version: '1',
	},
	{
		label: 'Straat',
		value: 'verblijfplaats.verblijfadres.officieleStraatnaam',
		version: '2',
	},
	{
		label: 'Huisnummer',
		value: 'verblijfplaats.huisnummer',
	},
	{
		label: 'Huisnummertoevoeging',
		value: 'verblijfplaats.huisletter',
	},
	{
		label: 'Postcode',
		value: 'verblijfplaats.postcode',
	},
	{
		label: 'Woonplaats',
		value: 'verblijfplaats.woonplaats',
		version: '1',
	},
	{
		label: 'Woonplaats naam',
		value: 'verblijfplaats.woonplaatsnaam',
		version: '1',
	},
	{
		label: 'Woonplaats',
		value: 'verblijfplaats.verblijfadres.woonplaats',
		version: '2',
	},
];

export default personalDataOptions;
