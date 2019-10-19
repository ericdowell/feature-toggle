<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Tests\TestCase;

/**
 * @coversDefaultClass \FeatureToggle\Api
 */
class FeatureToggleApiTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getToggles
     * @covers ::refreshToggles
     * @covers ::calculateToggles
     *
     * @return void
     */
    public function testNotArrayConfigToggles(): void
    {
        config()->set('feature-toggle.toggles', null);

        $featureToggleApi = feature_toggle_api()->refreshToggles();

        $this->assertCount(0, $featureToggleApi->getToggles());
    }

    /**
     * @covers ::__construct
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::activeTogglesToJson
     * @covers ::refreshToggles
     * @covers ::calculateToggles
     *
     * @return void
     */
    public function testActiveTogglesToJsonNotEmpty(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);

        $featureToggleApi = feature_toggle_api()->refreshToggles();

        $expected = json_encode(['foo' => ['name' => 'foo', 'is_active' => true]]);

        $this->assertSame($expected, $featureToggleApi->activeTogglesToJson());
    }

    /**
     * @covers ::__construct
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::activeTogglesToJson
     * @covers ::calculateToggles
     *
     * @return void
     */
    public function testActiveTogglesToJsonEmpty(): void
    {
        $this->assertSame('{}', feature_toggle_api()->activeTogglesToJson());
    }

    /**
     * @covers ::__construct
     * @covers ::isActive
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::refreshToggles
     * @covers ::calculateToggles
     *
     * @return void
     */
    public function testActiveToggle(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true, 'bar' => 'on']);

        $featureToggleApi = feature_toggle_api()->refreshToggles();

        $this->assertTrue($featureToggleApi->isActive('foo'));
        $this->assertTrue($featureToggleApi->isActive('bar'));
        $this->assertCount(2, $featureToggleApi->getToggles());
        $this->assertCount(2, $featureToggleApi->getActiveToggles());
    }

    /**
     * @covers ::__construct
     * @covers ::isActive
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::refreshToggles
     * @covers ::calculateToggles
     *
     * @return void
     */
    public function testInActiveToggle(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => false, 'bar' => 'off']);

        $featureToggleApi = feature_toggle_api()->refreshToggles();

        $this->assertFalse($featureToggleApi->isActive('foo'));
        $this->assertFalse($featureToggleApi->isActive('bar'));
        $this->assertCount(2, $featureToggleApi->getToggles());
        $this->assertCount(0, $featureToggleApi->getActiveToggles());
    }
}
