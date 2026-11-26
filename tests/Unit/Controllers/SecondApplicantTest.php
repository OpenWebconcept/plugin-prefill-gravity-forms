<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Tests\Controllers;

use GF_Field;
use Mockery;
use OWC\PrefillGravityForms\Controllers\EnableUV2Controller;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeController;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeV2Controller;
use OWC\PrefillGravityForms\Controllers\WeAreFrankController;
use OWC\PrefillGravityForms\Tests\TestCase;
use WP_Mock;

class SecondApplicantTest extends TestCase
{
    private const BSN = '123456782';
    private const SECOND_BSN = '987654321';

    public function setUp(): void
    {
        parent::setUp();

        WP_Mock::userFunction('rgar', [
            'return' => function (array $array, string $key, $default = null) {
                return $array[$key] ?? $default;
            },
        ]);
        WP_Mock::userFunction('get_option', ['return' => []]);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function fields_are_prefilled_per_applicant(string $controller): void
    {
        $requestedBSNs = [];
        $mock = $this->controller($controller, self::BSN, self::SECOND_BSN, $requestedBSNs);

        $form = $mock->handle($this->form());

        $this->assertSame([self::BSN, self::SECOND_BSN], $requestedBSNs);
        $this->assertSame('Primary', $form['fields'][0]->defaultValue);
        $this->assertSame('Second', $form['fields'][1]->defaultValue);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function second_applicant_fields_are_untouched_when_second_applicant_is_not_logged_in(string $controller): void
    {
        $requestedBSNs = [];
        $mock = $this->controller($controller, self::BSN, '', $requestedBSNs);

        $form = $mock->handle($this->form());

        $this->assertSame([self::BSN], $requestedBSNs);
        $this->assertSame('Primary', $form['fields'][0]->defaultValue);
        $this->assertSame('', $form['fields'][1]->defaultValue);
        $this->assertSame('', $form['fields'][1]->cssClass);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function second_applicant_is_not_requested_when_form_has_no_second_applicant_fields(string $controller): void
    {
        $requestedBSNs = [];
        $mock = $this->controller($controller, self::BSN, self::SECOND_BSN, $requestedBSNs);

        $form = $this->form();
        unset($form['fields'][1]);
        $mock->handle($form);

        $this->assertSame([self::BSN], $requestedBSNs);
    }

    /**
     * @test
     * @dataProvider controllers
     */
    public function personal_data_is_retrieved_for_the_requested_applicant(string $controller): void
    {
        $requestedBSNs = [];

        $this->assertSame('Primary', $this->controller($controller, self::BSN, self::SECOND_BSN, $requestedBSNs)->get()['naam']['voornamen']);
        $this->assertSame('Second', $this->controller($controller, self::BSN, self::SECOND_BSN, $requestedBSNs)->get('', '', true)['naam']['voornamen']);
        $this->assertSame([], $this->controller($controller, self::BSN, '', $requestedBSNs)->get('', '', true));
        $this->assertSame([self::BSN, self::SECOND_BSN], $requestedBSNs);
    }

    public function controllers(): array
    {
        return [
            'PinkRoccade' => [PinkRoccadeController::class],
            'PinkRoccade V2' => [PinkRoccadeV2Controller::class],
            'EnableU V2' => [EnableUV2Controller::class],
            'WeAreFrank' => [WeAreFrankController::class],
        ];
    }

    private function form(): array
    {
        return [
            'fields' => [
                new GF_Field(['type' => 'text', 'linkedFieldValue' => 'naam.voornamen']),
                new GF_Field(['type' => 'text', 'linkedFieldValue' => 'naam.voornamen', 'linkedFieldSecondApplicant' => true]),
            ],
        ];
    }

    private function controller(string $controller, string $bsn, string $secondBSN, array &$requestedBSNs)
    {
        $names = [self::BSN => 'Primary', self::SECOND_BSN => 'Second'];

        $mock = Mockery::mock($controller . '[getBSN,getSecondApplicantBSN,request]', [])->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getBSN')->andReturn($bsn);
        $mock->shouldReceive('getSecondApplicantBSN')->andReturn($secondBSN);
        $mock->shouldReceive('request')->andReturnUsing(function (string $bsn) use (&$requestedBSNs, $names) {
            $requestedBSNs[] = $bsn;
            $person = ['burgerservicenummer' => $bsn, 'naam' => ['voornamen' => $names[$bsn]]];

            return ['personen' => [$person]] + $person;
        });

        return $mock;
    }
}
