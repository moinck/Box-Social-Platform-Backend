<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\ImageStockManagementController;


Route::middleware('guest')->group(function () {
    Route::redirect('/', '/login');
    Route::get('/login', [LoginController::class,'index'])->name('login');
    Route::post('/login', [LoginController::class,'login']);
    // Route::get('/register', [RegisterController::class,'index'])->name('register');
    // Route::post('/register/check', [RegisterController::class,'register'])->name('register.check');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomePage::class, 'index'])->name('pages-home');

    // Stock image Management
    Route::get('/stock-image-management', [ImageStockManagementController::class, 'index'])->name('stock-image-management');
    Route::post('/get-image-management', [ImageStockManagementController::class, 'GetImages']);
    Route::post('/image-management/store', [ImageStockManagementController::class, 'imagesStore']);
    Route::get('/image-management/get/saved-images', [ImageStockManagementController::class, 'savedImages'])->name('image-management.get.saved-images');
    Route::post('/image-management/delete/saved-images', [ImageStockManagementController::class, 'deleteSavedImages'])->name('image-management.delete.saved-images');

    // extra pages
    Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
    Route::get('/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
    Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
    Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

    // User Management Controller
    Route::get('/user/datatable', [UserManagementController::class,'userDataTable'])->name('user.data-table');
    Route::get('/user/edit/{id}', [UserManagementController::class,'edit'])->name('user.edit');
    Route::post('/user/update', [UserManagementController::class,'update'])->name('user.update');
    Route::post('/user/delete', [UserManagementController::class,'destroy'])->name('user.delete');
    Route::post('/user/account-status', [UserManagementController::class,'accountStatus'])->name('user.account-status');
    Route::get('/user/export', [UserManagementController::class,'export'])->name('user.export');

    // categories controller
    Route::get('/categories', [CategoriesController::class,'index'])->name('categories');
    Route::post('/categories/store', [CategoriesController::class,'store'])->name('categories.store');
    Route::get('/categories/datatable', [CategoriesController::class,'categoriesDataTable'])->name('categories.data-table');
    Route::get('/categories/edit/{id}', [CategoriesController::class,'edit'])->name('categories.edit');
    Route::post('/categories/update', [CategoriesController::class,'update'])->name('categories.update');
    Route::post('/categories/delete', [CategoriesController::class,'destroy'])->name('categories.delete');
    Route::post('/categories/account-status', [CategoriesController::class,'accountStatus'])->name('categories.account-status');
    Route::get('/categories/export', [CategoriesController::class,'export'])->name('categories.export');

    // user feedback-management controller
    Route::get('/feedback-management', [ContactUsController::class,'index'])->name('feedback-management');
    Route::get('/feedback-management/datatable', [ContactUsController::class,'contactUsDataTable'])->name('feedback-management.data-table');

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/get/user', [RegisterController::class,'GetAllUser']);
});