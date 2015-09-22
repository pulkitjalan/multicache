<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\ArrayStore as IlluminateArrayStore;
use PulkitJalan\Cache\Contracts\StoreMany;

class ArrayStore extends IlluminateArrayStore implements StoreMany
{
    /**
     * Retrieve an array item from the cache by key.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMany(array $keys)
    {
        return array_merge(array_fill_keys($keys, null), array_only($this->storage, $this->prefixKeys($keys)));
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
        $items = array_combine($this->prefixKeys(array_keys($items)), array_values($items));

        $this->storage = array_merge($this->storage, $items);
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
        array_forget($this->storage, $this->prefixKeys($keys));

        return array_fill_keys($keys, true);
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
