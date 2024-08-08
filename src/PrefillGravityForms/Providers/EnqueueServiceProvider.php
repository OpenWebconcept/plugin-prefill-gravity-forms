<?php

namespace OWC\PrefillGravityForms\Providers;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;

class EnqueueServiceProvider extends ServiceProvider
{
    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueIconsStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
    }

    public function enqueueIconsStyles(): void
    {
        $path = $this->plugin->resourcePath('icons.asset.php');
        $scriptAsset = file_exists($path) ? require $path : ['dependencies' => [], 'version' => round(microtime(true))];

        wp_enqueue_style(
            'owc-pg-icons',
            $this->plugin->resourceUrl('icons.css'),
            $scriptAsset['dependencies'],
            $scriptAsset['version']
        );

        $this->enqueueStyles();
    }

    public function enqueueStyles(): void
    {
        $path = $this->plugin->resourcePath('style.asset.php');
        $scriptAsset = file_exists($path) ? require $path : ['dependencies' => [], 'version' => round(microtime(true))];

        wp_enqueue_style(
            'owc-pg-styles',
            $this->plugin->resourceUrl('style.css'),
            $scriptAsset['dependencies'],
            $scriptAsset['version']
        );
    }
}
