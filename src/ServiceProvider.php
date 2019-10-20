<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Api as ApiContract;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    /**
     * Register Resource Controller services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPrimaryToggleProvider();

        $this->registerToggleProviders();

        $this->mergeConfigFrom($this->packageConfigFilePath(), $this->packageName());
    }

    /**
     * Register the primary feature toggle provider implementation.
     *
     * @return void
     */
    protected function registerPrimaryToggleProvider()
    {
        $this->app->singleton(ApiContract::class, function () {
            $providers = config('feature-toggle.providers', [
                [
                    'driver' => 'local',
                ],
            ]);

            return new Api($providers);
        });
        $this->app->alias(ApiContract::class, 'feature-toggle.api');
    }

    /**
     * Register the primary feature toggle provider implementation.
     *
     * @return void
     */
    protected function registerToggleProviders()
    {
        $this->app->singleton('feature-toggle.local', function () {
            return new LocalToggleProvider();
        });

        $this->app->singleton('feature-toggle.conditional', function () {
            return new ConditionalToggleProvider();
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->packageConfigFilePath() => config_path($this->packageConfigFilename()),
        ], $this->packageName());
    }

    /**
     * Name of the package.
     *
     * @return string
     */
    protected function packageName()
    {
        return 'feature-toggle';
    }

    /**
     * Return the base path for this package.
     *
     * @param  string  $path
     *
     * @return string
     */
    protected function packageBasePath(string $path)
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Filename of config for package.
     *
     * @return string
     */
    protected function packageConfigFilename()
    {
        return $this->packageName().'.php';
    }

    /**
     * File path of config for package.
     *
     * @return string
     */
    protected function packageConfigFilePath()
    {
        return $this->packageBasePath('config'.DIRECTORY_SEPARATOR.$this->packageConfigFilename());
    }
}
