<?php

return [

    // Service Providers.
    'providers'    => [
        // Global providers.
        OWC\PrefillGravityForms\GravityForms\GravityFormsServiceProvider::class
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
            'type'    => 'plugin',
            'label'   => 'Gravity Forms',
            'version' => '>=2.5.8',
            'file'    => 'gravityforms/gravityforms.php',
        ]
    ]
];
