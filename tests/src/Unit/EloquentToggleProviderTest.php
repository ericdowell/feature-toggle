<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use Exception;
use FeatureToggle\Api;
use FeatureToggle\Concerns\Toggle;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\EloquentToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleProvider;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Toggle\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

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
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testCalculateTogglesCatchesErrorAndReturnEmptyCollection(): void
    {
        tap(new Eloquent([
            'name' => 'foo',
            'is_active' => true,
        ]), function (Eloquent $toggle) {
            $toggle->save();
        });
        $this->mock(EloquentToggleProvider::class, function ($mock) {
            /* @var \Mockery\MockInterface $mock */
            $mock->shouldReceive('refreshToggles')->passthru();
            $mock->shouldReceive('getToggles')->passthru();
            $mock->shouldReceive('isActive')->passthru();
            $mock->shouldReceive('newModel')->once()->andThrow(Exception::class, 'Something went wrong.');
        });
        /* @var EloquentToggleProvider $toggleProvider */
        $toggleProvider = app()->make(EloquentToggleProvider::class);
        $toggleProvider->refreshToggles();

        $this->assertCount(0, $toggleProvider->getToggles());
        $this->assertFalse($toggleProvider->isActive('foo'), '"foo" toggle check, should BE false.');
    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testPassingModelParameterToAppMake(): void
    {
        $toggleProvider = app()->make('feature-toggle.eloquent', ['model' => TestToggle::class]);

        $this->assertInstanceOf(TestToggle::class, $toggleProvider->newModel());
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
            tap(new Eloquent(compact('name', 'is_active')), function (Eloquent $toggle) {
                $toggle->save();
            });
        }

        return $this->getToggleProvider();
    }
}

class TestToggle extends Model implements ToggleContract
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
