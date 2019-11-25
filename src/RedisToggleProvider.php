<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Toggle\Redis as RedisToggle;
use Illuminate\Contracts\Redis\Factory as Redis;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Collection;

class RedisToggleProvider extends LocalToggleProvider
{
    /**
     * @var string
     */
    const NAME = 'redis';

    /**
     * The Redis connection that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * A string that should be used as the key.
     *
     * @var string
     */
    protected $key;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The Redis factory implementation.
     *
     * @var \Illuminate\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * RedisToggleProvider constructor.
     *
     * @param  \Illuminate\Contracts\Redis\Factory  $redis
     * @param  string  $key
     * @param  string  $prefix
     * @param  string  $connection
     */
    public function __construct(
        Redis $redis,
        string $key = 'feature_toggles',
        ?string $prefix = null,
        string $connection = 'default'
    ) {
        $this->redis = $redis;
        $this->key = $key;
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
        $this->connection = $connection;
    }

    /**
     * Get all toggles from redis and normalize.
     *
     * @return Collection
     */
    public function calculateToggles(): Collection
    {
        $toggles = collect();
        foreach ($this->calculateRedisToggles() as $name => $isActive) {
            if ($isActive instanceof ToggleContract) {
                $toggles->put($isActive->getName(), $isActive);
            } elseif (is_string($name)) {
                $toggles->put($name, new RedisToggle($name, $isActive));
            }
        }

        return $toggles;
    }

    /**
     * Pull feature toggles from the redis database.
     *
     * @return array
     */
    protected function calculateRedisToggles(): array
    {
        $value = $this->connection()->get($this->prefix.$this->key);
        if (is_null($value) || ! is_array($redisToggles = $this->unserialize($value))) {
            return [];
        }

        return $redisToggles;
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection(): Connection
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Get the feature toggle key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the feature toggle key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}
