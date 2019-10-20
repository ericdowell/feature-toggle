<?php

declare(strict_types=1);

namespace FeatureToggle;

use Throwable;
use Illuminate\Support\Collection;
use FeatureToggle\Toggle\FeatureToggle;
use Illuminate\Database\Eloquent\Model;
use FeatureToggle\Contracts\Toggle as ToggleContract;

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
        $this->model = $model ?? FeatureToggle::class;
    }

    /**
     * @return FeatureToggle|Model
     */
    public function newModel(): Model
    {
        return app($this->model);
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
