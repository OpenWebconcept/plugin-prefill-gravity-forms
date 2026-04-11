<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

namespace OWC\PrefillGravityForms\Foundation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;

/**
 * Handles encryption and decryption of strings using OpenSSL.
 *
 * @since 1.0.0
 */
class Cryptor
{
	protected string $method = 'aes-128-ctr'; // default cipher method if none supplied
	private string $key;

	/**
	 * @since 1.0.0
	 */
	public function __construct(bool $method = false )
	{
		$key = \AUTH_KEY ?? php_uname();
		if ( ctype_print( $key ) ) {
			// convert ASCII keys to binary format
			$this->key = openssl_digest( $key, 'SHA256', true );
		} else {
			$this->key = $key;
		}
		if ( $method ) {
			if ( ! in_array( strtolower( $method ), openssl_get_cipher_methods() ) ) {
				throw new Exception( __METHOD__ . ": unrecognised cipher method: {$method}" );
			}
			$this->method = $method;
		}
	}

	/**
	 * @since 1.0.0
	 */
	protected function iv_bytes(): int
	{
		return openssl_cipher_iv_length( $this->method );
	}

	/**
	 * @since 1.0.0
	 */
	public function encrypt($data )
	{
		$iv = openssl_random_pseudo_bytes( $this->iv_bytes() );

		return bin2hex( $iv ) . openssl_encrypt( $data, $this->method, $this->key, 0, $iv );
	}

	/**
	 * Decrypt an encrypted string.
	 *
	 * @since 1.0.0
	 */
	public function decrypt($data )
	{
		$iv_strlen = 2 * $this->iv_bytes();
		if ( preg_match( "/^(.{" . $iv_strlen . "})(.+)$/", $data, $regs ) ) {
			list(, $iv, $crypted_string) = $regs;
			if ( ctype_xdigit( $iv ) && 0 == strlen( $iv ) % 2 ) {
				return openssl_decrypt( $crypted_string, $this->method, $this->key, 0, hex2bin( $iv ) );
			}
		}

		return false; // failed to decrypt
	}
}
