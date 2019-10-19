<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\ConditionalToggleProvider;
use FeatureToggle\Tests\Traits\TestToggleProvider;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

/**
 * @coversDefaultClass \FeatureToggle\ConditionalToggleProvider
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
    public function toggleActive($value): callable
    {
        return function () use ($value) {
            return $value;
        };
    }
}