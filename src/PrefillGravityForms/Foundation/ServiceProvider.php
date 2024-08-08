<?php

namespace OWC\PrefillGravityForms\Foundation;

abstract class ServiceProvider
{
    protected Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    abstract public function register();
}
