<?php

declare(strict_types=1);

namespace FeatureToggle\Concerns;

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
     * @param  string|bool|int  $isActive
     * @return bool
     */
    public static function calculateIsActive($isActive): bool
    {
        if (is_bool($isActive)) {
            return $isActive;
        } elseif (is_string($isActive) || is_int($isActive)) {
            return filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }

    /**
     * @param  string|bool|int  $isActive
     * @return $this
     */
    protected function setIsActive($isActive): self
    {
        $this->is_active = $this->calculateIsActive($isActive);

        return $this;
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
