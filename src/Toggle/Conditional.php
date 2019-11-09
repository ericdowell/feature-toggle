<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

class Conditional extends Local
{
    /**
     * @var callable
     */
    protected $condition;

    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @var bool
     */
    protected $called = false;

    /**
     * Conditional constructor.
     *
     * @param  string  $name
     * @param  callable  $condition
     * @param  bool|null  $defer
     */
    public function __construct(string $name, callable $condition, bool $defer = null)
    {
        $this->condition = $condition;
        $this->defer = $defer ?? $this->defer;
        $isActive = ! $this->defer ? $this->call() : false;
        parent::__construct($name, $isActive);
    }

    /**
     * @return mixed
     */
    protected function call()
    {
        $isActive = app()->call($this->condition);

        $this->called = true;

        return $isActive;
    }

    /**
     * Check if toggle is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (! $this->defer || $this->called) {
            return $this->is_active;
        }

        return $this->setIsActive($this->call())->is_active;
    }
}
