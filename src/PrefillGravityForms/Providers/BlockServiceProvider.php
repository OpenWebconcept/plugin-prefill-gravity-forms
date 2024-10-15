<?php

namespace OWC\PrefillGravityForms\Providers;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;

class BlockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
    }

    public function enqueueBlockEditorAssets(): void
    {
        $script_asset_path = $this->plugin->resourcePath('blocks.asset.php');
        $script_asset = file_exists($script_asset_path) ? require ($script_asset_path) : ['dependencies' => [], 'version' => round(microtime(true))];

        wp_register_script(
            'pg-blocks',
            $this->plugin->resourceUrl('blocks.js'),
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_enqueue_script('pg-blocks');
    }
}