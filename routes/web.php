<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\ImageStockManagementController;

// Main Page Route
// Route::get('/', [HomePage::class, 'index'])->name('pages-home');
// Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// // locale
// Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
// Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// // authentication
// Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
// Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

Route::middleware('guest')->group(function () {
    Route::redirect('/', '/login');
    Route::get('/login', [LoginController::class,'index'])->name('login');
    Route::post('/login', [LoginController::class,'login']);
    Route::get('/register', [RegisterController::class,'index'])->name('register');
    Route::post('/register/check', [RegisterController::class,'register'])->name('register.check');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomePage::class, 'index'])->name('pages-home');
    Route::get('/stock-image-management', [ImageStockManagementController::class, 'index'])->name('stock-image-management');
    Route::post('/get-image-management', [ImageStockManagementController::class, 'GetImages']);
    Route::post('/image-management/store', [ImageStockManagementController::class, 'imagesStore']);
    Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
    Route::get('/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
    Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
    Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

    Route::post('logout', [LoginController::class, 'logout'])
    ->name('logout');

    Route::get('/get/user', [RegisterController::class,'GetAllUser']);
});