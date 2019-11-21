<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Traits;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait TestToggleProvider
{
    /**
     * @return ToggleProviderContract
     */
    abstract protected function getToggleProvider(): ToggleProviderContract;

    /**
     * @param  array|null  $toggles
     * @return ToggleProviderContract
     */
    abstract protected function setToggles(array $toggles = null): ToggleProviderContract;

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
    public function testNotArrayConfigToggles(): void
    {
        $this->assertCount(0, $this->setToggles()->getToggles());
    }

    /**
     * @return void
     */
    public function testActiveTogglesToJsonNotEmpty(): void
    {
        $expected = json_encode([
            'foo' => [
                'name' => 'foo',
                'is_active' => true,
            ],
        ]);

        $this->assertSame($expected,
            $this->setToggles(['foo' => $this->getIsActiveAttribute(true)])->activeTogglesToJson());
    }

    /**
     * @return void
     */
    public function testActiveTogglesToJsonEmpty(): void
    {
        $this->assertSame('{}', $this->getToggleProvider()->activeTogglesToJson());
    }

    /**
     * @return void
     */
    public function testOneActiveToggle(): void
    {
        $toggleProvider = $this->setToggles([
            'foo' => $this->getIsActiveAttribute(true),
        ]);

        $this->assertTrue($toggleProvider->isActive('foo'), '"foo" toggle check, should BE true.');
        $this->assertCount(1, $toggleProvider->getToggles(), 'Checking "getToggles" count.');
        $this->assertCount(1, $toggleProvider->getActiveToggles(), 'Checking "getActiveToggles" count.');
    }

    /**
     * @return void
     */
    public function testManyActiveToggle(): void
    {
        $toggleProvider = $this->setToggles([
            'foo' => $this->getIsActiveAttribute(true),
            'bar' => $this->getIsActiveAttribute('on'),
        ]);

        $this->assertTrue($toggleProvider->isActive('foo'), '"foo" toggle check, should BE true.');
        $this->assertTrue($toggleProvider->isActive('bar'), '"bar" toggle check, should BE true.');
        $this->assertCount(2, $toggleProvider->getToggles(), 'Checking "getToggles" count.');
        $this->assertCount(2, $toggleProvider->getActiveToggles(), 'Checking "getActiveToggles" count.');
    }

    /**
     * @return void
     */
    public function testOneInActiveToggle(): void
    {
        $toggleProvider = $this->setToggles([
            'bar' => $this->getIsActiveAttribute('off'),
        ]);

        $this->assertFalse($toggleProvider->isActive('bar'), '"bar" toggle check, should BE false.');
        $this->assertCount(1, $toggleProvider->getToggles(), 'Checking "getToggles" count.');
        $this->assertCount(0, $toggleProvider->getActiveToggles(), 'Checking "getActiveToggles" count.');
    }

    /**
     * @return void
     */
    public function testManyInActiveToggle(): void
    {
        $toggleProvider = $this->setToggles([
            'foo' => $this->getIsActiveAttribute(false),
            'bar' => $this->getIsActiveAttribute('off'),
        ]);

        $this->assertFalse($toggleProvider->isActive('foo'), '"foo" toggle check, should BE false.');
        $this->assertFalse($toggleProvider->isActive('bar'), '"bar" toggle check, should BE false.');
        $this->assertCount(2, $toggleProvider->getToggles(), 'Checking "getToggles" count.');
        $this->assertCount(0, $toggleProvider->getActiveToggles(), 'Checking "getActiveToggles" count.');
    }
}
