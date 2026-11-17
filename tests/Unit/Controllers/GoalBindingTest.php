<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Tests\Controllers;

use Mockery;
use OWC\PrefillGravityForms\Controllers\EnableUV2Controller;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeV2Controller;
use OWC\PrefillGravityForms\Controllers\WeAreFrankController;
use OWC\PrefillGravityForms\Tests\TestCase;
use WP_Mock;

class GoalBindingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        WP_Mock::userFunction('rgar', [
            'return' => function (array $array, string $key, $default = null) {
                return $array[$key] ?? $default;
            },
        ]);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function general_goal_binding_is_used_when_form_has_none(string $controller): void
    {
        $headers = $this->requestHeaders($controller, '', 'general');

        $this->assertContains('x-doelbinding: general', $headers);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function goal_binding_of_form_overrides_general_goal_binding(string $controller): void
    {
        $headers = $this->requestHeaders($controller, 'form', 'general');

        $this->assertContains('x-doelbinding: form', $headers);
        $this->assertNotContains('x-doelbinding: general', $headers);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function no_goal_binding_is_sent_when_both_are_empty(string $controller): void
    {
        $headers = $this->requestHeaders($controller, '', '');

        $this->assertNotEmpty($headers);
        $this->assertSame([], preg_grep('/^x-doelbinding:/', $headers));
    }

    public function controllers(): array
    {
        return [
            'PinkRoccade V2' => [PinkRoccadeV2Controller::class],
            'EnableU V2' => [EnableUV2Controller::class],
            'WeAreFrank' => [WeAreFrankController::class],
        ];
    }

    /**
     * Headers of the request made when the controller handles a form.
     */
    private function requestHeaders(string $controller, string $formGoalBinding, string $generalGoalBinding): array
    {
        WP_Mock::userFunction('get_option', [
            'return' => ['owc-iconnect-goal-binding' => $generalGoalBinding],
        ]);

        $bsn = '123456782';
        $headers = [];

        $mock = Mockery::mock($controller . '[getBSN,handleCurl,disableFormFields]', [])->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getBSN')->andReturn($bsn);
        $mock->shouldReceive('disableFormFields')->andReturn('');
        $mock->shouldReceive('handleCurl')->once()->andReturnUsing(function (array $args) use (&$headers, $bsn) {
            $headers = $args[CURLOPT_HTTPHEADER];

            return ['personen' => [['burgerservicenummer' => $bsn]]];
        });

        $mock->handle(['owc-iconnect-doelbinding' => $formGoalBinding, 'fields' => []]);

        return $headers;
    }
}
