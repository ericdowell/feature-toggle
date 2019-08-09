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
     * @covers ::isActive
     * @covers ::getActiveToggles
     * @covers ::refresh
     * @covers ::initialize
     *
     * @return void
     */
    public function testActiveToggle(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);

        $featureToggleApi = feature_toggle_api()->refresh();

        $this->assertTrue($featureToggleApi->isActive('foo'));
    }
}