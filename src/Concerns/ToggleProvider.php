<?php

declare(strict_types=1);

namespace FeatureToggle\Concerns;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use Illuminate\Support\Collection;

trait ToggleProvider
{
    /**
     * @var Collection
     */
    protected $toggles;

    /**
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Check if feature toggle is active.
     *
     * @param  string  $name
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
        return $this->toggles ?? $this->toggles = collect();
    }

    /**
     * @param  string  $name
     * @param  ToggleContract  $toggle
     * @return $this
     */
    protected function putToggle(string $name, ToggleContract $toggle): self
    {
        $this->toggles = $this->toggles ?? collect();

        $this->toggles->put($name, $toggle);

        return $this;
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
        $json = $toggles->toJson($options);

        return is_string($json) ? $json : '{}';
    }

    /**
     * Refresh all feature toggle data.
     *
     * @return $this|ToggleProviderContract
     */

    /**
     * Initialize all feature toggles.
     *
     * @return $this
     */
    public function refreshToggles(): ToggleProviderContract
    {
        $this->toggles = $this->calculateToggles();

        return $this;
    }

    /**
     * Get all toggles from source and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    abstract protected function calculateToggles(): Collection;
}
