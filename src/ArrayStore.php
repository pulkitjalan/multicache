<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\ArrayStore as IlluminateArrayStore;

class ArrayStore extends IlluminateArrayStore
{
    /**
     * Store an array of items in the cache for a given number of minutes.
     *
     * @param  array  $items
     * @param  int    $minutes
     * @return void
     */
    public function putMulti(array $items, $minutes)
    {
        $this->storage = array_merge($this->storage, $items);
    }
}
