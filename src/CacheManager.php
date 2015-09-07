<?php

namespace PulkitJalan\Cache;

use Illuminate\Cache\CacheManager as IlluminateCacheManager;
use Illuminate\Cache\ApcWrapper;
use Illuminate\Support\Arr;

class CacheManager extends IlluminateCacheManager
{
    /**
     * Create an instance of the APC cache driver.
     *
     * @param  array  $config
     * @return PulkitJalan\Cache\ApcStore
     */
    protected function createApcDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        return $this->repository(new ApcStore(new ApcWrapper, $prefix));
    }

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
     * Create an instance of the file cache driver.
     *
     * @param  array  $config
     * @return PulkitJalan\Cache\FileStore
     */
    protected function createFileDriver(array $config)
    {
        return $this->repository(new FileStore($this->app['files'], $config['path']));
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
     * Create an instance of the Null cache driver.
     *
     * @return PulkitJalan\Cache\NullStore
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullStore);
    }

    /**
     * Create an instance of the WinCache cache driver.
     *
     * @param  array  $config
     * @return PulkitJalan\Cache\WinCacheStore
     */
    protected function createWincacheDriver(array $config)
    {
        return $this->repository(new WinCacheStore($this->getPrefix($config)));
    }

    /**
     * Create an instance of the XCache cache driver.
     *
     * @param  array  $config
     * @return PulkitJalan\Cache\WinCacheStore
     */
    protected function createXcacheDriver(array $config)
    {
        return $this->repository(new XCacheStore($this->getPrefix($config)));
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param  array  $config
     * @return PulkitJalan\Cache\RedisStore
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
     * @return PulkitJalan\Cache\DatabaseStore
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
}
