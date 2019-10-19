<?php

use FeatureToggle\Api;
use FeatureToggle\Contracts\Api as ApiContract;

if (! function_exists('feature_toggle_api')) {
    /**
     * @return ApiContract|Api
     */
    function feature_toggle_api()
    {
        return app(ApiContract::class);
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
