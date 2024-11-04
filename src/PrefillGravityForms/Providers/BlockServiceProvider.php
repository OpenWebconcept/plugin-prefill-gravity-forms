<?php

namespace OWC\PrefillGravityForms\Providers;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BlockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
        add_action('init', [$this, 'registerBlocks']);
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

    public function registerBlocks(): void
    {
        $rootPath = $this->plugin->getRootPath() . '/resources/js/blocks';
        $blockFiles = $this->findBlockJsonFiles($rootPath);

        if (! count($blockFiles)) {
            return;
        }

        foreach ($blockFiles as $blockFile) {
            register_block_type($blockFile);
        }
    }

    protected function findBlockJsonFiles(string $directory): array
    {
        $files = [];

        $directoryIterator = new RecursiveDirectoryIterator(
            $directory,
            RecursiveDirectoryIterator::SKIP_DOTS // Ignores the "." and ".." (hidden) directories
        );

        $iterator = new RecursiveIteratorIterator($directoryIterator);

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'block.json') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
