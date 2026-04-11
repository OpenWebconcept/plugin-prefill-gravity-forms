<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    NEXT
 */

namespace OWC\PrefillGravityForms\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use OWC\PrefillGravityForms\Abstracts\PostController;
use OWC\PrefillGravityForms\Services\CacheService;

/**
 * Controller for the EnableU V2 BRP supplier.
 *
 * @since NEXT
 */
class EnableUV2Controller extends PostController
{
	/**
	 * @since NEXT
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

		$goal_binding     = rgar( $form, 'owc-iconnect-doelbinding', '' );
		$processing       = rgar( $form, 'owc-iconnect-processing', '' ) ?: $this->settings->get_processing();
		$expand           = rgar( $form, 'owc-iconnect-expand', '' );
		$exclude_deceased = (bool) rgar( $form, 'owc-iconnect-exclude-deceased', false );
		$api_response     = $this->fetch_api_response( $bsn, $expand, $goal_binding, $processing, $exclude_deceased );

		if ( empty( $api_response ) ) {
			return $form;
		}

		echo $this->disable_form_fields();

		return $this->pre_fill_fields( $form, $api_response );
	}

	/**
	 * @since NEXT
	 */
	protected function make_request(string $goal_binding = '', string $processing = '' ): array
	{
		$bsn = $this->get_bsn();

		if ( '' === $bsn ) {
			return array();
		}

		return $this->fetch_api_response( $bsn, '', $goal_binding, $processing );
	}

	/**
	 * @since NEXT
	 */
	protected function prepare_data(string $bsn, string $expand = '' ): array
	{
		$fields = array(
			'aNummer',
			'adressering',
			'burgerservicenummer',
			'datumEersteInschrijvingGBA',
			'datumInschrijvingInGemeente',
			'geboorte',
			'gemeenteVanInschrijving',
			'geslacht',
			'immigratie',
			'leeftijd',
			'naam',
			'nationaliteiten',
			'overlijden',
			'verblijfplaats',
			'verblijfstitel',
			'verblijfplaatsBinnenland',
			'adresseringBinnenland',
		);

		if ( ! empty( $expand ) ) {
			$expand_fields = $this->get_expand_fields( $expand );
			$fields        = array_merge( $fields, $expand_fields );
		}

		return array(
			'type'                => 'RaadpleegMetBurgerservicenummer',
			'fields'              => $fields,
			'burgerservicenummer' => array( $bsn ),
		);
	}

	/**
	 * @since NEXT
	 */
	protected function fetch_api_response(string $bsn, string $expand = '', string $goal_binding = '', string $processing = '', bool $exclude_deceased = false ): array
	{
		$api_response = $this->request( $bsn, $expand, $goal_binding, $processing );
		$person_data  = $api_response['personen'] ?? array();
		$first_person = reset( $person_data ); // Response is in a multidimensional array which differs from other suppliers.

		if ( isset( $api_response['status'] ) || ! is_array( $first_person ) || ! count( $first_person ) ) {
			$message = 'Retrieving prefill data failed';

			if ( isset( $api_response['message'] ) ) {
				$message = sprintf( '%s: %s', $message, $api_response['message'] );
			}

			$this->log_exception( new Exception( $message, (int) ( $api_response['status'] ?? 500 ) ) );

			return array();
		}

		if ( $exclude_deceased ) {
			foreach ( array_filter( explode( ',', $expand ) ) as $embed_type ) {
				$first_person = $this->filter_deceased_from_embedded_relations( $first_person, trim( $embed_type ), $goal_binding, $processing );
			}
		}

		return $first_person;
	}

	/**
	 * @since NEXT
	 */
	protected function request(string $bsn, string $expand = '', string $goal_binding = '', string $processing = '' ): array
	{
		$processing = 0 < strlen( $processing ) ? $processing : $this->settings->get_processing();

		$curl_args = array(
			CURLOPT_URL        => $this->settings->get_base_url(),
			CURLOPT_POSTFIELDS => json_encode( $this->prepare_data( $bsn, $expand ) ),
			CURLOPT_HTTPHEADER => $this->get_curl_headers( $goal_binding, $processing ),
		);

		$location_bsn_in_response = array( 'personen.0.burgerservicenummer' );
		$transient_key            = $this->is_personal_data_service_request ? $bsn . '_personal_data_service' : $bsn;

		return $this->handle_curl( $curl_args, CacheService::formatTransientKey( $transient_key ), $location_bsn_in_response );
	}

	/**
	 * @since NEXT
	 */
	protected function request_embedded(string $bsn, string $goal_binding = '', string $processing = '' ): array
	{
		$processing = 0 < strlen( $processing ) ? $processing : $this->settings->get_processing();

		$curl_args = array(
			CURLOPT_URL        => $this->settings->get_base_url(),
			CURLOPT_POSTFIELDS => json_encode( $this->prepare_data( $bsn ) ),
			CURLOPT_HTTPHEADER => $this->get_curl_headers( $goal_binding, $processing ),
		);

		$location_bsn_in_response = array( 'personen.0.burgerservicenummer' );

		return $this->handle_curl( $curl_args, CacheService::formatTransientKey( $bsn ), $location_bsn_in_response );
	}
}
