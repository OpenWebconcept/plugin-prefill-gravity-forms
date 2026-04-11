<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

return array(
	'providers'   => array(
		// Global providers.
		OWC\PrefillGravityForms\Providers\GravityFormsServiceProvider::class,
		OWC\PrefillGravityForms\Providers\BlockServiceProvider::class,
		OWC\PrefillGravityForms\Providers\EnqueueServiceProvider::class,
	),
	'text_domain' => PG_PLUGIN_SLUG,
);
