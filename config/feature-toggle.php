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
     | @todo: Add text here.
     |
     */

    'providers' => [
        FeatureToggle\ConditionalToggleProvider::class,
        FeatureToggle\LocalToggleProvider::class
    ],
];
