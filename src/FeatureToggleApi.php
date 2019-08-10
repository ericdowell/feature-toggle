<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Support\Collection;
use FeatureToggle\Toggle\Local as LocalToggle;
use FeatureToggle\Contacts\Toggle as ToggleContract;
use FeatureToggle\Contacts\FeatureToggleApi as FeatureToggleApiContract;

class FeatureToggleApi implements FeatureToggleApiContract
{
    /**
     * @var Collection
     */
    protected $toggles;

    /**
     * FeatureToggleApi constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Check if feature toggle is active.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function isActive(string $name): bool
    {
        $toggle = $this->getToggles()->get($name);

        return $toggle instanceof ToggleContract ? $toggle->isActive() : false;
    }

    /**
     * Returns all feature toggles.
     *
     * @return ToggleContract[]|Collection
     */
    public function getToggles(): Collection
    {
        return $this->toggles;
    }

    /**
     * Returns all active feature toggles.
     *
     * @return ToggleContract[]|Collection
     */
    public function getActiveToggles(): Collection
    {
        return $this->getToggles()->filter(function (ToggleContract $toggle) {
            return $toggle->isActive();
        });
    }

    /**
     * Get all active toggles as JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function activeTogglesToJson($options = 0): string
    {
        $toggles = $this->getActiveToggles();
        if ($toggles->isEmpty()) {
            return '{}';
        }
        $json = $toggles->toJson();

        return is_string($json) ? $json : '{}';
    }

    /**
     * Refresh all feature toggle data.
     *
     * @return $this
     */
    public function refresh(): FeatureToggleApiContract
    {
        return $this->initialize();
    }

    /**
     * Initialize all feature toggles.
     *
     * @return $this
     */
    protected function initialize(): self
    {
        $this->toggles = $this->calculateToggles();

        return $this;
    }

    /**
     * Get from all sources of toggles and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = collect();

        foreach ($this->calculateLocalToggles() as $name => $isActive) {
            $toggles->put($name, new LocalToggle($name, $isActive));
        }

        return $toggles;
    }

    /**
     * Pull feature toggles from the application config file.
     *
     * @return array
     */
    protected function calculateLocalToggles(): array
    {
        $localFeatures = config('feature-toggle.toggles', []);

        if (! is_array($localFeatures)) {
            return [];
        }

        return $localFeatures;
    }
}
