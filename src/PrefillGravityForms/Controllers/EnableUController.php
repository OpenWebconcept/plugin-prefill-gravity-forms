<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.1.0
 */

namespace OWC\PrefillGravityForms\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use OWC\PrefillGravityForms\Abstracts\GetController;
use OWC\PrefillGravityForms\Services\CacheService;

/**
 * Controller for the EnableU (V1) BRP supplier.
 *
 * @since 1.1.0
 */
class EnableUController extends GetController
{
	/**
	 * @since 1.1.0
	 */
	public function handle(array $form ): array
	{
		if ( $this->is_block_editor() ) {
			return $form;
		}

		$bsn = $this->get_bsn();

		if ( '' === $bsn ) {
			return $form;
		}

		$goal_binding = rgar( $form, 'owc-iconnect-doelbinding', '' );
		$expand       = rgar( $form, 'owc-iconnect-expand', '' );

		if ( ! is_string( $goal_binding ) ) {
			$goal_binding = (string) $goal_binding;
		}

		$exclude_deceased = (bool) rgar( $form, 'owc-iconnect-exclude-deceased', false );
		$api_response     = $this->fetch_api_response( $bsn, $goal_binding, $expand, $exclude_deceased );

		if ( empty( $api_response ) ) {
			return $form;
		}

		echo $this->disable_form_fields();

		return $this->pre_fill_fields( $form, $api_response );
	}

	/**
	 * @since 1.1.0
	 */
	protected function make_request(string $goal_binding = '', string $processing = '' ): array
	{
		$bsn = $this->get_bsn();

		if ( '' === $bsn ) {
			return array();
		}

		return $this->fetch_api_response( $bsn, $goal_binding );
	}

	/**
	 * @since 1.1.0
	 */
	protected function fetch_api_response(string $bsn, string $goal_binding = '', string $expand = '', bool $exclude_deceased = false ): array
	{
		$api_response = $this->request( $bsn, $goal_binding, $expand );

		if ( isset( $api_response['status'] ) ) {
			$message = 'Retrieving prefill data failed';

			if ( isset( $api_response['message'] ) ) {
				$message = sprintf( '%s: %s', $message, $api_response['message'] );
			}

			$this->log_exception( new Exception( $message, (int) ( $api_response['status'] ?? 500 ) ) );

			return array();
		}

		if ( $exclude_deceased ) {
			foreach ( array_filter( explode( ',', $expand ) ) as $expand_item ) {
				$api_response = $this->filter_deceased_from_embedded_relations( $api_response, trim( $expand_item ), $goal_binding );
			}
		}

		return $api_response;
	}

	/**
	 * @since 1.1.0
	 */
	protected function request(string $bsn = '', string $goal_binding = '', string $expand = '' ): array
	{
		$curl_args = array(
			CURLOPT_URL        => $this->get_request_url( $bsn, $expand ),
			CURLOPT_HTTPHEADER => $this->get_curl_headers( $goal_binding ),
		);

		$transient_key = $this->is_personal_data_service_request ? $bsn . '_personal_data_service' : $bsn;

		return $this->handle_curl( $curl_args, CacheService::formatTransientKey( $transient_key ) );
	}

	/**
	 * @since 1.1.0
	 */
	protected function request_embedded(string $url, string $goal_binding ): array
	{
		$curl_args = array(
			CURLOPT_URL        => $url,
			CURLOPT_HTTPHEADER => $this->get_curl_headers( $goal_binding ),
		);

		$url_parts = explode( '/', $url );
		$bsn       = is_array( $url_parts ) && 0 < count( $url_parts ) ? end( $url_parts ) : '';

		return $this->handle_curl( $curl_args, CacheService::formatTransientKey( $bsn ) );
	}
}
