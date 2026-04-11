<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.2.0
 */

namespace OWC\PrefillGravityForms\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Traits\ControllerTrait;
use OWC\PrefillGravityForms\Traits\Logger;

/**
 * Retrieves and formats individual personal data fields from a BRP supplier.
 *
 * @since 1.2.0
 */
class PersonalDataService
{
	use ControllerTrait;
	use Logger;

	private string $supplier;
	private ?BaseController $controller;

	/**
	 * @since 1.2.0
	 */
	public function __construct(string $supplier )
	{
		$this->supplier   = $supplier;
		$this->controller = $this->handle_controller();
	}

	/**
	 * @since 1.2.0
	 */
	private function handle_controller(): ?BaseController
	{
		try {
			return $this->get_controller( $this->supplier );
		} catch ( Exception $e ) {
			$this->log_exception( $e );

			return null;
		}
	}

	/**
	 * @since 1.2.0
	 */
	public function get(string $key, string $goal_binding = '', string $processing = '' ): string
	{
		if ( ! $this->controller instanceof BaseController || 1 > strlen( $key ) ) {
			return '';
		}

		$data = $this->controller->get( $goal_binding, $processing );

		if ( $this->controller->get_api_version() === '1' ) {
			$key   = $this->key_version_one( $key );
			$value = $this->get_value_from_nested_array( $key, $data );
		} elseif ( $this->controller->get_api_version() === '2' ) {
			$key   = $this->key_version_two( $key );
			$value = $this->get_value_from_nested_array( $key, $data );
		} else {
			return '';
		}

		return $this->format( $key, $value );
	}

	/**
	 * In API version 1, some keys had different names or were structured differently. This method maps the old
	 * keys to their new counterparts for suppliers that haven't updated to the new structure.
	 *
	 * @since 1.2.0
	 */
	private function key_version_one(string $key ): string
	{
		if ( 'naam.voornaam' === $key ) {
			return 'naam.voornamen';
		}

		$prefill_suppliers_with_key_exceptions = array(
			'vrijbrp',
		);

		if ( ! in_array( strtolower( $this->supplier ), $prefill_suppliers_with_key_exceptions ) ) {
			return $key;
		}

		$mapping = array(
			'verblijfplaats.woonplaats' => 'verblijfplaats.woonplaatsnaam',
			'verblijfplaats.straat'     => 'verblijfplaats.straatnaam',
		);

		return $mapping[ $key ] ?? $key;
	}

	/**
	 * In API version 2, some keys had different names or were structured differently. This method maps the old
	 * keys to their new counterparts for suppliers that haven't updated to the new structure.
	 *
	 * @since 1.2.0
	 */
	private function key_version_two(string $key ): string
	{
		if ( 'naam.voornaam' === $key ) {
			return 'naam.voornamen';
		}

		$mapping = array(
			'geslachtsaanduiding'       => 'geslacht.omschrijving',
			'verblijfplaats.straat'     => 'verblijfplaats.verblijfadres.officieleStraatnaam',
			'verblijfplaats.huisnummer' => 'verblijfplaats.verblijfadres.huisnummer',
			'verblijfplaats.huisletter' => 'verblijfplaats.verblijfadres.huisletter',
			'verblijfplaats.postcode'   => 'verblijfplaats.verblijfadres.postcode',
			'verblijfplaats.woonplaats' => 'verblijfplaats.verblijfadres.woonplaats',
		);

		return $mapping[ $key ] ?? $key;
	}

	/**
	 * @since 1.2.0
	 */
	private function get_value_from_nested_array(string $key_string, array $data ): string
	{
		$keys = explode( '.', $key_string );

		foreach ( $keys as $key ) {
			if ( is_array( $data ) && isset( $data[ $key ] ) ) {
				$data = $data[ $key ];
			} else {
				return '';
			}
		}

		return (string) $data;
	}

	/**
	 * @since 1.2.0
	 */
	private function format(string $key, string $value ): string
	{
		$key_format_mapping = array(
			'geslacht.omschrijving' => fn ($value ) => ucfirst( $value ),
			'geslachtsaanduiding'   => fn ($value ) => ucfirst( $value ),
			'naam.voornaam'         => fn ($value ) => explode( ' ', $value )[0],
			'geboorte.datum.datum'  => fn ($value ) => '' !== $value ? date_i18n( get_option( 'date_format', 'j F Y' ), strtotime( $value ) ) : '',
		);

		if ( isset( $key_format_mapping[ $key ] ) ) {
			return $key_format_mapping[ $key ]( $value );
		}

		return $value;
	}
}
