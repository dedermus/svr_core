<?php

use Illuminate\Support\Facades\Route;
use Svr\Core\Controllers\Api\ApiUsersController;

/*
|--------------------------------------------------------------------------
| Laravel Roles API CORE Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix(config('svr.api_prefix'))->group(function ()
{
    Route::get('auth/info', [ApiUsersController::class, 'authInfo'])->middleware(['auth:svr_api', 'api']);
    Route::get('auth/logout', [ApiUsersController::class, 'authLogout'])->middleware(['auth:svr_api', 'api']);
    Route::post('auth/login', [ApiUsersController::class, 'authLogin']);

});
