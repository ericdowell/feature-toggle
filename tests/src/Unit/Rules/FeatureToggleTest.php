<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Rules;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggleValidation;
use Illuminate\Validation\Rule;

class FeatureToggleTest extends TestCase
{
    use TestToggleValidation;

    /**
     * @return string
     */
    protected function getValidationMessage(): string
    {
        return 'The data field is required.';
    }

    /**
     * @param  string  $name
     * @param  bool  $checkActive
     * @return string|object
     */
    protected function getValidationRule(string $name, $checkActive = true)
    {
        return Rule::requiredIfFeature($name, $checkActive);
    }
}
