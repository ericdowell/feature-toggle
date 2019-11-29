<?php

declare(strict_types=1);

namespace FeatureToggle\Concerns;

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
        return filter_var($this->getOption('registerMiddleware', true), FILTER_VALIDATE_BOOLEAN);
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
        static::setOptionTrue('useMigrations');
    }

    /**
     * @return void
     */
    public static function ignoreMigrations(): void
    {
        static::setOptionFalse('useMigrations');
    }

    /**
     * @return void
     */
    public static function registerMiddleware(): void
    {
        static::setOptionTrue('registerMiddleware');
    }

    /**
     * @return void
     */
    public static function ignoreMiddleware(): void
    {
        static::setOptionFalse('registerMiddleware');
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
     * @return void
     */
    protected static function setOptionFalse(string $name): void
    {
        static::setOption($name, false);
    }

    /**
     * @param  string  $name
     * @return void
     */
    protected static function setOptionTrue(string $name): void
    {
        static::setOption($name, true);
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
