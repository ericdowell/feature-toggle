<?php

declare(strict_types=1);

namespace FeatureToggle;

use Throwable;
use Illuminate\Support\Collection;
use FeatureToggle\Toggle\FeatureToggle;
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

    public function __construct($model = null)
    {
        $this->model = $model ?? FeatureToggle::class;
    }

    /**
     * Get from all sources of toggles and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        try {
            return app($this->model)->all()->keyBy('name');
        } catch (Throwable $exception) {
            return collect();
        }
    }
}
