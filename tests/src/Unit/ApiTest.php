<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Api;
use FeatureToggle\ConditionalToggleProvider;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\EloquentToggleProvider;
use FeatureToggle\Facades\FeatureToggleApi;
use FeatureToggle\LocalToggleProvider;
use FeatureToggle\QueryStringToggleProvider;
use FeatureToggle\RedisToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleValidation;
use FeatureToggle\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\Concerns\InteractsWithRedis;
use OutOfBoundsException;
use RuntimeException;
use stdClass;

class ApiTest extends TestCase
{
    use InteractsWithRedis, TestToggleProvider, TestToggleValidation;

    /**
     * @return string
     */
    protected function getValidationMessage(): string
    {
        return 'The data field is required.';
    }

    /**
     * @param  string  $name
     * @param  bool  $checkActive
     * @return string|object
     */
    protected function getValidationRule(string $name, $checkActive = true)
    {
        return feature_toggle_api()->requiredIfRule($name, $checkActive);
    }

    /**
     * @return Api
     */
    protected function getToggleProvider(): Api
    {
        return feature_toggle_api()->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return Api|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        config()->set('feature-toggle.toggles', $toggles);

        return $this->getToggleProvider();
    }

    /**
     * Get redis driver provider.
     *
     * @return array
     */
    public function redisDriverProvider()
    {
        return [
            ['phpredis'],
        ];
    }

    /**
     * @return void
     */
    public function testRegisterOrIgnoreMiddlewareMethods(): void
    {
        $this->assertTrue(feature_toggle_api()->isMiddlewareEnabled(),
            '"isMiddlewareEnabled" should BE true by default.');

        FeatureToggleApi::ignoreMiddleware();
        $this->assertFalse(feature_toggle_api()->isMiddlewareEnabled(),
            '"isMiddlewareEnabled" should BE false after calling "Api::ignoreMiddleware()"');

        FeatureToggleApi::registerMiddleware();
        $this->assertTrue(feature_toggle_api()->isMiddlewareEnabled(),
            '"isMiddlewareEnabled" should BE true after calling "Api::registerMiddleware()"');
    }

    /**
     * @return void
     */
    public function testUseOrIgnoreMigrationsMethods(): void
    {
        $this->assertFalse(feature_toggle_api()->isMigrationsEnabled(),
            '"isMigrationsEnabled" should BE false by default.');

        FeatureToggleApi::useMigrations();
        $this->assertTrue(feature_toggle_api()->isMigrationsEnabled(),
            '"isMigrationsEnabled" should BE true after calling "Api::useMigrations()"');

        FeatureToggleApi::ignoreMigrations();
        $this->assertFalse(feature_toggle_api()->isMigrationsEnabled(),
            '"isMigrationsEnabled" should BE false after calling "Api::ignoreMigrations()"');
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $featureToggleApi = feature_toggle_api();

        $this->assertStringContainsString('primary-', $featureToggleApi->getName());
    }

    /**
     * @returns void
     */
    public function testGetRedisProviderReturnsInstance(): void
    {
        $this->ifRedisAvailable(function () {
            $redisProvider = feature_toggle_api()->loadProvider('redis')->getRedisProvider();
            $this->assertInstanceOf(RedisToggleProvider::class, $redisProvider);
        });
    }

    /**
     * @return void
     */
    public function testLocalAndConditionalToggleProviders(): void
    {
        $featureToggleApi = $this->setToggles([
            'foo' => false,
            'bar' => 'on',
        ]);

        feature_toggle_api()->setConditional('baz', function () {
            return true;
        })->setConditional('bar', function () {
            return false;
        });

        $this->assertFalse($featureToggleApi->isActive('foo'), '"foo" toggle check, should BE false.');
        $this->assertFalse($featureToggleApi->isActive('bar'), '"bar" toggle check, should BE false.');
        $this->assertTrue($featureToggleApi->isActive('baz'), '"baz" toggle check, should BE true.');
        $this->assertCount(3, $featureToggleApi->getToggles());
        $this->assertCount(2, $featureToggleApi->getProviderToggles(LocalToggleProvider::NAME));
        $this->assertCount(2, $featureToggleApi->getProviderToggles(ConditionalToggleProvider::NAME));
        $this->assertCount(1, $featureToggleApi->getActiveToggles());
    }

    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testSetProvidersEloquentAndConditionalToggleProviders(): void
    {
        $this->setToggles([
            'local' => true,
        ]);
        feature_toggle_api()->setProviders([
            [
                'driver' => 'conditional',
            ],
            [
                'driver' => 'querystring',
            ],
        ]);
        $toggles = [
            'foo' => false,
            'bar' => 'on',
        ];

        QueryStringToggleProviderTest::setupQueryToggles($this->app['request'], $toggles);

        $featureToggleApi = $this->getToggleProvider();

        feature_toggle_api()->setConditional('baz', function () {
            return true;
        })->setConditional('bar', function () {
            return false;
        });

        $this->assertFalse($featureToggleApi->isActive('local'), '"local" toggle check, should BE false.');
        $this->assertFalse($featureToggleApi->isActive('foo'), '"foo" toggle check, should BE false.');
        $this->assertFalse($featureToggleApi->isActive('bar'), '"bar" toggle check, should BE false.');
        $this->assertTrue($featureToggleApi->isActive('baz'), '"baz" toggle check, should BE true.');
        $this->assertCount(3, $featureToggleApi->getToggles());
        $this->assertCount(1, $featureToggleApi->getActiveToggles());
    }

    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testGetProviderAndHelperMethods(): void
    {
        feature_toggle_api()->setProviders([
            [
                'driver' => LocalToggleProvider::NAME,
            ],
            [
                'driver' => ConditionalToggleProvider::NAME,
            ],
            [
                'driver' => EloquentToggleProvider::NAME,
            ],
            [
                'driver' => QueryStringToggleProvider::NAME,
            ],
        ]);
        $providerLocal = feature_toggle_api()->getProvider(LocalToggleProvider::NAME);
        $this->assertInstanceOf(LocalToggleProvider::class, $providerLocal);

        $methods = [
            'getConditionalProvider' => ConditionalToggleProvider::class,
            'getEloquentProvider' => EloquentToggleProvider::class,
            'getLocalProvider' => LocalToggleProvider::class,
            'getQueryStringProvider' => QueryStringToggleProvider::class,
        ];
        foreach ($methods as $method => $instanceof) {
            $this->assertInstanceOf($instanceof, call_user_func([
                feature_toggle_api(),
                $method,
            ]));
        }
    }

    /**
     * @returns void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testLoadProviderAndRefreshProvider(): void
    {
        $driver = LocalToggleProvider::NAME;
        feature_toggle_api()->setProviders([]);
        $this->assertCount(0, feature_toggle_api()->getProviders());

        config()->set('feature-toggle.toggles', ['foo' => true]);
        feature_toggle_api()->loadProvider($driver);
        $this->assertCount(1, feature_toggle_api()->getProviders());

        $this->assertCount(0, feature_toggle_api()->getLocalProvider()->getToggles());

        $provider = feature_toggle_api()->refreshProvider($driver)->getLocalProvider();
        $this->assertCount(1, $provider->getToggles());
    }

    /**
     * @return void
     */
    public function testGetProviderWithProviderNameNotLoadedThrowsError(): void
    {
        $name = 'foobar';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Toggle provider '{$name}' is not loaded.");
        feature_toggle_api()->getProvider($name);
    }

    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testSetProvidersWithUnregisterProviderDriverThrowsError(): void
    {
        $this->expectException(BindingResolutionException::class);

        feature_toggle_api()->setProviders([
            [
                'driver' => 'foobar',
            ],
        ]);
    }

    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testSetProvidersWithNonProviderContractClassThrowsError(): void
    {
        $driver = 'foobar';
        $this->app->bind("feature-toggle.{$driver}", stdClass::class);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Could not load toggle provider: '{$driver}'");

        feature_toggle_api()->setProviders([
            [
                'driver' => 'foobar',
            ],
        ]);
    }
}
