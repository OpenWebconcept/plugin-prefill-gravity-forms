<?php

namespace OWC\PrefillGravityForms\Providers;

use OWC\PrefillGravityForms\Foundation\ServiceProvider;
use WP_Block_Editor_Context;

class BlockServiceProvider extends ServiceProvider
{
    public const BLOCK_CATEGORY = 'owc-prefill-gravityforms';

    public function register(): void
    {
        add_filter('block_categories_all', $this->registerBlockCategory(...), 10, 2);
        add_action('init', $this->registerBlocks(...));
    }

    public function registerBlockCategory(array $blockCategories, WP_Block_Editor_Context $block_editor_context): array
    {
        $blockCategories[] = [
            'slug' => self::BLOCK_CATEGORY,
            'title' => 'OWC Prefill GravityForms',
        ];

        return $blockCategories;
    }

    public function registerBlocks(): void
    {
        $blockFiles = [
            $this->plugin->resourcePath('personal-data-table'),
            $this->plugin->resourcePath('personal-data-table/personal-data-row')
        ];

        foreach ($blockFiles as $blockFile) {
            register_block_type($blockFile, [
                'category' => self::BLOCK_CATEGORY,
            ]);
        }
    }
}
