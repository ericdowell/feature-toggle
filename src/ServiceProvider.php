<?php

declare(strict_types=1);

namespace FeatureToggles;

use FeatureApi\FeatureToggleApi;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use FeatureApi\Contacts\FeatureToggleApi as FeatureToggleApiContract;

class ServiceProvider extends SupportServiceProvider
{
    /**
     * Register Resource Controller services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FeatureToggleApiContract::class, function () {
            return new FeatureToggleApi();
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}