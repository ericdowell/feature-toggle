<?php

declare(strict_types=1);

namespace FeatureToggle\Tests;

use FeatureToggle\ServiceProvider;
use FeatureToggle\Facades\FeatureToggleApi;
use Orchestra\Testbench\TestCase as SupportTestCase;

class TestCase extends SupportTestCase
{
    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['FeatureToggleApi' => FeatureToggleApi::class];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
