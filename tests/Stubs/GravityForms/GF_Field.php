<?php

if (! class_exists('GF_Field')) {
    #[AllowDynamicProperties]
    class GF_Field
    {
        public string $type = '';
        public string $cssClass = '';
        public $defaultValue = '';

        public function __construct(array $properties = [])
        {
            foreach ($properties as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}
