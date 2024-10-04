<?php

return [
    'providers' => [
        // Global providers.
        OWC\PrefillGravityForms\Providers\GravityFormsServiceProvider::class,
        OWC\PrefillGravityForms\Providers\BlockServiceProvider::class,
        OWC\PrefillGravityForms\Providers\EnqueueServiceProvider::class,
    ],

    /**
     * Dependencies upon which the plugin relies.
     *
     * Required: type, label
     * Optional: message
     *
     * Type: plugin
     * - Required: file
     * - Optional: version
     *
     * Type: class
     * - Required: name
     */
    'dependencies' => [
        [
            'type' => 'plugin',
            'label' => 'Gravity Forms',
            'version' => '>=2.5.8',
            'file' => 'gravityforms/gravityforms.php',
        ],
        [
            'type' => 'class',
            'name' => \OWC\IdpUserData\DigiDUserDataInterface::class,
        ],
    ],
    'text_domain' => PG_PLUGIN_SLUG,
];
