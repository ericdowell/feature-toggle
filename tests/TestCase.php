<?php

declare(strict_types=1);

namespace FeatureToggles\Tests;

use FeatureToggles\ServiceProvider;
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