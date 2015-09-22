<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\MemcachedStore as IlluminateMemcachedStore;
use PulkitJalan\Cache\Contracts\StoreMany;

class MemcachedStore extends IlluminateMemcachedStore implements StoreMany
{
    /**
     * Retrieve an array item from the cache by key.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMany(array $keys)
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
    public function putMany(array $items, $minutes)
    {
        $this->memcached->setMulti(array_combine($this->prefixKeys(array_keys($items)), array_values($items)), $minutes * 60);
    }

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return void
     */
    public function foreverMany(array $items)
    {
        $this->putMany($items, 0);
    }

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return array
     */
    public function forgetMany(array $keys)
    {
        if (!method_exists($this->memcached, 'deleteMulti')) {
            return array_combine($keys, array_map([$this, 'forget'], $keys));
        }

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
            return $this->getPrefix().$key;
        }, $keys);
    }
}
