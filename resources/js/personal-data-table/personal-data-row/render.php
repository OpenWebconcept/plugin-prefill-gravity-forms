<?php

use OWC\PrefillGravityForms\Services\PersonalDataService;

if ($attributes['isChildOfTable']) : ?>
	<tr>
		<th><?php echo $attributes['selectedOption']['label'] ?></th>
		<td><?php echo (new PersonalDataService())->get($attributes['selectedOption']['value'])?></td>
	</tr>
<?php else :
    echo sprintf(
        "<%s>%s</%s>",
        $attributes['htmlElement'] ?? 'p',
        (new PersonalDataService())->get($attributes['selectedOption']['value']),
        $attributes['htmlElement'] ?? 'p'
    );
endif;
