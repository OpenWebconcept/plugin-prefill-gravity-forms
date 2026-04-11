<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    1.0.0
 */

namespace OWC\PrefillGravityForms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use OWC\PrefillGravityForms\Traits\SessionTrait;

/**
 * @since 1.0.0
 */
class Helpers
{
	use SessionTrait;

	/**
	 * This method is publicly available for usage outside of this plugin.
	 *
	 * @since 1.0.0
	 */
	public static function current_user_has_bsn()
	{
		return ( new self() )->get_bsn();
	}
}
