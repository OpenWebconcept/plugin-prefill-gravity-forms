<?php

namespace OWC\PrefillGravityForms\Foundation;

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

    /**
     * Instance of the Hook loader.
     */
    public Loader $loader;

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
        load_plugin_textdomain($this->getName(), false, $this->getName() . '/languages/');
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
            'config' => function () {
                return new Config($this->rootPath . '/config');
            },
            'loader' => Loader::getInstance(),

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
        $this->loader = resolve('loader');

        $dependencyChecker = new DependencyChecker($this->config->get('core.dependencies'));

        if ($dependencyChecker->failed()) {
            $dependencyChecker->notify();
            deactivate_plugins(plugin_basename($this->rootPath . '/' . $this->getName() . '.php'));

            return false;
        }

        // Set up service providers
        $this->callServiceProviders('register');
        $this->callServiceProviders('boot');

        $this->loader->register();

        return true;
    }


    /**
     * Call method on service providers.
     *
     * @throws \Exception
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
                throw new \Exception('Provider must be an instance of ServiceProvider.');
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
}
