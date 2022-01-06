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
        Parent::setUp();

        \WP_Mock::userFunction('get_option', [
            'return' => []
        ]);

        $this->gravityForms = new GravityForms();
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

    /** @test */
    public function supplement_bsn_correctly(): void
    {
        $result = $this->gravityForms->supplementBSN('1');
        $expected = '000000001';

        $this->assertEquals($result, $expected);
    }
}
