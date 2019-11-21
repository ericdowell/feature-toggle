<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Validation;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggleValidation;

class FeatureToggleTest extends TestCase
{
    use TestToggleValidation;

    /**
     * @param  string  $name
     * @param  bool  $checkActive
     * @return string|object
     */
    protected function getValidationRule(string $name, $checkActive = true)
    {
        return "required_if_feature:{$name},{$checkActive}";
    }
}
