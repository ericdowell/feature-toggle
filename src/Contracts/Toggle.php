<?php

declare(strict_types=1);

namespace FeatureToggle\Contacts;

interface Toggle
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return bool
     */
    public function isActive(): bool;
}
