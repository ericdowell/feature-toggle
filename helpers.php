<?php

use FeatureToggle\Api;
use FeatureToggle\Concerns\Toggle;
use FeatureToggle\Contracts\Api as FeatureToggleApi;

if (! function_exists('feature_toggle_api')) {
    /**
     * @param  string|null  $provider
     * @return Api|FeatureToggleApi|\FeatureToggle\Contracts\ToggleProvider
     *
     * @throws RuntimeException
     */
    function feature_toggle_api(?string $provider = null)
    {
        /** @var FeatureToggleApi|Api $featureToggleApi */
        $featureToggleApi = app(FeatureToggleApi::class);
        if (! $provider) {
            return $featureToggleApi;
        }

        return $featureToggleApi->getProvider($provider);
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
