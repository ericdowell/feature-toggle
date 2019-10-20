<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Api;
use FeatureToggle\Traits\Toggle;
use FeatureToggle\Tests\TestCase;
use Illuminate\Support\Collection;
use FeatureToggle\Toggle\FeatureToggle;
use Illuminate\Database\Eloquent\Model;
use FeatureToggle\EloquentToggleProvider;
use FeatureToggle\Tests\Traits\TestToggleProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

/**
 * @coversDefaultClass \FeatureToggle\EloquentToggleProvider
 */
class EloquentToggleProviderTest extends TestCase
{
    use RefreshDatabase, TestToggleProvider;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        Api::useMigrations();

        parent::setUp();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        Api::ignoreMigrations();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testPassingModelParameterToAppMake()
    {
        $provider = app()->make('feature-toggle.eloquent', ['model' => TestFeatureToggle::class]);

        $this->assertInstanceOf(TestFeatureToggle::class, $provider->newModel());
    }

    /**
     * @return \FeatureToggle\EloquentToggleProvider
     */
    protected function getToggleProvider(): EloquentToggleProvider
    {
        return (new EloquentToggleProvider())->refreshToggles();
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\EloquentToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        if (! $toggles) {
            return $this->getToggleProvider();
        }
        foreach ($toggles as $name => $is_active) {
            tap(new FeatureToggle(compact('name', 'is_active')), function (FeatureToggle $toggle) {
                $toggle->save();
            });
        }

        return $this->getToggleProvider();
    }
}

class TestFeatureToggle extends Model implements ToggleContract
{
    use Toggle;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'name' => 'testing',
        'is_active' => 1,
    ];

    /**
     * @param  array  $columns
     * @return $this
     */
    public static function all($columns = ['*'])
    {
        return new static();
    }

    /**
     * @return Collection
     */
    public function keyBy(): Collection
    {
        return collect([new static()]);
    }
}
