<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

class Conditional extends Local
{
    /**
     * Conditional constructor.
     *
     * @param  string  $name
     * @param  callable  $condition
     */
    public function __construct(string $name, callable $condition)
    {
        parent::__construct($name, call_user_func($condition));
    }
}
