<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

namespace OWC\PrefillGravityForms\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use GF_Fields;
use GFAddOn;
use GFForms;
use function OWC\PrefillGravityForms\Foundation\Helpers\config;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use OWC\PrefillGravityForms\Foundation\ServiceProvider;
use OWC\PrefillGravityForms\GravityForms\GravityForms;
use OWC\PrefillGravityForms\GravityForms\GravityFormsAddon;
use OWC\PrefillGravityForms\GravityForms\GravityFormsFieldSettings;
use OWC\PrefillGravityForms\GravityForms\GravityFormsFormSettings;

/**
 * Registers all Gravity Forms related hooks, fields, and add-on settings.
 *
 * @since 1.0.0
 */
class GravityFormsServiceProvider extends ServiceProvider
{
	/**
	 * @since 1.0.0
	 */
	public function register(): void
	{
		$this->register_hooks();
		$this->register_fields_with_tabs();
		$this->register_settings_addon();
	}

	/**
	 * @since 1.0.0
	 */
	protected function register_hooks(): void
	{
		add_filter( 'gform_pre_render', ( new GravityForms() )->pre_render( ... ) );
		add_filter( 'gform_form_settings_fields', ( new GravityFormsFormSettings() )->add_form_settings( ... ), 9999, 2 );
		add_filter( 'gform_field_groups_form_editor', $this->field_groups_form_editor( ... ), 999, 1 );
		add_action( 'gform_field_standard_settings', GravityFormsFieldSettings::add_supplier_prefill_options_select( ... ), 10, 2 );
		add_action( 'gform_editor_js', GravityFormsFieldSettings::add_select_script( ... ), 10, 0 );
	}

	/**
	 * Registers the custom fields and corresponding editor settings tabs with their content.
	 *
	 * Since this plug-in is loaded on the 'plugins_loaded' hook,
	 * it is not necessary to register the fields inside the 'gform_loaded' hook.
	 *
	 * @since 1.0.0
	 */
	public function register_fields_with_tabs(): void
	{
		$fields = config( 'gf-custom-fields' );

		if ( ! is_array( $fields ) || count( $fields ) == 0 ) {
			return;
		}

		foreach ( $fields as $field ) {
			$field = new $field();

			GF_Fields::register( $field );

			add_filter(
				'gform_field_settings_tabs',
				function ($tabs, $form ) use ($field ) {
					$tabs[] = array(
						'id'    => $field->type,
						'title' => $field->get_form_editor_field_title(),
					);

					return $tabs;
				},
				10,
				2
			);

			add_action(
				'gform_field_settings_tab_content',
				function ($form, $tab_id ) use ($field ) {
					if ( $field->type !== $tab_id ) {
						return;
					}

					echo view( $field->get_field_tab_content_template_path() );
				},
				10,
				2
			);
		}
	}

	/**
	 * @since 1.0.0
	 */
	private function register_settings_addon(): void
	{
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		GFForms::include_addon_framework();
		GFAddOn::register( GravityFormsAddon::class );
		GravityFormsAddon::get_instance();
	}

	/**
	 * Adds a custom field group to the form editor.
	 *
	 * @since 1.0.0
	 */
	public function field_groups_form_editor(array $field_groups ): array
	{
		$custom_group = array(
			'name'   => 'owc_pg',
			'label'  => __( 'BRP Prefill velden', 'prefill-gravity-forms' ),
			'fields' => array(),
		);

		array_unshift( $field_groups, $custom_group );

		return $field_groups;
	}
}
