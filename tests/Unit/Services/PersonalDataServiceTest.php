<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Tests\Services;

use Mockery;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Controllers\EnableUV2Controller;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeController;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeV2Controller;
use OWC\PrefillGravityForms\Services\PersonalDataService;
use OWC\PrefillGravityForms\Tests\TestCase;
use ReflectionProperty;
use WP_Mock;

class PersonalDataServiceTest extends TestCase
{
    private const ID = '0599200000219678';

    /**
     * @dataProvider v2ControllerProvider
     */
    public function test_residents_count_for_v2_suppliers(string $controllerClass): void
    {
        $controller = Mockery::mock($controllerClass)->makePartial();
        $controller->shouldReceive('get')->with('doelbinding', '')->andReturn($this->person());
        $controller->shouldReceive('countResidents')->once()->with(self::ID, 'doelbinding', '')->andReturn(3);

        $this->assertSame('3', $this->service($controller)->get('aantalBewoners', 'doelbinding'));
    }

    public static function v2ControllerProvider(): array
    {
        return [
            'PinkRoccadeV2' => [PinkRoccadeV2Controller::class],
            'EnableUV2' => [EnableUV2Controller::class],
        ];
    }

    public function test_residents_count_is_empty_for_v1_supplier(): void
    {
        $controller = Mockery::mock(PinkRoccadeController::class)->makePartial();
        $controller->shouldNotReceive('get');

        $this->assertSame('', $this->service($controller)->get('aantalBewoners'));
    }

    public function test_residents_count_is_empty_without_controller(): void
    {
        $this->assertSame('', $this->service(null)->get('aantalBewoners'));
    }

    public function test_residents_count_is_empty_without_session(): void
    {
        $controller = Mockery::mock(PinkRoccadeV2Controller::class)->makePartial();
        $controller->shouldReceive('get')->andReturn([]);
        $controller->shouldNotReceive('countResidents');

        $this->assertSame('', $this->service($controller)->get('aantalBewoners'));
    }

    public function test_residents_count_is_empty_without_nummeraanduiding(): void
    {
        $person = $this->person();
        unset($person['verblijfplaats']['nummeraanduidingIdentificatie']);

        $controller = Mockery::mock(PinkRoccadeV2Controller::class)->makePartial();
        $controller->shouldReceive('get')->andReturn($person);
        $controller->shouldNotReceive('countResidents');

        $this->assertSame('', $this->service($controller)->get('aantalBewoners'));
    }

    public function test_residents_count_is_empty_when_count_is_unknown(): void
    {
        $controller = Mockery::mock(PinkRoccadeV2Controller::class)->makePartial();
        $controller->shouldReceive('get')->andReturn($this->person());
        $controller->shouldReceive('countResidents')->once()->andReturnNull();

        $this->assertSame('', $this->service($controller)->get('aantalBewoners'));
    }

    public function test_other_keys_are_unchanged(): void
    {
        $controller = Mockery::mock(PinkRoccadeV2Controller::class)->makePartial();
        $controller->shouldReceive('get')->andReturn($this->person());
        $controller->shouldNotReceive('countResidents');

        $service = $this->service($controller);

        $this->assertSame('1234AB', $service->get('verblijfplaats.postcode'));
        $this->assertSame('Voorbeeldstraat', $service->get('verblijfplaats.straat'));
        $this->assertSame('31', $service->get('verblijfplaats.huisnummer'));
    }

    private function service(?BaseController $controller): PersonalDataService
    {
        WP_Mock::userFunction('get_option', ['return' => []]);

        $service = new PersonalDataService('PinkRoccadeV2');

        $property = new ReflectionProperty(PersonalDataService::class, 'controller');
        $property->setValue($service, $controller);

        return $service;
    }

    private function person(): array
    {
        $data = json_decode((string) file_get_contents(__DIR__ . '/../Fixtures/brp-v2-raadpleeg-persoon.json'), true);

        return is_array($data) ? $data : [];
    }
}
