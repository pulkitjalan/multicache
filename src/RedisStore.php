<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\RedisStore as IlluminateRedisStore;
use PulkitJalan\Cache\Contracts\StoreMulti;

class RedisStore extends IlluminateRedisStore implements StoreMulti
{
    /**
     * Retrieve an array item from the cache by key.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMulti(array $keys)
    {
        $values = $this->connection()->mget($this->prefixKeys($keys));

        return array_combine($keys, array_map(function ($value) {
            return is_numeric($value) ? $value : unserialize($value);
        }, $values));
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
        $minutes = max(1, $minutes);

        return $this->connection()->transaction(function ($tx) use ($items, $minutes) {
            foreach ($items as $key => $value) {
                $value = is_numeric($value) ? $value : serialize($value);
                $tx->setex($this->getPrefix().$key, $minutes * 60, $value);
            }
        });
    }

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return void
     */
    public function foreverMulti(array $items)
    {
        $items = array_combine(
            $this->prefixKeys(array_keys($items)),
            array_map(function ($value) {
                return is_numeric($value) ? $value : serialize($value);
            }, array_values($items))
        );

        $this->connection()->mset($items);
    }

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return bool
     */
    public function forgetMulti(array $keys)
    {
        $this->connection()->del($this->prefixKeys($keys));

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
