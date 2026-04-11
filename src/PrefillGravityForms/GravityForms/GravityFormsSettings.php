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

use function OWC\PrefillGravityForms\Foundation\Helpers\config;

/**
 * Provides typed accessors for the plugin's stored Gravity Forms addon settings.
 * Supports loading a specific named configuration when a config_id is provided.
 *
 * @since 1.0.0
 */
class GravityFormsSettings
{
	protected string $prefix   = 'owc-iconnect-';
	protected string $name     = 'gravityformsaddon_owc-gravityforms-iconnect_settings';
	protected array $options   = array();
	protected string $config_id = '';
	protected ?array $config    = null;

	/**
	 * @since 1.0.0
	 */
	private function __construct( string $config_id = '' )
	{
		$this->options = \get_option( $this->name, array() );

		if ( '' !== $config_id ) {
			$this->config_id = $config_id;
			$this->config    = $this->resolve_config( $config_id );
		}
	}

	/**
	 * Static constructor. Pass a configuration ID to load a specific configuration's
	 * settings instead of the global plugin settings.
	 *
	 * @since 1.0.0
	 */
	public static function make( string $config_id = '' ): self
	{
		return new static( $config_id );
	}

	/**
	 * Return all stored supplier configurations.
	 *
	 * @since NEXT
	 */
	public static function get_configurations(): array
	{
		$raw = \get_option( 'owc_prefill_configurations', array() );

		return is_array( $raw ) ? $raw : array();
	}

	/**
	 * Returns true when the given string matches a stored configuration ID.
	 *
	 * @since NEXT
	 */
	public static function is_configuration( string $value ): bool
	{
		foreach ( static::get_configurations() as $config ) {
			if ( ( $config['id'] ?? '' ) === $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve a single configuration object by its ID.
	 *
	 * @since NEXT
	 */
	private function resolve_config( string $config_id ): ?array
	{
		foreach ( static::get_configurations() as $config ) {
			if ( ( $config['id'] ?? '' ) === $config_id ) {
				return $config;
			}
		}

		return null;
	}

	/**
	 * Get a setting value. When a named configuration is active its values take
	 * precedence over the global plugin settings.
	 *
	 * Falls back to the first saved configuration when the global option is
	 * missing a value — this keeps legacy forms working even if the global option
	 * was partially reset by a settings save that no longer included the old fields.
	 *
	 * @since 1.0.0
	 */
	public function get( string $key ): string
	{
		if ( null !== $this->config ) {
			return (string) ( $this->config[ $key ] ?? '' );
		}

		$value = (string) ( $this->options[ $this->prefix . $key ] ?? '' );

		if ( '' !== $value ) {
			return $value;
		}

		$configs = static::get_configurations();

		return 0 < count( $configs ) ? (string) ( $configs[0][ $key ] ?? '' ) : '';
	}

	/**
	 * @since 1.0.0
	 */
	public function get_base_url(): string
	{
		return $this->get( 'base-url' );
	}

	/**
	 * Returns the supplier class name (e.g. 'EnableUV2').
	 * When using a named configuration the stored slug is mapped to the class name.
	 *
	 * @since 1.0.0
	 */
	public function get_supplier(): string
	{
		$mapping = config( 'suppliers.mapping', array() );

		if ( null !== $this->config ) {
			$slug = $this->config['supplier'] ?? '';

			return is_array( $mapping ) ? ( $mapping[ $slug ] ?? '' ) : '';
		}

		$value = $this->options[ $this->prefix . 'supplier' ] ?? '';

		if ( '' !== $value ) {
			return $value;
		}

		// Fall back to the first configuration's supplier (slug → class name).
		$configs = static::get_configurations();

		if ( 0 < count( $configs ) ) {
			$slug = $configs[0]['supplier'] ?? '';

			return is_array( $mapping ) ? ( $mapping[ $slug ] ?? '' ) : '';
		}

		return '';
	}

	/**
	 * @since 1.0.0
	 */
	public function is_user_model_enabled(): bool
	{
		return boolval( $this->get( 'enable-user-model' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function use_ssl_certificates(): bool
	{
		return boolval( $this->get( 'use-ssl-certificates' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_number_oin(): string
	{
		return $this->get( 'oin-number' );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_processing(): string
	{
		return $this->get( 'processing' );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_user(): string
	{
		return $this->get( 'user' );
	}

	/**
	 * @since 1.0.0
	 */
	public function use_api_authentication(): bool
	{
		return boolval( $this->get( 'api-use-authentication' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_api_key(): string
	{
		return $this->get( 'api-key' );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_api_key_header_name(): string
	{
		$value = $this->get( 'api-key-header-name' );

		return '' !== $value ? $value : 'x-api-key';
	}

	/**
	 * @since 1.0.0
	 */
	public function get_api_token_username(): string
	{
		return $this->get( 'api-basic-token-username' );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_api_token_password(): string
	{
		return $this->get( 'api-basic-token-password' );
	}

	/**
	 * @since 1.0.0
	 */
	public function logging_enabled(): bool
	{
		return boolval( $this->get( 'logging-enabled' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_public_certificate(): string
	{
		$path = $this->get( 'public-certificate' );

		if ( '' === $path ) {
			return '';
		}

		return file_exists( $path ) ? $path : '';
	}

	/**
	 * @since 1.0.0
	 */
	public function get_private_certificate(): string
	{
		$path = $this->get( 'private-certificate' );

		if ( '' === $path ) {
			return '';
		}

		return file_exists( $path ) ? $path : '';
	}

	/**
	 * @since 1.0.0
	 */
	public function get_supplier_certificate(): string
	{
		$path = $this->get( 'supplier-certificate' );

		if ( '' === $path ) {
			return '';
		}

		return file_exists( $path ) ? $path : '';
	}

	/**
	 * @since 1.0.0
	 */
	public function get_passphrase(): string
	{
		return $this->get( 'passphrase' );
	}
}
