<?php

declare(strict_types=1);

/**
 * Maps the 'selectedOption' attribute of the 'personal-data-row' block from the V1 to the V2 option.
 *
 * Options that are not listed remain unchanged, these are translated on runtime by the PersonalDataService.
 * The mapped values must be available in resources/js/blocks/personal-data-table/personal-data-row/config/personalDataOptions.js.
 *
 * @since NEXT
 */
return [
    'verblijfplaats.straat' => ['value' => 'verblijfplaats.verblijfadres.officieleStraatnaam', 'label' => 'Straat'],
    'verblijfplaats.straatnaam' => ['value' => 'verblijfplaats.verblijfadres.officieleStraatnaam', 'label' => 'Straat'],
    'verblijfplaats.woonplaats' => ['value' => 'verblijfplaats.verblijfadres.woonplaats', 'label' => 'Woonplaats'],
    'verblijfplaats.woonplaatsnaam' => ['value' => 'verblijfplaats.verblijfadres.woonplaats', 'label' => 'Woonplaats'],
    'naam.aanduidingNaamgebruik' => ['value' => 'naam.aanduidingNaamgebruik.omschrijving', 'label' => 'Aanduiding Naamgebruik'],
];
