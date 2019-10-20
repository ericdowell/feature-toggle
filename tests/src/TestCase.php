<?php

declare(strict_types=1);

namespace FeatureToggle\Tests;

use FeatureToggle\ServiceProvider;
use Orchestra\Testbench\TestCase as SupportTestCase;

class TestCase extends SupportTestCase
{
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
