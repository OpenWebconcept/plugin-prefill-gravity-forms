<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    NEXT
 */

namespace OWC\PrefillGravityForms\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use Monolog\Level;
use function OWC\PrefillGravityForms\Foundation\Helpers\resolve;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use Throwable;

/**
 * Provides exception logging via Monolog.
 *
 * @since NEXT
 */
trait Logger
{
	/**
	 * @since NEXT
	 */
	public function log_exception(Exception $exception, array $context = array() ): void
	{
		try {
			$level  = Level::from( $exception->getCode() );
			$method = $level->toPsrLogLevel();
		} catch ( Throwable $e ) {
			$method = 'error';
		}

		/** @var Logger */
		$logger = resolve( 'logger' );

		if ( ! method_exists( $logger, $method ) ) {
			$method = 'error';
		}

		/**
		 * Intercept the exception for further processing, such as logging to e.g. Sentry from the project itself.
		 *
		 * @param Exception $exception The exception to intercept.
		 * @param string    $method    PSR-3 log level name (e.g. 'error', 'debug').
		 *
		 * @since NEXT
		 */
		do_action( 'pg::exception/intercept', $exception, $method );

		if ( ! GravityFormsSettings::make()->logging_enabled() ) {
			return;
		}

		$logger->$method( sprintf( 'Yard | BRP Prefill GravityForms: %s', $exception->getMessage() ), $context );
	}
}
