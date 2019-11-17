<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggle;
use FeatureToggle\Toggle\Conditional;

/**
 * @group unit
 */
class ConditionalTest extends TestCase
{
    use TestToggle;

    /**
     * @param  string  $name
     * @param  mixed  $is_active
     * @param  bool|null  $defer
     * @return ToggleContract|Conditional
     */
    protected function getInstance(string $name, $is_active, bool $defer = null): ToggleContract
    {
        return new Conditional($name, $is_active, $defer);
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function getIsActiveAttribute($value): callable
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * @return void
     */
    public function testCalledPropertyIsRespectedWhenDeferIsTrue(): void
    {
        $condition = function () {
            static $count = 0;
            $count++;

            return $count === 1;
        };
        $toggle = $this->getInstance('foo', $condition, true);

        $this->assertTrue($toggle->isActive(), 'toggle calling isActive should return true.');
        $this->assertTrue($toggle->isActive(), 'toggle calling isActive should return true.');
        $this->assertTrue($toggle->isActive(), 'toggle calling isActive should return true.');
    }
}
