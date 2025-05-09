<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;


Route::middleware('guest')->group(function () {
    Route::redirect('/', '/login');
    Route::get('/login', [LoginController::class,'index'])->name('login');
    Route::post('/login', [LoginController::class,'login']);
    // Route::get('/register', [RegisterController::class,'index'])->name('register');
    // Route::post('/register/check', [RegisterController::class,'register'])->name('register.check');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomePage::class, 'index'])->name('home');

    // Users Controller
    Route::get('/user/datatable', [UserManagementController::class,'userDataTable'])->name('user.data-table');
    Route::get('/user/edit/{id}', [UserManagementController::class,'edit'])->name('user.edit');
    Route::post('/user/update', [UserManagementController::class,'update'])->name('user.update');
    Route::post('/user/delete', [UserManagementController::class,'destroy'])->name('user.delete');
    Route::post('/user/account-status', [UserManagementController::class,'accountStatus'])->name('user.account-status');
    Route::get('/user/export', [UserManagementController::class,'export'])->name('user.export');

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/get/user', [RegisterController::class,'GetAllUser']);
});