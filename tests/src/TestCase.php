<?php

declare(strict_types=1);

namespace FeatureToggle\Tests;

use FeatureToggle\ServiceProvider;
use Orchestra\Testbench\TestCase as SupportTestCase;

class TestCase extends SupportTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $migrations = realpath(dirname(dirname(__DIR__)).'/migrations');

        $this->loadMigrationsFrom($migrations);
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
