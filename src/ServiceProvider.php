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
    public function register(): void
    {
        $this->registerPrimaryToggleProvider();

        $this->registerConditionalToggleProviders();
        $this->registerEloquentToggleProviders();
        $this->registerLocalToggleProviders();

        $this->mergeConfigFrom($this->packageConfigFilePath(), $this->packageName());
    }

    /**
     * Register the primary feature toggle provider implementation.
     *
     * @return void
     */
    protected function registerPrimaryToggleProvider(): void
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
     * Register the "conditional" feature toggle provider..
     *
     * @return void
     */
    protected function registerConditionalToggleProviders(): void
    {
        $this->app->singleton('feature-toggle.conditional', function () {
            return new ConditionalToggleProvider();
        });
    }

    /**
     * Register the "eloquent" feature toggle provider.
     *
     * @return void
     */
    protected function registerEloquentToggleProviders(): void
    {
        $this->app->singleton('feature-toggle.eloquent', function () {
            return new EloquentToggleProvider();
        });
    }

    /**
     * Register the "local" feature toggle provider.
     *
     * @return void
     */
    protected function registerLocalToggleProviders(): void
    {
        $this->app->singleton('feature-toggle.local', function () {
            return new LocalToggleProvider();
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
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
    protected function packageName(): string
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
    protected function packageBasePath(string $path): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Filename of config for package.
     *
     * @return string
     */
    protected function packageConfigFilename(): string
    {
        return $this->packageName().'.php';
    }

    /**
     * File path of config for package.
     *
     * @return string
     */
    protected function packageConfigFilePath(): string
    {
        return $this->packageBasePath('config'.DIRECTORY_SEPARATOR.$this->packageConfigFilename());
    }
}
