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
     * @covers ::toArray
     *
     * @return void
     */
    public function testToArray(): void
    {
        $this->assertSame(['name' => 'foo', 'is_active' => true], (new Local('foo', true))->toArray());
    }

    /**
     * @covers ::__construct
     * @covers ::isActive
     *
     * @return void
     */
    public function testIsActiveTrue(): void
    {
        $this->assertTrue((new Local('foo', true))->isActive());
        $this->assertTrue((new Local('foo', 1))->isActive());
        $this->assertTrue((new Local('foo', '1'))->isActive());
        $this->assertTrue((new Local('foo', 'on'))->isActive());
    }

    /**
     * @covers ::__construct
     * @covers ::isActive
     *
     * @return void
     */
    public function testIsActiveFalse(): void
    {
        $this->assertFalse((new Local('foo', false))->isActive());
        $this->assertFalse((new Local('foo', 0))->isActive());
        $this->assertFalse((new Local('foo', '0'))->isActive());
        $this->assertFalse((new Local('foo', 'off'))->isActive());
        $this->assertFalse((new Local('foo', 'bar'))->isActive());
    }
}
