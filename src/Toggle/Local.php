<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

use FeatureToggle\Traits\Toggle;
use Illuminate\Contracts\Support\Arrayable;
use FeatureToggle\Contracts\Toggle as ToggleContract;

class Local implements ToggleContract, Arrayable
{
    use Toggle;

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
}
