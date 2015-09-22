<?php

namespace PulkitJalan\Cache\Providers;

use Illuminate\Cache\CacheServiceProvider;
use PulkitJalan\Cache\CacheManager;

class LaravelServiceProvider extends CacheServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        // replace cache manager
        $this->app->singleton('cache', function ($app) {
            return new CacheManager($app);
        });
    }
}
