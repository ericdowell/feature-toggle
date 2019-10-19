<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\Toggle\Conditional;

/**
 * @coversDefaultClass \FeatureToggle\Toggle\Conditional
 */
class ConditionalTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     *
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertSame('foo', (new Conditional('foo', function () {
            return true;
        }))->getName());
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
        ], (new Conditional('foo', function () {
            return true;
        }))->toArray());
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
            $this->{$assertMethod}((new Conditional('foo', $isActive))->isActive());
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
                    function () {
                        return true;
                    },
                    function () {
                        return 'true';
                    },
                    function () {
                        return 1;
                    },
                    function () {
                        return '1';
                    },
                    function () {
                        return 'on';
                    },
                ],
            ],
            [
                'assertFalse',
                [
                    function () {
                        return false;
                    },
                    function () {
                        return 0;
                    },
                    function () {
                        return '0';
                    },
                    function () {
                        return 'off';
                    },
                    function () {
                        return 'bar';
                    },
                ],
            ],
        ];
    }
}
