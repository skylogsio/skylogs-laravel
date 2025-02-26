<?php

use App\Enums\Constants;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DataSourceController;
use App\Http\Controllers\Api\V1\EndpointController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::post("auth/login", [AuthController::class, "login"]);

    Route::middleware('auth')->group(function () {

        Route::prefix('auth')
            ->controller(AuthController::class)
            ->group(function () {
                Route::post('logout', 'logout');
                Route::post('refresh', 'refresh');
                Route::post('me', 'me');
            });

        Route::prefix("/user")
            ->controller(UserController::class)
            ->group(function () {
                Route::get('/', 'Index');
                Route::get('/{id}', 'Show');
                Route::post('/', 'Create');
                Route::put('/pass/{id}', 'ChangePassword');
                Route::put('/{id}', 'Update');
                Route::delete('/{id}', 'Delete');
            });

        Route::prefix("/endpoint")
            ->controller(EndpointController::class)
            ->group(function () {
                Route::get('/', 'Index');
                Route::get('/{id}', 'Show');
                Route::post('/', 'Create');
                Route::put('/{id}', 'Update');
                Route::delete('/{id}', 'Delete');
            });

        Route::prefix("/data-source")
            ->controller(DataSourceController::class)
            ->middleware("role:".Constants::ROLE_OWNER->value."|".Constants::ROLE_MANAGER->value)
            ->group(function () {
                Route::get('/', 'Index');
                Route::get('/types', 'GetTypes');
                Route::get('/{id}', 'Show');
                Route::post('/', 'Create');
                Route::put('/{id}', 'Update');
                Route::delete('/{id}', 'Delete');
            });

    });


});
