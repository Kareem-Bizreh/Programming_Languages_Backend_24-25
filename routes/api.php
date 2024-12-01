<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\StatusController;
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

    Route::middleware('auth:user-api')->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/uploadImage', 'uploadImage');
        Route::put('/resetPassword', 'resetPassword');
        Route::put('/editUser', 'edit');
        Route::get('/currentUser', 'current');
        Route::get('/getImage', 'getImage');
        Route::delete('/deleteImage', 'deleteImage');
    });
});

Route::controller(LocationController::class)->prefix('locations')->middleware('auth:user-api')->group(function () {
    Route::post('/addLocation', 'addLocation');
    Route::get('/getLocations', 'getLocations');
    Route::delete('/deleteLocation/{location_id}', 'deleteLocation');
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/getAll', 'getAll');
});

Route::controller(StatusController::class)->prefix('statuses')->group(function () {
    Route::get('/getAll', 'getAll');
});

Route::controller(ProductController::class)->prefix('products')->middleware('auth:user-api')->group(function () {
    //
});

Route::controller(ManagerController::class)->prefix('managers')->group(function () {

    Route::post('/login', 'login');
    Route::put('/refreshToken', 'refreshToken');

    Route::middleware('auth:manager-api')->group(function () {
        Route::post('/logout', 'logout');
        Route::put('/resetPassword', 'resetPassword');
    });
});

Route::middleware('auth:manager-api')->group(function () {

    Route::controller(AdminController::class)->prefix('admins')->middleware('role:admin')->group(function () {
        Route::post('/addMarket', 'addMarket');
        Route::post('/addAdmin', 'addAdmin');
        Route::put('/editMarket/{manager}', 'editMarket');
        Route::delete('/deleteMarket/{manager}', 'deleteMarket');
        Route::get('/getMarkets', 'getMarkets');
        Route::get('/getProducts/{market}', 'getProductsForMarket');
        Route::get('/getTopProducts', 'getTopProducts');
        Route::get('/getTopProducts/{market}', 'getTopProductsForMarket');
    });

    Route::controller(SellerController::class)->prefix('sellers')->middleware('role:seller')->group(function () {
        Route::post('/addProduct', 'addProduct');
        Route::put('/editProduct', 'editProduct');
        Route::delete('/deleteProduct', 'deleteProduct');
        Route::get('/getProducts', 'getProductsForSeller');
        Route::get('/getTopProducts', 'getTopProductsForSeller');
    });
});
