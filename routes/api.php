<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('users')->controller(UserController::class)->group(function () {

    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/verifyNumber', 'verifyNumber');
    Route::post('/forgetPassword', 'forgetPassword');
    Route::post('/verifyNewPassword', 'verifyNewPassword');
    Route::put('/setPassword', 'setPassword');
    Route::put('/refreshToken', 'refreshToken');

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', 'logout');
        Route::put('/resetPassword', 'resetPassword');
        Route::get('/currentUser', 'current');
        Route::put('/editUser', 'edit');
    });
});
