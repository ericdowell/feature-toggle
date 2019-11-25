<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Api as FeatureToggleApi;
use FeatureToggle\Middleware\FeatureToggle;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Illuminate\Validation\Rule;

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
        'redis' => RedisToggleProvider::class,
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
        $this->app->singleton(FeatureToggleApi::class, function () {
            return new Api($this->getRegisteredProviders(), $this->getApiOptions());
        });
        $this->app->alias(FeatureToggleApi::class, 'feature-toggle.api');
    }

    /**
     * @return array
     */
    protected function getRegisteredProviders(): array
    {
        return config('feature-toggle.providers', [
            [
                'driver' => 'local',
            ],
        ]);
    }

    /**
     * @return array
     */
    protected function getApiOptions(): array
    {
        $options = [];
        $supportOptions = [
            'registerMiddleware' => true,
            'useMigrations' => false,
        ];
        foreach ($supportOptions as $name => $default) {
            $options[$name] = config("feature-toggle.{$name}", $default);
        }

        return $options;
    }

    /**
     * Register the "eloquent" feature toggle provider.
     *
     * @return void
     */
    protected function registerToggleProviderDrivers(): void
    {
        $drivers = config('feature-toggle.drivers', []) + $this->defaultDrivers;
        foreach ($drivers as $name => $concrete) {
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
        $this->registerValidation();
    }

    /**
     * Register the custom 'featureToggle' blade directive.
     *
     * @return void
     */
    protected function registerBladeDirective(): void
    {
        Blade::if('featureToggle', function (string $name, $checkActive = true) {
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
        if (! feature_toggle_api()->isMiddlewareEnabled()) {
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
        if (! $this->app->runningInConsole() || ! feature_toggle_api()->isMigrationsEnabled()) {
            return;
        }
        $this->loadMigrationsFrom($this->packageDatabaseMigrationsPath());
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }
        $this->publishes([
            $this->packageConfigFilePath() => $this->app->configPath($this->packageConfigFilename()),
        ], $this->packageName().'-config');

        $this->publishes([
            $this->packageDatabaseMigrationsPath() => $this->app->databasePath('migrations'),
        ], $this->packageName().'-migrations');
    }

    /**
     * Register the package's validation rules.
     *
     * @return void
     */
    public function registerValidation(): void
    {
        Rule::macro('requiredIfFeature', function (string $name, $checkActive = true) {
            return new Rules\FeatureToggle($name, $checkActive);
        });

        Validator::extendImplicit('required_if_feature', Validation\FeatureToggle::class);
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
        return $this->packageBasePath('config', $this->packageConfigFilename());
    }

    /**
     * File path of database migrations folder for package.
     *
     * @return string
     */
    protected function packageDatabaseMigrationsPath(): string
    {
        return $this->packageBasePath('database', 'migrations');
    }
}
