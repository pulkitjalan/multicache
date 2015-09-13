<?php

namespace PulkitJalan\Cache\Contracts;

use Closure;
use Illuminate\Contracts\Cache\Repository as IlluminateRepository;

interface Repository extends IlluminateRepository
{
    /**
     * Determine if an array of items exists in the cache.
     *
     * @param  array  $keys
     * @return array
     */
    public function hasMulti(array $keys);

    /**
     * Retrieve an array of items from the cache by keys.
     *
     * @param  array  $keys
     * @param  mixed  $default
     * @return array
     */
    public function getMulti(array $keys, $default = null);

    /**
     * Retrieve an array of items from the cache and delete them.
     *
     * @param  array  $keys
     * @param  mixed  $default
     * @return array
     */
    public function pullMulti(array $keys, $default = null);

    /**
     * Store an array of items in the cache.
     *
     * @param  array  $items
     * @param  \DateTime|int  $minutes
     * @return void
     */
    public function putMulti(array $items, $minutes);

    /**
     * Store an array of items in the cache if the key does not exist.
     *
     * @param  array  $items
     * @param  \DateTime|int  $minutes
     * @return bool
     */
    public function addMulti(array $items, $minutes);

    /**
     * Store an array of items in the cache indefinitely.
     *
     * @param  array  $items
     * @return void
     */
    public function foreverMulti(array $items);

    /**
     * Get an array of items from the cache, or store the default value.
     *
     * @param  array  $keys
     * @param  \DateTime|int  $minutes
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rememberMulti(array $keys, $minutes, Closure $callback);

    /**
     * Get an array of items from the cache, or store the default value forever.
     *
     * @param  array  $keys
     * @param  \Closure  $callback
     * @return mixed
     */
    public function searMulti(array $keys, Closure $callback);

    /**
     * Get an array of items from the cache, or store the default value forever.
     *
     * @param  array  $keys
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rememberForeverMulti(array $keys, Closure $callback);

    /**
     * Remove an array of items from the cache.
     *
     * @param  array  $keys
     * @return bool
     */
    public function forgetMulti(array $keys);
}
