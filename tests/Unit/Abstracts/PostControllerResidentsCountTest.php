<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Tests\Abstracts;

use Exception;
use Mockery;
use Mockery\MockInterface;
use OWC\PrefillGravityForms\Controllers\BaseController;
use OWC\PrefillGravityForms\Controllers\PinkRoccadeV2Controller;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;
use OWC\PrefillGravityForms\Services\EncryptionService;
use OWC\PrefillGravityForms\Tests\TestCase;
use ReflectionProperty;
use WP_Mock;

class PostControllerResidentsCountTest extends TestCase
{
    private const ID = '0599200000219678';
    private const OPTIONS = [
        'owc-iconnect-base-url' => 'https://brp.example.test/personen',
        'owc-iconnect-oin-number' => '00000001234567890000',
        'owc-iconnect-processing' => 'StandaardVerwerking',
        'owc-iconnect-goal-binding' => 'AlgemeneDoelbinding',
    ];

    public function setUp(): void
    {
        parent::setUp();

        // The test without encryption key runs in a separate process where the constant must stay undefined.
        if ('test_count_is_returned_without_encryption_key' !== $this->getName(false) && ! defined('PG_CACHE_ENCRYPTION_KEY')) {
            define('PG_CACHE_ENCRYPTION_KEY', str_repeat('k', 32));
        }
    }

    public function test_counts_residents_and_caches_only_the_count(): void
    {
        $controller = $this->controller();
        $controller->shouldReceive('executeCurlRequest')->once()->andReturn([
            'status' => 200,
            'body' => $this->fixture('brp-v2-zoek-nummeraanduiding.json'),
        ]);
        $controller->shouldNotReceive('logException');

        WP_Mock::userFunction('get_transient', ['return' => false]);

        $stored = null;
        WP_Mock::userFunction('set_transient', [
            'times' => 1,
            'args' => [hash('sha256', 'residents_count_' . self::ID), '*', HOUR_IN_SECONDS],
            'return' => function (string $key, string $value) use (&$stored): bool {
                $stored = $value;

                return true;
            },
        ]);

        $this->assertSame(3, $controller->countResidents(self::ID));
        $this->assertIsString($stored);
        $this->assertSame(['count' => 3], EncryptionService::decrypt($stored));
    }

    public function test_returns_cached_count_without_request(): void
    {
        $controller = $this->controller();
        $controller->shouldNotReceive('executeCurlRequest');

        WP_Mock::userFunction('get_transient', [
            'args' => [hash('sha256', 'residents_count_' . self::ID)],
            'return' => EncryptionService::encrypt(['count' => 2]),
        ]);

        $this->assertSame(2, $controller->countResidents(self::ID));
    }

    public function test_empty_persons_list_counts_as_zero(): void
    {
        $controller = $this->controller();
        $controller->shouldReceive('executeCurlRequest')->once()->andReturn(['status' => 200, 'body' => ['personen' => []]]);

        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient', ['times' => 1, 'return' => true]);

        $this->assertSame(0, $controller->countResidents(self::ID));
    }

    /**
     * @dataProvider failedStatusProvider
     */
    public function test_failed_status_returns_null_and_logs_without_caching(int $status): void
    {
        $controller = $this->controller();
        $controller->shouldReceive('executeCurlRequest')->once()->andReturn([
            'status' => $status,
            'body' => ['title' => 'Probleem', 'detail' => 'Nummeraanduiding ' . self::ID . ' niet toegestaan'],
        ]);
        $controller->shouldReceive('logException')->once()->with(Mockery::on(function (Exception $e) use ($status): bool {
            return sprintf('Residents count request failed with HTTP status %d.', $status) === $e->getMessage();
        }));

        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient', ['times' => 0]);

        $this->assertNull($controller->countResidents(self::ID));
    }

    public static function failedStatusProvider(): array
    {
        return [
            'forbidden (goal binding)' => [403],
            'server error' => [500],
        ];
    }

    public function test_request_exception_returns_null_and_logs_fixed_message(): void
    {
        $controller = $this->controller();
        $controller->shouldReceive('executeCurlRequest')->once()->andThrow(new Exception('Response body is not valid JSON.', 500));
        $controller->shouldReceive('logException')->once()->with(Mockery::on(function (Exception $e): bool {
            return 'Response body is not valid JSON.' === $e->getMessage();
        }));

        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient', ['times' => 0]);

        $this->assertNull($controller->countResidents(self::ID));
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function test_invalid_id_returns_null_without_request(string $id): void
    {
        $controller = $this->controller();
        $controller->shouldNotReceive('executeCurlRequest');
        $controller->shouldNotReceive('logException');

        $this->assertNull($controller->countResidents($id));
    }

    public static function invalidIdProvider(): array
    {
        return [
            'empty' => [''],
            'letters' => ['abc'],
            'too short' => ['123'],
        ];
    }

    public function test_requests_disabled_returns_null_without_request(): void
    {
        $controller = $this->controller(['owc-iconnect-disable-requests' => '1']);
        $controller->shouldNotReceive('executeCurlRequest');

        $this->assertNull($controller->countResidents(self::ID));
    }

    public function test_request_body_and_headers(): void
    {
        $controller = $this->controller();
        $captured = [];
        $controller->shouldReceive('executeCurlRequest')->once()->with(Mockery::on(function (array $args) use (&$captured): bool {
            $captured = $args;

            return true;
        }))->andReturn(['status' => 200, 'body' => ['personen' => []]]);

        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient', ['return' => true]);

        $controller->countResidents(self::ID, 'BRPDoelbinding');

        $this->assertSame(
            '{"type":"ZoekMetNummeraanduidingIdentificatie","nummeraanduidingIdentificatie":"0599200000219678","fields":["burgerservicenummer"]}',
            $captured[CURLOPT_POSTFIELDS]
        );
        $this->assertSame(self::OPTIONS['owc-iconnect-base-url'], $captured[CURLOPT_URL]);
        $this->assertContains('x-doelbinding: BRPDoelbinding', $captured[CURLOPT_HTTPHEADER]);
        $this->assertContains('x-verwerking: StandaardVerwerking', $captured[CURLOPT_HTTPHEADER]);
    }

    public function test_general_goal_binding_is_used_when_none_is_given(): void
    {
        $controller = $this->controller();
        $captured = [];
        $controller->shouldReceive('executeCurlRequest')->once()->with(Mockery::on(function (array $args) use (&$captured): bool {
            $captured = $args;

            return true;
        }))->andReturn(['status' => 200, 'body' => ['personen' => []]]);

        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient', ['return' => true]);

        $controller->countResidents(self::ID);

        $this->assertContains('x-doelbinding: AlgemeneDoelbinding', $captured[CURLOPT_HTTPHEADER]);
    }

    public function test_bsn_cache_keys_are_never_used(): void
    {
        $controller = $this->controller();
        $controller->shouldReceive('executeCurlRequest')->andReturn([
            'status' => 200,
            'body' => $this->fixture('brp-v2-zoek-nummeraanduiding.json'),
        ]);

        $residentsKey = hash('sha256', 'residents_count_' . self::ID);
        WP_Mock::userFunction('get_transient', ['times' => 1, 'args' => [$residentsKey], 'return' => false]);
        WP_Mock::userFunction('set_transient', ['times' => 1, 'args' => [$residentsKey, '*', '*'], 'return' => true]);

        $this->assertSame(3, $controller->countResidents(self::ID));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_count_is_returned_without_encryption_key(): void
    {
        $controller = $this->controller();
        $controller->shouldReceive('executeCurlRequest')->once()->andReturn([
            'status' => 200,
            'body' => $this->fixture('brp-v2-zoek-nummeraanduiding.json'),
        ]);
        $controller->shouldReceive('logException')->once();

        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient', ['times' => 0]);

        $this->assertSame(3, $controller->countResidents(self::ID));
    }

    /**
     * @return PinkRoccadeV2Controller&MockInterface
     */
    private function controller(array $options = []): PinkRoccadeV2Controller
    {
        WP_Mock::userFunction('get_option', ['return' => $options + self::OPTIONS]);

        /** @var PinkRoccadeV2Controller&MockInterface $controller */
        $controller = Mockery::mock(PinkRoccadeV2Controller::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $settings = new ReflectionProperty(BaseController::class, 'settings');
        $settings->setValue($controller, GravityFormsSettings::make());

        return $controller;
    }

    private function fixture(string $name): array
    {
        $data = json_decode((string) file_get_contents(__DIR__ . '/../Fixtures/' . $name), true);

        return is_array($data) ? $data : [];
    }
}
