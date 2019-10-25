<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Tests\TestCase;
use FeatureToggle\Toggle\Database;
use FeatureToggle\Tests\Traits\TestToggle;
use FeatureToggle\Contracts\Toggle as ToggleContract;

class FeatureToggleTest extends TestCase
{
    use TestToggle;

    /**
     * @param  string  $name
     * @param  mixed  $is_active
     * @return ToggleContract|Database
     */
    protected function getInstance(string $name, $is_active): ToggleContract
    {
        return new Database(compact('name', 'is_active'));
    }
}
