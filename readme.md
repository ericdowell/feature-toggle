# Feature Toggles
[![CircleCI](https://circleci.com/gh/ericdowell/feature-toggle.svg?style=svg)](https://circleci.com/gh/ericdowell/feature-toggle)
[![StyleCI](https://github.styleci.io/repos/201544436/shield?branch=master)](https://github.styleci.io/repos/201544436)
[![Test Coverage](https://api.codeclimate.com/v1/badges/78a76b330bb8654c44ff/test_coverage)](https://codeclimate.com/github/ericdowell/feature-toggle/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/78a76b330bb8654c44ff/maintainability)](https://codeclimate.com/github/ericdowell/feature-toggle/maintainability)

[![License](https://poser.pugx.org/ericdowell/feature-toggle/license?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)
[![Latest Stable Version](https://poser.pugx.org/ericdowell/feature-toggle/version?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)
[![Latest Unstable Version](https://poser.pugx.org/ericdowell/feature-toggle/v/unstable?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)
[![Total Downloads](https://poser.pugx.org/ericdowell/feature-toggle/downloads?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)

A simple feature toggle api for Laravel applications.

## Table of Contents
- [Installation](#installation)
- [Testing](#testing)
- [Usage](#usage)
    - [Toggle Booting](#toggle-booting)
    - [Helper Functions](#helper-functions)
    - [Use with Laravel Task Scheduling](#use-with-laravel-task-scheduling)
- [Toggle Providers](#toggle-providers)
    - [Local Feature Toggles](#local-feature-toggles)
    - [Conditional Feature Toggles](#conditional-feature-toggles)
    - [Eloquent Feature Toggles](#eloquent-feature-toggles)
        - [Database Migration](#database-migration)
        - [Eloquent Model](#eloquent-model)
    - [QueryString Toggle Provider](#querystring-toggle-provider)
        - [Configure Query String Keys](#configure-query-string-keys)
        - [Add Api Key Authorization](#add-api-key-authorization)
- [Frontend Feature Toggle Api](#frontend-feature-toggle-api)
- [Road Map](#road-map)

## Installation
Install using composer by running:
```bash
composer require ericdowell/feature-toggle ^1.6
```

Publish the `feature-toggle.php` config file by running:
```bash
php artisan vendor:publish --tag="feature-toggle-config"
```

## Testing
Run `composer test`.

## Usage
If a feature toggle is not defined then `isActive` will return `false`.

## Toggle Booting
The Feature Toggle Api will pull all possible toggles at the boot of the application. This design allows there to
be one database/cache/redis query instead of possibly many calls. This only becomes a problem if
there're 100s of feature toggles.

Be mindful of how many database toggles are setup at given time, instead setup or move toggles to the local provider
in `config/feature-toggle.php`.

### Helper Functions
`feature_toggle_api`:
```php
if (feature_toggle_api()->isActive('Example')) {
    // do something
}
```
Or a shorter function that does the same as above called `feature_toggle`:
```php
if (feature_toggle('Example')) {
    // do something
}
```
The `feature_toggle` function also allows a second parameter to be passed to allow for checking if the toggle is
active (`true`) or if it is inactive (`false`):
```php
if (feature_toggle('Example', false)) {
    // do something when toggle is inactive
}
// OR
if (feature_toggle('Example', 'off')) {
    // do something when toggle is inactive
}
```
The second parameter will parse as the local toggle does, read me in the [Toggle Parsing](#toggle-parsing) section to
learn more.

### Use with Laravel Blade Custom Directive
This custom directive uses the `feature_toggle` helper function directly, you can expect the same behavior:
```php
@feature('Example')
    // do something
@endfeature
```
Or if you'd like to check if the `Example` is inactive then you may pass a falsy value as the second parameter:
```php
// returns true if toggle is inactive
@feature('Example', false)
    // do something
@endfeature
// OR
@feature('Example', 'off')
    // do something
@endfeature
```

### Use with Laravel Middleware


### Use with Laravel Task Scheduling
You can use the built-in `when` function in combination with the `feature_toggle` helper function in the `app/Console/Kernel.php`
`schedule` method.
```php
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly()
                 ->when(feature_toggle('Inspire Command'));
    }
}
```

## Toggle Providers
Currently there're are only four feature toggle providers:
- `conditional`
- `eloquent`
- `local` (config)
- `querystring`

You can access these directly via:
```php
$localProvider = feature_toggle_api()->getLocalProvider();
// return false
$localProvider->isActive('Example');

// Returns by reference.
$conditionalProvider = feature_toggle_api()->getConditionalProvider();
$conditionalProvider->setToggle('Example', function() { return true; });
// return true
$conditionalProvider->isActive('Example');

// Request ?feature=Example
$queryStringProvider = feature_toggle_api()->getQueryStringProvider();
// return true
$queryStringProvider->isActive('Example');

$eloquentProvider = feature_toggle_api()->getEloquentProvider();
// return false
$eloquentProvider->isActive('Example');
```

If you would like to set the `providers` in code you may call the following in the `boot` method of your
`AppServiceProvider`:
```php
feature_toggle_api()->setProviders([
    [
        'driver' => 'conditional',
    ],
    [
        'driver' => 'eloquent',
    ],
]);
```

### Add Additional Toggle Providers
You may add additional custom toggle providers or override the default toggle providers by adding them to the `drivers`
key within `config/feature-toggle.php`:
```php
'drivers' => [
    'cache' => \App\FeatureToggle\CacheToggleProvider::class,
    'local' => \App\FeatureToggle\LocalToggleProvider::class,
    'session' => \App\FeatureToggle\SessionToggleProvider::class,
],
```
Then just add them in the order you'd like them to be checked within `providers` as you would the defaults:
```php
'providers' => [
    [
        'driver' => 'conditional',
    ],
    [
        'driver' => 'cache',
    ],
    [
        'driver' => 'session',
    ],
    [
        'driver' => 'local',
    ],
],
```

### Local Feature Toggles
To add new toggle(s) you will need to update the published `config/feature-toggles.php` file:
```php
<?php

return [
    // ...
    'toggles' => [
        'Example' => env('FEATURE_EXAMPLE'),
        'Show Something' => env('FEATURE_SHOW_SOMETHING'),
    ],
];
```
#### Toggle Parsing
The value passed from the `.env` file or set directly within config file can be:
- A `boolean`: `true`/`false`
- An `int` version of `boolean`: `1`/`0`
- Finally all supported values of  `filter_var($value, FILTER_VALIDATE_BOOLEAN)` [https://www.php.net/manual/en/filter.filters.validate.php](https://www.php.net/manual/en/filter.filters.validate.php)

### Conditional Feature Toggles
To add new conditional toggle(s) you will need to call `feature_toggle_api()->setConditional` method:
```php
// calling conditional function is deferred by default
feature_toggle_api()->setConditional('Example' function (Request $request) {
    $user = $request->user();
    return $user instanceof \App\User && $user->email === 'johndoe@example.com';
});

// OR call right away by passing false as $defer parameter
feature_toggle_api()->setConditional('Example' function () {
    return cache()->get('feature:example');
}, false);
```
**NOTE:** The function passed to `setConditional` does not get called right by default, it is deferred to allow
the Laravel app to bootstrap User/Request information. The conditional function is only called once and the value
is cached to help prevent expensive operations from being recalculated when adding additional conditional toggles.
Because of this design it is best to define these in `AppServiceProvider@boot` or in a
`FeatureToggleServiceProvider@boot` that you create.

### Eloquent Feature Toggles
To use the `eloquent` driver you will need to update the `feature-toggle` config/`setProviders` method call,
place the following within the `providers` key:
```php
'providers' => [
    [
        'driver' => 'eloquent',
    ],
],
```
or
```php
feature_toggle_api()->setProviders([
    [
        'driver' => 'conditional',
    ],
    [
        'driver' => 'eloquent',
    ],
    [
        'driver' => 'local',
    ],
]);
```
**NOTE:** Be sure to place this value in the order you would like it to be prioritized by the feature toggle api.

#### Database Migration
By default the migration for `feature_toggles` is not loaded, to load this you can update the `options` key
within `feature-toggle` config setting the `useMigrations` value to `true`:
```php
'options' => [
    'useMigrations' => true,
],
```

If you would like to set the `useMigrations` in code you may call the following in the `register` method of your
`AppServiceProvider`:
```php
use FeatureToggle\Api;

Api::useMigrations();
```

You may also publish the `migrations` to your application by running the following:
```php
php artisan vendor:publish --tag="feature-toggles-migrations"
```

Once you've used one of the methods above to setup the `feature_toggles` migrations run the following to update your database:
```php
php artisan migrate
```

#### Eloquent Model
If you would like to use a different eloquent model you may do so by adding `model` to the config file:
```php
'providers' => [
    [
        'driver' => 'eloquent',
        'model' => App\CustomFeatureToggle::class
    ],
],
```

### QueryString Toggle Provider
To use the `querystring` driver you will need to update the `feature-toggle` config/`setProviders` method call,
place the following within the `providers` key:
```php
'providers' => [
    [
        'driver' => 'querystring',
    ],
],
```

When making a request to your application you may now use the following query strings to make feature toggles active/inactive:
- `feature`
- `feature_off`

e.g. `http://localhost/?feature=Example&feature_off[]=Example%20Off&feature_off[]=Example%20Query%20String`

The following example will result in `Example` as active and `Example Off`/`Example Query String` as inactive. **NOTE:**
This will only true if the `querystring` provider is placed above other toggle providers that haven't already defined
these feature toggles.

#### Configure Query String Keys
If you'd like to configure what the `active`/`inactive` feature toggle input keys are you may add `activeKey`
and `inactiveKey` to config file.

Below is an example of configuring the query string keys as `active` and `inactive`:
```php
'providers' => [
    [
        'driver' => 'querystring',
        'activeKey' => 'active',
        'inactiveKey' => 'inactive',
    ],
],
```

#### Add Api Key Authorization
To keep users or bad actors from enabling/disabling a given feature toggle via the `querystring` toggle provider you
may configure the driver with a `token`/api key. By default the query string input is configured as `feature_token`,
but this can be also be configured to any value.
```php
'providers' => [
    [
        'driver' => 'querystring',
        'apiKey' => env('FEATURE_TOGGLE_API_KEY'),
        // Optional change to sometihing different than 'feature_token'.
        // 'apiInputKey' => 'feature_toggle_api_token',
    ],
],
```

## Frontend Feature Toggle Api
Place the following in your main layout blade template in the `<head>` tag.
```blade
<script>
    window.activeToggles = Object.freeze({!! feature_toggle_api()->activeTogglesToJson() !!});
</script>
```

Then create a new js file within `resources/js` called `featureToggle.js`:
```js
const toggles = Object.keys(window.activeToggles || {})

export const featureToggle = name => toggles.includes(name)
```

Expose on the `window` within `app.js`:
```js
import { featureToggle } from './featureToggle'

// ...

window.featureToggle = featureToggle
```

and/or simply use `featureToggle` within your other js files:
```js
import { featureToggle } from './featureToggle'

if (featureToggle('Example')) {
    // do something about it.
}
```

and/or create a `Feature` component that uses `featureToggle.js`:
```js
// Feature.js
import { featureToggle } from './featureToggle'

export const Feature = ({ name, active = true, children }) => {
    if (active === true) {
        return featureToggle(name) && children
    }
    return !featureToggle(name) && children
}
```
```jsx
// App.js
import React, { Component, Fragment } from 'react'
import { Feature } from './Feature'

class App extends Component {
    render() {
        return (
            <Fragment>
                <Navigation />
                <Feature name="Show Something">
                   <Something />
                </Feature>
            </Fragment>
        )
    }
}
```

## Road Map
### v1.x
- [x] Local Feature Toggles via Config.
- [x] Conditionally Enable/Disable Feature Toggles e.g. Authorization.
- [x] Eloquent Feature Toggles.
- [x] Query String Feature Toggles.
- [ ] Integrate toggles into:
    - [x] Blade
    - [x] Middleware
    - [ ] Validation
- [ ] Classmap Feature Toggles (FeatureToggleServiceProvider similar to AuthServiceProvider $policies).
