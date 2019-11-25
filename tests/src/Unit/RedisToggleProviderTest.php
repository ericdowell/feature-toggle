<?php

declare(strict_types=1);

namespace FeatureToggle\Tests;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\RedisToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleProvider;
use FeatureToggle\Toggle\Redis as RedisToggle;
use Illuminate\Foundation\Testing\Concerns\InteractsWithRedis;

class RedisToggleProviderTest extends TestCase
{
    use InteractsWithRedis, TestToggleProvider;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRedis();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownRedis();
    }

    /**
     * @returns void
     */
    public function testAllowSerializingRedisToggleInstances(): void
    {
        $toggleProvider = $this->setToggles([
            'foo' => new RedisToggle('foo', true),
            'bar' => new RedisToggle('bar', false),
            'baz' => new RedisToggle('baz','on'),
        ]);

        $this->assertCommonToggles($toggleProvider);
    }

    /**
     * @return \FeatureToggle\RedisToggleProvider
     */
    protected function getToggleProvider(): RedisToggleProvider
    {
        return (new RedisToggleProvider($this->redis['phpredis']))->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\RedisToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        $toggleProvider = $this->getToggleProvider();
        if (! $toggles) {
            return $toggleProvider;
        }
        $key = $toggleProvider->getPrefix().$toggleProvider->getKey();
        $toggleProvider->connection()->set($key, serialize($toggles));

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
}
