<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\Toggle\Local as LocalToggle;
use FeatureToggle\Traits\ToggleProvider;
use Illuminate\Support\Collection;

class LocalToggleProvider implements ToggleProviderContract
{
    use ToggleProvider;

    /**
     * @var string
     */
    const NAME = 'local';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Initialize all feature toggles.
     *
     * @return $this
     */
    public function refreshToggles(): ToggleProviderContract
    {
        $this->toggles = $this->calculateToggles();

        return $this;
    }

    /**
     * Get from all sources of toggles and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = collect();

        foreach ($this->calculateLocalToggles() as $name => $isActive) {
            $toggles->put($name, new LocalToggle($name, $isActive));
        }

        return $toggles;
    }

    /**
     * Pull feature toggles from the application config file.
     *
     * @return array
     */
    protected function calculateLocalToggles(): array
    {
        $localFeatures = config('feature-toggle.toggles', []);

        if (! is_array($localFeatures)) {
            return [];
        }

        return $localFeatures;
    }
}