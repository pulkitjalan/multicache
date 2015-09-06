<?php

namespace PulkitJalan\Cache\Contracts;

use Illuminate\Contracts\Cache\Store;

interface StoreMulti extends Store
{
    /**
     * Retrieve an array item from the cache by key.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMulti(array $keys);

    /**
     * Store an array of items in the cache for a given number of minutes.
     *
     * @param  array  $items
     * @param  int    $minutes
     * @return void
     */
    public function putMulti(array $items, $minutes);

    /**
     * Increment the value of an array of items in the cache.
     *
     * @param  array  $items
     * @param  mixed  $value
     * @return array
     */
    public function incrementMulti(array $items, $value = 1);

    /**
     * Decrement the value of an array of items in the cache.
     *
     * @param  array  $items
     * @param  mixed  $value
     * @return array
     */
    public function decrementMulti(array $items, $value = 1);

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return array|bool
     */
    public function foreverMulti(array $items);

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return bool
     */
    public function forgetMulti(array $keys);
}
