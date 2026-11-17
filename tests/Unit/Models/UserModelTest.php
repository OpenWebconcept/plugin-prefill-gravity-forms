<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Tests\Models;

use Mockery;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeController;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeV2Controller;
use OWC\PrefillGravityForms\Models\UserModel;
use OWC\PrefillGravityForms\Tests\TestCase;
use WP_Mock;

class UserModelTest extends TestCase
{
    private const ADDRESS_GETTERS = [
        'zipcode',
        'houseNumber',
        'houseLetter',
        'houseNumberAddition',
        'street',
        'city',
        'nummeraanduidingIdentificatie',
    ];

    public function test_v2_address_getters(): void
    {
        $user = $this->userModel($this->fixture('brp-v2-raadpleeg-persoon.json'));

        $this->assertSame('1234AB', $user->zipcode());
        $this->assertSame('31', $user->houseNumber());
        $this->assertSame('A', $user->houseLetter());
        $this->assertSame('2', $user->houseNumberAddition());
        $this->assertSame('Voorbeeldstraat', $user->street());
        $this->assertSame('Voorbeeldstad', $user->city());
        $this->assertSame('0599200000219678', $user->nummeraanduidingIdentificatie());
    }

    public function test_v1_address_getters_are_unchanged(): void
    {
        $user = $this->userModel($this->fixture('brp-v1-ingeschrevenpersoon.json'));

        $this->assertSame('1234AB', $user->zipcode());
        $this->assertSame('31', $user->houseNumber());
        $this->assertSame('A', $user->houseLetter());
        $this->assertSame('2', $user->houseNumberAddition());
        $this->assertSame('Voorbeeldstraat', $user->street());
        $this->assertSame('Voorbeeldstad', $user->city());
        $this->assertSame('0599200000219678', $user->nummeraanduidingIdentificatie());
    }

    public function test_v1_alternative_street_and_city_keys(): void
    {
        $user = $this->userModel(['verblijfplaats' => ['straatnaam' => 'Laan', 'woonplaatsnaam' => 'Dorp']]);

        $this->assertSame('Laan', $user->street());
        $this->assertSame('Dorp', $user->city());
    }

    public function test_v2_path_takes_precedence_over_v1_path(): void
    {
        $user = $this->userModel([
            'verblijfplaats' => [
                'postcode' => '9999ZZ',
                'verblijfadres' => ['postcode' => '1234AB'],
            ],
        ]);

        $this->assertSame('1234AB', $user->zipcode());
    }

    public function test_empty_data_returns_empty_strings(): void
    {
        $user = $this->userModel([]);

        foreach (self::ADDRESS_GETTERS as $getter) {
            $this->assertSame('', $user->{$getter}(), $getter);
        }
    }

    public function test_location_without_address_returns_empty_strings(): void
    {
        $user = $this->userModel([
            'verblijfplaats' => [
                'type' => 'Locatie',
                'verblijfadres' => ['locatiebeschrijving' => 'Woonboot bij de brug'],
            ],
        ]);

        foreach (self::ADDRESS_GETTERS as $getter) {
            $this->assertSame('', $user->{$getter}(), $getter);
        }
    }

    /**
     * @dataProvider invalidNummeraanduidingProvider
     */
    public function test_invalid_nummeraanduiding_returns_empty_string(mixed $value): void
    {
        $user = $this->userModel(['verblijfplaats' => ['nummeraanduidingIdentificatie' => $value]]);

        $this->assertSame('', $user->nummeraanduidingIdentificatie());
    }

    public static function invalidNummeraanduidingProvider(): array
    {
        return [
            'letters' => ['abc'],
            'too short' => ['123'],
            'too long' => ['05992000002196789'],
            'array' => [['0599200000219678']],
            'null' => [null],
            'bool' => [true],
        ];
    }

    public function test_non_array_verblijfplaats_does_not_throw(): void
    {
        $user = $this->userModel(['verblijfplaats' => 'onbekend']);

        foreach (self::ADDRESS_GETTERS as $getter) {
            $this->assertSame('', $user->{$getter}(), $getter);
        }
    }

    public function test_residents_count_uses_v2_controller_and_is_memoized(): void
    {
        $this->mockSettings(['owc-iconnect-goal-binding' => 'BRPDoelbinding', 'owc-iconnect-processing' => 'Verwerking']);

        $controller = Mockery::mock(PinkRoccadeV2Controller::class);
        $controller->shouldReceive('countResidents')
            ->once()
            ->with('0599200000219678', 'BRPDoelbinding', 'Verwerking')
            ->andReturn(3);

        $user = $this->userModel($this->fixture('brp-v2-raadpleeg-persoon.json'), $controller);

        $this->assertSame(3, $user->residentsCount());
        $this->assertSame(3, $user->residentsCount());
    }

    public function test_residents_count_is_null_for_v1_supplier(): void
    {
        $controller = Mockery::mock(PinkRoccadeController::class);
        $controller->shouldNotReceive('countResidents');

        $user = $this->userModel($this->fixture('brp-v1-ingeschrevenpersoon.json'), $controller);

        $this->assertNull($user->residentsCount());
    }

    public function test_residents_count_is_null_without_controller(): void
    {
        $user = $this->userModel($this->fixture('brp-v2-raadpleeg-persoon.json'));

        $this->assertNull($user->residentsCount());
    }

    public function test_residents_count_is_null_when_not_logged_in(): void
    {
        $data = $this->fixture('brp-v2-raadpleeg-persoon.json');
        unset($data['burgerservicenummer']);

        $controller = Mockery::mock(PinkRoccadeV2Controller::class);
        $controller->shouldNotReceive('countResidents');

        $this->assertNull($this->userModel($data, $controller)->residentsCount());
    }

    public function test_residents_count_is_null_without_nummeraanduiding(): void
    {
        $data = $this->fixture('brp-v2-raadpleeg-persoon.json');
        unset($data['verblijfplaats']['nummeraanduidingIdentificatie']);

        $controller = Mockery::mock(PinkRoccadeV2Controller::class);
        $controller->shouldNotReceive('countResidents');

        $this->assertNull($this->userModel($data, $controller)->residentsCount());
    }

    private function userModel(array $data, ?BaseController $controller = null): UserModel
    {
        return new class($data, $controller) extends UserModel {
            public function __construct(array $data, ?BaseController $controller)
            {
                $this->data = $data;
                $this->controller = $controller;
            }
        };
    }

    private function mockSettings(array $options): void
    {
        WP_Mock::userFunction('get_option', ['return' => $options]);
    }

    private function fixture(string $name): array
    {
        $contents = file_get_contents(__DIR__ . '/../Fixtures/' . $name);
        $data = json_decode((string) $contents, true);

        return is_array($data) ? $data : [];
    }
}
