<?php

namespace Svr\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            $this->publishes([__DIR__.'/../database/seeders' => database_path('seeders')], 'svr-core-seeders');
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'svr-core-lang');
        }

        CoreManager::boot();
    }

}
