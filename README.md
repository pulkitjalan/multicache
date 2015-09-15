# laravel-multicache
(WIP) Adds array caching

New Methods:

```php
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
  ```
  
Most of the existing methods like `has`, `get`, `put`... will also accept an array and automatically run the relevent function.

The `put` method works a little differently to `putMulti`. Where `putMulti` accepts a key value array as the first paramater and the number of minutes to store for as the second paramater, the `put` method takes 2 seperate arrays and minutes as the paramaters.

Eg:

```php
$data = [
  'key1' => 'value1',
  'key2' => 'value2',
  'key3' => 'value3',
];

Cache::putMulti($data, 10);

// or

Cache::put(array_keys($data), array_values($data), 10);

```
