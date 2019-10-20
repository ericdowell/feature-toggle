<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\Toggle\FeatureToggle;
use FeatureToggle\EloquentToggleProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FeatureToggle\Tests\Traits\TestToggleProvider;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

class EloquentToggleProviderTest extends TestCase
{
    use RefreshDatabase, TestToggleProvider;

    /**
     * @return \FeatureToggle\EloquentToggleProvider
     */
    protected function getToggleProvider(): EloquentToggleProvider
    {
        return (new EloquentToggleProvider())->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\EloquentToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        if (! $toggles) {
            return $this->getToggleProvider();
        }
        foreach ($toggles as $name => $is_active) {
            tap(new FeatureToggle(compact('name', 'is_active')), function (FeatureToggle $toggle) {
                $toggle->save();
            });
        }

        return $this->getToggleProvider();
    }
}