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
 * Abstract base for GET-based BRP API controllers.
 *
 * @since 1.9.0
 */
abstract class GetController extends BaseController
{
	protected const BRP_API_VERSION = '1';

	/**
	 * Filters deceased persons from the given embedded relation collection.
	 *
	 * For each embedded item containing a BSN, additional person data is requested
	 * and the relation is removed when the person is marked as deceased
	 * ('opschortingBijhouding.reden.omschrijving' === 'overlijden').
	 *
	 * @since 1.9.0
	 */
	protected function filter_deceased_from_embedded_relations(array $api_response, string $embed_type = '', string $goal_binding = '' ): array
	{
		if ( ! isset( $api_response['_embedded'][ $embed_type ] ) ) {
			return $api_response;
		}

		foreach ( $api_response['_embedded'][ $embed_type ] as $key => $embedded_item ) {
			if ( ! isset( $embedded_item['_links']['ingeschrevenPersoon']['href'] ) ) {
				continue;
			}

			$response = $this->request_embedded( $this->normalize_common_ground_url( $embedded_item['_links']['ingeschrevenPersoon']['href'] ), $goal_binding );

			if ( true === ( $response['overlijden']['indicatieOverleden'] ?? false ) ) {
				unset( $api_response['_embedded'][ $embed_type ][ $key ] );
			}
		}

		$api_response['_embedded'][ $embed_type ] = array_values( $api_response['_embedded'][ $embed_type ] );

		return $api_response;
	}

	/**
	 * Normalizes incorrectly embedded HaalCentraal BRP endpoints originating from a supplier.
	 *
	 * Certain Common Ground VrijBRP environments return URLs without the required `/api`
	 * prefix (e.g. `/haal-centraal-brp-bevragen/...` instead of
	 * `/api/haalcentraal-brp-bevragen/...`). As a result, follow-up requests would fail.
	 *
	 * This method transparently corrects the malformed path for commonground.nu domains
	 * until the supplier resolves the issue in their endpoint generation.
	 *
	 * IMPORTANT:
	 * This is a temporary compatibility workaround and should be removed once the
	 * supplier provides correctly structured URLs.
	 *
	 * @since 1.9.0
	 */
	private function normalize_common_ground_url(string $url ): string
	{
		$parts = parse_url( $url );

		if ( ! is_array( $parts ) || ! isset( $parts['host'], $parts['path'] ) ) {
			return $url;
		}

		$host = $parts['host'];
		$path = $parts['path'];

		// Only for commonground.nu domains (incl. subdomains).
		if ( substr( $host, -strlen( 'commonground.nu' ) ) !== 'commonground.nu' ) {
			return $url;
		}

		// Only correct if the path starts incorrectly.
		if ( strpos( $path, '/haal-centraal-brp-bevragen' ) !== 0 ) {
			return $url;
		}

		// Replace only the leading segment (safer than blind str_replace).
		$path = preg_replace(
			'#^/haal-centraal-brp-bevragen#',
			'/api/haalcentraal-brp-bevragen',
			$path
		);

		// Rebuild URL safely
		$scheme   = isset( $parts['scheme'] ) ? $parts['scheme'] : 'https';
		$port     = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		$query    = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
		$fragment = isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';

		return $scheme . '://' . $host . $port . $path . $query . $fragment;
	}

	abstract protected function request_embedded(string $url, string $goal_binding ): array;
}
