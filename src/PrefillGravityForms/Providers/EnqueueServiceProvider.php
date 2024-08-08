<?php

namespace OWC\PrefillGravityForms\Providers;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;

class EnqueueServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->plugin->loader->addAction('admin_enqueue_scripts', $this, 'enqueueIconsStyles');
        $this->plugin->loader->addAction('wp_enqueue_scripts', $this, 'enqueueStyles');
    }

    public function enqueueIconsStyles(): void
    {
        $path = $this->plugin->resourcePath('icons.asset.php');
        $scriptAsset = file_exists($path) ? require $path : ['dependencies' => [], 'version' => round(microtime(true))];

        wp_enqueue_style(
            'pg-icons',
            $this->plugin->resourceUrl('icons.css'),
            $scriptAsset['dependencies'],
            $scriptAsset['version']
        );
    }

    public function enqueueStyles(): void
    {
        $path = $this->plugin->resourcePath('style.asset.php');
        $scriptAsset = file_exists($path) ? require $path : ['dependencies' => [], 'version' => round(microtime(true))];

        wp_enqueue_style(
            'owc-gfa-styles',
            $this->plugin->resourceUrl('style.css'),
            $scriptAsset['dependencies'],
            $scriptAsset['version']
        );
    }
}
