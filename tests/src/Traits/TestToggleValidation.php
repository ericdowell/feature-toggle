<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Traits;

use Illuminate\Support\Arr;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait TestToggleValidation
{
    /**
     * @param  string  $name
     * @param  bool  $checkActive
     * @return string|object
     */
    abstract protected function getValidationRule(string $name, $checkActive = true);

    /**
     * @return string
     */
    protected function getValidationMessage(): string
    {
        return 'validation.required_if_feature';
    }

    /**
     * @returns void
     */
    public function testValidationFailsIfToggleIsNotActiveAndRuleCheckMatchingStatus(): void
    {
        $key = 'data';
        $validation = validator([], [
            $key => $this->getValidationRule('foo', 'off'),
        ]);
        $this->assertFalse($validation->passes(), 'Validation passed when it should fail.');
        $this->assertSame([$this->getValidationMessage()], Arr::get($validation->getMessageBag()->toArray(), $key));
    }

    /**
     * @returns void
     */
    public function testValidationPassIfToggleIsNotActiveAndRuleCheckMatchingStatusAndValueGiven(): void
    {
        $key = 'data';
        $validation = validator([$key => 'value'], [
            $key => $this->getValidationRule('foo', 'off'),
        ]);
        $this->assertTrue($validation->passes(), 'Validation failed when it should pass.');
        $this->assertEmpty($validation->getMessageBag()->toArray());
    }

    /**
     * @returns void
     */
    public function testValidationPassesIfToggleIsNotActiveAndRuleCheckMatchingActiveStatus(): void
    {
        $key = 'data';
        $validation = validator([], [
            $key => $this->getValidationRule('foo'),
        ]);
        $this->assertTrue($validation->passes(), 'Validation failed when it should pass.');
        $this->assertEmpty($validation->getMessageBag()->toArray());
    }
}
