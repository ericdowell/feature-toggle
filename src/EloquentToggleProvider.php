<?php

declare(strict_types=1);

namespace FeatureToggle;

use Throwable;
use FeatureToggle\Toggle\Eloquent;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use Illuminate\Contracts\Container\BindingResolutionException;

class EloquentToggleProvider extends LocalToggleProvider
{
    /**
     * @var string
     */
    const NAME = 'eloquent';

    /**
     * @var string
     */
    protected $model;

    /**
     * EloquentToggleProvider constructor.
     *
     * @param  null|string  $model
     */
    public function __construct($model = null)
    {
        $this->model = $model ?? Eloquent::class;
    }

    /**
     * @return Eloquent|Model
     * @throws BindingResolutionException
     */
    public function newModel(): Model
    {
        return app()->make($this->model);
    }

    /**
     * Get from all sources of toggles and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        try {
            return $this->newModel()->all()->keyBy('name');
        } catch (Throwable $exception) {
            return collect();
        }
    }
}
