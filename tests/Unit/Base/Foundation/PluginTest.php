<?php

namespace OWC\PrefillGravityForms\Tests\Base\Foundation;

use WP_Mock;
use OWC\PrefillGravityForms\Foundation\Plugin;
use OWC\PrefillGravityForms\Tests\TestCase;

class PluginTest extends TestCase
{
    /** @var Plugin */
    protected $plugin;

    public function setUp(): void
    {
        WP_Mock::setUp();
        WP_Mock::passthruFunction('load_plugin_textdomain');

        if (!defined('PG_VERSION')) {
            define('PG_VERSION', '1.0.0');
        }

        $this->plugin = Plugin::getInstance('test');
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
    }

    /** @test */
    public function plugin_object_is_instance_of_plugin_class(): void
    {
        $this->assertInstanceOf(Plugin::class, $this->plugin);
    }

    /** @test */
    public function name_of_plugin_is_correct()
    {
        $this->assertEquals('prefill-gravity-forms', $this->plugin->getName());
    }

    /** @test */
    public function version_of_plugin_is_correct()
    {
        $this->assertEquals(PG_VERSION, $this->plugin->getVersion());
    }

    /** @test */
    public function rootpath_of_plugin_is_correct()
    {
        $this->assertEquals('test', $this->plugin->getRootPath());
    }

    /** @test */
    public function container_is_returned_correctly()
    {
        $this->assertInstanceOf(\DI\Container::class, $this->plugin->getContainer());
    }
}
