<?php

declare(strict_types=1);

use OWC\PrefillGravityForms\SupplierMigration\V1ToV2FieldMapping;

/**
 * Maps the 'linkedFieldValue' of EnableU (V1) fields to the V2 option.
 * Fields without a V2 counterpart (null or not listed) need to be linked manually after migrating.
 *
 * @since NEXT
 */
return array_merge(V1ToV2FieldMapping::base(), [
    'verblijfplaats.straatnaam' => 'verblijfplaats.verblijfadres.officieleStraatnaam',
    'verblijfplaats.woonplaatsnaam' => 'verblijfplaats.verblijfadres.woonplaats',
]);
