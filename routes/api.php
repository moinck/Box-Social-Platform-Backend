<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BrnadKitApiController;
use App\Http\Controllers\Api\CategoriesApiController;
use App\Http\Controllers\Api\PostContentApiController;
use App\Http\Controllers\Api\ProfileManagementApiController;
use App\Http\Controllers\Api\StockImageApiController;
use App\Http\Controllers\Api\TemplateApiController;
use App\Http\Controllers\Api\UserTemplatesApiController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [RegisterController::class, 'login']);
Route::post('/fca-check', [RegisterController::class, 'checkFca']);

// Email verification routes
Route::post('/email/verify', [AuthApiController::class, 'verify'])
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/resend-verification', [AuthApiController::class, 'resend'])
    ->middleware(['throttle:6,1']);

// forget password
Route::post('/forget-password', [AuthApiController::class, 'forgetPassword'])->middleware(['throttle:5,2']);
// reset password
Route::post('/reset-password', [AuthApiController::class, 'resetPassword'])->middleware(['throttle:5,2']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Auth Routes
Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::group([], function () {
        Route::get('/get/user', [RegisterController::class, 'GetAllUser']);

        // category list
        Route::get('/category/list', [CategoriesApiController::class, 'list']);

        // brandkit api
        Route::get('/brandkit/get', [BrnadKitApiController::class, 'get']);
        Route::post('/brandkit/store', [BrnadKitApiController::class, 'store']);

        // design styles
        Route::get('/design/styles', [BrnadKitApiController::class, 'getDesignStyles']);

        // profile management api
        Route::get('/profile-management/get', [ProfileManagementApiController::class, 'index']);
        Route::post('/profile-management/update', [ProfileManagementApiController::class, 'update']);
        Route::post('/profile-management/profile/update', [ProfileManagementApiController::class, 'profileUpdate']);
        Route::post('/profile-management/password/update', [ProfileManagementApiController::class, 'passwordUpdate']);

        // post content api
        // Route::get('/post-content/get/all', [PostContentApiController::class, 'index']);
        // Route::get('/post-content/get/{id}', [PostContentApiController::class, 'show']);
        Route::post('/post-content/get/data', [PostContentApiController::class, 'getData']);
        Route::post('/post-content/get/category', [PostContentApiController::class, 'getCategoryPostContent']);

        // Admin Create Template 
        Route::post('/template/store', [TemplateApiController::class, 'store']);
        Route::get('/template/get/{id}', [TemplateApiController::class, 'getTemplate']);
        Route::post('/template/list', [TemplateApiController::class, 'getTemplateList']);

        // User templates APIS
        Route::get('/user-template/list', [UserTemplatesApiController::class, 'list']);
        Route::get('/user-template/get/{id}', [UserTemplatesApiController::class, 'get']);
        Route::post('/user-template/store', [UserTemplatesApiController::class, 'store']);
        Route::post('/user-template/delete', [UserTemplatesApiController::class, 'delete']);
        
        
        // get stock image
        Route::get('/stock-image/get', [StockImageApiController::class, 'get']);

        // user images routes
        Route::post('/user-image/store', [StockImageApiController::class, 'store']);
        Route::post('/user-image/delete', [StockImageApiController::class, 'delete']);

        // logout api
        Route::post('/logout', [RegisterController::class, 'logout']);
    });
});

Route::post('/store/contact-us', [ContactUsController::class, 'store']);


