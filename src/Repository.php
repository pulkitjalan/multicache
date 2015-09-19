<?php

namespace PulkitJalan\Cache;

use Closure;
use Illuminate\Cache\Repository as IlluminateRepository;
use PulkitJalan\Cache\Contracts\Repository as CacheMultiContract;

class Repository extends IlluminateRepository implements CacheMultiContract
{
    /**
     * Determine if an item exists in the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        if (is_array($key)) {
            return $this->hasMulti($key);
        }

        return parent::has($key);
    }

    /**
     * Determine if an array of items exists in the cache.
     *
     * @param  array  $keys
     * @return array
     */
    public function hasMulti(array $keys)
    {
        $values = $this->getMulti($keys);

        return array_map(function ($value) {
            return ! is_null($value);
        }, $values);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMulti($key, $default);
        }

        return parent::get($key, $default);
    }

    /**
     * Retrieve an array of items from the cache by keys.
     *
     * @param  array  $keys
     * @param  mixed  $default
     * @return array
     */
    public function getMulti(array $keys, $default = null)
    {
        $keys = array_fill_keys($keys, $default);

        if (!method_exists($this->store, 'getMulti')) {
            return array_combine(array_keys($keys), array_map([$this, 'get'], array_keys($keys), array_values($keys)));
        }

        $values = array_combine(array_keys($keys), $this->store->getMulti(array_keys($keys)));

        foreach ($values as $key => &$value) {
            if (is_null($value)) {
                $this->fireCacheEvent('missed', [$key]);

                $value = value(array_get($keys, $key, $default));
            } else {
                $this->fireCacheEvent('hit', [$key, $value]);
            }
        }

        return $values;
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        if (is_array($key)) {
            return $this->pullMulti($key, $default);
        }

        return parent::pull($key, $default);
    }

    /**
     * Retrieve an array of items from the cache and delete them.
     *
     * @param  array  $keys
     * @param  mixed  $default
     * @return array
     */
    public function pullMulti(array $keys, $default = null)
    {
        $values = $this->getMulti($keys, $default);

        $this->forgetMulti($keys);

        return $values;
    }

    /**
     * Store an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTime|int  $minutes
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
     * Store an array of items in the cache.
     *
     * @param  array  $items
     * @param  \DateTime|int  $minutes
     * @return void
     */
    public function putMulti(array $items, $minutes)
    {
        if (!method_exists($this->store, 'putMulti')) {
            array_map([$this, 'put'], array_keys($items), array_values($items), array_fill(0, count($items), $minutes));
        } else {
            $minutes = $this->getMinutes($minutes);

            if (! is_null($minutes)) {
                $this->store->putMulti($items, $minutes);

                foreach ($items as $key => $value) {
                    $this->fireCacheEvent('write', [$key, $value, $minutes]);
                }
            }
        }
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTime|int  $minutes
     * @return bool
     */
    public function add($key, $value, $minutes)
    {
        if (is_array($key) && is_array($value)) {
            return $this->addMulti(array_combine($key, $value), $minutes);
        }

        return parent::add($key, $value, $minutes);
    }

    /**
     * Store an array of items in the cache if the key does not exist.
     *
     * @param  array  $items
     * @param  \DateTime|int  $minutes
     * @return array
     */
    public function addMulti(array $items, $minutes)
    {
        $values = $this->hasMulti(array_keys($items));

        $fill = array_where($values, function ($key, $value) {
            return !$value;
        });

        $fill = array_intersect_key($items, $fill);

        $this->putMulti($fill, $minutes);

        return array_map(function ($value) {
            return !$value;
        }, $values);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        if (is_array($key) && is_array($value)) {
            $this->foreverMulti(array_combine($key, $value));
        } else {
            parent::forever($key, $value);
        }
    }

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return void
     */
    public function foreverMulti(array $items)
    {
        if (!method_exists($this->store, 'foreverMulti')) {
            array_map([$this, 'forever'], array_keys($items), array_values($items));
        } else {
            $this->store->foreverMulti($items);

            foreach ($items as $key => $value) {
                $this->fireCacheEvent('write', [$key, $value, 0]);
            }
        }
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  string  $key
     * @param  \DateTime|int  $minutes
     * @param  \Closure  $callback
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback)
    {
        if (is_array($key)) {
            return $this->rememberMulti($key, $minutes, $callback);
        }

        return parent::remember($key, $minutes, $callback);
    }

    /**
     * Get an array of items from the cache, or store the default value.
     *
     * @param  array  $keys
     * @param  \DateTime|int  $minutes
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rememberMulti(array $keys, $minutes, Closure $callback)
    {
        $values = $this->getMulti($keys);

        $items = array_where($values, function ($key, $value) {
            return is_null($value);
        });

        $items = array_map($callback, $items);

        $this->putMulti($items, $minutes);

        return array_replace($values, $items);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string   $key
     * @param  \Closure  $callback
     * @return mixed
     */
    public function sear($key, Closure $callback)
    {
        if (is_array($key)) {
            return $this->searMulti($key, $callback);
        }

        return parent::sear($key, $callback);
    }

    /**
     * Get an array of items from the cache, or store the default value forever.
     *
     * @param  array  $keys
     * @param  \Closure  $callback
     * @return mixed
     */
    public function searMulti(array $keys, Closure $callback)
    {
        return $this->rememberForeverMulti($keys, $callback);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string   $key
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rememberForever($key, Closure $callback)
    {
        if (is_array($key)) {
            return $this->rememberForeverMulti($key, $callback);
        }

        return parent::rememberForever($key, $callback);
    }

    /**
     * Get an array of items from the cache, or store the default value forever.
     *
     * @param  array  $keys
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rememberForeverMulti(array $keys, Closure $callback)
    {
        $values = $this->getMulti($keys);

        $items = array_where($values, function ($key, $value) {
            return is_null($value);
        });

        $items = array_map($callback, $items);

        $this->foreverMulti($items);

        return array_replace($values, $items);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
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
        if (!method_exists($this->store, 'forgetMulti')) {
            return array_combine($keys, array_map([$this, 'forget'], $keys));
        }

        $success = $this->store->forgetMulti($keys);

        foreach ($success as $key => $value) {
            $this->fireCacheEvent('delete', [$key]);
        }

        return $success;
    }
}
