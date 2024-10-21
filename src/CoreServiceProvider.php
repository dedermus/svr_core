<?php

namespace Svr\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot($extension): void
    {
        // зарегистрировать переводы
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'svr-core-lang');
        //зарегистрировать шаблоны пакета
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'svr-core');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/seeders');

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'svr-core');
        }

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        CoreManager::boot();
    }
}
