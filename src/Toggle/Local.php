<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

use Illuminate\Contracts\Support\Arrayable;
use FeatureToggle\Contracts\Toggle as ToggleContract;

class Local implements ToggleContract, Arrayable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $is_active = false;

    /**
     * Local constructor.
     *
     * @param  string  $name
     * @param  string|bool|int  $isActive
     */
    public function __construct(string $name, $isActive)
    {
        $this->name = $name;

        if (is_bool($isActive)) {
            $this->is_active = $isActive;
        } elseif (is_string($isActive) || is_int($isActive)) {
            $this->is_active = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
        }
    }

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
        return ['name' => $this->getName(), 'is_active' => $this->isActive()];
    }
}
