<?php

declare(strict_types=1);

namespace FeatureToggle\Traits;

use Illuminate\Support\Arr;

trait Options
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param  string  $name
     * @param  null|mixed  $default
     * @return mixed
     */
    protected function getOption(string $name, $default = null)
    {
        return Arr::get($this->options, $name, $default);
    }

    /**
     * @param  string  $name
     * @param  mixed  $value
     * @return $this
     */
    protected function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }
}
