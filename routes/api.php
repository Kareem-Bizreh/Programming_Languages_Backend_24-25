<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(UserController::class)->prefix('users')->group(function () {

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

Route::controller(LocationController::class)->prefix('locations')->middleware('auth:api')->group(function () {
    Route::post('/addLocation', 'addLocation');
    Route::get('/getLocations', 'getLocations');
    Route::delete('/deleteLocation/{location_id}', 'deleteLocation');
});
