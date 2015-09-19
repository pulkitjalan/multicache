<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\MemcachedStore as IlluminateMemcachedStore;

class MemcachedStore extends IlluminateMemcachedStore
{
    /**
     * Retrieve an array item from the cache by key.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMulti(array $keys)
    {
        $preserve = defined(get_class($this->memcached).'::GET_PRESERVE_ORDER') ? constant(get_class($this->memcached).'::GET_PRESERVE_ORDER') : null;
        $tokens = null;

        $values = $this->memcached->getMulti($this->prefixKeys($keys), $tokens, $preserve);

        if ($this->memcached->getResultCode() === 0) {
            return $values;
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
        $this->memcached->setMulti(array_combine($this->prefixKeys(array_keys($items)), array_values($items)), $minutes * 60);
    }

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return void
     */
    public function foreverMulti(array $items)
    {
        $this->putMulti($items, 0);
    }

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return bool
     */
    public function forgetMulti(array $keys)
    {
        $result = $this->memcached->deleteMulti($this->prefixKeys($keys));

        return array_fill_keys($keys, $result);
    }

    /**
     * Prefix and array of keys with the cache prefix.
     *
     * @param array $keys
     * @return array
     */
    protected function prefixKeys(array $keys)
    {
        return array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys);
    }
}
