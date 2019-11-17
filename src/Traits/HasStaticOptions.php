<?php

declare(strict_types=1);

namespace FeatureToggle\Traits;

use Illuminate\Support\Arr;

trait HasStaticOptions
{
    /**
     * @var array
     */
    protected static $options = [];

    /**
     * @return bool
     */
    public function isMiddlewareEnabled(): bool
    {
        return filter_var($this->getOption('registerMiddleware', false), FILTER_VALIDATE_BOOLEAN);
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
     * @return void
     */
    public static function registerMiddleware(): void
    {
        static::setOption('registerMiddleware', true);
    }

    /**
     * @return void
     */
    public static function ignoreMiddleware(): void
    {
        static::setOption('registerMiddleware', false);
    }

    /**
     * @param  string  $name
     * @param  null|mixed  $default
     * @return mixed
     */
    protected function getOption(string $name, $default = null)
    {
        return Arr::get(static::$options, $name, $default);
    }

    /**
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    protected static function setOption(string $name, $value): void
    {
        static::$options[$name] = $value;
    }
}
