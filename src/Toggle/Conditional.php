<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

class Conditional extends Local
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * Conditional constructor.
     *
     * @param  string  $name
     * @param  callable  $callback
     */
    public function __construct(string $name, callable $callback)
    {
        parent::__construct($name, null);

        $this->callback = $callback;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return call_user_func($this->callback);
    }
}