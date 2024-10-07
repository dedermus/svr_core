<?php

namespace Svr\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(SvrCore $extension)
    {
        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'svr-core');
        }
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'svr-core');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            $this->loadMigrationsFrom(__DIR__.'/../database/seeders');
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'svr-core-lang');
        }

        CoreManager::boot();
    }

}
