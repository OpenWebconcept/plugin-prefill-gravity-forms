<?php

declare(strict_types=1);

use OWC\PrefillGravityForms\SupplierMigration\V1ToV2FieldMapping;

/**
 * Maps the 'linkedFieldValue' of VrijBRP (V1) fields to the V2 option.
 * Fields without a V2 counterpart (null or not listed) need to be linked manually after migrating.
 *
 * @since NEXT
 */

// VrijBRP has a single partner, which is available with most of its attributes in V2 as well.
$partner = [
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
] + V1ToV2FieldMapping::relative();

return array_merge(
    V1ToV2FieldMapping::base(),
    [
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
    ],
    V1ToV2FieldMapping::prefix($partner, '_embedded.partners.0', 'partners.0')
);
