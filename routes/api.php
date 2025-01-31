<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\OrderController;
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
    Route::post('/generateVerificationCode', 'generateVerificationCode');
    Route::put('/setPassword', 'setPassword');
    Route::put('/refreshToken', 'refreshToken');

    Route::middleware('auth:user-api')->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/uploadImage', 'uploadImage');
        Route::put('/resetPassword', 'resetPassword');
        Route::put('/editUser', 'edit');
        Route::get('/currentUser', 'current');
        Route::get('/getImage', 'getImage');
        Route::get('/checkToken', 'checkToken');
        Route::delete('/deleteImage', 'deleteImage');
    });
});

Route::middleware('auth:user-api')->group(function () {

    Route::controller(ProductController::class)->prefix('products')->group(function () {
        Route::post('/toggleFavorite/{product}/', 'toggleFavorite');
        Route::get('/getProducts', 'getProducts');
        Route::get('/getFavoriteProducts', 'getFavoriteProducts');
        Route::get('/getProductsByCategory/{category}', 'getProductsByCategory');
        Route::get('/getProduct/{product}', 'getProduct');
        Route::get('/getProductsByName/{product_name}', 'getProductsByName');
        Route::get('/getImage/{product}', 'getImage');
        Route::get('/getTopProducts', 'getTopProducts');
        Route::get('/getTopProducts/{market}', 'getTopProductsForMarket');
    });

    Route::controller(MarketController::class)->prefix('markets')->group(function () {
        Route::get('/getMarkets', 'getMarkets');
        Route::get('/getMarketsByName/{market_name}', 'getMarketsByName');
        Route::get('/getProductsForMarket/{market}', 'getProductsForMarket');
    });

    Route::controller(LocationController::class)->prefix('locations')->group(function () {
        Route::post('/addLocation', 'addLocation');
        Route::get('/getLocations', 'getLocations');
        Route::get('/getLocation/{location}', 'getLocation');
        Route::get('/cost/{location}', 'getCost');
        Route::delete('/deleteLocation/{location_id}', 'deleteLocation');
    });

    Route::controller(CartController::class)->prefix('carts')->group(function () {
        Route::post('/addProduct/{product}', 'addProduct');
        Route::put('/plusProductOne/{product}', 'plusProductOne');
        Route::put('/minusProductOne/{product}', 'minusProductOne');
        Route::delete('/deleteProduct/{product}', 'deleteProduct');
        Route::delete('/clearCart', 'clearCart');
        Route::get('/getCart', 'getCart');
    });

    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::post('/createOrder', 'createOrder');
        Route::put('/editOrder/{order}', 'editOrder');
        Route::delete('/deleteProduct/{order}/{product}', 'deleteProduct');
        Route::put('/cancelOrder/{order}', 'cancelOrder');
        Route::get('/getOrders', 'getOrders');
        Route::get('/getOrdersByStatus/{status}', 'getOrdersByStatus');
        Route::get('/getOrder/{order}', 'getOrder');
    });
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/getAll', 'getAll');
    Route::get('/get/{category}', 'getCategoryById');
});

Route::controller(StatusController::class)->prefix('statuses')->group(function () {
    Route::get('/getAll', 'getAll');
    Route::get('/get/{status}', 'getStatusById');
});

Route::controller(ManagerController::class)->prefix('managers')->group(function () {

    Route::post('/login', 'login');
    Route::put('/refreshToken', 'refreshToken');

    Route::middleware('auth:manager-api')->group(function () {
        Route::post('/logout', 'logout');
        Route::put('/resetPassword', 'resetPassword');
        Route::get('/currentManager', 'current');
        Route::get('/checkToken', 'checkToken');
    });
});

Route::middleware('auth:manager-api')->group(function () {

    Route::controller(AdminController::class)->prefix('admins')->middleware('role:admin')->group(function () {
        Route::post('/addMarket', 'addMarket');
        Route::post('/addAdmin', 'addAdmin');
        Route::put('/editMarket/{manager}', 'editMarket');
        Route::delete('/deleteMarket/{manager}', 'deleteMarket');
        Route::delete('/delete/{product}', 'deleteProduct');
        Route::get('/getMarkets', 'getMarkets');
        Route::get('/getMarketsByName', 'getMarketsByName');
        Route::get('/getProducts', 'getProducts');
        Route::get('/getProducts/{market}', 'getProductsForMarket');
        Route::get('/getProductsByName', 'getProductsByName');
        Route::get('/getTopProducts', 'getTopProducts');
        Route::get('/getTopProducts/{market}', 'getTopProductsForMarket');
        Route::put('/completeOrder/{order}', 'completeOrder');
        Route::put('/rejectOrder/{order}', 'rejectOrder');
        Route::put('/deliverOrder/{order}', 'deliverOrder');
        Route::get('/getOrders', 'getOrders');
        Route::get('/getMarketOrders/{order}', 'getMarketOrders');
        Route::get('/getOrders/{market}', 'getOrdersOfMarket');
        Route::get('/statistics', 'getStatistics');
        Route::get('/admins', 'getAdmins');
    });

    Route::controller(SellerController::class)->prefix('sellers')->middleware('role:seller')->group(function () {
        Route::post('/addProduct', 'addProduct');
        Route::post('/uploadImage', 'uploadImageForMarket');
        Route::post('/uploadImage/{product}', 'uploadImageForProduct');
        Route::put('/edit/{product}', 'editProduct');
        Route::delete('/delete/{product}', 'deleteProduct');
        Route::get('/getProducts', 'getProductsForSeller');
        Route::get('/getTopProducts', 'getTopProductsForSeller');
        Route::get('/getImage', 'getImageForMarket');
        Route::get('/getImage/{product}', 'getImageForProduct');
        Route::delete('/deleteImage', 'deleteImageForMarket');
        Route::delete('/deleteImage/{product}', 'deleteImageForProduct');
        Route::put('/completeOrder/{order}', 'completeOrder');
        Route::put('/rejectOrder/{order}', 'rejectOrder');
        Route::get('/getOrders', 'getOrders');
        Route::get('/getOrdersByStatus/{status}', 'getOrdersByStatus');
        Route::get('/getOrder/{order}', 'getOrder');
        Route::get('/statistics', 'getStatistics');
    });
});