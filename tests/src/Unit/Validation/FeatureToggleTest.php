<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Validation;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggleValidation;
use FeatureToggle\Validation\FeatureToggle;
use InvalidArgumentException;

class FeatureToggleTest extends TestCase
{
    use TestToggleValidation;

    /**
     * @returns void
     */
    public function testNotPassingNameToValidatorThrowsError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(FeatureToggle::ERROR_MESSAGE);

        $validation = validator([], [
            'data' => 'required_if_feature',
        ]);
        $validation->passes();
    }

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
