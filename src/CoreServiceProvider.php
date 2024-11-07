<?php

namespace Svr\Core;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Svr\Core\Middleware\ApiValidationErrors;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(SvrCore $extension): void
    {
        // Регистрируем routs
        $this->loadRoutesFrom(__DIR__ . '/../routes/Api/api.php');
        $this->register();


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

        // Регистрируем глобально миддлвар
        $this->registerMiddleware(ApiValidationErrors::class);

        CoreManager::boot();
    }

    /**
     * Регистрация Middleware
     *
     * @param string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];
        $kernel->appendMiddlewareToGroup('api', $middleware); // доббавить мидлвар в группу api
    }
}
