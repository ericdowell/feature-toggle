<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Support\Collection;
use FeatureToggle\Traits\ToggleProvider;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\Conditional as ConditionalToggle;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

class ConditionalToggleProvider implements ToggleProviderContract
{
    use ToggleProvider;

    /**
     * @var string
     */
    const NAME = 'conditional';

    /**
     * @var callable[]
     */
    protected static $conditions = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param  string  $name
     * @param  callable  $condition
     * @return \FeatureToggle\ConditionalToggleProvider
     */
    public function setToggle(string $name, callable $condition): self
    {
        self::$conditions[$name] = $condition;

        return $this->refreshToggles();
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

        foreach (self::$conditions as $name => $condition) {
            $toggles->put($name, new ConditionalToggle($name, $condition));
        }

        return $toggles;
    }
}