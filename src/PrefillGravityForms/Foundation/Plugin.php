<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Foundation;

use function DI\create;
use Exception;
use function OWC\PrefillGravityForms\Foundation\Helpers\resolve;

/**
 * BasePlugin which sets all the serviceproviders.
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
    protected string $rootPath;

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
     * Constructor of the BasePlugin
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        require_once __DIR__ . '/Helpers.php';
        $this->buildContainer();
    }

    /**
     * Return the Plugin instance
     */
    public static function getInstance(string $rootPath = ''): self
    {
        if (null == static::$instance) {
            static::$instance = new static($rootPath);
        }

        return static::$instance;
    }

    protected function buildContainer(): void
    {
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions([
            'app' => $this,
            'config' => create(Config::class)->constructor($this->rootPath . '/config'),
            'logger' => function () {
                $logger = new \Monolog\Logger('pg_log');
                $maxFiles = apply_filters('pg::logger/rotating_filer_handler_max_files', PG_LOGGER_DEFAULT_MAX_FILES);

                $handler = (new \Monolog\Handler\RotatingFileHandler(
                    filename:  sprintf('%s/pg-log.json', $this->getLogFilePath()),
                    maxFiles: is_int($maxFiles) && 0 < $maxFiles ? $maxFiles : PG_LOGGER_DEFAULT_MAX_FILES,
                    level: \Monolog\Level::Debug
                ))->setFormatter(new \Monolog\Formatter\JsonFormatter());

                $logger->pushHandler($handler);
                $logger->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

                return $logger;
            },
        ]);
        $this->container = $builder->build();
    }

    public function getContainer(): \DI\Container
    {
        return $this->container;
    }

    /**
     * Boot the plugin.
     */
    public function boot(): bool
    {
        $this->config = resolve('config');

        $this->loadTextDomain();

        // Set up service providers
        $this->callServiceProviders('register');
        $this->callServiceProviders('boot');

        return true;
    }

    private function loadTextDomain(): void
    {
        load_plugin_textdomain($this->getName(), false, $this->getName() . '/languages/');
    }

    /**
     * Call method on service providers.
     *
     * @throws Exception
     */
    public function callServiceProviders(string $method, string $key = ''): void
    {
        $offset = $key ? "core.providers.{$key}" : 'core.providers';
        $services = $this->config->get($offset);

        foreach ($services as $service) {
            if (is_array($service)) {
                continue;
            }

            $service = new $service($this);

            if (! $service instanceof ServiceProvider) {
                throw new Exception('Provider must be an instance of ServiceProvider.');
            }

            if (method_exists($service, $method)) {
                $service->$method();
            }
        }
    }

    /**
     * Get the name of the plugin.
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * Get the version of the plugin.
     */
    public function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * Return root path of plugin.
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Get the path to a particular resource.
     */
    public function resourceUrl(string $file, string $directory = ''): string
    {
        $directory = ! empty($directory) ? $directory . '/' : '';

        return plugins_url("build/{$directory}{$file}", $this->getName() . '/plugin.php');
    }

    public function resourcePath(string $file, string $directory = ''): string
    {
        $directory = ! empty($directory) ? $directory . '/' : '';

        return $this->rootPath . "/build/{$directory}{$file}";
    }

    /**
     * Wrapper function to get the path the log file is written to.
     * Why? The default dirname(ABSPATH) resolves to the __PUBLIC__ website root directory when WordPress
     * is installed in a subdirectory (for example: composer-based websites. This is not ideal for logging,
     * so we allow the user to override this path via a filter, constant, or environment variable.
     *
     * @return mixed|string|null
     */
    private function getLogFilePath()
    {
        $logFilePath = dirname(ABSPATH);

        $potentialLogFilePath = null;
        // Allow log override via filter;
        $potentialLogFilePath = apply_filters('pg::logger/log_file_path', $potentialLogFilePath);

        // Allow log override via environment variable;
        if ( !empty($_ENV['PREFILL_GRAVITY_FORMS_LOG_FILE_PATH'])) {
            $potentialLogFilePath = $_ENV['PREFILL_GRAVITY_FORMS_LOG_FILE_PATH'];
        }

        // Allow log override via constant;
        if (defined('PREFILL_GRAVITY_FORMS_LOG_FILE_PATH')) {
            $potentialLogFilePath = PREFILL_GRAVITY_FORMS_LOG_FILE_PATH;
        }

        // If the potential log file path is valid, use it instead of the default.
        if (!empty($potentialLogFilePath) && is_string($potentialLogFilePath) && is_dir($potentialLogFilePath) && is_writable($potentialLogFilePath)) {
            $logFilePath = $potentialLogFilePath;
        }

        return $logFilePath;
    }
}
