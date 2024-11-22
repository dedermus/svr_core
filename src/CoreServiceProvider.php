<?php

namespace Svr\Core;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Svr\Core\Middleware\ApiValidationErrors;
use Svr\Core\Middleware\CheckUserPermissions;
use Svr\Core\Exceptions\ExceptionHandler;
use Svr\Core\Models\SystemUsers;

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
        $this->registerMiddleware(ApiValidationErrors::class, 'api');
        $this->registerMiddleware(CheckUserPermissions::class, 'api');

        // Регистрируем глобального  обработчик исключений
         $this->withExceptions(new ExceptionHandler());

        CoreManager::boot();
    }

    /**
     * Регистрация Middleware
     *
     * @param string $middleware
     */
    protected function registerMiddleware($middleware, $group_name = 'api')
    {
        $kernel = $this->app[Kernel::class];
        $kernel->appendMiddlewareToGroup($group_name, $middleware); // добавить мидлвар в группу
    }

    /**
     * Регистрация обработчика исключений приложения.
     *
     * @param callable|null $using
     * @return $this
     *
     */
    protected function withExceptions(?callable $using = null)
    {
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Illuminate\Foundation\Exceptions\Handler::class
        );

        $using ??= fn () => true;

        $this->app->afterResolving(
            \Illuminate\Foundation\Exceptions\Handler::class,
            fn ($handler) => $using(new \Illuminate\Foundation\Configuration\Exceptions($handler)),
        );

        return $this;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /** Добавим защитника для API роутеров */
        config(
            [
                'auth.guards.svr_api' => [
                    'driver' => 'sanctum',
                    'provider' => 'svr_users',
                    'hash' => false,
                ],
            ]);

        /** Добавим провайдера для API роутеров */
        config(
            [
                'auth.providers.svr_users' => [
                    'driver' => 'eloquent',
                    'model' => SystemUsers::class,
                    'user_password' => 'user_password',
                    'user_email' => 'user_email',
                ]
            ]);

        /** Добавим в конфиг файл config/app.php ключ 'api_prefix' равный значению ключа API_PREFIX из окружения (.env)
         * @example Получить значение: config('svr.api_prefix') config('svr.api_prefix')
         */
        $this->mergeConfigFrom(__DIR__ . '/../config/app.php', 'svr');
    }
}
