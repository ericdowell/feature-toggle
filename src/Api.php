<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Concerns\HasStaticOptions;
use FeatureToggle\Concerns\ToggleProvider;
use FeatureToggle\Contracts\Api as ApiContract;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use OutOfBoundsException;
use RuntimeException;

class Api implements ApiContract
{
    use HasStaticOptions, Macroable, ToggleProvider;

    /**
     * @var string
     */
    protected static $name = 'primary';

    /**
     * @var ToggleProviderContract[]
     */
    protected $providers = [];

    /**
     * Api constructor.
     *
     * @param  array  $providers
     * @param  array  $options
     *
     * @throws OutOfBoundsException
     * @throws BindingResolutionException
     */
    public function __construct(array $providers, array $options = [])
    {
        self::$name = 'primary-'.Str::random(5);

        static::$options = static::$options + $options;

        $this->setProviders($providers);
    }

    /**
     * @param  array  $providers
     * @return $this
     *
     * @throws OutOfBoundsException
     * @throws BindingResolutionException
     */
    public function setProviders(array $providers): self
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $driver = Arr::get($provider, 'driver');
            $parameters = Arr::except($provider, 'driver');
            $this->loadProvider($driver, $parameters);
        }

        $this->refreshToggles();

        return $this;
    }

    /**
     * @return ToggleProviderContract[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return self::$name;
    }

    /**
     * @return ToggleProviderContract|ConditionalToggleProvider
     */
    public function &getConditionalProvider(): ConditionalToggleProvider
    {
        return $this->getProvider(ConditionalToggleProvider::getName());
    }

    /**
     * @return ToggleProviderContract|EloquentToggleProvider
     */
    public function getEloquentProvider(): EloquentToggleProvider
    {
        return $this->getProvider(EloquentToggleProvider::getName());
    }

    /**
     * @return ToggleProviderContract|LocalToggleProvider
     */
    public function getLocalProvider(): LocalToggleProvider
    {
        return $this->getProvider(LocalToggleProvider::getName());
    }

    /**
     * @return ToggleProviderContract|QueryStringToggleProvider
     */
    public function getQueryStringProvider(): QueryStringToggleProvider
    {
        return $this->getProvider(QueryStringToggleProvider::getName());
    }

    /**
     * @return ToggleProviderContract|RedisToggleProvider
     */
    public function getRedisProvider(): RedisToggleProvider
    {
        return $this->getProvider(RedisToggleProvider::getName());
    }

    /**
     * @return ToggleProviderContract|SessionToggleProvider
     */
    public function getSessionProvider(): SessionToggleProvider
    {
        return $this->getProvider(SessionToggleProvider::getName());
    }

    /**
     * @param  string  $name
     * @param  callable  $condition
     * @param  bool|null  $defer
     * @return $this
     */
    public function setConditional(string $name, callable $condition, bool $defer = null): ApiContract
    {
        $this->getConditionalProvider()->setToggle($name, $condition, $defer);

        return $this;
    }

    /**
     * Returns all feature toggles.
     *
     * @return ToggleContract[]|Collection
     */
    public function getToggles(): Collection
    {
        return $this->calculateToggles();
    }

    /**
     * Returns all feature toggles for a specific provider.
     *
     * @param  string  $name
     * @return ToggleContract[]|Collection
     *
     * @throws RuntimeException
     */
    public function getProviderToggles(string $name): Collection
    {
        return $this->getProvider($name)->getToggles();
    }

    /**
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = [];
        foreach ($this->getProviders() as $provider) {
            $toggles = $toggles + $provider->getToggles()->all();
        }

        return collect($toggles);
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
     * @param  string|int|bool  $checkActive
     * @return \FeatureToggle\Rules\FeatureToggle
     */
    public function requiredIfRule(string $name, $checkActive = true): Rules\FeatureToggle
    {
        return new Rules\FeatureToggle($name, $checkActive);
    }

    /**
     * Refresh all feature toggle data for a specific provider.
     *
     * @param  string  $name
     * @return $this
     */
    public function refreshProvider(string $name): self
    {
        $this->getProvider($name)->refreshToggles();

        return $this;
    }

    /**
     * @param  string  $name
     * @return ToggleProviderContract
     *
     * @throws RuntimeException
     */
    public function &getProvider(string $name): ToggleProviderContract
    {
        if (! isset($this->providers[$name])) {
            throw new RuntimeException("Toggle provider '{$name}' is not loaded.");
        }

        return $this->providers[$name];
    }

    /**
     * @param  string  $driver
     * @param  array  $parameters
     * @return $this
     *
     * @throws OutOfBoundsException
     * @throws BindingResolutionException
     */
    public function loadProvider(string $driver, array $parameters = []): self
    {
        $provider = app()->make("feature-toggle.{$driver}", $parameters);
        if ($provider instanceof ToggleProviderContract) {
            $this->providers[$provider->getName()] = $provider;

            return $this;
        }
        throw new OutOfBoundsException("Could not load toggle provider: '{$driver}'");
    }
}
