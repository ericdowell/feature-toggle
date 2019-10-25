<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Toggle\QueryString;
use FeatureToggle\Contracts\Toggle as ToggleContract;

class QueryStringTest extends LocalTest
{
    /**
     * @param  string  $name
     * @param  mixed  $is_active
     * @return ToggleContract|QueryString
     */
    protected function getInstance(string $name, $is_active): ToggleContract
    {
        return new QueryString($name, $is_active);
    }
}
