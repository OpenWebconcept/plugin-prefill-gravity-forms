<?php

declare(strict_types=1);

/**
 * @package  OWC\PrefillGravityForms
 * @author   Yard | Digital Agency
 * @since    NEXT
 */

namespace OWC\PrefillGravityForms\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use Exception;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Traits\ControllerTrait;
use OWC\PrefillGravityForms\Traits\Logger;

/**
 * Provides typed accessors for the currently logged-in citizen's personal data.
 *
 * @since NEXT
 */
class UserModel
{
	use ControllerTrait;
	use Logger;

	protected string $supplier;
	protected ?BaseController $controller;
	protected array $data;

	/**
	 * @since NEXT
	 */
	public function __construct()
	{
		$this->supplier   = GravityFormsSettings::make()->get_supplier();
		$this->controller = $this->handle_controller();
		$this->data       = $this->controller?->get() ?? array();
	}

	/**
	 * @since NEXT
	 */
	private function handle_controller(): ?BaseController
	{
		if ( ! GravityFormsSettings::make()->is_user_model_enabled() ) {
			return null;
		}

		try {
			return $this->get_controller( $this->supplier );
		} catch ( Exception $e ) {
			$this->log_exception( $e );

			return null;
		}
	}

	/**
	 * Use this method to determine whether the user is logged in or not before using any of the class methods.
	 * A DigiD login is required to retrieve user data.
	 *
	 * @since NEXT
	 */
	public function is_logged_in(): bool
	{
		$bsn = (string) $this->bsn();

		return 7 < strlen( $bsn ) && 10 > strlen( $bsn );
	}

	/**
	 * @since NEXT
	 */
	public function bsn(): int
	{
		return (int) ( $this->data['burgerservicenummer'] ?? 0 );
	}

	/**
	 * @since NEXT
	 */
	public function age(): int
	{
		return (int) ( $this->data['leeftijd'] ?? 0 );
	}

	/**
	 * @since NEXT
	 */
	public function initials(): string
	{
		return (string) ( $this->data['naam']['voorletters'] ?? '' );
	}

	/**
	 * @since NEXT
	 */
	public function first_names(): string
	{
		return (string) ( $this->data['naam']['voornamen'] ?? '' );
	}

	/**
	 * @since NEXT
	 */
	public function last_name(): string
	{
		return (string) ( $this->data['naam']['geslachtsnaam'] ?? '' );
	}

	/**
	 * @since NEXT
	 */
	public function last_name_prefix(): string
	{
		return (string) ( $this->data['naam']['voorvoegsel'] ?? '' );
	}

	/**
	 * @since NEXT
	 */
	public function full_name(bool $with_initials = false ): string
	{
		$name_parts = array(
			$with_initials ? $this->initials() : $this->first_names(),
			$this->last_name_prefix(),
			$this->last_name(),
		);

		return implode( ' ', array_filter( $name_parts ) );
	}

	/**
	 * @since NEXT
	 */
	public function zipcode(): string
	{
		return (string) ( $this->data['verblijfplaats']['postcode'] ?? '' );
	}

	/**
	 * @since NEXT
	 */
	public function house_number(): string
	{
		return (string) ( $this->data['verblijfplaats']['huisnummer'] ?? '' );
	}

	/**
	 * @since NEXT
	 */
	public function house_letter(): string
	{
		return (string) ( $this->data['verblijfplaats']['huisletter'] ?? '' );
	}
}
