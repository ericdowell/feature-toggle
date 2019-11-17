<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Api as ApiContract;
use FeatureToggle\Middleware\FeatureToggle;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

/**
 * @codeCoverageIgnore
 */
class ServiceProvider extends SupportServiceProvider
{
    /**
     * @var array
     */
    protected $defaultDrivers = [
        'conditional' => ConditionalToggleProvider::class,
        'eloquent' => EloquentToggleProvider::class,
        'local' => LocalToggleProvider::class,
        'querystring' => QueryStringToggleProvider::class,
    ];

    /**
     * Register Resource Controller services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerPrimaryToggleProvider();
        $this->registerToggleProviderDrivers();

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
            $options = config('feature-toggle.options', [
                'registerMiddleware' => true,
                'useMigrations' => false,
            ]);

            return new Api($providers, $options);
        });
        $this->app->alias(ApiContract::class, 'feature-toggle.api');
    }

    /**
     * Register the "eloquent" feature toggle provider.
     *
     * @return void
     */
    protected function registerToggleProviderDrivers(): void
    {
        $drivers = config('feature-toggle.drivers', []) + $this->defaultDrivers;
        foreach($drivers as $name => $concrete) {
            $this->app->singleton("feature-toggle.{$name}", $concrete);
        }
    }

    /**
     * Perform post-registration booting of services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->registerBladeDirective();
        $this->registerMiddleware($router);
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the custom 'featureToggle' blade directive.
     *
     * @return void
     */
    protected function registerBladeDirective(): void
    {
        Blade::if('featureToggle', function (string $name, bool $checkActive = true) {
            return feature_toggle($name, $checkActive);
        });
    }

    /**
     * Register the package middleware.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function registerMiddleware(Router $router): void
    {
        if (!feature_toggle_api()->isMiddlewareEnabled()) {
            return;
        }
        $router->aliasMiddleware('featureToggle', FeatureToggle::class);
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        if (!$this->app->runningInConsole() || !feature_toggle_api()->isMigrationsEnabled()) {
            return;
        }
        $this->loadMigrationsFrom($this->packageBasePath('database', 'migrations'));
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        $this->publishes([
            $this->packageConfigFilePath() => $this->app->configPath($this->packageConfigFilename()),
        ], $this->packageName().'-config');

        $this->publishes([
            $this->packageBasePath('database', 'migrations') => $this->app->databasePath('migrations'),
        ], $this->packageName().'-migrations');
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
     * @param  array  $path
     *
     * @return string
     */
    protected function packageBasePath(...$path): string
    {
        return realpath(dirname(__DIR__).DIRECTORY_SEPARATOR.implode($path, DIRECTORY_SEPARATOR));
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
