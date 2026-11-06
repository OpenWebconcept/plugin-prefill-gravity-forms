<?php

declare(strict_types=1);

/**
 * Migrations from a V1 (HaalCentraal BRP 1.x) supplier to a V2 (HaalCentraal BRP 2.x) supplier.
 *
 * Every supplier in 'sources' can be migrated to every supplier in 'targets', e.g. VrijBRP to EnableU V2.
 * The V2 suppliers share the same field options, so the field mapping only depends on the source.
 *
 * 'sources' maps the 'linkedFieldValue' of a Gravity Forms field from the V1 option to the V2 option.
 * A value of null means there is no V2 counterpart, these fields need to be linked manually after migrating.
 * Values that are not listed are treated as having no counterpart as well.
 *
 * 'targets' contains the 'block_supplier_label' which is stored in the 'selectedSupplier' attribute of the block,
 * it must match the label in resources/js/blocks/personal-data-table/personal-data-row/config/supplierOptions.js.
 *
 * 'block_options' maps the 'selectedOption' attribute of the 'personal-data-row' block. Options that are not
 * listed remain unchanged, these are translated on runtime by the PersonalDataService. The mapped values must be
 * available in resources/js/blocks/personal-data-table/personal-data-row/config/personalDataOptions.js.
 */

$v1ToV2Fields = [
    'burgerservicenummer' => 'burgerservicenummer',
    'aNummer' => 'aNummer',
    'geslachtsaanduiding' => 'geslacht.omschrijving',
    'leeftijd' => 'leeftijd',

    'naam.geslachtsnaam' => 'naam.geslachtsnaam',
    'naam.voorletters' => 'naam.voorletters',
    'naam.voornamen' => 'naam.voornamen',
    'naam.voorvoegsel' => 'naam.voorvoegsel',
    'naam.aanschrijfwijze' => 'naam.aanschrijfwijze',
    'naam.aanduidingNaamgebruik' => 'naam.aanduidingNaamgebruik.omschrijving',

    'nationaliteiten.datumIngangGeldigheid.datum' => 'nationaliteiten.0.datumIngangGeldigheid.datum',
    'nationaliteiten.datumIngangGeldigheid.jaar' => null,
    'nationaliteiten.datumIngangGeldigheid.maand' => null,
    'nationaliteiten.datumIngangGeldigheid.dag' => null,
    'nationaliteiten.nationaliteit.omschrijving' => 'nationaliteiten.0.nationaliteit.omschrijving',
    'nationaliteiten.nationaliteit.code' => 'nationaliteiten.0.nationaliteit.code',

    'geboorte.datum.datum' => 'geboorte.datum.datum',
    'geboorte.land.omschrijving' => 'geboorte.land.omschrijving',
    'geboorte.plaats.omschrijving' => 'geboorte.plaats.omschrijving',

    'verblijfplaats.huisnummer' => 'verblijfplaats.verblijfadres.huisnummer',
    'verblijfplaats.huisletter' => 'verblijfplaats.verblijfadres.huisletter',
    'verblijfplaats.postcode' => 'verblijfplaats.verblijfadres.postcode',
    'verblijfplaats.adresregel1' => 'adressering.adresregel1',
    'verblijfplaats.adresregel2' => 'adressering.adresregel2',
    'verblijfplaats.gemeenteVanInschrijving.omschrijving' => 'gemeenteVanInschrijving.omschrijving',
    'verblijfplaats.gemeenteVanInschrijving.code' => 'gemeenteVanInschrijving.code',

    '_embedded.partners.soortVerbintenis' => 'partners.0.soortVerbintenis.omschrijving',
    '_embedded.partners.naam.geslachtsnaam' => 'partners.0.naam.geslachtsnaam',
    '_embedded.partners.naam.voornamen' => 'partners.0.naam.voornamen',
];

// Parents and children share the same structure in V1, in V2 most of these attributes are available as well.
$v1ToV2Relatives = [
    'burgerservicenummer' => null,
    'naam.geslachtsnaam' => 'naam.geslachtsnaam',
    'naam.voorletters' => 'naam.voorletters',
    'naam.voornamen' => 'naam.voornamen',
    'naam.voorvoegsel' => 'naam.voorvoegsel',
    'geboorte.datum.datum' => 'geboorte.datum.datum',
    'geboorte.datum.dag' => null,
    'geboorte.datum.maand' => null,
    'geboorte.plaats.code' => null,
    'geboorte.plaats.omschrijving' => 'geboorte.plaats.omschrijving',
    'geboorte.land.code' => null,
    'geboorte.land.omschrijving' => 'geboorte.land.omschrijving',
];

foreach (['ouders.0', 'ouders.1', 'kinderen.*'] as $relative) {
    foreach ($v1ToV2Relatives as $v1 => $v2) {
        $v1ToV2Fields["_embedded.{$relative}.{$v1}"] = null === $v2 ? null : "{$relative}.{$v2}";
    }

    // Children do not have a gender attribute in the V2 mapping options.
    $v1ToV2Fields["_embedded.{$relative}.geslachtsaanduiding"] = 'kinderen.*' === $relative ? null : "{$relative}.geslacht.omschrijving";
}

$pinkRoccadeFields = array_merge($v1ToV2Fields, [
    'verblijfplaats.straat' => 'verblijfplaats.verblijfadres.officieleStraatnaam',
    'verblijfplaats.woonplaats' => 'verblijfplaats.verblijfadres.woonplaats',
    'verblijfplaats.huisnummertoevoeging' => null,
]);

// VrijBRP has a single partner, which is available with most of its attributes in V2 as well.
$vrijBrpPartner = [
    'burgerservicenummer' => null,
    'soortVerbintenis' => 'soortVerbintenis.omschrijving',
    'aangaanHuwelijkPartnerschap.datum.datum' => 'aangaanHuwelijkPartnerschap.datum.datum',
    'aangaanHuwelijkPartnerschap.datum.dag' => null,
    'aangaanHuwelijkPartnerschap.datum.maand' => null,
    'aangaanHuwelijkPartnerschap.datum.jaar' => null,
    'aangaanHuwelijkPartnerschap.land.code' => null,
    'aangaanHuwelijkPartnerschap.land.omschrijving' => 'aangaanHuwelijkPartnerschap.land.omschrijving',
    'aangaanHuwelijkPartnerschap.plaats.code' => null,
    'aangaanHuwelijkPartnerschap.plaats.omschrijving' => 'aangaanHuwelijkPartnerschap.plaats.omschrijving',
] + $v1ToV2Relatives;

$vrijBrpFields = array_merge($v1ToV2Fields, [
    'naam.gebruikInLopendeTekst' => null,
    'naam.aanduidingNaamgebruik.code' => 'naam.aanduidingNaamgebruik.code',
    'naam.aanduidingNaamgebruik.omschrijving' => 'naam.aanduidingNaamgebruik.omschrijving',
    'nationaliteiten.0.nationaliteit.omschrijving' => 'nationaliteiten.0.nationaliteit.omschrijving',
    'nationaliteiten.0.nationaliteit.code' => 'nationaliteiten.0.nationaliteit.code',
    'geboorte.datum.dag' => null,
    'geboorte.datum.maand' => null,
    'geboorte.plaats.code' => null,
    'geboorte.land.code' => null,
    'verblijfplaats.straat' => 'verblijfplaats.verblijfadres.officieleStraatnaam',
    'verblijfplaats.straatnaam' => 'verblijfplaats.verblijfadres.officieleStraatnaam',
    'verblijfplaats.woonplaats' => 'verblijfplaats.verblijfadres.woonplaats',
    'verblijfplaats.woonplaatsnaam' => 'verblijfplaats.verblijfadres.woonplaats',
]);

foreach ($vrijBrpPartner as $v1 => $v2) {
    $vrijBrpFields["_embedded.partners.0.{$v1}"] = null === $v2 ? null : "partners.0.{$v2}";
}

return [
    'sources' => [
        'enable-u' => array_merge($v1ToV2Fields, [
            'verblijfplaats.straatnaam' => 'verblijfplaats.verblijfadres.officieleStraatnaam',
            'verblijfplaats.woonplaatsnaam' => 'verblijfplaats.verblijfadres.woonplaats',
        ]),
        'pink-roccade' => $pinkRoccadeFields,
        'vrij-brp' => $vrijBrpFields,
    ],
    'targets' => [
        'enable-u-v2' => ['block_supplier_label' => 'EnableU V2'],
        'pink-roccade-v2' => ['block_supplier_label' => 'PinkRoccadeV2'],
    ],
    'block_options' => [
        'verblijfplaats.straat' => ['value' => 'verblijfplaats.verblijfadres.officieleStraatnaam', 'label' => 'Straat'],
        'verblijfplaats.straatnaam' => ['value' => 'verblijfplaats.verblijfadres.officieleStraatnaam', 'label' => 'Straat'],
        'verblijfplaats.woonplaats' => ['value' => 'verblijfplaats.verblijfadres.woonplaats', 'label' => 'Woonplaats'],
        'verblijfplaats.woonplaatsnaam' => ['value' => 'verblijfplaats.verblijfadres.woonplaats', 'label' => 'Woonplaats'],
        'naam.aanduidingNaamgebruik' => ['value' => 'naam.aanduidingNaamgebruik.omschrijving', 'label' => 'Aanduiding Naamgebruik'],
    ],
];
