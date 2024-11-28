<?php

use Illuminate\Support\Facades\Route;
use Svr\Core\Controllers\Api\ApiUsersController;

/*
|--------------------------------------------------------------------------
| Laravel Roles API CORE Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix(config('svr.api_prefix'))->group(function () {
    // авторизация
    Route::post('auth/login', [ApiUsersController::class, 'authLogin']);
    // информация о текущем пользователе по ключу авторизации
    Route::get('auth/info', [ApiUsersController::class, 'authInfo'])->middleware(['auth:svr_api', 'api']);
    // установить (выбрать) привязку к компании, региону, району
    Route::get('auth/set', [ApiUsersController::class, 'authSet'])->middleware(['auth:svr_api', 'api']);
    // редактирование реквизитов для подключения к хорриоту
    Route::get('auth/herriot_requisites', [ApiUsersController::class, 'authHerriotRequisites'])->middleware(
        ['auth:svr_api', 'api']
    );
    // выход из сеанса
    Route::get('auth/logout', [ApiUsersController::class, 'authLogout'])->middleware(['auth:svr_api', 'api']);
});
