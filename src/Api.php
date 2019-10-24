<?php

declare(strict_types=1);

namespace FeatureToggle;

use RuntimeException;
use OutOfBoundsException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use FeatureToggle\Traits\ToggleProvider;
use FeatureToggle\Traits\HasStaticOptions;
use FeatureToggle\Contracts\Api as ApiContract;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;

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
    protected $providers;

    /**
     * Api constructor.
     *
     * @param  array  $providers
     * @param  array  $options
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
     */
    public function setProviders(array $providers): self
    {
        foreach ($providers as $provider) {
            $driver = Arr::get($provider, 'driver');
            $parameters = Arr::except($provider, 'driver');
            $this->loadProvider($driver, $parameters);
        }

        $mode = $this->getOption('mode');
        switch ($mode) {
            case 'lazy':
                break;
            default:
                $this->refreshToggles();
                break;
        }

        return $this;
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
     * @param  string|null  $intended
     * @return ToggleContract[]|Collection
     */
    public function getToggles(string $intended = null): Collection
    {
        $mode = $this->getOption('mode');
        switch ($mode) {
            case 'lazy':
                if ($intended && ! is_null($toggle = $this->findToggle($intended))) {
                    return collect([$toggle->getName() => $toggle]);
                }

                return $this->calculateToggles($intended);
            default:
                return $this->calculateToggles();
        }
    }

    /**
     * Returns a feature toggle.
     *
     * @param  string  $name
     * @return ToggleContract|null
     */
    public function findToggle(string $name): ?ToggleContract
    {
        $toggle = Arr::get($this->toggles, $name);
        if (! $toggle instanceof ToggleContract) {
            return null;
        }

        return $toggle;
    }

    /**
     * @param  string|null  $intended
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(string $intended = null): Collection
    {
        $toggles = [];
        foreach ($this->providers as $provider) {
            if (! $intended) {
                $toggles = $toggles + $provider->getToggles()->all();
                continue;
            }
            $toggle = $provider->findToggle($intended);
            if (! is_null($toggle)) {
                $toggles[$toggle->getName()] = $this->toggles[$toggle->getName()] = $toggle;
                break;
            }
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
     * @return ToggleProviderContract
     * @throws RuntimeException
     */
    public function &getProvider(string $name): ToggleProviderContract
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
