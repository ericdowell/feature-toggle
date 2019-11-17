<?php

use FeatureToggle\Api;
use FeatureToggle\Contracts\Api as ApiContract;
use FeatureToggle\Traits\Toggle;

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
     * @param  string|bool|int  $checkActive
     * @return bool
     */
    function feature_toggle(string $name, $checkActive = true)
    {
        $isActive = feature_toggle_api()->isActive($name);
        $checkActive = Toggle::calculateIsActive($checkActive);

        return $checkActive === true ? $isActive : ! $isActive;
    }
}
