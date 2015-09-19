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
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return void
     */
    public function foreverMulti(array $items);

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return array
     */
    public function forgetMulti(array $keys);
}
