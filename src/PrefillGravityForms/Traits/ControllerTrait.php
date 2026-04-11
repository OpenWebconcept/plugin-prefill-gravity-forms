<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.7.0
 */

namespace OWC\PrefillGravityForms\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;

/**
 * Provides controller resolution by supplier name.
 *
 * @since 1.7.0
 */
trait ControllerTrait
{
	/**
	 * Get the controller instance for the given supplier.
	 *
	 * @throws Exception
	 * @since 1.7.0
	 */
	private function get_controller( string $supplier, string $config_id = '' ): BaseController
	{
		$controller = sprintf( 'OWC\PrefillGravityForms\Controllers\%sController', $supplier );

		if ( ! class_exists( $controller ) ) {
			throw new Exception( sprintf( 'Controller class %s does not exist.', $controller ), 500 );
		}

		return new $controller( $config_id );
	}
}
