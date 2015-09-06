<?php

namespace PulkitJalan\Cache;

use Closure;
use Illuminate\Cache\RedisStore as IlluminateRedisStore;
use PulkitJalan\Cache\Contracts\StoreMulti;

class RedisStore extends IlluminateRedisStore implements StoreMulti
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key)) {
            return $this->getMulti($key);
        }

        return parent::get($key);
    }

    /**
     * Retrieve an array item from the cache by key.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMulti(array $keys)
    {
        return array_combine(array_values($keys), array_map([$this, 'get'], $keys));
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  int    $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        if (is_array($key) && is_array($value)) {
            $this->putMulti(array_combine($key, $value), $minutes);
        } else {
            parent::put($key, $value, $minutes);
        }
    }

    /**
     * Store an array of items in the cache for a given number of minutes.
     *
     * @param  array  $items
     * @param  int    $minutes
     * @return void
     */
    public function putMulti(array $items, $minutes)
    {
        $time = [];
        while (count($time) < count($items)) {
            $time[] = $minutes;
        }

        array_map([$this, 'put'], array_keys($items), array_values($items), $time);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        if (is_array($key)) {
            return $this->incrementMulti($key, $value);
        }

        return parent::increment($key, $value);
    }

    /**
     * Increment the value of an array of items in the cache.
     *
     * @param  array  $items
     * @param  mixed  $value
     * @return array
     */
    public function incrementMulti(array $items, $value = 1)
    {
        return $this->incrementOrDecrementMulti($items, $value, function ($key, $val) {
            return $this->increment($key, $val);
        });
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        if (is_array($key)) {
            return $this->decrementMulti($key, $value);
        }

        return parent::decrement($key, $value);
    }

    /**
     * Decrement the value of an array of items in the cache.
     *
     * @param  array  $items
     * @param  mixed  $value
     * @return array
     */
    public function decrementMulti(array $items, $value = 1)
    {
        return $this->incrementOrDecrementMulti($items, $value, function ($key, $val) {
            return $this->decrement($key, $val);
        });
    }

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return array|bool
     */
    public function foreverMulti(array $items)
    {
        return $this->putMulti($items, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function forget($key)
    {
        if (is_array($key)) {
            return $this->forgetMulti($key);
        }

        return parent::forget($key);
    }

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return bool
     */
    public function forgetMulti(array $keys)
    {
        return array_combine(array_values($keys), array_map([$this, 'forget'], $keys));
    }

    /**
     * Increment or decrement an array of items in the cache.
     *
     * @param  array  $items
     * @param  mixed  $value
     * @param  \Closure  $callback
     * @return array|bool
     */
    protected function incrementOrDecrementMulti(array $items, $value, Closure $callback)
    {
        return array_combine(array_values($items), array_map(function ($key, $val) use ($value, $callback) {
            if (is_int($key)) {
                return $callback($val, $value);
            }

            if (is_int($val)) {
                return $callback($key, $val);
            }

            return $callback($key, $value);
        }, array_keys($items), array_values($items)));
    }
}
