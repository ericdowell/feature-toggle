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
    - [Use with Laravel Blade Custom Directive](#use-with-laravel-blade-custom-directive)
    - [Use with Laravel Middleware](#use-with-laravel-middleware)
    - [Use with Laravel Task Scheduling](#use-with-laravel-task-scheduling)
    - [Use with Laravel Validation](#use-with-laravel-validation)
        - [Simple String](#simple-string)
        - [Via Illuminate\Validation\Rule](#via-illuminatevalidationrule)
        - [requiredIfRule Method on FeatureToggleApi](#requiredifrule-method-on-featuretoggleapi)
- [Toggle Providers](#toggle-providers)
    - [Add Additional Toggle Providers](#add-additional-toggle-providers)
    - [Local Feature Toggles](#local-feature-toggles)
        - [Toggle Parsing](#toggle-parsing)
    - [Conditional Feature Toggles](#conditional-feature-toggles)
    - [Eloquent Feature Toggles](#eloquent-feature-toggles)
        - [Database Migration](#database-migration)
        - [Eloquent Model](#eloquent-model)
    - [QueryString Toggle Provider](#querystring-toggle-provider)
        - [Configure Query String Keys](#configure-query-string-keys)
        - [Add Api Key Authorization](#add-api-key-authorization)
    - [Redis Toggle Provider](#redis-toggle-provider)
    - [Session Toggle Provider](#session-toggle-provider)
- [Frontend Feature Toggle Api](#frontend-feature-toggle-api)
- [Road Map](#road-map)

## Installation
Install using composer by running:
```bash
composer require ericdowell/feature-toggle ^1.9
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
The second parameter will parse as the local toggle does, read more in the [Toggle Parsing](#toggle-parsing) section to
learn more.

### Use with Laravel Blade Custom Directive
This custom directive uses the `feature_toggle` helper function directly, you can expect the same behavior:
```blade
@featureToggle('Example')
    // do something
@endfeatureToggle
```
Or if you'd like to check if the `Example` is inactive then you may pass a falsy value as the second parameter:
```blade
// returns true if toggle is inactive
@featureToggle('Example', false)
    // do something
@endfeatureToggle
// OR
@featureToggle('Example', 'off')
    // do something
@endfeatureToggle
```
Or you can use the normal `@if` blade directive and call `feature_toggle` function directly:
```blade
@if(feature_toggle('Example'))
    // do something
@endif
// OR
@if(feature_toggle('Example', 'off'))
    // do something
@endif
```

### Use with Laravel Middleware
The middleware signature is as follows:
```
featureToggle:{name},{status},{abort}
```
Where `status` and `abort` are optional parameters. `status` will default to `true` (truthy) and `abort` will default to
`404` status code. `name` is required.

**Examples:**
```php
// Passing all three parameters, changing abort to 403 status code.
Route::get('user/billing')->middleware('featureToggle:subscription,true,403')->uses('User\\BillingController@index')->name('billing.index');
// Passing two parameters.
Route::get('user/subscribe')->middleware('featureToggle:subscription,true')->uses('User\\SubscribeController@index')->name('subscribe.index');
// Passing just the name.
Route::get('user/trial')->middleware('featureToggle:trial')->uses('User\\TrialController@index')->name('trial.index');
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

### Use with Laravel Validation
There are three ways you can use the validation logic:
- Simple String
- Via `Illuminate\Validation\Rule`
- `requiredIfRule` Method on `FeatureToggleApi`

#### Simple String
Use the normal simple string signature via `required_if_feature`:
```
required_if_feature:{name},{status}
```
Where `status` is an optional parameter. `status` will default to `true` (truthy). `name` parameter is required.

```php
Validator::make($request->all(), [
    'phone' => 'required_if_feature:Require phone',
]);

Validator::make($request->all(), [
    'phone' => 'required_if_feature:Require phone,on',
]);
```

#### Via Illuminate\Validation\Rule
A macro method has been added to the `Rule` class called `requiredIfFeature`:
```php
use Illuminate\Validation\Rule;

Validator::make($request->all(), [
    'phone' => Rule::requiredIfFeature('Require phone'),
]);

Validator::make($request->all(), [
    'phone' => Rule::requiredIfFeature('Require phone', true),
]);
```

#### requiredIfRule Method on FeatureToggleApi
You may also use the `requiredIfRule` method on the `FeatureToggleApi`/`feature_toggle_api` Facade or helper function:
```php
Validator::make($request->all(), [
    'phone' => FeatureToggleApi::requiredIfRule('Require phone'),
]);

Validator::make($request->all(), [
    'phone' => feature_toggle_api()->requiredIfRule('Require phone', true),
]);
```

## Toggle Providers
The default feature toggle providers are as follows:
- `conditional`
- `eloquent`
- `local` (config)
- `querystring`
- `redis`
- `session`

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
    'local' => \App\FeatureToggle\LocalToggleProvider::class,
    'redis' => \App\FeatureToggle\RedisToggleProvider::class,
    'session' => \App\FeatureToggle\SessionToggleProvider::class,
],
```
Then just add them in the order you'd like them to be checked within `providers` as you would the defaults:
```php
'providers' => [
    [
        'driver' => 'session',
    ],
    [
        'driver' => 'conditional',
    ],
    [
        'driver' => 'redis',
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
- Finally all supported values of  [filter_var($value, FILTER_VALIDATE_BOOLEAN)](https://www.php.net/manual/en/filter.filters.validate.php)

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
the Laravel app to bootstrap User/Session information. The conditional function is only called once and the value
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
OR
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

Once you've used one of the methods above, you can run the following command to update your database with
the `feature_toggles` migration(s):
```php
php artisan migrate
```

#### Eloquent Model
If you would like to use a different eloquent model you may do so by adding `model` to the config file:
```php
'providers' => [
    [
        'driver' => 'eloquent',
        'model' => App\FeatureToggle::class
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
This will only be true if the `querystring` provider is placed above other toggle providers that haven't already defined
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
To keep users or bad actors from enabling/disabling feature toggles via the `querystring` toggle provider you
may configure the driver with a `token`/api key. By default the query string input is configured as
`feature_token`, but this can be also be configured to any value.
```php
'providers' => [
    [
        'driver' => 'querystring',
        'apiKey' => env('FEATURE_TOGGLE_API_KEY'),
        // Optionally change to something different.
        // 'apiInputKey' => 'feature_toggle_api_token',
    ],
],
```

### Redis Toggle Provider
To use the `redis` driver you will need to update the `feature-toggle` config/`setProviders` method call,
place the following within the `providers` key:
```php
'providers' => [
    [
        'driver' => 'redis',
    ],
],
```

There are three options that can be configured:
- `key`, defaults to `feature_toggles`
- `prefix`, defaults to `null`
- `connection`, defaults to `default`
```php
'providers' => [
    [
        'driver' => 'querystring',
        'key' => 'toggles', // Optional, otherwise 'feature_toggles'
        'prefix' => 'feature', // Optional
        'connection' => 'toggles', // Must match key in database.redis.{connection}
    ],
],
```

Current implementation requires the array of toggles to be serialized in redis, you can use
`Illuminate\Cache\RedisStore` `forever` method to persist toggle values.

### Session Toggle Provider
To use the `session` driver you will need to update the `feature-toggle` config/`setProviders` method call,
place the following within the `providers` key:
```php
'providers' => [
    [
        'driver' => 'session',
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

export const featureToggle = (name, checkActive = true) =>
    checkActive ? toggles.includes(name) : !toggles.includes(name)
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

export const Feature = ({ name, active: checkActive = true, children }) => {
    return featureToggle(name, checkActive) && children
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
                <Feature name="Show Something" active={false}>
                   <p>Nothing to see here!</p>
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
- [x] Integrate toggles into:
    - [x] Blade
    - [x] Middleware
    - [x] Validation
- [ ] Create/update toggles via common contract interface.
- [ ] Create Command to create/update toggles to be active/inactive.
- [ ] Classmap Feature Toggles (FeatureToggleServiceProvider similar to AuthServiceProvider $policies).
