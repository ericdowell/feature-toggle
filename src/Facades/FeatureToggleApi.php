<?php

declare(strict_types=1);

namespace FeatureToggle\Facades;

use FeatureToggle\Contracts\Api as ApiContract;
use Illuminate\Support\Facades\Facade;

/**
 * FeatureToggle\Facades\FeatureToggleApi.
 *
 * @see \FeatureToggle\Api
 *
 * @codeCoverageIgnore
 *
 * @method static \FeatureToggle\Contracts\Api|\FeatureToggle\Api setProviders(array $providers)
 * @method static array getProviders()
 * @method static bool isMiddlewareEnabled()
 * @method static bool isMigrationsEnabled()
 * @method static void useMigrations()
 * @method static void ignoreMigrations()
 * @method static void registerMiddleware()
 * @method static void ignoreMiddleware()
 * @method static string getName()
 * @method static \FeatureToggle\ConditionalToggleProvider getConditionalProvider()
 * @method static \FeatureToggle\EloquentToggleProvider getEloquentProvider()
 * @method static \FeatureToggle\LocalToggleProvider getLocalProvider()
 * @method static \FeatureToggle\QueryStringToggleProvider getQueryStringProvider()
 * @method static \FeatureToggle\Contracts\Api|\FeatureToggle\Api setConditional(string $name, callable $condition)
 * @method static \Illuminate\Support\Collection getToggles()
 * @method static \FeatureToggle\Contracts\ToggleProvider|\FeatureToggle\Api refreshToggles()
 * @method static \FeatureToggle\Contracts\ToggleProvider getProvider(string $name)
 * @method static \FeatureToggle\Api loadProvider(string $driver, array $parameters)
 */
class FeatureToggleApi extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ApiContract::class;
    }
}
