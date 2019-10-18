<?php

declare(strict_types=1);

namespace FeatureToggle\Contracts;

interface Api extends ToggleProvider
{
    /**
     * @return $this
     */
    public function refreshProviderToggles(): self;
}