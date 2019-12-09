<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\Session as SessionToggle;
use Illuminate\Contracts\Session\Session;

class SessionToggleProvider extends AbstractToggleProvider
{
    /**
     * SessionToggleProvider constructor.
     *
     * @param  \Illuminate\Contracts\Session\Session  $repository
     * @param  string  $key
     */
    public function __construct(Session $repository, string $key = 'feature-toggles')
    {
        $this->key = $key;
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'session';
    }

    /**
     * @param  string  $name
     * @param  mixed  $isActive
     * @return SessionToggle
     */
    public function newRepositoryToggle(string $name, $isActive): ToggleContract
    {
        return new SessionToggle($name, $isActive);
    }
}
