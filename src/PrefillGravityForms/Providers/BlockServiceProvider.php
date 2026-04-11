<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.4.0
 */

namespace OWC\PrefillGravityForms\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use OWC\PrefillGravityForms\Foundation\ServiceProvider;
use WP_Block_Editor_Context;

/**
 * Registers Gutenberg blocks and block categories.
 *
 * @since 1.4.0
 */
class BlockServiceProvider extends ServiceProvider
{
	public const BLOCK_CATEGORY = 'owc-prefill-gravityforms';

	/**
	 * @since 1.4.0
	 */
	public function register(): void
	{
		add_filter( 'block_categories_all', $this->register_block_category( ... ), 10, 2 );
		add_action( 'init', $this->register_blocks( ... ) );
	}

	/**
	 * @since 1.4.0
	 */
	public function register_block_category(array $block_categories, WP_Block_Editor_Context $block_editor_context ): array
	{
		$block_categories[] = array(
			'slug'  => self::BLOCK_CATEGORY,
			'title' => 'OWC Prefill GravityForms',
		);

		return $block_categories;
	}

	/**
	 * @since 1.4.0
	 */
	public function register_blocks(): void
	{
		$block_files = array(
			$this->plugin->resource_path( 'personal-data-table' ),
			$this->plugin->resource_path( 'personal-data-table/personal-data-row' ),
		);

		foreach ( $block_files as $block_file ) {
			register_block_type(
				$block_file,
				array(
					'category' => self::BLOCK_CATEGORY,
				)
			);
		}
	}
}
