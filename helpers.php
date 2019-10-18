<?php

use FeatureToggle\FeatureToggleApi;
use FeatureToggle\Contracts\FeatureToggleApi as FeatureToggleApiContract;

if (! function_exists('feature_toggle_api')) {
    /**
     * @return FeatureToggleApiContract|FeatureToggleApi
     */
    function feature_toggle_api()
    {
        return app(FeatureToggleApiContract::class);
    }
}

if (! function_exists('feature_toggle')) {
    /**
     * @param  string  $name
     *
     * @return bool
     */
    function feature_toggle(string $name)
    {
        return feature_toggle_api()->isActive($name);
    }
}
