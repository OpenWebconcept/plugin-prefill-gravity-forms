<?php

namespace OWC\PrefillGravityForms\Foundation;

/**
 * Provider which handles the registration of the plugin.
 */
abstract class ServiceProvider
{
    /**
     * Instance of the plugin.
     *
     * @var Plugin
     */
    protected $plugin;

    /**
     * Construction of the service provider.
     *
     * @param Plugin $plugin
     *
     * @return void
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Register the service provider.
     */
    abstract public function register();
}
