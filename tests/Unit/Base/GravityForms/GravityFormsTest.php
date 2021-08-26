<?php

namespace OWC\PrefillGravityForms\Tests\Base\GravityForms;

use WP_Mock;
use OWC\PrefillGravityForms\GravityForms\GravityForms;
use OWC\PrefillGravityForms\Foundation\Plugin;
use OWC\PrefillGravityForms\Tests\TestCase;

class GravityFormsTest extends TestCase
{
    /** @var Plugin */
    protected $plugin;

    public function setUp(): void
    {
        WP_Mock::setUp();

        \WP_Mock::userFunction('get_option', [
            'return' => []
        ]);

        $this->gravityForms = new GravityForms();
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
    }

    /** @test */
    public function find_nested_item_in_array_from_dot_notation_string(): void
    {
        $result   = $this->gravityForms->findLinkedValue('depthOne.depthTwo.depthThree', ['depthOne' => ['depthTwo' => ['depthThree' => 'test']]]);
        $expected = 'test';

        $this->assertEquals($result, $expected);
    }

    /** @test */
    public function not_find_nested_item_in_array_from_dot_notation_string(): void
    {
        $result   = $this->gravityForms->findLinkedValue('depthOne.depthTwo.depthThree', ['depthOne' => ['depthTwo' => ['depthThree' => 'test']]]);
        $expected = 'testt';

        $this->assertNotEquals($result, $expected);
    }
}
