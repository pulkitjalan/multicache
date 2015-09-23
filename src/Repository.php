<?php

namespace PulkitJalan\Cache;

use Closure;
use Illuminate\Cache\Repository as IlluminateRepository;
use Illuminate\Cache\TagSet;
use PulkitJalan\Cache\Contracts\Repository as CacheManyContract;

class Repository extends IlluminateRepository implements CacheManyContract
{
    /**
     * Determine if an item exists in the cache.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function has($key)
    {
        if (is_array($key)) {
            return $this->hasMany($key);
        }

        return parent::has($key);
    }

    /**
     * Determine if an array of items exists in the cache.
     *
     * @param  array  $keys
     * @return array
     */
    public function hasMany(array $keys)
    {
        $values = $this->getMany($keys);

        return array_map(function ($value) {
            return !is_null($value);
        }, $values);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key, $default);
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
    public function getMany(array $keys, $default = null)
    {
        $keys = array_fill_keys($keys, $default);

        if (!method_exists($this->store, 'getMany')) {
            return array_combine(array_keys($keys), array_map([$this, 'get'], array_keys($keys), array_values($keys)));
        }

        $values = array_combine(array_keys($keys), $this->store->getMany(array_keys($keys)));

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
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        if (is_array($key)) {
            return $this->pullMany($key, $default);
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
    public function pullMany(array $keys, $default = null)
    {
        $values = $this->getMany($keys, $default);

        $this->forgetMany($keys);

        return $values;
    }

    /**
     * Store an item in the cache.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  \DateTime|int  $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        if (is_array($key) && is_array($value)) {
            $this->putMany(array_combine($key, $value), $minutes);
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
    public function putMany(array $items, $minutes)
    {
        if (!method_exists($this->store, 'putMany')) {
            array_map([$this, 'put'], array_keys($items), array_values($items), array_fill(0, count($items), $minutes));
        } else {
            $minutes = $this->getMinutes($minutes);

            if (!is_null($minutes)) {
                $this->store->putMany($items, $minutes);

                foreach ($items as $key => $value) {
                    $this->fireCacheEvent('write', [$key, $value, $minutes]);
                }
            }
        }
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  \DateTime|int  $minutes
     * @return mixed
     */
    public function add($key, $value, $minutes)
    {
        if (is_array($key) && is_array($value)) {
            return $this->addMany(array_combine($key, $value), $minutes);
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
    public function addMany(array $items, $minutes)
    {
        $values = $this->getMany(array_keys($items));

        $fill = array_where($values, function ($key, $value) {
            return is_null($value);
        });

        $items = array_intersect_key($items, $fill);

        $this->putMany($items, $minutes);

        return array_map(function ($value) {
            return is_null($value);
        }, $values);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function forever($key, $value)
    {
        if (is_array($key) && is_array($value)) {
            $this->foreverMany(array_combine($key, $value));
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
    public function foreverMany(array $items)
    {
        if (!method_exists($this->store, 'foreverMany')) {
            array_map([$this, 'forever'], array_keys($items), array_values($items));
        } else {
            $this->store->foreverMany($items);

            foreach ($items as $key => $value) {
                $this->fireCacheEvent('write', [$key, $value, 0]);
            }
        }
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  mixed  $key
     * @param  \DateTime|int  $minutes
     * @param  \Closure  $callback
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback)
    {
        if (is_array($key)) {
            return $this->rememberMany($key, $minutes, $callback);
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
    public function rememberMany(array $keys, $minutes, Closure $callback)
    {
        $values = $this->getMany($keys);

        $items = array_where($values, function ($key, $value) {
            return is_null($value);
        });

        if (!empty($items)) {
            $items = array_combine(array_keys($items), $callback(array_keys($items)));

            $this->putMany($items, $minutes);

            $values = array_replace($values, $items);
        }

        return $values;
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
            return $this->searMany($key, $callback);
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
    public function searMany(array $keys, Closure $callback)
    {
        return $this->rememberManyForever($keys, $callback);
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
            return $this->rememberManyForever($key, $callback);
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
    public function rememberManyForever(array $keys, Closure $callback)
    {
        $values = $this->getMany($keys);

        $items = array_where($values, function ($key, $value) {
            return is_null($value);
        });

        if (!empty($items)) {
            $items = array_combine(array_keys($items), $callback(array_keys($items)));

            $this->foreverMany($items);

            $values = array_replace($values, $items);
        }

        return $values;
    }

    /**
     * Remove an item from the cache.
     *
     * @param  mixed $key
     * @return bool
     */
    public function forget($key)
    {
        if (is_array($key)) {
            return $this->forgetMany($key);
        }

        return parent::forget($key);
    }

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return bool
     */
    public function forgetMany(array $keys)
    {
        if (!method_exists($this->store, 'forgetMany')) {
            return array_combine($keys, array_map([$this, 'forget'], $keys));
        }

        $success = $this->store->forgetMany($keys);

        foreach ($success as $key => $value) {
            $this->fireCacheEvent('delete', [$key]);
        }

        return $success;
    }

    /**
     * Begin executing a new tags operation.
     *
     * @param  array|mixed  $names
     * @return \PulkitJalan\Cache\TaggedCache
     */
    public function tags($names)
    {
        if (!method_exists($this->store, 'tags')) {
            throw new \BadMethodCallException('Class '.get_class($this->store).' does not have a method \'tags\'');
        }

        return new TaggedCache($this->store, new TagSet($this->store, is_array($names) ? $names : func_get_args()));
    }
}
