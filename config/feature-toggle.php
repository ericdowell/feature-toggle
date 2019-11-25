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
     | Ordered list of which providers to load, the order is important because
     | it is the exact order the api will search for a toggle active/inactive
     | status. The first provider to have a feature toggle defined, active
     | or not, will be used as the status value.
     |
     | Default Drivers: "conditional", "eloquent", "local", "querystring",
     |                  "redis", "session"
     |
     */

    'providers' => [
        [
            'driver' => 'conditional',
        ],
        [
            'driver' => 'local',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Feature Toggle Options
     |--------------------------------------------------------------------------
     |
     | Options to enable/disable behavior.
     |
     | 'registerMiddleware' => true/false,
     | 'useMigrations' => true/false,
     |
     */

    'registerMiddleware' => true,

    'useMigrations' => false,

    /*
     |--------------------------------------------------------------------------
     | Additional Feature Toggle Drivers
     |--------------------------------------------------------------------------
     |
     | Classmap of drivers that can be used in the "providers" config key above.
     | Drivers will be registered within the 'feature-api.*' container namespace.
     |
     | The driver class must implement FeatureToggle\Contracts\ToggleProvider.
     |
     | By default the following drivers are registered:
     | - 'conditional' => \FeatureToggle\ConditionalToggleProvider::class,
     | - 'eloquent' => \FeatureToggle\EloquentToggleProvider::class,
     | - 'local' => \FeatureToggle\LocalToggleProvider::class,
     | - 'querystring' => \FeatureToggle\QueryStringToggleProvider::class,
     | - 'redis' => \FeatureToggle\RedisToggleProvider::class,
     | - 'session' => \FeatureToggle\SessionToggleProvider::class,
     |
     */

    'drivers' => [],
];
