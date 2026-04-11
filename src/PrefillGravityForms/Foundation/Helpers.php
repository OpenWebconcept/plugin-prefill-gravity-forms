<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

namespace OWC\PrefillGravityForms\Foundation\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use OWC\PrefillGravityForms\Foundation\Plugin;

function app(): Plugin
{
	return resolve( 'app' );
}

function make($name, $container )
{
	return \Yard\DigiD\Foundation\Plugin::get_instance()->getContainer()->set( $name, $container );
}

function storage_path(string $path = '' ): string
{
	return \ABSPATH . '../../storage/' . $path;
}

function resolve($container, $arguments = array() )
{
	return \OWC\PrefillGravityForms\Foundation\Plugin::get_instance()->get_container()->get( $container, $arguments );
}

/**
 * Encrypt a string.
 *
 * @since 1.0.0
 */
function encrypt($string ): string
{
	try {
		$encrypted = resolve( \OWC\PrefillGravityForms\Foundation\Cryptor::class )->encrypt( $string );
	} catch ( Exception $e ) {
		$encrypted = '';
	}

	return $encrypted;
}

/**
 * Decrypt a string.
 *
 * @since 1.0.0
 */
function decrypt($string ): string
{
	try {
		$decrypted = resolve( \OWC\PrefillGravityForms\Foundation\Cryptor::class )->decrypt( $string );
	} catch ( Exception $e ) {
		$decrypted = '';
	}

	return $decrypted ?: '';
}

function config(string $setting, $default = '' )
{
	return resolve( 'config' )->get( $setting, $default );
}

function view(string $template, array $vars = array() ): string
{
	$view = resolve( \OWC\PrefillGravityForms\Foundation\View::class );

	if ( ! $view->exists( $template ) ) {
		return '';
	}

	return $view->render( $template, $vars );
}

/**
 * Get the current selected supplier on a per form basis.
 * Returns label as default, use parameter $getKey to return the key from the config array.
 *
 * @since 1.0.0
 */
function get_supplier(array $form, bool $getKey = false ): string
{
	$allowed  = config( 'suppliers.mapping', array() );
	$supplier = $form['owc-form-setting-supplier'] ?? '';

	if ( ! is_array( $allowed ) || 0 === count( $allowed ) || '' === $supplier ) {
		return '';
	}

	if ( ! in_array( $supplier, array_keys( $allowed ) ) ) {
		return '';
	}

	if ( $getKey ) {
		return $supplier;
	}

	return $allowed[ $supplier ] ?? '';
}
