<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\Conditional as ConditionalToggle;
use Illuminate\Support\Collection;

class ConditionalToggleProvider extends LocalToggleProvider
{
    /**
     * @var string
     */
    const NAME = 'conditional';

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @param  string  $name
     * @param  callable  $condition
     * @param  bool|null  $defer
     * @return \FeatureToggle\ConditionalToggleProvider
     */
    public function setToggle(string $name, callable $condition, bool $defer = null): self
    {
        $this->conditions[$name] = compact('condition', 'defer');

        return $this->putToggle($name, new ConditionalToggle($name, $condition, $defer));
    }

    /**
     * Get all toggles from conditions array and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = collect();

        foreach ($this->conditions as $name => ['condition' => $condition, 'defer' => $defer]) {
            $toggles->put($name, new ConditionalToggle($name, $condition, $defer));
        }

        return $toggles;
    }
}
