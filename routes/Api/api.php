<?php

use Illuminate\Support\Facades\Route;
use Svr\Core\Controllers\Api\ApiAuthController;
use Svr\Core\Controllers\Api\ApiNotificationsController;
use Svr\Core\Controllers\Api\ApiUsersController;

/*
|--------------------------------------------------------------------------
| Laravel Roles API CORE Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix(config('svr.api_prefix'))->group(function () {
    // авторизация
    Route::post('auth/login', [ApiAuthController::class, 'authLogin']);
    // информация о текущем пользователе по ключу авторизации
    Route::get('auth/info', [ApiAuthController::class, 'authInfo'])->middleware(['auth:svr_api', 'api']);
    // установить (выбрать) привязку к компании, региону, району
    Route::get('auth/set', [ApiAuthController::class, 'authSet'])->middleware(['auth:svr_api', 'api']);
    // редактирование реквизитов для подключения к хорриоту
    Route::get('auth/herriot_requisites', [ApiAuthController::class, 'authHerriotRequisites'])->middleware(
        ['auth:svr_api', 'api']
    );
    // выход из сеанса
    Route::get('auth/logout', [ApiAuthController::class, 'authLogout'])->middleware(['auth:svr_api', 'api']);
});
Route::prefix(config('svr.api_prefix'))->group(function () {
    // получить информацию о пользователе
    Route::get('users/data/{user_id}', [ApiUsersController::class, 'usersData'])->middleware(['auth:svr_api', 'api']);
    // редактирование пользователя
    Route::post('users/edit', [ApiUsersController::class, 'usersEdit'])->middleware(['auth:svr_api', 'api']);
    // изменение пароля пользователя
    Route::post('users/password_change', [ApiUsersController::class, 'userPasswordChange'])->middleware(['auth:svr_api', 'api']);
    // изменение аватара у пользователя
    Route::post('users/avatar_add', [ApiUsersController::class, 'userAvatarAdd'])->middleware(['auth:svr_api', 'api']);
    // удаление аватара у пользователя
    Route::post('users/avatar_delete', [ApiUsersController::class, 'userAvatarDelete'])->middleware(['auth:svr_api', 'api']);
    // добавить реквизиты Хорриота пользователю
    Route::post('users/herriot_req_add', [ApiUsersController::class, 'userHerriotReqAdd'])->middleware(['auth:svr_api', 'api']);
});
Route::prefix(config('svr.api_prefix'))->group(function () {
    // получить информацию о пользователе
    Route::get('notifications/data/{notifications_id}', [ApiNotificationsController::class, 'notificationsData'])->middleware(['auth:svr_api', 'api']);
});


