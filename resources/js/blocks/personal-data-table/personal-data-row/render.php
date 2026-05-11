<?php

use OWC\PrefillGravityForms\Services\PersonalDataService;

$retrievedValue = (new PersonalDataService($attributes['selectedSupplier']['value'] ?? ''))->get($attributes['selectedOption']['value'] ?? '', $attributes['goalBinding'] ?? '');
$label = preg_replace('/\s*\(V\d+\)$/', '', $attributes['selectedOption']['label'] ?? '');

if ($attributes['isChildOfTable'] && ! empty($retrievedValue)) : ?>
	<tr>
		<th><?php echo esc_html($label); ?></th>
		<td><?php echo esc_html($retrievedValue); ?></td>
	</tr>
<?php elseif (! empty($retrievedValue)) :
    echo sprintf(
        "<%s>%s</%s>",
        $attributes['htmlElement'] ?? 'p',
        esc_html($retrievedValue),
        $attributes['htmlElement'] ?? 'p'
    );
endif;
