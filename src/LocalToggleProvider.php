<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\Local as LocalToggle;
use Illuminate\Contracts\Config\Repository as ConfigContract;

class LocalToggleProvider extends AbstractToggleProvider
{
    /**
     * LocalToggleProvider constructor.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @param  string  $key
     */
    public function __construct(ConfigContract $repository, string $key = 'feature-toggle.toggles')
    {
        $this->repository = $repository;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'local';
    }

    /**
     * @param  string  $name
     * @param  mixed  $isActive
     * @return LocalToggle
     */
    public function newRepositoryToggle(string $name, $isActive): ToggleContract
    {
        return new LocalToggle($name, $isActive);
    }
}
