<?php

declare(strict_types=1);

namespace FeatureToggle\Contracts;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use Illuminate\Support\Collection;

/**
 * @codeCoverageIgnore
 */
interface ToggleProvider
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Check if feature toggle is active.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function isActive(string $name): bool;

    /**
     * Returns all feature toggles.
     *
     * @return ToggleContract[]|Collection
     */
    public function getToggles(): Collection;

    /**
     * Returns all active feature toggles.
     *
     * @return ToggleContract[]|Collection
     */
    public function getActiveToggles(): Collection;

    /**
     * Get all active toggles as JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function activeTogglesToJson($options = 0): string;

    /**
     * Refresh all feature toggle data.
     *
     * @return $this
     */
    public function refreshToggles(): self;
}
