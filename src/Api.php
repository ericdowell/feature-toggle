<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Support\Arr;
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
            $driver = Arr::get($provider, 'driver');
            $parameters = Arr::except($provider, 'driver');
            $this->loadProvider($driver, $parameters);
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
     * @param  string  $driver
     * @param  array  $parameters
     * @return $this
     * @throws OutOfBoundsException
     */
    protected function loadProvider(string $driver, array $parameters): self
    {
        $provider = app("feature-toggle.{$driver}", $parameters);
        if ($provider instanceof ToggleProviderContract) {
            $this->providers[$provider->getName()] = $provider;

            return $this;
        }
        throw new OutOfBoundsException("Could not load toggle provider: '{$driver}'");
    }
}
