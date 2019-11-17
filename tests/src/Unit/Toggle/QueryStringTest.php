<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit\Toggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\QueryString;

/**
 * @group unit
 */
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
