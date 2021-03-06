<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\LocalToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleProvider;
use FeatureToggle\Tests\TestCase;

class LocalToggleProviderTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @return \FeatureToggle\LocalToggleProvider
     */
    protected function getToggleProvider(): LocalToggleProvider
    {
        return (new LocalToggleProvider($this->app['config']))->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\LocalToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        $this->app['config']->set('feature-toggle.toggles', $toggles);

        return $this->getToggleProvider();
    }
}
