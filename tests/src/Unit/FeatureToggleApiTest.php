<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Tests\TestCase;

/**
 * @coversDefaultClass \FeatureToggle\FeatureToggleApi
 */
class FeatureToggleApiTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getToggles
     * @covers ::refresh
     * @covers ::initialize
     * @covers ::calculateToggles
     * @covers ::calculateLocalToggles
     *
     * @return void
     */
    public function testNotArrayConfigToggles(): void
    {
        config()->set('feature-toggle.toggles', null);

        $featureToggleApi = feature_toggle_api()->refresh();

        $this->assertCount(0, $featureToggleApi->getToggles());
    }
    /**
     * @covers ::__construct
     * @covers ::isActive
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::refresh
     * @covers ::initialize
     * @covers ::calculateToggles
     * @covers ::calculateLocalToggles
     *
     * @return void
     */
    public function testActiveToggle(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true, 'bar' => 'on']);

        $featureToggleApi = feature_toggle_api()->refresh();

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
     * @covers ::refresh
     * @covers ::initialize
     * @covers ::calculateToggles
     * @covers ::calculateLocalToggles
     *
     * @return void
     */
    public function testInActiveToggle(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => false, 'bar' => 'off']);

        $featureToggleApi = feature_toggle_api()->refresh();

        $this->assertFalse($featureToggleApi->isActive('foo'));
        $this->assertFalse($featureToggleApi->isActive('bar'));
        $this->assertCount(2, $featureToggleApi->getToggles());
        $this->assertCount(0, $featureToggleApi->getActiveToggles());
    }
}
