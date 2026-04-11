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

use GFAddOn;
use function OWC\PrefillGravityForms\Foundation\Helpers\config;
use function OWC\PrefillGravityForms\Foundation\Helpers\storage_path;

/**
 * Gravity Forms Add-On providing plugin settings UI.
 *
 * @since 1.0.0
 */
class GravityFormsAddon extends GFAddOn
{
	/**
	 * Subview slug.
	 *
	 * @var string
	 */
	protected $_slug = 'owc-gravityforms-iconnect';

	/**
	 * The complete title of the Add-On.
	 *
	 * @var string
	 */
	protected $_title = 'OWC Prefill';

	/**
	 * The short title of the Add-On to be used in limited spaces.
	 *
	 * @var string
	 */
	protected $_short_title = 'OWC Prefill';

	/**
	 * Instance object.
	 *
	 * @var self
	 */
	private static $_instance = null;

	/**
	 * The full path to the Add-On file.
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Singleton loader.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance(): self
	{
		if ( null == self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 2.5
	 *
	 * @return string
	 */
	public function get_menu_icon()
	{
		return 'dashicons-yard-y';
	}

	/**
	 * Run a one-time migration of legacy global settings into the first named configuration.
	 * Called on every admin init but exits immediately after the first successful run.
	 *
	 * @since NEXT
	 */
	public function init_admin(): void
	{
		$this->maybe_migrate_legacy_settings();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_config_manager' ) );
		parent::init_admin();
	}

	/**
	 * Enqueue the configuration manager script and styles on the plugin settings page.
	 *
	 * @since NEXT
	 */
	public function enqueue_config_manager(): void
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ( $_GET['page'] ?? '' ) !== 'gf_settings' || ( $_GET['subview'] ?? '' ) !== $this->_slug ) {
			return;
		}

		$plugin_url = plugin_dir_url( \PG_ROOT_PATH . '/index.php' );
		$asset_file = \PG_ROOT_PATH . '/build/admin/config-manager.asset.php';

		$version = '1.0.0';
		$deps    = array( 'jquery' );

		if ( file_exists( $asset_file ) ) {
			$asset   = require $asset_file;
			$version = $asset['version'] ?? $version;
			$deps    = array_unique( array_merge( $deps, $asset['dependencies'] ?? array() ) );
		}

		wp_enqueue_script(
			'owc-pg-config-manager',
			$plugin_url . 'build/admin/config-manager.js',
			$deps,
			$version,
			true
		);

		wp_enqueue_style(
			'owc-pg-config-manager',
			$plugin_url . 'resources/css/admin/config-manager.css',
			array(),
			'1.0.0'
		);

		$supplier_map    = config( 'suppliers.mapping', array() );
		$supplier_choices = array();

		foreach ( (array) $supplier_map as $slug => $class ) {
			$supplier_choices[] = array(
				'value' => $slug,
				'label' => $class,
			);
		}

		wp_localize_script(
			'owc-pg-config-manager',
			'owcPgConfigManager',
			array(
				'configs'         => GravityFormsSettings::get_configurations(),
				'supplierChoices' => $supplier_choices,
				'i18n'            => array(
					'editConfig'     => __( 'Configuratie bewerken', 'prefill-gravity-forms' ),
					'newConfig'      => __( 'Nieuwe configuratie', 'prefill-gravity-forms' ),
					'edit'           => __( 'Bewerken', 'prefill-gravity-forms' ),
					'delete'         => __( 'Verwijderen', 'prefill-gravity-forms' ),
					'confirmDelete'  => __( 'Weet je zeker dat je de configuratie wilt verwijderen?', 'prefill-gravity-forms' ),
					'selectSupplier' => __( '— Selecteer een leverancier —', 'prefill-gravity-forms' ),
					'errorLabel'     => __( 'Naam is verplicht.', 'prefill-gravity-forms' ),
					'errorSupplier'  => __( 'Leverancier is verplicht.', 'prefill-gravity-forms' ),
					'errorBaseUrl'   => __( 'Basis URL is verplicht.', 'prefill-gravity-forms' ),
				),
			)
		);
	}

	/**
	 * Configures the settings which should be rendered on the plugin settings page.
	 * The old per-field global settings have been replaced by the Configuraties manager.
	 * Only the shared certificate root-path remains as a global setting.
	 *
	 * @since 1.0.0
	 */
	public function plugin_settings_fields(): array
	{
		$prefix = 'owc-iconnect-';

		return array(
			array(
				'title'       => esc_html__( 'Certificaten', 'prefill-gravity-forms' ),
				'description' => esc_html__( 'De hoofd locatie van de certificaten wordt gedeeld door alle configuraties.', 'prefill-gravity-forms' ),
				'fields'      => array(
					array(
						'label'         => __( 'Certificaten hoofd locatie', 'prefill-gravity-forms' ),
						'type'          => 'text',
						'class'         => 'medium',
						'name'          => "{$prefix}location-root-path-certificates",
						'default_value' => $this->get_root_path_to_certificates(),
						'required'      => true,
					),
				),
			),
			array(
				'title'  => esc_html__( 'Configuraties', 'prefill-gravity-forms' ),
				'fields' => array(
					array(
						'type' => 'html',
						'name' => 'owc-iconnect-configurations-manager',
						'html' => $this->render_configurations_manager(),
					),
				),
			),
		);
	}

	/**
	 * Migrate existing global settings to a named configuration on first deployment.
	 * Runs once — skipped when configurations already exist or when no legacy data is found.
	 *
	 * @since NEXT
	 */
	private function maybe_migrate_legacy_settings(): void
	{
		if ( 0 !== count( GravityFormsSettings::get_configurations() ) ) {
			return;
		}

		$settings = \get_option( 'gravityformsaddon_owc-gravityforms-iconnect_settings', array() );
		$prefix   = 'owc-iconnect-';
		$base_url = $settings[ $prefix . 'base-url' ] ?? '';

		if ( '' === $base_url ) {
			return;
		}

		// Map the stored class name back to its slug for the new config format.
		$supplier_class = $settings[ $prefix . 'supplier' ] ?? '';
		$mapping        = config( 'suppliers.mapping', array() );
		$supplier_slug  = is_array( $mapping ) ? ( array_search( $supplier_class, $mapping, true ) ?: '' ) : '';

		$config = array(
			'id'                       => 'cfg-' . \uniqid(),
			'label'                    => __( 'Standaard configuratie', 'prefill-gravity-forms' ),
			'supplier'                 => $supplier_slug,
			'base-url'                 => $settings[ $prefix . 'base-url' ] ?? '',
			'oin-number'               => $settings[ $prefix . 'oin-number' ] ?? '',
			'processing'               => $settings[ $prefix . 'processing' ] ?? '',
			'user'                     => $settings[ $prefix . 'user' ] ?? '',
			'api-use-authentication'   => $settings[ $prefix . 'api-use-authentication' ] ?? '0',
			'api-key'                  => $settings[ $prefix . 'api-key' ] ?? '',
			'api-key-header-name'      => $settings[ $prefix . 'api-key-header-name' ] ?? 'x-api-key',
			'api-basic-token-username' => $settings[ $prefix . 'api-basic-token-username' ] ?? '',
			'api-basic-token-password' => $settings[ $prefix . 'api-basic-token-password' ] ?? '',
			'use-ssl-certificates'     => $settings[ $prefix . 'use-ssl-certificates' ] ?? '0',
			'public-certificate'       => $settings[ $prefix . 'public-certificate' ] ?? '',
			'private-certificate'      => $settings[ $prefix . 'private-certificate' ] ?? '',
			'supplier-certificate'     => $settings[ $prefix . 'supplier-certificate' ] ?? '',
			'passphrase'               => $settings[ $prefix . 'passphrase' ] ?? '',
			'logging-enabled'          => $settings[ $prefix . 'logging-enabled' ] ?? '0',
			'enable-user-model'        => $settings[ $prefix . 'enable-user-model' ] ?? '0',
		);

		\update_option( 'owc_prefill_configurations', array( $config ) );
	}

	/**
	 * Persist supplier configurations submitted from the configuration manager.
	 * Called by GFAddOn when the plugin settings form is saved.
	 *
	 * @since NEXT
	 */
	public function update_plugin_settings( $settings ): void
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$json = isset( $_POST['owc-pg-configurations-json'] ) ? wp_unslash( $_POST['owc-pg-configurations-json'] ) : '';

		if ( '' !== $json ) {
			$decoded = json_decode( $json, true );

			if ( is_array( $decoded ) ) {
				$sanitized = array_map(
					function ( $config ) {
						if ( ! is_array( $config ) ) {
							return array();
						}

						$result = array();

						foreach ( $config as $key => $value ) {
							if ( ! is_string( $value ) ) {
								continue;
							}

							// Passwords and passphrases are stored as-is.
							if ( in_array( $key, array( 'api-basic-token-password', 'passphrase' ), true ) ) {
								$result[ $key ] = $value;
							} else {
								$result[ $key ] = sanitize_text_field( $value );
							}
						}

						return $result;
					},
					$decoded
				);

				update_option( 'owc_prefill_configurations', $sanitized );
			}
		}

		// GFAddOn::update_plugin_settings() replaces the entire stored option with
		// only the fields present in plugin_settings_fields(). We removed the old
		// per-supplier fields from that list, but legacy forms still read them from
		// the same option. Merging the existing settings first ensures those keys
		// are never wiped.
		$settings = array_merge( $this->get_plugin_settings(), $settings );

		parent::update_plugin_settings( $settings );

		// GFAddOn enqueues scripts (and localises config data) before processing the
		// POST, so without a redirect the page would render with stale JS data.
		// Redirect to the same settings subview to force a clean GET request.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'gf_settings',
					'subview' => $this->_slug,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render the HTML configuration manager for the plugin settings page.
	 * JavaScript and i18n data are provided via wp_localize_script in enqueue_config_manager().
	 *
	 * @since NEXT
	 */
	private function render_configurations_manager(): string
	{
		$pub_cert_html = $this->format_cert_options_html( $this->get_public_certificates() );
		$prv_cert_html = $this->format_cert_options_html( $this->get_private_certificates() );

		ob_start();
		?>
		<div id="owc-pg-config-manager">

			<p class="description" id="owc-pg-config-empty" style="display:none">
				<?php esc_html_e( 'Er zijn nog geen configuraties aangemaakt. Klik op de knop hieronder om te beginnen.', 'prefill-gravity-forms' ); ?>
			</p>

			<table class="widefat striped" id="owc-pg-config-table" style="display:none">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Naam', 'prefill-gravity-forms' ); ?></th>
						<th><?php esc_html_e( 'Leverancier', 'prefill-gravity-forms' ); ?></th>
						<th><?php esc_html_e( 'Basis URL', 'prefill-gravity-forms' ); ?></th>
						<th><?php esc_html_e( 'Acties', 'prefill-gravity-forms' ); ?></th>
					</tr>
				</thead>
				<tbody id="owc-pg-config-list"></tbody>
			</table>

			<p>
				<button type="button" class="button button-secondary" id="owc-pg-add-config">
					&#43; <?php esc_html_e( 'Configuratie toevoegen', 'prefill-gravity-forms' ); ?>
				</button>
			</p>

			<div id="owc-pg-config-editor" class="owc-pg-editor" style="display:none">
				<h4 id="owc-pg-editor-title"><?php esc_html_e( 'Configuratie', 'prefill-gravity-forms' ); ?></h4>
				<div class="owc-pg-validation-errors"><p></p></div>
				<input type="hidden" id="owc-pg-editing-id" value="" />

				<table class="form-table owc-pg-config-form">
					<tr>
						<th scope="row">
							<label for="owc-pg-cfg-label">
								<?php esc_html_e( 'Naam', 'prefill-gravity-forms' ); ?>
								<span class="owc-pg-required">*</span>
							</label>
						</th>
						<td><input type="text" id="owc-pg-cfg-label" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="owc-pg-cfg-supplier">
								<?php esc_html_e( 'Leverancier', 'prefill-gravity-forms' ); ?>
								<span class="owc-pg-required">*</span>
							</label>
						</th>
						<td><select id="owc-pg-cfg-supplier"></select></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="owc-pg-cfg-base-url">
								<?php esc_html_e( 'Basis URL', 'prefill-gravity-forms' ); ?>
								<span class="owc-pg-required">*</span>
							</label>
						</th>
						<td><input type="text" id="owc-pg-cfg-base-url" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="owc-pg-cfg-oin"><?php esc_html_e( 'OIN nummer', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="text" id="owc-pg-cfg-oin" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="owc-pg-cfg-processing"><?php esc_html_e( 'Verwerking (V2)', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="text" id="owc-pg-cfg-processing" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="owc-pg-cfg-user"><?php esc_html_e( 'Gebruiker (V2)', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="text" id="owc-pg-cfg-user" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'API authenticatie', 'prefill-gravity-forms' ); ?></th>
						<td><label><input type="checkbox" id="owc-pg-cfg-api-auth" value="1" /> <?php esc_html_e( 'Gebruik API authenticatie', 'prefill-gravity-forms' ); ?></label></td>
					</tr>
					<tr class="owc-pg-api-auth-field">
						<th scope="row"><label for="owc-pg-cfg-api-key"><?php esc_html_e( 'API sleutel', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="text" id="owc-pg-cfg-api-key" class="regular-text" /></td>
					</tr>
					<tr class="owc-pg-api-auth-field">
						<th scope="row"><label for="owc-pg-cfg-api-key-header"><?php esc_html_e( 'Header naam', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="text" id="owc-pg-cfg-api-key-header" class="regular-text" placeholder="x-api-key" /></td>
					</tr>
					<tr class="owc-pg-api-auth-field">
						<th scope="row"><label for="owc-pg-cfg-api-username"><?php esc_html_e( 'OAuth gebruikersnaam', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="text" id="owc-pg-cfg-api-username" class="regular-text" /></td>
					</tr>
					<tr class="owc-pg-api-auth-field">
						<th scope="row"><label for="owc-pg-cfg-api-password"><?php esc_html_e( 'OAuth wachtwoord', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="password" id="owc-pg-cfg-api-password" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'SSL certificaten', 'prefill-gravity-forms' ); ?></th>
						<td><label><input type="checkbox" id="owc-pg-cfg-ssl" value="1" /> <?php esc_html_e( 'Gebruik SSL certificaten', 'prefill-gravity-forms' ); ?></label></td>
					</tr>
					<tr class="owc-pg-ssl-field">
						<th scope="row"><label for="owc-pg-cfg-pub-cert"><?php esc_html_e( 'Publiek certificaat', 'prefill-gravity-forms' ); ?></label></th>
						<td><select id="owc-pg-cfg-pub-cert"><?php echo $pub_cert_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></select></td>
					</tr>
					<tr class="owc-pg-ssl-field">
						<th scope="row"><label for="owc-pg-cfg-priv-cert"><?php esc_html_e( 'Privé certificaat', 'prefill-gravity-forms' ); ?></label></th>
						<td><select id="owc-pg-cfg-priv-cert"><?php echo $prv_cert_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></select></td>
					</tr>
					<tr class="owc-pg-ssl-field">
						<th scope="row"><label for="owc-pg-cfg-supplier-cert"><?php esc_html_e( 'Leverancier certificaat', 'prefill-gravity-forms' ); ?></label></th>
						<td><select id="owc-pg-cfg-supplier-cert"><?php echo $pub_cert_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></select></td>
					</tr>
					<tr class="owc-pg-ssl-field">
						<th scope="row"><label for="owc-pg-cfg-passphrase"><?php esc_html_e( 'Wachtwoord certificaat', 'prefill-gravity-forms' ); ?></label></th>
						<td><input type="password" id="owc-pg-cfg-passphrase" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Overig', 'prefill-gravity-forms' ); ?></th>
						<td>
							<label><input type="checkbox" id="owc-pg-cfg-logging" value="1" /> <?php esc_html_e( 'Logging inschakelen', 'prefill-gravity-forms' ); ?></label><br />
							<label><input type="checkbox" id="owc-pg-cfg-user-model" value="1" /> <?php esc_html_e( 'Gebruikersmodel activeren', 'prefill-gravity-forms' ); ?></label>
						</td>
					</tr>
				</table>

				<p class="owc-pg-editor-actions">
					<button type="button" class="button" id="owc-pg-cancel-config"><?php esc_html_e( 'Annuleren', 'prefill-gravity-forms' ); ?></button>
				</p>
			</div>

			<input type="hidden" name="owc-pg-configurations-json" id="owc-pg-configurations-json" value="" />

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Format certificate choices as HTML option elements.
	 *
	 * @since NEXT
	 */
	private function format_cert_options_html( array $choices ): string
	{
		$html = '';

		foreach ( $choices as $choice ) {
			$html .= sprintf(
				'<option value="%s">%s</option>',
				esc_attr( $choice['value'] ?? '' ),
				esc_html( $choice['label'] ?? '' )
			);
		}

		return $html;
	}

	/**
	 * Format the list of certificates for the selectbox.
	 *
	 * @since 1.0.0
	 */
	private function format_list_of_certificates(array $certificates ): array
	{
		$no_certificate = array(
			array(
				'label' => __( 'Geen certificaat geselecteerd', 'prefill-gravity-forms' ),
				'value' => 'no-certificate',
			),
		);

		$certificates = array_values(
			array_map(
				function ($certificate ) {
					return array(
						'label' => basename( $certificate ),
						'value' => $certificate,
					);
				},
				$certificates
			)
		);

		return array_merge( $no_certificate, $certificates );
	}

	/**
	 * Get all the public certificates from the storage map.
	 *
	 * @since 1.0.0
	 */
	private function get_public_certificates(): array
	{
		return $this->format_list_of_certificates( glob( $this->get_certificate_location() . '/*.{crt,cer}', GLOB_BRACE ) );
	}

	/**
	 * Get all the private certificates from the storage map.
	 *
	 * @since 1.0.0
	 */
	private function get_private_certificates(): array
	{
		return $this->format_list_of_certificates( glob( $this->get_certificate_location() . '/*.{key}', GLOB_BRACE ) );
	}

	/**
	 * Get the correct path for the certificates of the current site.
	 *
	 * @since 1.0.0
	 */
	private function get_certificate_location(): string
	{
		if ( is_multisite() ) {
			return sprintf( '%s/%s', $this->get_root_path_to_certificates(), get_current_blog_id() ?? '1' );
		}

		return sprintf( '%s', $this->get_root_path_to_certificates() );
	}

	/**
	 * Get root path to certificates.
	 *
	 * @since 1.0.0
	 */
	private function get_root_path_to_certificates(): string
	{
		$configured = GravityFormsSettings::make()->get( 'location-root-path-certificates' );
		$fallback   = storage_path( 'certificates' );

		if ( '' === $configured ) {
			return $fallback;
		}

		$real_path = realpath( $configured );

		if ( false === $real_path ) {
			return $fallback;
		}

		$safe_base = realpath( \ABSPATH . '/../../' );

		if ( ! str_starts_with( $real_path, $safe_base . DIRECTORY_SEPARATOR ) ) {
			return $fallback;
		}

		if ( ! is_dir( $real_path ) || ! is_readable( $real_path ) ) {
			return $fallback;
		}

		return $real_path;
	}
}
