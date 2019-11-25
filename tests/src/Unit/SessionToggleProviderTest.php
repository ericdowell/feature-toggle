<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\SessionToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleProvider;
use FeatureToggle\Tests\TestCase;

class SessionToggleProviderTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @return \FeatureToggle\SessionToggleProvider
     */
    protected function getToggleProvider(): SessionToggleProvider
    {
        return (new SessionToggleProvider($this->app['session']->driver()))->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\SessionToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        $this->app['session']->put('feature-toggles', $toggles);

        return $this->getToggleProvider();
    }
}
