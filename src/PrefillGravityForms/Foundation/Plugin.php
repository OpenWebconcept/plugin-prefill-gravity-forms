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
    const NAME = 'prefill-gravity-forms';

    /**
     * Version of the plugin.
     * Used for setting versions of enqueue scripts and styles.
     *
     * @var string VERSION
     */
    const VERSION = \PG_VERSION;

    /**
     * Path to the root of the plugin.
     *
     * @var string $rootPath
     */
    protected $rootPath;

    /**
     * Instance of the configuration repository.
     *
     * @var Config
     */
    public $config;

    /**
     * Instance of the Hook loader.
     *
     * @var Loader
     */
    public $loader;

    /**
     * @var \DI\Container
     */
    protected $container;

    /**
     * @var Plugin
     */
    protected static $instance;

    /**
     * Constructor of the BasePlugin
     *
     * @param string $rootPath
     *
     * @return void
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
     *
     * @param string $rootPath
     *
     * @return self
     */
    public static function getInstance($rootPath = ''): self
    {
        if (null == static::$instance) {
            static::$instance = new static($rootPath);
        }

        return static::$instance;
    }

    /**
     * @return \DI\Container
     */
    protected function buildContainer()
    {
        $builder         = new \DI\ContainerBuilder();
        $builder->addDefinitions([
            'app'         => $this,
            'config'   => function () {
                return new Config($this->rootPath . '/config');
            },
            'loader' => Loader::getInstance(),

        ]);
        $this->container = $builder->build();
    }

    /**
     * Return container
     *
     * @return \DI\Container
     */
    public function getContainer(): \DI\Container
    {
        return $this->container;
    }

    /**
     * Boot the plugin.
     *
     * @return bool
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
     * @param string $method
     * @param string $key
     *
     * @return void
     *
     * @throws \Exception
     */
    public function callServiceProviders($method, $key = '')
    {
        $offset   = $key ? "core.providers.{$key}" : 'core.providers';
        $services = $this->config->get($offset);

        foreach ($services as $service) {
            if (is_array($service)) {
                continue;
            }

            $service = new $service($this);

            if (!$service instanceof ServiceProvider) {
                throw new \Exception('Provider must be an instance of ServiceProvider.');
            }

            if (method_exists($service, $method)) {
                $service->$method();
            }
        }
    }

    /**
     * Get the name of the plugin.
     *
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * Get the version of the plugin.
     *
     * @return string
     */
    public function getVersion()
    {
        return static::VERSION;
    }

    /**
     * Return root path of plugin.
     *
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
