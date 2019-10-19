<?php

declare(strict_types=1);

namespace FeatureToggle;

use RuntimeException;
use OutOfBoundsException;
use Illuminate\Support\Str;
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
        }

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
        return $this->getProvider(LocalToggleProvider::NAME);
    }

    /**
     * @return ToggleProviderContract|ConditionalToggleProvider
     */
    public function &getConditionalProvider(): ConditionalToggleProvider
    {
        return $this->getProvider(ConditionalToggleProvider::NAME);
    }

    /**
     * @param  string  $name
     * @return ToggleProviderContract
     * @throws RuntimeException
     */
    protected function &getProvider(string $name): ToggleProviderContract
    {
        if (! $this->providers[$name]) {
            throw new RuntimeException("Toggle provider '{$name}' is not loaded.");
        }

        return $this->providers[$name];
    }

    /**
     * @param  string|ToggleProviderContract  $provider
     * @return $this
     * @throws OutOfBoundsException
     */
    protected function loadProvider($provider): self
    {
        $instance = $this->getProviderInstance($provider);
        if ($instance instanceof ToggleProviderContract) {
            $this->providers[$instance->getName()] = $instance;

            return $this;
        }
        // @todo: Add better messaging if $provider is a class, but doesn't implement ToggleProviderContract.
        throw new OutOfBoundsException('Could not load toggle provider: '.print_r($provider, true));
    }

    /**
     * @param  string|ToggleProviderContract  $provider
     * @return ToggleProviderContract|null
     */
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

    /**
     * @param  string  $name
     * @param  callable  $condition
     * @return $this
     */
    public function setConditional(string $name, callable $condition): ApiContract
    {
        $this->getConditionalProvider()->setToggle($name, $condition);

        return $this;
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
        foreach ($this->providers as &$provider) {
            $provider->refreshToggles();
        }

        return $this;
    }
}
