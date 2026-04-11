<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.2.0
 */

namespace OWC\PrefillGravityForms\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;

/**
 * Provides BSN retrieval from DigiD session sources.
 *
 * @since 1.2.0
 */
trait SessionTrait
{
	use Logger;

	/**
	 * @since 1.2.0
	 */
	protected function get_bsn(): string
	{
		if ( $bsn = $this->idp_digid() ) {
			return $this->validate_bsn( $bsn );
		}

		if ( $bsn = $this->saml_digid() ) {
			return $this->validate_bsn( $bsn );
		}

		return '';
	}

	/**
	 * @since 1.2.0
	 */
	private function idp_digid(): string
	{
		if ( ! class_exists( '\OWC\IdpUserData\DigiDSession' ) ) {
			return '';
		}

		if ( ! \OWC\IdpUserData\DigiDSession::isLoggedIn() || is_null( \OWC\IdpUserData\DigiDSession::getUserData() ) ) {
			return '';
		}

		return \OWC\IdpUserData\DigiDSession::getUserData()->getBsn();
	}

	/**
	 * @since 1.2.0
	 */
	private function saml_digid(): string
	{
		if ( ! function_exists( '\\Yard\\DigiD\\Foundation\\Helpers\\resolve' ) ) {
			return '';
		}

		if ( ! function_exists( '\\Yard\\DigiD\\Foundation\\Helpers\\decrypt' ) ) {
			return '';
		}

		$bsn = \Yard\DigiD\Foundation\Helpers\resolve( 'session' )->getSegment( 'digid' )->get( 'bsn' );

		return ! empty( $bsn ) && is_string( $bsn ) ? \Yard\DigiD\Foundation\Helpers\decrypt( $bsn ) : '';
	}

	/**
	 * @since 1.2.0
	 */
	private function validate_bsn(string $bsn )
	{
		$bsn = $this->supplement_bsn( $bsn );

		if ( strlen( $bsn ) !== 9 ) {
			$this->log_exception( new Exception( 'BSN does not meet the required length of 9.', 400 ) );

			return '';
		}

		return $bsn;
	}

	/**
	 * BSN numbers could start with one or more zero's at the beginning.
	 * The zero's are not returned by DigiD so the required length of 9 characters is not met.
	 * Supplement the value so it meets the required length of 9.
	 *
	 * @since 1.2.0
	 */
	private function supplement_bsn(string $bsn ): string
	{
		$bsn_length      = strlen( $bsn );
		$required_length = 9;
		$difference      = $required_length - $bsn_length;

		if ( 1 > $difference || $difference > $required_length ) {
			return $bsn;
		}

		return sprintf( "%'.0" . $required_length . 'd', $bsn );
	}
}
