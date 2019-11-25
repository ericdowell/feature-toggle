<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Concerns\ToggleProvider;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\Toggle\Session as SessionToggle;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

class SessionToggleProvider implements ToggleProviderContract
{
    use ToggleProvider;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * SessionToggleProvider constructor.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @param  string|null  $key
     */
    public function __construct(Session $session, string $key = 'feature-toggles')
    {
        $this->key = $key;
        $this->session = $session;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'session';
    }

    /**
     * Get all toggles from session and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = collect();

        foreach ($this->calculateSessionToggles() as $name => $isActive) {
            $toggles->put($name, new SessionToggle($name, $isActive));
        }

        return $toggles;
    }

    /**
     * Pull feature toggles from the application user session.
     *
     * @return array
     */
    protected function calculateSessionToggles(): array
    {
        $sessionFeatures = $this->session->get($this->key, []);

        if (! is_array($sessionFeatures)) {
            return [];
        }

        return $sessionFeatures;
    }
}
