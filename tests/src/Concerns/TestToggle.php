<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Concerns;

use FeatureToggle\Contracts\Toggle as ToggleContract;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait TestToggle
{
    /**
     * @param  string  $name
     * @param  mixed  $is_active
     * @return \FeatureToggle\Contracts\Toggle
     */
    abstract protected function getInstance(string $name, $is_active): ToggleContract;

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function getIsActiveAttribute($value)
    {
        return $value;
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertSame('foo', ($this->getInstance('foo', $this->getIsActiveAttribute(true)))->getName());
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->assertSame([
            'name' => 'foo',
            'is_active' => true,
        ], ($this->getInstance('foo', $this->getIsActiveAttribute(true)))->toArray());
    }

    /**
     * @dataProvider isActiveDataProvider
     *
     * @param  string  $assertMethod
     * @param  array  $values
     * @return void
     */
    public function testIsActiveTrueOFalse(string $assertMethod, array $values): void
    {
        foreach ($values as $isActive) {
            $isActive = ($this->getInstance('foo', $this->getIsActiveAttribute($isActive)))->isActive();
            $this->{$assertMethod}($isActive, 'The value being tested is '.$isActive);
        }
    }

    /**
     * @return array
     */
    public function isActiveDataProvider(): array
    {
        return [
            [
                'assertTrue',
                [
                    true,
                    'true',
                    1,
                    '1',
                    'on',
                ],
            ],
            [
                'assertFalse',
                [
                    false,
                    'false',
                    0,
                    [],
                    '0',
                    'off',
                    'bar',
                ],
            ],
        ];
    }
}
