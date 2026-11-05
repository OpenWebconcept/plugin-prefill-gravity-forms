<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\SupplierMigration;

/**
 * Field mappings from HaalCentraal BRP 1.x (V1) to HaalCentraal BRP 2.x (V2) that are shared by all V1 suppliers.
 *
 * The supplier specific mappings live in config/supplier-migrations/sources and extend the base mapping.
 * A value of null means there is no V2 counterpart, these fields need to be linked manually after migrating.
 *
 * @since NEXT
 */
final class V1ToV2FieldMapping
{
    /**
     * Mapping shared by all V1 suppliers, including the embedded parents and children.
     */
    public static function base(): array
    {
        $fields = [
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

        foreach (['ouders.0', 'ouders.1', 'kinderen.*'] as $relative) {
            $fields += self::prefix(self::relative(), "_embedded.{$relative}", $relative);

            // Children do not have a gender attribute in the V2 mapping options.
            $fields["_embedded.{$relative}.geslachtsaanduiding"] = 'kinderen.*' === $relative ? null : "{$relative}.geslacht.omschrijving";
        }

        return $fields;
    }

    /**
     * Attributes of an embedded relative (parent, child or partner), which share the same structure in V1.
     */
    public static function relative(): array
    {
        return [
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
    }

    /**
     * Prefix the V1 keys and the V2 values of a mapping, values without a V2 counterpart remain null.
     */
    public static function prefix(array $mapping, string $v1Prefix, string $v2Prefix): array
    {
        $prefixed = [];

        foreach ($mapping as $v1 => $v2) {
            $prefixed["{$v1Prefix}.{$v1}"] = null === $v2 ? null : "{$v2Prefix}.{$v2}";
        }

        return $prefixed;
    }
}
