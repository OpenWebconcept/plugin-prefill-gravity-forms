<?php

return [
    'providers' => [
        // Global providers.
        OWC\PrefillGravityForms\Providers\GravityFormsServiceProvider::class,
        OWC\PrefillGravityForms\Providers\BlockServiceProvider::class,
        OWC\PrefillGravityForms\Providers\EnqueueServiceProvider::class,
    ],
    'text_domain' => PG_PLUGIN_SLUG,
];
