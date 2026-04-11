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

/**
 * Registers per-form settings fields for the OWC Prefill configuration.
 *
 * @since 1.0.0
 */
class GravityFormsFormSettings
{
	/**
	 * @since 1.0.0
	 */
	public function add_form_settings( array $fields ): array
	{
		$fields[] = array(
			'title'  => __( 'OWC Prefill', 'prefill-gravity-forms' ),
			'fields' => array_filter(
				array(
					array(
						'name'    => 'owc-iconnect-exclude-deceased',
						'label'   => 'Sluit overledenen uit',
						'type'    => 'toggle',
						'tooltip' => __( 'Schakel deze optie in om overleden partners, kinderen en ouders uit te sluiten van de resultaten.', 'prefill-gravity-forms' ),
					),
					$this->build_supplier_field(),
					array(
						'name'        => 'owc-iconnect-processing',
						'label'       => __( 'Verwerking (V2)', 'prefill-gravity-forms' ),
						'description' => __( 'Schrijf de globale instelling over op formulier niveau.', 'prefill-gravity-forms' ),
						'type'        => 'text',
						'required'    => false,
					),
					array(
						'name'  => 'owc-iconnect-doelbinding',
						'label' => __( 'Doelbinding', 'prefill-gravity-forms' ),
						'type'  => 'text',
					),
					$this->theme_mapping_options_field(),
					array(
						'name'        => 'owc-iconnect-expand',
						'label'       => __( 'Uitbreiden (V1)', 'prefill-gravity-forms' ),
						'type'        => 'text',
						'description' => __( 'Breidt de resultaten uit met andere entiteiten. Kommagescheiden waardes in vullen. Bijvoorbeeld: \'ouders,partners,kinderen\'. Alleen gebruiken wanneer de doelbinding niet verantwoordelijk is voor het ophalen van extra velden. (vaak alleen voor versie 1 van de HaalCentraal)', 'prefill-gravity-forms' ),
					),
				)
			),
		);

		return $fields;
	}

	/**
	 * Build the supplier / configuration dropdown field for the form settings panel.
	 * Named configurations are listed first. Legacy supplier slugs follow in a
	 * separate group for backward compatibility with forms created before the
	 * multi-configuration feature was introduced.
	 *
	 * @since NEXT
	 */
	private function build_supplier_field(): array
	{
		$configurations = GravityFormsSettings::get_configurations();

		$config_choices = array();

		if ( 0 === count( $configurations ) ) {
			$config_choices[] = array(
				'label' => __( '— Geen configuraties aangemaakt —', 'prefill-gravity-forms' ),
				'value' => 'none',
			);
		} else {
			foreach ( $configurations as $config ) {
				$config_choices[] = array(
					'label' => esc_html( $config['label'] ?? $config['id'] ?? '' ),
					'value' => esc_attr( $config['id'] ?? '' ),
				);
			}
		}

		$legacy_choices = array(
			array( 'label' => '— ' . __( 'Verouderd', 'prefill-gravity-forms' ) . ' —', 'value' => 'none' ),
			array( 'label' => 'OpenZaak',       'value' => 'openzaak' ),
			array( 'label' => 'EnableU',         'value' => 'enable-u' ),
			array( 'label' => 'EnableU V2',      'value' => 'enable-u-v2' ),
			array( 'label' => 'PinkRoccade',     'value' => 'pink-roccade' ),
			array( 'label' => 'PinkRoccade V2',  'value' => 'pink-roccade-v2' ),
			array( 'label' => 'VrijBRP',         'value' => 'vrij-brp' ),
			array( 'label' => 'WeAreFrank!',     'value' => 'we-are-frank' ),
		);

		return array(
			'name'          => 'owc-form-setting-supplier',
			'default_value' => 'none',
			'tooltip'       => '<h6>' . __( 'Selecteer een configuratie', 'prefill-gravity-forms' ) . '</h6>'
				. __( 'Kies een leveranciersconfiguratie. Configuraties worden aangemaakt in de algemene plugin-instellingen.', 'prefill-gravity-forms' ),
			'type'          => 'select',
			'label'         => __( 'Selecteer een configuratie', 'prefill-gravity-forms' ),
			'choices'       => array_merge(
				array(
					array(
						'label' => __( 'Selecteer een configuratie', 'prefill-gravity-forms' ),
						'value' => 'none',
					),
				),
				$config_choices,
				$legacy_choices
			),
		);
	}

	/**
	 * Get the theme mapping options field when there are options available.
	 *
	 * @since 1.0.0
	 */
	private function theme_mapping_options_field(): array
	{
		$options = $this->get_theme_mapping_options();

		if ( is_null( $options ) ) {
			return array();
		}

		return array(
			'name'        => 'owc-iconnect-theme-mapping-options-file',
			'label'       => __( 'Selecteer een mappingbestand uit het thema', 'prefill-gravity-forms' ),
			'description' => __( 'Gebruik een eigen bestand vanuit bijv. een thema om formuliervelden te kunnen mappen.', 'prefill-gravity-forms' ),
			'type'        => 'select',
			'choices'     => $options,
			'required'    => true,
		);
	}

	/**
	 * Retrieve theme mapping options (which are file paths) if a directory is provided via the filter.
	 * The options are derived from the files within the specified directory.
	 *
	 * @since 1.0.0
	 */
	protected function get_theme_mapping_options(): ?array
	{
		$theme_dir = apply_filters( 'pg::theme/dir_mapping_options', null );

		if ( is_null( $theme_dir ) || ! is_dir( $theme_dir ) ) {
			return null;
		}

		$mapping_options = glob( trailingslashit( $theme_dir ) . '*.php' );

		if ( ! is_array( $mapping_options ) || ! count( $mapping_options ) ) {
			return null;
		}

		$mapping_options = array_map(
			function ($file ) {
				return array(
					'label' => basename( $file, '.php' ),
					'value' => $file,
				);
			},
			$mapping_options
		);

		return array_merge(
			array(
				array(
					'label' => __( 'Selecteer een bestand', 'prefill-gravity-forms' ),
					'value' => '0',
				),
			),
			$mapping_options
		);
	}
}
