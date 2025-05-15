<?php

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
    });
});

Route::post('/store/contact-us', [ContactUsController::class, 'store']);

