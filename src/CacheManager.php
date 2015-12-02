<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\CacheManager as IlluminateCacheManager;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

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
     * Create an instance of the Redis cache driver.
     *
     * @param  array  $config
     * @return \Illuminate\Cache\RedisStore
     */
    protected function createRedisDriver(array $config)
    {
        $redis = $this->app['redis'];
        $connection = Arr::get($config, 'connection', 'default') ?: 'default';

        return $this->repository(new RedisStore($redis, $this->getPrefix($config), $connection));
    }

    /**
     * Create an instance of the database cache driver.
     *
     * @param  array  $config
     * @return \PulkitJalan\Cache\DatabaseStore
     */
    protected function createDatabaseDriver(array $config)
    {
        $connection = $this->app['db']->connection(Arr::get($config, 'connection'));

        return $this->repository(
            new DatabaseStore(
                $connection,
                $this->app['encrypter'],
                $config['table'],
                $this->getPrefix($config)
            )
        );
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

        if ($this->app->bound(Dispatcher::class)) {
            $repository->setEventDispatcher(
                $this->app[Dispatcher::class]
            );
        }

        return $repository;
    }
}
