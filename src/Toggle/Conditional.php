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
     * Conditional constructor.
     *
     * @param  string  $name
     * @param  callable  $condition
     */
    public function __construct(string $name, callable $condition)
    {
        parent::__construct($name, null);

        $this->condition = $condition;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return call_user_func($this->condition);
    }
}
