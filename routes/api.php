<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'users'], function () {

    Route::post('/register', [UserController::class, 'register']);
    Route::post('/verifyNumber', [UserController::class, 'verifyNumber']);
    Route::post('/login', [UserController::class, 'login']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/currentUser', [UserController::class, 'current']);
        Route::put('/editUser', [UserController::class, 'edit']);
        Route::put('/refreshToken', [UserController::class, 'refreshToken']);
    });
});
