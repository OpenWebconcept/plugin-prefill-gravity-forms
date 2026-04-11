<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

namespace OWC\PrefillGravityForms\GravityForms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use function OWC\PrefillGravityForms\Foundation\Helpers\get_supplier;
use OWC\PrefillGravityForms\Traits\ControllerTrait;

/**
 * Handles the pre-render hook to prefill Gravity Forms fields.
 *
 * @since 1.0.0
 */
class GravityForms
{
	use ControllerTrait;

	protected string $supplier  = '';
	protected string $config_id = '';

	/**
	 * @since 1.0.0
	 */
	public function pre_render( array $form ): array
	{
		$this->set_supplier( $form );

		if ( '' === $this->supplier ) {
			return $form;
		}

		return $this->handle_supplier( $form );
	}

	/**
	 * Resolves the active supplier class name and configuration ID from the form settings.
	 * Supports both named configurations (new) and legacy direct supplier slugs (backward compat).
	 *
	 * @since 1.0.0
	 */
	protected function set_supplier( array $form ): void
	{
		$value = $form['owc-form-setting-supplier'] ?? '';

		if ( '' === $value || 'none' === $value ) {
			$this->supplier  = '';
			$this->config_id = '';
			return;
		}

		if ( GravityFormsSettings::is_configuration( $value ) ) {
			$this->config_id = $value;
			$supplier        = GravityFormsSettings::make( $value )->get_supplier();

			if ( 'OpenZaak' === $supplier ) {
				$supplier = 'PinkRoccade';
			}

			$this->supplier = $supplier;
			return;
		}

		// Legacy: value is a supplier slug like 'enable-u-v2'.
		$this->config_id = '';
		$supplier        = get_supplier( $form );

		if ( 'OpenZaak' === $supplier ) {
			$supplier = 'PinkRoccade';
		}

		$this->supplier = $supplier;
	}

	/**
	 * Instantiate the correct controller and delegate form handling.
	 *
	 * @since 1.0.0
	 */
	protected function handle_supplier( array $form ): array
	{
		try {
			$instance = $this->get_controller( $this->supplier, $this->config_id );
		} catch ( Exception $e ) {
			return $form;
		}

		if ( ! method_exists( $instance, 'handle' ) ) {
			return $form;
		}

		return $instance->handle( $form );
	}
}
