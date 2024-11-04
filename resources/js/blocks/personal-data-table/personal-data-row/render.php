<?php

use OWC\PrefillGravityForms\Services\PersonalDataService;

$retrievedValue = (new PersonalDataService($attributes['selectedSupplier']['value'] ?? ''))->get($attributes['selectedOption']['value'] ?? '');

if ($attributes['isChildOfTable']) : ?>
	<tr>
		<th><?php echo $attributes['selectedOption']['label']; ?></th>
		<td><?php echo $retrievedValue; ?></td>
	</tr>
<?php else :
    echo sprintf(
        "<%s>%s</%s>",
        $attributes['htmlElement'] ?? 'p',
        $retrievedValue,
        $attributes['htmlElement'] ?? 'p'
    );
endif;
