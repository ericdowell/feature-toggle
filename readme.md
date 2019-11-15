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
    - [Helper Functions](#helper-functions)
    - [Toggle Providers](#toggle-providers)
    - [Add Local Feature Toggles](#add-local-feature-toggles)
    - [Other Feature Toggles Types](#other-feature-toggles-types)
        - [Conditional Feature Toggles](#conditional-feature-toggles)
        - [Eloquent Feature Toggles](#eloquent-feature-toggles)
            - [Database Migration](#database-migration)
            - [Eloquent Model](#eloquent-model)
        - [QueryString Toggle Provider](#querystring-toggle-provider)
    - [Frontend Feature Toggle Api](#frontend-feature-toggle-api)
- [Road Map](#road-map)

## Installation
Install using composer by running:
```bash
composer require ericdowell/feature-toggle ^1.5
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

### Toggle Providers
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

### Add Local Feature Toggles
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
The value passed from the `.env` file or set directly within config file can be:
- A `boolean`: `true`/`false`
- An `int` version of `boolean`: `1`/`0`
- Finally all supported values of  `filter_var($value, FILTER_VALIDATE_BOOLEAN)` [https://www.php.net/manual/en/filter.filters.validate.php](https://www.php.net/manual/en/filter.filters.validate.php)

### Other Feature Toggles Types
#### Conditional Feature Toggles
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

#### Eloquent Feature Toggles
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

##### Database Migration
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

##### Eloquent Model
If you would like to use a different eloquent model you may do so by adding `model` to the config file:
```php
'providers' => [
    [
        'driver' => 'eloquent',
        'model' => App\CustomFeatureToggle::class
    ],
],
```

#### QueryString Toggle Provider
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
- `featureOff`

e.g. `http://localhost/?feature=Example&featureOff[]=Example%20Off&featureOff[]=Example%20Query%20String`

The following example will result in `Example` as active and `Example Off`/`Example Query String` as inactive. **NOTE:**
This will only true if the `querystring` provider is placed above other toggle providers that haven't already defined
these feature toggles.

### Frontend Feature Toggle Api
Place the following in your main layout blade template in the `<head>` tag.
```blade
<script>
    window.featureToggles = Object.freeze({!! feature_toggle_api()->activeTogglesToJson() !!});
</script>
```

Then create a new js file within `resources/js` called `featureToggleApi.js`:
```js
const toggles = Object.keys(window.featureToggles || {})

class FeatureToggleApi {
    isActive(name) {
        return toggles.includes(name)
    }
}

export const Feature = new FeatureToggleApi()
```

Expose on the `window` within `app.js`:
```js
import { Feature } from './featureToggleApi'

// ...

window.feature = Feature
```

and/or simply use `Feature` within your other js files:
```jsx
import React, { Component, Fragment } from 'react'
import { Feature } from './featureToggleApi'

// ...

if (Feature.isActive('Example')) {
    // do something about it.
}

// ...

class App extends Component {
    render() {
        return (
            <Fragment>
                <Navigation />
                {Feature.isActive('Show Something') && <Something />}
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
- [ ] Integrate toggles into: Blade, Middleware, and Validation.
- [ ] Classmap Feature Toggles (FeatureToggleServiceProvider similar to AuthServiceProvider $policies).
