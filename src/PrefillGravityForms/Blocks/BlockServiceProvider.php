<?php

namespace OWC\PrefillGravityForms\Blocks;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;

class BlockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        add_action('init', fn() => register_block_type($this->plugin->getRootPath() . '/build/personal-data-table'));
        add_action('init', fn() => register_block_type($this->plugin->getRootPath() . '/build/personal-data-table/personal-data-row'));
    }
}
