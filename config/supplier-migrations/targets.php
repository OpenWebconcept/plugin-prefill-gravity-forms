<?php

declare(strict_types=1);

/**
 * V2 suppliers a V1 supplier can be migrated to, every source can be migrated to every target.
 *
 * The 'block_supplier_label' is stored in the 'selectedSupplier' attribute of the 'personal-data-row' block,
 * it must match the label in resources/js/blocks/personal-data-table/personal-data-row/config/supplierOptions.js.
 *
 * @since NEXT
 */
return [
    'enable-u-v2' => ['block_supplier_label' => 'EnableU V2'],
    'pink-roccade-v2' => ['block_supplier_label' => 'PinkRoccadeV2'],
];
