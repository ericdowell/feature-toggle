<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Local Feature Toggles
     |--------------------------------------------------------------------------
     |
     | Here you can define the local feature toggle state for when the
     | application deploys.
     |
     | Structure:
     |  'toggles' => [
     |      'Example Environment' => env('FEATURE_EXAMPLE'),
     |      'Example Active String' => 'true',
     |      'Example Active' => true,
     |      'Example Active Numeric' => 1,
     |      'Example On' => 'on',
     |      'Example Inactive String' => 'false',
     |      'Example Inactive' => false,
     |      'Example Inactive Numeric' => 0,
     |      'Example Off' => 'off',
     |  ],
     |
     */

    'toggles' => [],

    /*
     |--------------------------------------------------------------------------
     | Feature Toggle Providers
     |--------------------------------------------------------------------------
     |
     | Classmap of which providers to load and what order to check them
     | for a given feature toggles status. The first provider to have
     | a feature toggle defined will be used as the status value.
     |
     | Supported:
     | - FeatureToggle\ConditionalToggleProvider::class
     | - FeatureToggle\LocalToggleProvider::class
     |
     */

    'providers' => [
        FeatureToggle\ConditionalToggleProvider::class,
        FeatureToggle\LocalToggleProvider::class,
    ],
];
