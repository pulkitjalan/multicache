<?php

namespace PulkitJalan\Cache\Providers;

use Illuminate\Support\ServiceProvider;
use PukitJalan\Cache\CacheManager;

class MultiCacheServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cache', function ($app) {
            return new CacheManager($app);
        });
    }
}
