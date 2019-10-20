<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Api;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggleProvider;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

/**
 * @coversDefaultClass \FeatureToggle\Api
 */
class ApiTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @return Api
     */
    protected function getToggleProvider(): Api
    {
        return feature_toggle_api()->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return Api|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        config()->set('feature-toggle.toggles', $toggles);

        return $this->getToggleProvider();
    }

    /**
     * @covers ::__construct
     * @covers ::setConditional
     * @covers ::isActive
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::refreshToggles
     *
     * @return void
     */
    public function testLocalAndConditionalToggleProviders(): void
    {
        $featureToggleApi = $this->setToggles([
            'foo' => false,
            'bar' => 'on',
        ]);

        feature_toggle_api()->setConditional('baz', function () {
            return true;
        })->setConditional('bar', function () {
            return false;
        });

        $this->assertFalse($featureToggleApi->isActive('foo'));
        $this->assertFalse($featureToggleApi->isActive('bar'));
        $this->assertTrue($featureToggleApi->isActive('baz'));
        $this->assertCount(3, $featureToggleApi->getToggles());
        $this->assertCount(1, $featureToggleApi->getActiveToggles());
    }
}
