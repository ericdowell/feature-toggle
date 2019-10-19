<?php

declare(strict_types=1);

namespace FeatureToggle\Traits;

/**
 * @property string $name
 * @property bool $is_active
 */
trait Toggle
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if toggle is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'is_active' => $this->isActive(),
        ];
    }
}