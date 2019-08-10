# Feature Toggles
[![CircleCI](https://circleci.com/gh/ericdowell/feature-toggle.svg?style=svg)](https://circleci.com/gh/ericdowell/feature-toggle)
[![StyleCI](https://github.styleci.io/repos/201544436/shield?branch=master)](https://github.styleci.io/repos/201544436)
[![Test Coverage](https://api.codeclimate.com/v1/badges/78a76b330bb8654c44ff/test_coverage)](https://codeclimate.com/github/ericdowell/feature-toggle/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/78a76b330bb8654c44ff/maintainability)](https://codeclimate.com/github/ericdowell/feature-toggle/maintainability)

[![License](https://poser.pugx.org/ericdowell/feature-toggle/license?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)
[![Latest Stable Version](https://poser.pugx.org/ericdowell/feature-toggle/version?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)
[![Latest Unstable Version](https://poser.pugx.org/ericdowell/feature-toggle/v/unstable?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)
[![Total Downloads](https://poser.pugx.org/ericdowell/feature-toggle/downloads?format=flat-square)](https://packagist.org/packages/ericdowell/feature-toggle)

A simple feature toggle api.

This package is written to work with Laravel. The code can still be used with non-Laravel applications.

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
php artisan vendor:publish --tag=feature-toggle
```

## Testing
Run `composer test`.

## Usage
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

### Add Feature Toggles
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

### Frontend Feature Toggle Api
Place the following in your main layout blade template in the `<head>` tag.
```blade
<script>
    window.featureToggles = Object.freeze({!! feature_toggle_api()->activeTogglesToJson() !!});
</script>
```

Then create a new js file within `resources/js` called `featureToggleApi.js`:
```js
class FeatureToggleApi {
    isActive(name) {
        return Object.keys(window.featureToggles || {}).includes(name)
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
                {Feature.isActive('Show Something') ? <Something /> : ''}
            </Fragment>
        )
    }
}
```

## Road Map
### v1.x
- [x] Local Feature Toggles via Config
- [ ] Feature Toggle Facade (Similar to Gate, defined on the fly)
- [ ] Classmap Feature Toggles (FeatureToggleServiceProvider similar to AuthServiceProvider $policies)
- [ ] Database Feature Toggles
- [ ] Conditionally Enable/Disable Feature Toggles e.g. Authorization
- [ ] Query String Feature Toggles