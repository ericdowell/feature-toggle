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
