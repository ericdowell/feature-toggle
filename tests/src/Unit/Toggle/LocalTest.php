<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggle;
use FeatureToggle\Toggle\Local;

/**
 * @group unit
 */
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
