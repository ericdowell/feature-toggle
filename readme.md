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
    - [Add Feature Toggles](#add-feature-toggles)
    - [Frontend Feature Toggle Api](#frontend-feature-toggle-api)
- [Road Map](#road-map)

## Installation
Install using composer by running:
```bash
composer require ericdowell/feature-toggle ^1.0
```

Publish the `feature-toggle.php` config file by running:
```bash
php artisan vendor:publish --tag="feature-toggle-config"
```

## Testing
Run `composer test`.

## Usage
If a feature toggle is not defined then `isActive` will return `false`.

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

### Toggle Providers
Currently there're are only two feature toggle providers:
- `conditional`
- `eloquent`
- `local` (config)
- `querystring`

You can access these directly via:
```php
$localProvider = feature_toggle_api()->getLocalProvider();

$localProvider->isActive('Example'); // false

// Returns by reference.
$conditionalProvider = feature_toggle_api()->getConditionalProvider();
$conditionalProvider->setToggle('Example', function() { return true; });

$conditionalProvider->isActive('Example'); // true
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
feature_toggle_api()->setConditional('Example' function () {
    return true;
});
```
**NOTE:** The function passed to `setConditional` is executed right away to prevent expensive operations from be recalculated
when adding additional conditional toggles. Because of this design it is best to define these in `AppServiceProvider@boot`
or it's own `ServiceProvider` `boot` method.

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
- [ ] Integrate toggles into: Blade, Middleware, Task Scheduling, and Validation.
- [ ] Classmap Feature Toggles (FeatureToggleServiceProvider similar to AuthServiceProvider $policies).
