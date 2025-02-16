<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EndpointController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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

    });


});
