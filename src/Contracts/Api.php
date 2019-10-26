<?php

declare(strict_types=1);

namespace FeatureToggle\Contracts;

/**
 * @codeCoverageIgnore
 */
interface Api extends ToggleProvider
{
    /**
     * @param  string  $name
     * @param  callable  $condition
     * @return $this
     */
    public function setConditional(string $name, callable $condition): self;
}
