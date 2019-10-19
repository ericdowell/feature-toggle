<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Support\Str;
use RuntimeException;
use OutOfBoundsException;
use Illuminate\Support\Collection;
use FeatureToggle\Traits\ToggleProvider;
use FeatureToggle\Contracts\Api as ApiContract;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

class Api implements ApiContract
{
    use ToggleProvider;

    /**
     * @var ToggleProviderContract[]
     */
    protected $providers;

    /**
     * @var string
     */
    protected $name;

    /**
     * Api constructor.
     *
     * @param  array  $providers
     */
    public function __construct(array $providers)
    {
        $this->name = 'primary-'.Str::random(5);

        foreach ($providers as $provider) {
            $this->loadProvider($provider);
        };

        $this->refreshToggles();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ToggleProviderContract|LocalToggleProvider
     */
    public function getLocalProvider(): LocalToggleProvider
    {
        if (! $this->hasProvider(LocalToggleProvider::NAME)) {
            $this->loadProvider(LocalToggleProvider::class);
        }

        return $this->getProvider(LocalToggleProvider::NAME);
    }

    /**
     * @return ToggleProviderContract|ConditionalToggleProvider
     */
    public function getConditionalProvider(): ConditionalToggleProvider
    {
        if (! $this->hasProvider(ConditionalToggleProvider::NAME)) {
            $this->loadProvider(ConditionalToggleProvider::class);
        }

        return $this->getProvider(ConditionalToggleProvider::NAME);
    }

    /**
     * @param  string  $name
     * @return bool
     */
    public function hasProvider(string $name): bool
    {
        return isset($this->providers[$name]) && $this->providers[$name] instanceof ToggleProviderContract;
    }

    /**
     * @param  string  $name
     * @return ToggleProviderContract
     * @throws RuntimeException
     */
    public function getProvider(string $name): ToggleProviderContract
    {
        if (! $this->hasProvider($name)) {
            throw new RuntimeException("Toggle provider '{$name}' is not loaded.");
        }

        return $this->providers[$name];
    }

    /**
     * @param  string|ToggleProviderContract  $provider
     * @return $this
     * @throws OutOfBoundsException
     */
    public function loadProvider($provider): self
    {
        $instance = $this->getProviderInstance($provider);
        if ($instance instanceof ToggleProviderContract) {
            $this->providers[$instance->getName()] = $instance;

            return $this;
        }
        throw new OutOfBoundsException('Could not load toggle provider.');
    }

    protected function getProviderInstance($provider): ?ToggleProviderContract
    {
        if ($provider instanceof ToggleProviderContract) {
            return $provider;
        }
        if (! is_string($provider) || ! class_exists($provider)) {
            return null;
        }
        $instance = new $provider();
        if ($instance instanceof ToggleProviderContract) {
            return $instance;
        }

        return $instance;
    }

    public function setConditional(string $name, callable $condition)
    {
        $this->getProvider('conditional');
    }

    /**
     * Returns all feature toggles.
     *
     * @return ToggleContract[]|Collection
     */
    public function getToggles(): Collection
    {
        $toggles = collect();
        foreach ($this->providers as $provider) {
            foreach ($provider->getToggles() as $toggle) {
                if ($toggles->has($toggle->getName())) {
                    continue;
                }
                $toggles->put($toggle->getName(), $toggle);
            }
        }

        return $toggles;
    }

    /**
     * Refresh all feature toggle data.
     *
     * @return $this
     */
    public function refreshToggles(): ToggleProviderContract
    {
        foreach ($this->providers as $provider) {
            $provider->refreshToggles();
        }

        return $this;
    }
}
