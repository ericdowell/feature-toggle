<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Api as ApiContract;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\Traits\HasStaticOptions;
use FeatureToggle\Traits\ToggleProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OutOfBoundsException;
use RuntimeException;

class Api implements ApiContract
{
    use HasStaticOptions, ToggleProvider;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ToggleProviderContract[]
     */
    protected $providers = [];

    /**
     * Api constructor.
     *
     * @param  array  $providers
     * @param  array  $options
     * @throws OutOfBoundsException
     * @throws BindingResolutionException
     */
    public function __construct(array $providers, array $options = [])
    {
        $this->name = 'primary-'.Str::random(5);

        static::$options = static::$options + $options;

        $this->setProviders($providers);
    }

    /**
     * @param  array  $providers
     * @return $this
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
     * @return array
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return bool
     */
    public function isMigrationsEnabled(): bool
    {
        return filter_var($this->getOption('useMigrations', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return void
     */
    public static function useMigrations(): void
    {
        static::setOption('useMigrations', true);
    }

    /**
     * @return void
     */
    public static function ignoreMigrations(): void
    {
        static::setOption('useMigrations', false);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ToggleProviderContract|ConditionalToggleProvider
     */
    public function &getConditionalProvider(): ConditionalToggleProvider
    {
        return $this->getProvider(ConditionalToggleProvider::NAME);
    }

    /**
     * @return ToggleProviderContract|EloquentToggleProvider
     */
    public function getEloquentProvider(): EloquentToggleProvider
    {
        return $this->getProvider(EloquentToggleProvider::NAME);
    }

    /**
     * @return ToggleProviderContract|LocalToggleProvider
     */
    public function getLocalProvider(): LocalToggleProvider
    {
        return $this->getProvider(LocalToggleProvider::NAME);
    }

    /**
     * @return ToggleProviderContract|QueryStringToggleProvider
     */
    public function getQueryStringProvider(): QueryStringToggleProvider
    {
        return $this->getProvider(QueryStringToggleProvider::NAME);
    }

    /**
     * @param  string  $name
     * @param  callable  $condition
     * @param  bool|null  $delay
     * @return $this
     */
    public function setConditional(string $name, callable $condition, bool $delay = null): ApiContract
    {
        $this->getConditionalProvider()->setToggle($name, $condition, $delay);

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
