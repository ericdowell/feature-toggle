<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\LocalToggleProvider;
use FeatureToggle\Tests\Traits\TestToggleProvider;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

/**
 * @coversDefaultClass \FeatureToggle\LocalToggleProvider
 */
class LocalToggleProviderTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @return \FeatureToggle\LocalToggleProvider
     */
    protected function getToggleProvider(): LocalToggleProvider
    {
        return (new LocalToggleProvider())->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\LocalToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        config()->set('feature-toggle.toggles', $toggles);

        return $this->getToggleProvider();
    }
}