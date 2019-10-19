<?php

declare(strict_types=1);

namespace FeatureToggle\Contracts;

interface Api extends ToggleProvider
{
    /**
     * @param  string  $name
     * @return \FeatureToggle\Contracts\ToggleProvider
     */
    public function getProvider(string $name): ToggleProvider;
}