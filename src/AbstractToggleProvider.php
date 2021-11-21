<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Concerns\ToggleProvider;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use Illuminate\Support\Collection;

abstract class AbstractToggleProvider implements ToggleProviderContract
{
    use ToggleProvider;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var object
     */
    protected $repository;

    /**
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * @param  string  $name
     * @param  mixed  $isActive
     * @return \FeatureToggle\Contracts\Toggle
     */
    abstract public function newRepositoryToggle(string $name, $isActive): ToggleContract;

    /**
     * Get all toggles from session and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = collect();

        foreach ($this->calculateRepositoryToggles() as $name => $isActive) {
            $toggles->put($name, $this->newRepositoryToggle($name, $isActive));
        }

        return $toggles;
    }

    /**
     * Pull feature toggles from the application repository.
     *
     * @return array
     */
    protected function calculateRepositoryToggles(): array
    {
        $repositoryFeatures = $this->repository->get($this->key, []);

        if (! is_array($repositoryFeatures)) {
            return [];
        }

        return $repositoryFeatures;
    }
}
