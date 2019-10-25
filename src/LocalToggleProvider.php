<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Support\Collection;
use FeatureToggle\Traits\ToggleProvider;
use FeatureToggle\Toggle\Local as LocalToggle;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

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
        return static::NAME;
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
