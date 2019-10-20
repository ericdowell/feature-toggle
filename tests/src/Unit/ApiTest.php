<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Api;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Toggle\FeatureToggle;
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
     * @covers ::useMigrations
     * @covers ::ignoreMigrations
     * @covers ::isMigrationsEnabled
     *
     * @return void
     */
    public function testUseMigrationsMethods(): void
    {
        $this->assertFalse(feature_toggle_api()->isMigrationsEnabled());

        Api::useMigrations();
        $this->assertTrue(feature_toggle_api()->isMigrationsEnabled());

        Api::ignoreMigrations();
        $this->assertFalse(feature_toggle_api()->isMigrationsEnabled());
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

    /**
     * @covers ::__construct
     * @covers ::setProviders
     * @covers ::setConditional
     * @covers ::isActive
     * @covers ::getToggles
     * @covers ::getActiveToggles
     * @covers ::refreshToggles
     *
     * @return void
     */
    public function testSetProvidersEloquentAndConditionalToggleProviders(): void
    {
        $this->setToggles([
            'local' => true,
        ]);
        feature_toggle_api()->setProviders([
            [
                'driver' => 'conditional',
            ],
            [
                'driver' => 'eloquent',
            ],
        ]);
        $toggles = [
            'foo' => false,
            'bar' => 'on',
        ];
        foreach ($toggles as $name => $is_active) {
            tap(new FeatureToggle(compact('name', 'is_active')), function (FeatureToggle $toggle) {
                $toggle->save();
            });
        }

        $featureToggleApi = $this->getToggleProvider();

        feature_toggle_api()->setConditional('baz', function () {
            return true;
        })->setConditional('bar', function () {
            return false;
        });

        $this->assertFalse($featureToggleApi->isActive('local'));
        $this->assertFalse($featureToggleApi->isActive('foo'));
        $this->assertFalse($featureToggleApi->isActive('bar'));
        $this->assertTrue($featureToggleApi->isActive('baz'));
        $this->assertCount(3, $featureToggleApi->getToggles());
        $this->assertCount(1, $featureToggleApi->getActiveToggles());
    }
}
