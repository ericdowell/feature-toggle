<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\ConditionalToggleProvider;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggleProvider;

/**
 * @group unit
 */
class ConditionalToggleProviderTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @return \FeatureToggle\ConditionalToggleProvider
     */
    protected function getToggleProvider(): ConditionalToggleProvider
    {
        return new ConditionalToggleProvider();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\ConditionalToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        $provider = $this->getToggleProvider();
        if (! is_array($toggles)) {
            return $provider;
        }

        foreach ($toggles as $name => $toggle) {
            $provider->setToggle($name, $toggle);
        }

        return $provider;
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function getIsActiveAttribute($value): callable
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * @returns void
     */
    public function testCalculateTogglesViaRefreshToggles(): void
    {
        $provider = $this->getToggleProvider();
        $provider->setToggle('baz', function () {
            return true;
        })->setToggle('bar', function () {
            return false;
        });

        $provider->refreshToggles();

        $this->assertCount(2, $provider->getToggles());
    }
}
