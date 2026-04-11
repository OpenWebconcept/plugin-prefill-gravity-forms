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

/**
 * Renders template views with variable binding.
 *
 * @since 1.0.0
 */
class View
{
	protected string $template_directory = PG_ROOT_PATH . '/resources/views/';
	protected array $vars                = array();
	protected array $bindings            = array(); // Associative array of variables that will be accessible from the template.

	/**
	 * @since 1.0.0
	 */
	public function __construct($template_directory = null )
	{
		if ( null !== $template_directory ) {
			// Check here whether this directory really exists
			$this->template_directory = $template_directory;
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function exists(string $template_file = '' ): bool
	{
		return is_file( $this->template_directory . $template_file );
	}

	/**
	 * Render the view.
	 *
	 * @since 1.0.0
	 */
	public function render(string $template_file = '', array $vars = array() ): string
	{
		if ( ! is_file( $this->template_directory . $template_file ) ) {
			return '';
		}

		$this->bind_all( $vars );
		ob_start();
		include $this->template_directory . $template_file;
		$data = trim( ob_get_clean() );

		return $this->parse_template( $data, $this->bindings );
	}

	/**
	 * Render a view by providing the absolute path to the view file.
	 * This method is useful for rendering views that are not located in the plugin's views directory.
	 *
	 * @since 1.0.0
	 */
	public function render_full_path(string $full_path = '', array $vars = array() ): string
	{
		if ( ! is_file( $full_path ) ) {
			return '';
		}

		$this->bind_all( $vars );
		ob_start();
		include $full_path;
		$data = trim( ob_get_clean() );

		return $this->parse_template( $data, $this->bindings );
	}

	/**
	 * Search and replace of variables.
	 * Searching for {{VARIABLE}}.
	 *
	 * @since 1.0.0
	 */
	protected function parse_template(string $template, array $bindings = array() ): string
	{
		return preg_replace_callback(
			'#{{\s?(.*?)\s?}}#',
			function ($match ) use ($bindings ) {
				$match[1] = trim( $match[1], '' );

				return $bindings[ $match[1] ] ?? '';
			},
			$template
		);
	}

	/**
	 * Bind a single variable that will be accessible when the view is rendered.
	 *
	 * @since 1.0.0
	 */
	public function bind(string $parameter, $value )
	{
		$this->bindings[ $parameter ] = $value;
	}

	/**
	 * Bind multiple parameters at once.
	 *
	 * @see   View:bind()
	 * @since 1.0.0
	 */
	public function bind_all(array $bindings ): void
	{
		foreach ( $bindings as $parameter => $value ) {
			$this->bind( $parameter, $value );
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function __set($name, $value )
	{
		$this->vars[ $name ] = $value;
	}

	/**
	 * @since 1.0.0
	 */
	public function __get($name )
	{
		return $this->vars[ $name ];
	}
}
