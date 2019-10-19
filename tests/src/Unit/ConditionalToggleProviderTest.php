<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\ConditionalToggleProvider;
use FeatureToggle\Tests\Traits\TestToggleProvider;

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
        if (! is_array($toggles)) {
            return $this->getToggleProvider();
        }
        $provider = $this->getToggleProvider();
        foreach ($toggles as $name => $toggle) {
            $provider->setToggle($name, $toggle);
        }

        return $provider;
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function toggleActive($value)
    {
        return function () use ($value) {
            return $value;
        };
    }
}