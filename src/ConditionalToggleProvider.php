<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Support\Collection;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\Conditional as ConditionalToggle;

class ConditionalToggleProvider extends LocalToggleProvider
{
    /**
     * @var string
     */
    const NAME = 'conditional';

    /**
     * @var callable[]
     */
    protected $conditions = [];

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
        $this->conditions[$name] = $condition;

        return $this->putToggle($name, new ConditionalToggle($name, $condition));
    }

    /**
     * Returns a feature toggle.
     *
     * @param  string  $name
     * @return ConditionalToggle|null
     */
    public function findToggle(string $name): ?ToggleContract
    {
        return $this->getToggles()->get($name);
    }

    /**
     * Get from all sources of toggles and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = collect();

        foreach ($this->conditions as $name => $condition) {
            $toggles->put($name, new ConditionalToggle($name, $condition));
        }

        return $toggles;
    }
}
