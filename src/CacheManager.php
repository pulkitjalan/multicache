<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\CacheManager as IlluminateCacheManager;

class CacheManager extends IlluminateCacheManager
{
    /**
     * Create an instance of the array cache driver.
     *
     * @return PulkitJalan\Cache\ArrayStore
     */
    protected function createArrayDriver()
    {
        return $this->repository(new ArrayStore);
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param  array  $config
     * @return PulkitJalan\Cache\MemcachedStore
     */
    protected function createMemcachedDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        $memcached = $this->app['memcached.connector']->connect($config['servers']);

        return $this->repository(new MemcachedStore($memcached, $prefix));
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Contracts\Cache\Store  $store
     * @return \PulkitJalan\Cache\Repository
     */
    public function repository(Store $store)
    {
        $repository = new Repository($store);

        if ($this->app->bound('Illuminate\Contracts\Events\Dispatcher')) {
            $repository->setEventDispatcher(
                $this->app['Illuminate\Contracts\Events\Dispatcher']
            );
        }

        return $repository;
    }
}
