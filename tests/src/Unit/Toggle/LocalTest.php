<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Toggle\Local;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggle;
use FeatureToggle\Contracts\Toggle as ToggleContract;

class LocalTest extends TestCase
{
    use TestToggle;

    /**
     * @param  string  $name
     * @param  mixed  $is_active
     * @return ToggleContract|Local
     */
    protected function getInstance(string $name, $is_active): ToggleContract
    {
        return new Local($name, $is_active);
    }
}
