<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

namespace OWC\PrefillGravityForms\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use CurlHandle;
use DateTime;
use Exception;
use GF_Field;
use function OWC\PrefillGravityForms\Foundation\Helpers\view;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Services\CacheService;
use OWC\PrefillGravityForms\Traits\Logger;
use OWC\PrefillGravityForms\Traits\SessionTrait;
use TypeError;
use WP_Screen;

/**
 * Abstract base controller providing shared prefill logic for all supplier controllers.
 *
 * @since 1.0.0
 */
abstract class BaseController
{
	use Logger;
	use SessionTrait;

	protected const CUSTOM_FIELDS_TYPES = array(
		'owc_pg_age_check',
		'owc_pg_municipality_check',
	);

	protected const BRP_API_VERSION = '';

	protected GravityFormsSettings $settings;
	protected array $prefilled_children_mapping_options = array();
	protected bool $is_personal_data_service_request    = false;

	/**
	 * @since 1.0.0
	 */
	public function __construct( string $config_id = '' )
	{
		$this->settings = GravityFormsSettings::make( $config_id );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_api_version(): string
	{
		return static::BRP_API_VERSION;
	}

	abstract public function handle(array $form ): array;

	/**
	 * @since 1.0.0
	 */
	protected function is_block_editor(): bool
	{
		global $current_screen;

		if ( ! $current_screen instanceof WP_Screen ) {
			return false;
		}

		return method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();
	}

	/**
	 * @since 1.0.0
	 */
	public function get(string $goal_binding = '', string $processing = '' ): array
	{
		$this->is_personal_data_service_request = true;

		return static::make_request( $goal_binding, $processing );
	}

	abstract protected function make_request(string $goal_binding = '', string $processing = '' ): array;

	/**
	 * @since 1.0.0
	 */
	protected function pre_fill_fields(array $form, array $response ): array
	{
		foreach ( $form['fields'] as $field ) {
			$linked_mapping_option = $field->linkedFieldValue ?? '';

			if ( ! is_string( $linked_mapping_option ) || 1 > strlen( $linked_mapping_option ) ) {
				continue;
			}

			$linked_mapping_option = $this->replace_child_index_placeholder( $linked_mapping_option );
			$found_value           = $this->find_value_of_mapped_option( $linked_mapping_option, $response );

			if ( empty( $found_value ) ) {
				$field->cssClass = 'owc_prefilled'; // When field has mapping but there is no value found, set to read-only.

				continue;
			}

			if ( 'text' === $field->type ) {
				$this->handle_field_text( $field, $found_value );

				continue;
			}

			if ( 'date' === $field->type ) {
				$this->handle_field_date( $field, $found_value );

				continue;
			}

			if ( in_array( $field->type, self::CUSTOM_FIELDS_TYPES ) ) {
				$field->defaultValue = $found_value;

				continue;
			}
		}

		return $form;
	}

	/**
	 * Replaces the child index placeholder (*) in the linked field reference.
	 *
	 * In the prefill options, child-related fields use an asterisk (*) as a placeholder
	 * for the child index (e.g., "kinderen.*"). This method replaces the
	 * asterisk with an incremented number to ensure unique identifiers for each child.
	 *
	 * Example:
	 * Input:  "kinderen.*.burgerservicenummer"
	 * Output: "kinderen.0.burgerservicenummer" (for the first child)
	 *         "kinderen.1.burgerservicenummer" (for the second child)
	 *
	 * @since 1.0.0
	 */
	protected function replace_child_index_placeholder(string $linked_mapping_option ): string
	{
		if ( strpos( $linked_mapping_option, 'kinderen.*' ) === false ) {
			return $linked_mapping_option;
		}

		// Store the children mapping option used to keep track of the number of times a mapping option is used.
		$this->prefilled_children_mapping_options[] = $linked_mapping_option;

		$times_mapping_option_is_used = count(
			array_filter(
				$this->prefilled_children_mapping_options,
				function ($field ) use ($linked_mapping_option ) {
					return $field === $linked_mapping_option;
				}
			)
		);

		$linked_mapping_option = str_replace(
			'kinderen.*',
			sprintf( 'kinderen.%d', $times_mapping_option_is_used ? $times_mapping_option_is_used - 1 : 0 ),
			$linked_mapping_option
		);

		return $linked_mapping_option;
	}

	/**
	 * @since 1.0.0
	 */
	protected function find_value_of_mapped_option(string $linked_mapping_option = '', array $response = array() ): string
	{
		if ( 1 > strlen( $linked_mapping_option ) || ! count( $response ) ) {
			return $linked_mapping_option;
		}

		return $this->explode_dot_notation_value( $linked_mapping_option, $response );
	}

	/**
	 * Explode dot notation string into array items.
	 * Use these array items to retrieve nested array values from the response.
	 *
	 * @since 1.0.0
	 */
	protected function explode_dot_notation_value(string $dot_notation_string, array $response ): string
	{
		$exploded = explode( '.', $dot_notation_string );

		// Initialize the holder with the first part of the response array.
		$holder = $response[ $exploded[0] ] ?? '';

		foreach ( array_slice( $exploded, 1 ) as $item ) {
			if ( empty( $holder ) ) {
				break;
			}

			// Flatten if the holder is a single multidimensional array and the item is not numeric
			if ( is_array( $holder ) && $this->is_single_multidimensional_array( $holder ) && ! is_numeric( $item ) ) {
				$holder = $this->flatten_multidimensional_array( $holder );
			}

			// Move deeper into the nested array.
			$holder = $holder[ $item ] ?? '';
		}

		// Return the result, ensuring it's a string or numeric value.
		return is_string( $holder ) || is_numeric( $holder ) ? (string) $holder : '';
	}

	/**
	 * Checks if the array contains only one element, and that element is itself an array.
	 *
	 * @since 1.0.0
	 */
	protected function is_single_multidimensional_array(array $array ): bool
	{
		return isset( $array[0] ) && ! isset( $array[1] ) && is_array( $array[0] );
	}

	/**
	 * Flatten a multidimensional array with identical keys into a single array where the values of the last array remain.
	 *
	 * @since 1.0.0
	 */
	protected function flatten_multidimensional_array(array $array ): array
	{
		$holder = array();

		foreach ( $array as $part ) {
			$holder = array_merge( $holder, $part );
		}

		return $holder;
	}

	/**
	 * @since 1.0.0
	 */
	protected function handle_field_text(GF_Field $field, string $found_value ): void
	{
		if ( $this->is_possible_date( $found_value ) ) {
			$field->defaultValue = ( new DateTime( $found_value ) )->format( 'd-m-Y' );
		} else {
			$field->defaultValue = $found_value;
		}

		$field->cssClass = 'owc_prefilled';
	}

	/**
	 * @since 1.0.0
	 */
	public function is_possible_date(string $value ): bool
	{
		try {
			return ( date( 'Y-m-d', strtotime( $value ) ) == $value );
		} catch ( Exception | TypeError $e ) {
			return false;
		}
	}

	/**
	 * Handles prefilling of date fields based on their date type.
	 *
	 * This method processes date fields, specifically handling different date input types.
	 * The 'datefield' and 'datedropdown' types require a unique approach for pre-populating
	 * their inputs as they consist of multiple parts (month, day, year). The 'datepicker' type,
	 * which consists of a single input, is handled differently.
	 *
	 * @since 1.0.0
	 */
	protected function handle_field_date(GF_Field $field, string $found_value ): void
	{
		try {
			$date = new DateTime( $found_value );
		} catch ( Exception $e ) {
			return;
		}

		// Field consists of 1 part.
		if ( empty( $field->inputs ) || 'datepicker' === $field->dateType ) {
			$field->defaultValue = $date->format( 'd-m-Y' );
			$field->displayOnly  = true;
			$field->cssClass     = 'owc_prefilled';

			return;
		}

		// Field consists of 3 parts which are represented by the input attribute.
		if ( ! empty( $field->inputs ) && ( 'datefield' === $field->dateType || 'datedropdown' === $field->dateType ) ) {
			$field->inputs[0]['defaultValue'] = $date->format( 'm' );
			$field->inputs[1]['defaultValue'] = $date->format( 'd' );
			$field->inputs[2]['defaultValue'] = $date->format( 'Y' );
			$field->cssClass                  = 'owc_prefilled';
		}
	}

	/**
	 * @since 1.0.0
	 */
	protected function get_request_url(string $identifier = '', string $expand = '' ): string
	{
		$base_url = $this->settings->get_base_url();

		if ( 1 > strlen( $base_url ) || 1 > strlen( $identifier ) ) {
			return '';
		}

		$url = sprintf( '%s/%s', $base_url, $identifier );

		if ( 0 < strlen( $expand ) ) {
			$url = sprintf( '%s?%s', $url, $this->create_expand_arguments( $expand ) );
		}

		return $url;
	}

	/**
	 * @since 1.0.0
	 */
	protected function create_expand_arguments(string $expand ): string
	{
		$exploded = explode( ',', $expand );
		$filtered = array_filter( $exploded );
		$new      = array_map( 'trim', $filtered );
		$imploded = implode( ',', $new );

		return urldecode( http_build_query( array( 'expand' => $imploded ), '', ',' ) );
	}

	/**
	 * @since 1.0.0
	 */
	protected function get_curl_headers(string $goal_binding = '', string $processing = '' ): array
	{
		$settings = $this->settings;

		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json',
			'x-origin-oin: ' . $settings->get_number_oin(),
		);

		if ( '' !== $goal_binding ) {
			$headers[] = 'x-doelbinding: ' . $goal_binding;
		}

		if ( '' !== $processing ) {
			$headers[] = 'x-verwerking: ' . $processing;
		}

		$user = $settings->get_user();
		if ( '' !== $user ) {
			$headers[] = 'x-gebruiker: ' . $user;
		}

		return $this->get_curl_headers_api_authentication( $settings, $headers );
	}

	/**
	 * @since 1.0.0
	 */
	private function get_curl_headers_api_authentication($settings, array $headers ): array
	{
		if ( $settings->use_api_authentication() ) {
			$api_key = $settings->get_api_key();
			if ( '' !== $api_key ) {
				$headers[] = sprintf(
					'%s: %s',
					$settings->get_api_key_header_name(),
					$api_key
				);
			} else {
				$username = $settings->get_api_token_username();
				$password = $settings->get_api_token_password();

				if ( '' !== $username && '' !== $password ) {
					$headers[] = 'Authorization: Basic ' . base64_encode( $username . ':' . $password );
				}
			}
		}

		return $headers;
	}

	/**
	 * @since 1.0.0
	 */
	protected function handle_curl(array $args, string $transient_key, array $location_bsn_in_response = array() ): array
	{
		try {
			/**
			 * IMPORTANT NOTE: when adjusting this piece of code, please make sure
			 * that the transient key is unique per request. Otherwise, different requests
			 * might return the same cached response.
			 */
			if ( $cached_response = CacheService::getArrayFromTransient( $transient_key ) ) {
				return $cached_response;
			}
		} catch ( Exception $e ) {
			$this->log_exception( $e );
		}

		$curl = curl_init();

		try {
			curl_setopt_array( $curl, $this->get_default_curl_args() + $args );

			if ( ! empty( $this->settings->get_passphrase() ) ) {
				curl_setopt( $curl, CURLOPT_SSLKEYPASSWD, $this->settings->get_passphrase() );
			}

			$this->apply_curl_ssl_options( $curl );

			$output = curl_exec( $curl );

			if ( curl_error( $curl ) ) {
				throw new Exception( curl_error( $curl ) );
			}

			$response = json_decode( $output, true );

			if ( ! is_array( $response ) || array() === $response || json_last_error() !== JSON_ERROR_NONE ) {
				throw new Exception( 'Something went wrong with decoding of the JSON output.', 500 );
			}

			$http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if ( 200 !== $http_status ) {
				throw new Exception( sprintf( '%s', $decoded['detail'] ?? ( $decoded['Error Details'] ?? 'Request failed, error unknown' ) ), is_int( $http_status ) ? $http_status : 500 );
			}

			$this->handle_transient( $response, $transient_key, $location_bsn_in_response );

			return $response;
		} catch ( Exception $e ) {
			return array(
				'message' => $e->getMessage(),
				'status'  => $e->getCode(),
			);
		} finally {
			unset( $curl );
		}
	}

	/**
	 * Verification of the peer's SSL certificate and host is only necessary when
	 * SSL certificates are used and a supplier certificate is provided.
	 *
	 * @since 1.0.0
	 */
	private function should_verify_peer_host(): bool
	{
		return $this->settings->use_ssl_certificates() && $this->settings->get_supplier_certificate();
	}

	/**
	 * Applies SSL options to the cURL handle based on the settings.
	 *
	 * @since 1.0.0
	 */
	private function apply_curl_ssl_options(CurlHandle $curl ): void
	{
		$should_verify_peer_host = $this->should_verify_peer_host();

		if ( $should_verify_peer_host ) {
			curl_setopt( $curl, CURLOPT_CAINFO, $this->settings->get_supplier_certificate() );
		}

		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, $should_verify_peer_host );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, $should_verify_peer_host ? 2 : 0 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, $this->timeout_option_curl() );
	}

	/**
	 * Validates whether the necessary conditions are met before setting the transient.
	 *
	 * Ensures that:
	 * - A valid BSN (burgerservicenummer) is present in the response.
	 * - The transient key derived from that BSN matches the one generated from the current session.
	 *
	 * @since 1.0.0
	 */
	protected function handle_transient(array $response, string $transient_key, array $location_bsn_in_response = array() ): void
	{
		$response_bsn = $this->extract_bsn( $response, $location_bsn_in_response );

		if ( '' === $response_bsn ) {
			throw new Exception( 'No burgerservicenummer found in the response.', 404 );
		}

		$transient_key_by_response = CacheService::formatTransientKey(
			$this->is_personal_data_service_request ? $response_bsn . '_personal_data_service' : $response_bsn
		);

		// Ensure the transient keys generated from the BSN out of the response and current session match.
		if ( $transient_key_by_response !== $transient_key ) {
			throw new Exception( 'Transient key mismatch.', 500 );
		}

		try {
			CacheService::setTransient( $transient_key, $response );
		} catch ( Exception $e ) {
			$this->log_exception( $e );
		}
	}

	/**
	 * Extracts the burgerservicenummer (BSN) from the API response.
	 *
	 * @since 1.0.0
	 */
	protected function extract_bsn(array $response, array $location_bsn_in_response = array() ): string
	{
		if ( array() !== $location_bsn_in_response ) {
			$response = $this->explode_dot_notation_value( implode( '.', $location_bsn_in_response ), $response );
			$bsn      = $response;
		} else {
			if ( ! isset( $response['burgerservicenummer'] ) ) {
				throw new Exception( 'Burgerservicenummer not found in response.', 404 );
			}

			$bsn = $response['burgerservicenummer'];
		}

		if ( '' === $bsn || ! is_numeric( $bsn ) ) {
			throw new Exception( 'Invalid burgerservicenummer format, value is not numeric.', 500 );
		}

		return (string) $bsn;
	}

	/**
	 * @since 1.0.0
	 */
	protected function timeout_option_curl(): int
	{
		$timeout = apply_filters( 'owc_prefill_gravity_forms_curl_timeout', 10 );

		return is_int( $timeout ) && 0 < $timeout ? $timeout : 10;
	}

	/**
	 * @since 1.0.0
	 */
	protected function get_default_curl_args(): array
	{
		$args = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'GET',
		);

		if ( $this->settings->use_ssl_certificates() ) {
			$args[ CURLOPT_SSLCERT ] = $this->settings->get_public_certificate();
			$args[ CURLOPT_SSLKEY ]  = $this->settings->get_private_certificate();

			$supplier_certificate = $this->settings->get_supplier_certificate();
			if ( 0 < strlen( $supplier_certificate ) ) {
				$args[ CURLOPT_CAINFO ] = $supplier_certificate;
			}
		}

		return $args;
	}

	/**
	 * Prefilled fields have a custom css class.
	 * Based on this custom class fields are disabled.
	 *
	 * @since 1.0.0
	 */
	protected function disable_form_fields(): string
	{
		return view( 'disabledFormFields.php' );
	}
}
