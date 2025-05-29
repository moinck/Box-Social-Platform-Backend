<?php

use App\Http\Controllers\Api\BrnadKitApiController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\PostContentApiController;
use App\Http\Controllers\Api\ProfileManagementController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [RegisterController::class, 'login']);
Route::post('/fca-check', [RegisterController::class, 'checkFca']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::group([], function () {
        Route::get('/get/user', [RegisterController::class, 'GetAllUser']);

        // api for submit contact-us form
        // Route::post('store/contact-us', [ContactUsController::class, 'store']);

        // category list
        Route::get('/category/list', [CategoriesController::class, 'list']);

        // brandkit api
        Route::get('/brandkit/get', [BrnadKitApiController::class, 'get']);
        Route::post('/brandkit/store', [BrnadKitApiController::class, 'store']);

        // profile management api
        Route::get('/profile-management/get', [ProfileManagementController::class, 'index']);
        Route::post('/profile-management/update', [ProfileManagementController::class, 'update']);

        // post content api
        Route::get('/post-content/get', [PostContentApiController::class, 'index']);
        Route::get('/post-content/get/{id}', [PostContentApiController::class, 'show']);

        // logout api
        Route::post('/logout', [RegisterController::class, 'logout']);
    });
});

Route::post('/store/contact-us', [ContactUsController::class, 'store']);


