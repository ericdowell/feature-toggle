<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

use FeatureToggle\Concerns\Toggle;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractToggle implements ToggleContract, Arrayable
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
        $this->setIsActive($isActive);
    }
}
