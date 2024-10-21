<?php

namespace Svr\Core;

use OpenAdminCore\Admin\Admin;
use OpenAdminCore\Admin\Auth\Database\Menu;
use OpenAdminCore\Admin\Auth\Database\Permission;
use OpenAdminCore\Admin\Extension;
use Svr\Core\Controlles\System\ModulesActionsController;
use Svr\Core\Controlles\System\ModulesController;
use Svr\Core\Controlles\System\RolesController;
use Svr\Core\Controlles\System\SettingsController;
use Svr\Core\Controlles\System\UsersController;
use Svr\Core\Controlles\System\UsersNotificationsMessagesController;
use Svr\Core\Controlles\System\UsersTokensController;

class CoreManager extends Extension
{

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('svr-core', __CLASS__);
    }

    /**
     * Register routes for open-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */

            $router->resource('core/users', UsersController::class);
            $router->resource('core/roles', RolesController::class);
            $router->resource('core/rights', ModulesActionsController::class);
            $router->resource('core/modules', ModulesController::class);
            $router->resource('core/settings', SettingsController::class);
            $router->resource('core/users_tokens', UsersTokensController::class);
            $router->resource('core/message_templates', UsersNotificationsMessagesController::class);
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        $lastOrder = Menu::max('order');

        $root = [
            'parent_id' => 0,
            'order'     => $lastOrder++,
            'title'     => 'СВР - пользователи',
            'icon'      => 'icon-user-injured',
            'uri'       => 'core',
        ];
        // Если нет пункта в меню, добавляем его
        if (!Menu::where('uri', 'core')->exists()) {
            $root = Menu::create($root);

            $menus = [
                [
                    'title'     => 'Пользователи',
                    'icon'      => 'icon-blind',
                    'uri'       => 'core/users',
                ],
                [
                    'title'     => 'Роли',
                    'icon'      => 'icon-cart-plus',
                    'uri'       => 'core/roles',
                ],
                [
                    'title'     => 'Права',
                    'icon'      => 'icon-bread-slice',
                    'uri'       => 'core/rights',
                ],
                [
                    'title'     => 'Модули',
                    'icon'      => 'icon-list',
                    'uri'       => 'core/modules',
                ],
                [
                    'title'     => 'Настройки',
                    'icon'      => 'icon-cog',
                    'uri'       => 'core/settings',
                ],
                [
                    'title'     => 'Метрика',
                    'icon'      => 'icon-address-card',
                    'uri'       => 'core/users_tokens',
                ],
                [
                    'title'     => 'Шаблоны сообщений',
                    'icon'      => 'icon-archive',
                    'uri'       => 'core/message_templates',
                ],
            ];

            foreach ($menus as $menu) {
                $menu['parent_id'] = $root->id;
                $menu['order'] = $lastOrder++;

                Menu::create($menu);
            }
        }
        // Установка разрешения на роуты по слагу если его нет
        if (!Permission::where('slug', 'svr.core')->exists()) {
            parent::createPermission('Exceptions SVR-CORE', 'svr.core', 'core/*');
        }
    }

}
