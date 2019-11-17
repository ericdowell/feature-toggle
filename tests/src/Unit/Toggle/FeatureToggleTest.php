<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggle;
use FeatureToggle\Toggle\Eloquent;

/**
 * @group unit
 */
class FeatureToggleTest extends TestCase
{
    use TestToggle;

    /**
     * @param  string  $name
     * @param  mixed  $is_active
     * @return ToggleContract|Eloquent
     */
    protected function getInstance(string $name, $is_active): ToggleContract
    {
        return new Eloquent(compact('name', 'is_active'));
    }
}
