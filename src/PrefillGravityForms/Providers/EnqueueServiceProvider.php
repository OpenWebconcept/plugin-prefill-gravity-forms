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

/**
 * Enqueues plugin styles on the front-end and admin.
 *
 * @since 1.4.0
 */
class EnqueueServiceProvider extends ServiceProvider
{
	/**
	 * @since 1.4.0
	 */
	public function register()
	{
		add_action( 'admin_enqueue_scripts', $this->enqueue_icons_styles( ... ) );
		add_action( 'wp_enqueue_scripts', $this->enqueue_styles( ... ) );
	}

	/**
	 * @since 1.4.0
	 */
	public function enqueue_icons_styles(): void
	{
		$path         = $this->plugin->resource_path( 'icons.asset.php' );
		$script_asset = file_exists( $path ) ? require $path : array(
			'dependencies' => array(),
			'version'      => round( microtime( true ) ),
		);

		wp_enqueue_style(
			'owc-pg-icons',
			$this->plugin->resource_url( 'icons.css' ),
			$script_asset['dependencies'],
			$script_asset['version']
		);

		$this->enqueue_styles();
	}

	/**
	 * @since 1.4.0
	 */
	public function enqueue_styles(): void
	{
		$path         = $this->plugin->resource_path( 'style.asset.php' );
		$script_asset = file_exists( $path ) ? require $path : array(
			'dependencies' => array(),
			'version'      => round( microtime( true ) ),
		);

		wp_enqueue_style(
			'owc-pg-styles',
			$this->plugin->resource_url( 'style.css' ),
			$script_asset['dependencies'],
			$script_asset['version']
		);
	}
}
