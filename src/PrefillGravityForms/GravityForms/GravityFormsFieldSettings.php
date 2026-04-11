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

use GFAPI;
use function OWC\PrefillGravityForms\Foundation\Helpers\get_supplier;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use OWC\PrefillGravityForms\Foundation\View;

/**
 * Handles custom field settings in the Gravity Forms editor.
 *
 * @since 1.0.0
 */
class GravityFormsFieldSettings
{
	/**
	 * @since 1.0.0
	 */
	public static function add_select_script(): void
	{
		echo view( 'gf-script-custom-field-settings.php' );
	}

	/**
	 * Add custom select to Gravity Form fields.
	 * Used for mapping a form field to a supplier setting.
	 *
	 * @since 1.0.0
	 */
	public static function add_supplier_prefill_options_select( $position, $form_id ): void
	{
		if ( ! class_exists( 'GFAPI' ) ) {
			return;
		}

		$form  = GFAPI::get_form( $form_id );
		$value = $form['owc-form-setting-supplier'] ?? '';

		if ( GravityFormsSettings::is_configuration( $value ) ) {
			// Named configuration: read the supplier slug from the config.
			$supplier = GravityFormsSettings::make( $value )->get( 'supplier' );
		} else {
			// Legacy: value is the supplier slug directly.
			$supplier = get_supplier( $form, true );
		}

		if ( 0 !== $position || '' === $supplier ) {
			return;
		}

		$theme_mapping_options = (string) ( $form['owc-iconnect-theme-mapping-options-file'] ?? '0' );

		// Check if the theme mapping options file exists.
		if ( '0' !== $theme_mapping_options && is_file( $theme_mapping_options ) ) {
			echo ( new View() )->render_full_path( $theme_mapping_options );

			return;
		}

		// Render the supplier based options.
		$mapping_options = sprintf( 'partials/gf-field-options-%s.php', $supplier );

		echo view( $mapping_options );
	}
}
