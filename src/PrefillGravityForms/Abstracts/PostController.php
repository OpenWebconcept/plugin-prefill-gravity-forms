<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.9.0
 */

namespace OWC\PrefillGravityForms\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use OWC\PrefillGravityForms\Controllers\BaseController;

/**
 * Abstract base for POST-based BRP API controllers.
 *
 * @since 1.9.0
 */
abstract class PostController extends BaseController
{
	protected const BRP_API_VERSION = '2';

	/**
	 * Filters deceased persons from the given embedded relation collection.
	 *
	 * For each embedded item containing a BSN, additional person data is requested
	 * and the relation is removed when the person is marked as deceased
	 * ('opschortingBijhouding.reden.omschrijving' === 'overlijden').
	 *
	 * @since 1.9.0
	 */
	protected function filter_deceased_from_embedded_relations(array $api_response, string $embed_type = '', string $goal_binding = '', string $processing = '' ): array
	{
		if ( ! isset( $api_response[ $embed_type ] ) ) {
			return $api_response;
		}

		foreach ( $api_response[ $embed_type ] as $key => $embedded_item ) {
			if ( ! isset( $embedded_item['burgerservicenummer'] ) || ! is_numeric( $embedded_item['burgerservicenummer'] ) ) {
				continue;
			}

			$response     = $this->request_embedded( (string) $embedded_item['burgerservicenummer'], $goal_binding, $processing );
			$person_data  = $response['personen'] ?? array();
			$first_person = reset( $person_data ); // Response is in a multidimensional array which differs from other suppliers.

			if ( ! is_array( $first_person ) || 0 === count( $first_person ) ) {
				continue;
			}

			if ( 'overlijden' === ( $first_person['opschortingBijhouding']['reden']['omschrijving'] ?? '' ) ) {
				unset( $api_response[ $embed_type ][ $key ] );
			}
		}

		$api_response[ $embed_type ] = array_values( $api_response[ $embed_type ] );

		return $api_response;
	}

	/**
	 * Splits the expand parameter into an array of fields.
	 *
	 * @param string $expand Comma-separated list of additional fields.
	 *
	 * @return array An array of expanded fields.
	 * @since  1.9.0
	 */
	protected function get_expand_fields(string $expand ): array
	{
		return array_filter( explode( ',', $expand ) );
	}

	/**
	 * @since 1.9.0
	 */
	protected function get_default_curl_args(): array
	{
		$args = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
		);

		if ( $this->settings->use_ssl_certificates() ) {
			$args[ CURLOPT_SSLCERT ] = $this->settings->get_public_certificate();
			$args[ CURLOPT_SSLKEY ]  = $this->settings->get_private_certificate();
		}

		return $args;
	}

	/**
	 * Prepares the data payload for querying a citizen's information using their BSN (Burgerservicenummer).
	 *
	 * This method constructs a data array containing the necessary fields for a query.
	 * Additional fields can be included by passing a comma-separated string to the $expand parameter.
	 *
	 * @since 1.9.0
	 */
	abstract protected function prepare_data(string $bsn, string $expand = '' ): array;

	/**
	 * Sends a request to retrieve embedded data for a given BSN.
	 * Embedded data include related entities such as family members, addresses, etc.
	 *
	 * @since 1.9.0
	 */
	abstract protected function request_embedded(string $bsn, string $goal_binding = '', string $processing = '' ): array;
}
