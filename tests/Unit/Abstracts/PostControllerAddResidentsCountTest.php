<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Tests\Abstracts;

use GF_Field;
use Mockery;
use Mockery\MockInterface;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Controllers\EnableUV2Controller;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeV2Controller;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Tests\TestCase;
use ReflectionProperty;
use WP_Mock;

class PostControllerAddResidentsCountTest extends TestCase
{
    private const ID = '0599200000219678';

    public function test_adds_count_when_a_field_is_mapped(): void
    {
        $controller = $this->controller(PinkRoccadeV2Controller::class);
        $controller->shouldReceive('countResidents')->once()->with(self::ID, 'doelbinding', 'verwerking')->andReturn(3);

        $response = $this->invokeMethod($controller, 'addResidentsCountToResponse', [
            $this->form(['aantalBewoners']),
            $this->person(),
            'doelbinding',
            'verwerking',
        ]);

        $this->assertSame('3', $response['aantalBewoners']);
    }

    public function test_does_not_count_when_no_field_is_mapped(): void
    {
        $controller = $this->controller(PinkRoccadeV2Controller::class);
        $controller->shouldNotReceive('countResidents');

        $response = $this->invokeMethod($controller, 'addResidentsCountToResponse', [
            $this->form(['verblijfplaats.verblijfadres.postcode']),
            $this->person(),
        ]);

        $this->assertSame($this->person(), $response);
    }

    public function test_response_is_unchanged_when_count_is_unknown(): void
    {
        $controller = $this->controller(PinkRoccadeV2Controller::class);
        $controller->shouldReceive('countResidents')->once()->andReturnNull();

        $response = $this->invokeMethod($controller, 'addResidentsCountToResponse', [
            $this->form(['aantalBewoners']),
            $this->person(),
        ]);

        $this->assertArrayNotHasKey('aantalBewoners', $response);
    }

    public function test_does_not_count_without_nummeraanduiding(): void
    {
        $controller = $this->controller(PinkRoccadeV2Controller::class);
        $controller->shouldNotReceive('countResidents');

        $person = $this->person();
        unset($person['verblijfplaats']['nummeraanduidingIdentificatie']);

        $response = $this->invokeMethod($controller, 'addResidentsCountToResponse', [$this->form(['aantalBewoners']), $person]);

        $this->assertSame($person, $response);
    }

    /**
     * @dataProvider v2ControllerProvider
     */
    public function test_text_field_mapped_to_residents_count_is_prefilled(string $controllerClass): void
    {
        $controller = $this->controller($controllerClass);
        $controller->shouldReceive('isBlockEditor')->andReturnFalse();
        $controller->shouldReceive('getBSN')->andReturn('999990019');
        $controller->shouldReceive('fetchApiResponse')->once()->andReturn($this->person());
        $controller->shouldReceive('disableFormFields')->andReturn('');
        $controller->shouldReceive('countResidents')->once()->with(self::ID, 'doelbinding', 'verwerking')->andReturn(3);

        WP_Mock::userFunction('rgar', [
            'return' => function (array $form, string $key, $default = null) {
                return $form[$key] ?? $default;
            },
        ]);

        $form = $this->form(['aantalBewoners']);
        $form['owc-iconnect-doelbinding'] = 'doelbinding';
        $form['owc-iconnect-processing'] = 'verwerking';

        $result = $controller->handle($form);

        $this->assertSame('3', $result['fields'][0]->defaultValue);
        $this->assertSame('owc_prefilled', $result['fields'][0]->cssClass);
    }

    public static function v2ControllerProvider(): array
    {
        return [
            'PinkRoccadeV2' => [PinkRoccadeV2Controller::class],
            'EnableUV2' => [EnableUV2Controller::class],
        ];
    }

    /**
     * @template T of BaseController
     *
     * @param class-string<T> $class
     *
     * @return T&MockInterface
     */
    private function controller(string $class): BaseController
    {
        WP_Mock::userFunction('get_option', ['return' => []]);

        /** @var T&MockInterface $controller */
        $controller = Mockery::mock($class)->makePartial()->shouldAllowMockingProtectedMethods();

        $settings = new ReflectionProperty(BaseController::class, 'settings');
        $settings->setValue($controller, GravityFormsSettings::make());

        return $controller;
    }

    /**
     * @param list<string> $mappings
     */
    private function form(array $mappings): array
    {
        $fields = array_map(function (string $mapping): GF_Field {
            $field = new GF_Field();
            $field->type = 'text';
            $field->linkedFieldValue = $mapping;

            return $field;
        }, $mappings);

        return ['fields' => $fields];
    }

    private function person(): array
    {
        $data = json_decode((string) file_get_contents(__DIR__ . '/../Fixtures/brp-v2-raadpleeg-persoon.json'), true);

        return is_array($data) ? $data : [];
    }
}
