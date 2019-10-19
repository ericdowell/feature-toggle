<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Toggle\Local;
use FeatureToggle\Tests\TestCase;

/**
 * @coversDefaultClass \FeatureToggle\Toggle\Local
 */
class LocalTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     *
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertSame('foo', (new Local('foo', true))->getName());
    }

    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::isActive
     * @covers ::toArray
     *
     * @return void
     */
    public function testToArray(): void
    {
        $this->assertSame([
            'name' => 'foo',
            'is_active' => true,
        ], (new Local('foo', true))->toArray());
    }

    /**
     * @dataProvider isActiveDataProvider
     *
     * @covers ::__construct
     * @covers ::isActive
     *
     * @param  string  $assertMethod
     * @param  array  $values
     *
     * @return void
     */
    public function testIsActiveTrueOFalse(string $assertMethod, array $values): void
    {
        foreach ($values as $isActive) {
            $this->{$assertMethod}((new Local('foo', $isActive))->isActive());
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
                    '0',
                    'off',
                    'bar',
                ],
            ],
        ];
    }
}
