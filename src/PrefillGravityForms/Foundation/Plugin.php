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

use function DI\create;
use Exception;
use function OWC\PrefillGravityForms\Foundation\Helpers\resolve;

/**
 * BasePlugin which sets all the service providers.
 *
 * @since 1.0.0
 */
class Plugin
{
	/**
	 * Name of the plugin.
	 *
	 * @var string
	 */
	public const NAME = 'prefill-gravity-forms';

	/**
	 * Version of the plugin.
	 * Used for setting versions of enqueue scripts and styles.
	 */
	public const VERSION = \PG_VERSION;

	/**
	 * Path to the root of the plugin.
	 */
	protected string $root_path;

	/**
	 * Instance of the configuration repository.
	 */
	public Config $config;

	protected \DI\Container $container;

	/**
	 * @var Plugin
	 */
	protected static $instance;

	/**
	 * Constructor of the Plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $root_path )
	{
		$this->root_path = $root_path;
		require_once __DIR__ . '/Helpers.php';
		$this->build_container();
	}

	/**
	 * Return the Plugin instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance(string $root_path = '' ): self
	{
		if ( null == static::$instance ) {
			static::$instance = new static( $root_path );
		}

		return static::$instance;
	}

	/**
	 * @since 1.0.0
	 */
	protected function build_container(): void
	{
		$builder = new \DI\ContainerBuilder();
		$builder->addDefinitions(
			array(
				'app'    => $this,
				'config' => create( Config::class )->constructor( $this->root_path . '/config' ),
				'logger' => function () {
					$logger = new \Monolog\Logger( 'pg_log' );
					$max_files = apply_filters( 'pg::logger/rotating_filer_handler_max_files', PG_LOGGER_DEFAULT_MAX_FILES );

					$handler = ( new \Monolog\Handler\RotatingFileHandler(
						filename:  sprintf( '%s/pg-log.json', dirname( ABSPATH ) ),
						maxFiles: is_int( $max_files ) && 0 < $max_files ? $max_files : PG_LOGGER_DEFAULT_MAX_FILES,
						level: \Monolog\Level::Debug
					) )->setFormatter( new \Monolog\Formatter\JsonFormatter() );

					$logger->pushHandler( $handler );
					$logger->pushProcessor( new \Monolog\Processor\IntrospectionProcessor() );

					return $logger;
				},
			)
		);
		$this->container = $builder->build();
	}

	/**
	 * @since 1.0.0
	 */
	public function get_container(): \DI\Container
	{
		return $this->container;
	}

	/**
	 * Boot the plugin.
	 *
	 * @since 1.0.0
	 */
	public function boot(): bool
	{
		$this->config = resolve( 'config' );

		$this->load_text_domain();

		// Set up service providers
		$this->call_service_providers( 'register' );
		$this->call_service_providers( 'boot' );

		return true;
	}

	/**
	 * @since 1.0.0
	 */
	private function load_text_domain(): void
	{
		load_plugin_textdomain( $this->get_name(), false, $this->get_name() . '/languages/' );
	}

	/**
	 * Call method on service providers.
	 *
	 * @throws Exception
	 * @since  1.0.0
	 */
	public function call_service_providers(string $method, string $key = '' ): void
	{
		$offset   = $key ? "core.providers.{$key}" : 'core.providers';
		$services = $this->config->get( $offset );

		foreach ( $services as $service ) {
			if ( is_array( $service ) ) {
				continue;
			}

			$service = new $service( $this );

			if ( ! $service instanceof ServiceProvider ) {
				throw new Exception( 'Provider must be an instance of ServiceProvider.' );
			}

			if ( method_exists( $service, $method ) ) {
				$service->$method();
			}
		}
	}

	/**
	 * Get the name of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function get_name(): string
	{
		return static::NAME;
	}

	/**
	 * Get the version of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function get_version(): string
	{
		return static::VERSION;
	}

	/**
	 * Return root path of plugin.
	 *
	 * @since 1.0.0
	 */
	public function get_root_path(): string
	{
		return $this->root_path;
	}

	/**
	 * Get the path to a particular resource.
	 *
	 * @since 1.0.0
	 */
	public function resource_url(string $file, string $directory = '' ): string
	{
		$directory = ! empty( $directory ) ? $directory . '/' : '';

		return plugins_url( "build/{$directory}{$file}", $this->get_name() . '/plugin.php' );
	}

	/**
	 * @since 1.0.0
	 */
	public function resource_path(string $file, string $directory = '' ): string
	{
		$directory = ! empty( $directory ) ? $directory . '/' : '';

		return $this->root_path . "/build/{$directory}{$file}";
	}
}
