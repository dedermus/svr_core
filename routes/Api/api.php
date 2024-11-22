<?php

use Svr\Core\Middleware\ApiValidationErrors;
use Illuminate\Support\Facades\Route;
use Svr\Core\Controllers\Api\ApiModulesActionsController;
use Svr\Core\Controllers\Api\ApiRolesController;
use Svr\Core\Controllers\Api\ApiUsersTokenController;
use Svr\Core\Controllers\Api\ApiUsersNotificationsMessagesController;
use Svr\Core\Controllers\Api\ApiSettingController;
use Svr\Core\Controllers\Api\ApiModulesController;
use Svr\Core\Controllers\Api\ApiUsersController;

/*
|--------------------------------------------------------------------------
| Laravel Roles API CORE Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix(config('svr.api_prefix'))->group(function () {

    Route::get('auth/info', [ApiUsersController::class, 'show_auth_info'])->middleware(['auth:svr_api', 'api']);

    Route::post('auth/login', [ApiUsersController::class, 'authLogin']);


        Route::get('right/list/', [ApiModulesActionsController::class, 'index']);      // Для получения списка записей
        Route::post('right/create/', [ApiModulesActionsController::class, 'store']);      // Для создания новой записи
        Route::post('right/edit/', [ApiModulesActionsController::class, 'update']
        );     // Для обновления существующей записи

        Route::get('roles/list/', [ApiRolesController::class, 'index']);      // Для получения списка записей
        Route::post('roles/create/', [ApiRolesController::class, 'store']);      // Для создания новой записи
        Route::post('roles/edit/', [ApiRolesController::class, 'update']);     // Для обновления существующей записи

        Route::get('users_tokens/list/', [ApiUsersTokenController::class, 'index']
        );      // Для получения списка записей
        Route::post('users_tokens/create/', [ApiUsersTokenController::class, 'store']
        );      // Для создания новой записи
        Route::post('users_tokens/edit/', [ApiUsersTokenController::class, 'update']
        );     // Для обновления существующей записи

        Route::get('message_templates/list/', [ApiUsersNotificationsMessagesController::class, 'index']
        );      // Для получения списка записей
        Route::post('message_templates/create/', [ApiUsersNotificationsMessagesController::class, 'store']
        );      // Для создания новой записи
        Route::post('message_templates/edit/', [ApiUsersNotificationsMessagesController::class, 'update']
        );     // Для обновления существующей записи

        Route::get('settings/list/', [ApiSettingController::class, 'index']);      // Для получения списка записей
        Route::post('settings/create/', [ApiSettingController::class, 'store']);      // Для создания новой записи
        Route::post('settings/edit/', [ApiSettingController::class, 'update']);

        Route::get('modules/list/', [ApiModulesController::class, 'index']);      // Для получения списка записей
        Route::post('modules/create/', [ApiModulesController::class, 'store']);      // Для создания новой записи
        Route::post('modules/edit/', [ApiModulesController::class, 'update']);

        Route::get('modules/list/', [ApiUsersController::class, 'index']);      // Для получения списка записей
        Route::post('modules/create/', [ApiUsersController::class, 'store']);      // Для создания новой записи
        Route::post('modules/edit/', [ApiUsersController::class, 'update']);
    });
