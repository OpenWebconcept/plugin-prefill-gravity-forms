<?php

if (! class_exists('GF_Field')) {
    /**
     * Minimal stub of the Gravity Forms field class for unit tests.
     */
    #[AllowDynamicProperties]
    class GF_Field
    {
        public $type = '';
        public $linkedFieldValue = '';
        public $defaultValue = '';
        public $cssClass = '';
        public $inputs = null;
        public $dateType = '';
        public $displayOnly = false;
    }
}
