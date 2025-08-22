<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BetaTesterController;
use App\Http\Controllers\Api\BrnadKitApiController;
use App\Http\Controllers\Api\CategoriesApiController;
use App\Http\Controllers\Api\IconManagementAPiController;
use App\Http\Controllers\Api\PostContentApiController;
use App\Http\Controllers\Api\PrivacyPolicyApiController;
use App\Http\Controllers\Api\ProfileManagementApiController;
use App\Http\Controllers\Api\StockImageApiController;
use App\Http\Controllers\Api\SubscriptionApiController;
use App\Http\Controllers\Api\SubscriptionPlanApiController;
use App\Http\Controllers\Api\TemplateApiController;
use App\Http\Controllers\Api\TermsAndConditionApiController;
use App\Http\Controllers\Api\UserDownloadsManagementApiController;
use App\Http\Controllers\Api\UserManagementApiController;
use App\Http\Controllers\Api\UserSubscriptionHistoryApiController;
use App\Http\Controllers\Api\UserSubscriptionNewApiController;
use App\Http\Controllers\Api\UserTemplateDownloadController;
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
    ->middleware(['throttle:6,1']);

Route::post('/email/resend-verification', [AuthApiController::class, 'resend'])
    ->middleware(['throttle:6,1']);

// forget password
Route::post('/forget-password', [AuthApiController::class, 'forgetPassword'])->middleware(['throttle:5,2']);
Route::post('/resend/forget-password', [AuthApiController::class, 'resendForgetPassword'])->middleware(['throttle:5,2']);
// reset password
Route::post('/reset-password', [AuthApiController::class, 'resetPassword'])->middleware(['throttle:5,2']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Auth Routes
Route::group([
    'middleware' => ['auth:sanctum', 'checkUserStatus']
], function () {
    Route::group([], function () {
        Route::get('/get/user', [RegisterController::class, 'GetAllUser']);
        Route::get('/account-status-check', [RegisterController::class, 'statusCheck']);


        // Route::get('/admin/download/document/{id}', [AdminApiController::class, 'downloadDocument']);

        // category list
        Route::get('/category/list', [CategoriesApiController::class, 'list']);
        Route::get('/sub-category/list', [CategoriesApiController::class, 'subList']);
        Route::get('/sub-category/get/{id}', [CategoriesApiController::class, 'getSubCategory']);

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
        // Route::post('/post-content/get/category', [PostContentApiController::class, 'getCategoryPostContent']);

        // stock image api
        Route::get('/stock-image/get', [StockImageApiController::class, 'get']);
        
        // admin-template API
        Route::get('/template/get/{id}', [TemplateApiController::class, 'getTemplate']);
        Route::post('/template/list', [TemplateApiController::class, 'getTemplateList']);
        Route::post('/text-content/template/list', [TemplateApiController::class, 'getTextContentTemplateList']);
        Route::post('/template/delete', [TemplateApiController::class, 'delete']);

        // User templates APIS
        Route::get('/user-template/list', [UserTemplatesApiController::class, 'list']);
        Route::get('/user-template/get/{id}', [UserTemplatesApiController::class, 'get']);
        Route::post('/user-template/store', [UserTemplatesApiController::class, 'store']);
        Route::post('/user-template/multiple/store', [UserTemplatesApiController::class, 'MultiStore']);
        Route::post('/user-template/update', [UserTemplatesApiController::class, 'update']);
        Route::post('/user-template/delete', [UserTemplatesApiController::class, 'delete']);
        Route::post('/user-template/download/document', [UserTemplateDownloadController::class, 'downloadDocument']);

        // user subscription api
        // Route::post('/user-subscription/subscribe', [UserSubscriptionNewApiController::class, 'subscribe']);
        Route::get('/user-subscription/current', [UserSubscriptionNewApiController::class, 'getCurrentSubscription']);
        // Route::get('/user-subscription/cancel', [UserSubscriptionNewApiController::class, 'cancelSubscription']);
        Route::get('/user-subscription/download-limit', [UserSubscriptionNewApiController::class, 'downloadLimit']);

        // user downloads Mangement
        Route::get('/user-downloads/state', [UserDownloadsManagementApiController::class, 'currentState']);
        Route::get('/user-downloads/increment', [UserDownloadsManagementApiController::class, 'incrementDownload']);

        /** New Subscription API */
        Route::post('/user-subscription/subscribe', [SubscriptionApiController::class, 'userPlanSubscribe']);
        Route::get('/user-subscription/verify', [SubscriptionApiController::class, 'userSubscriptionVerify']);
        Route::post('/user-subscription/cancel', [SubscriptionApiController::class, 'cancelSubscription']);
        Route::get('/user-subscription/canceled-verification', [SubscriptionApiController::class, 'verifyUserSubscriptionCanceled']);
        Route::post('/user-subscription/continue', [SubscriptionApiController::class, 'continueSubscription']);
        Route::get('/user-subscription/generate-invoice', [SubscriptionApiController::class, 'generateSubscriptionInvoice']);

        // Subscription Plan History API
        Route::get('/user-subscription/history', [UserSubscriptionHistoryApiController::class, 'userSubscriptionHistory']);

        // subscription plan api
        Route::get('/subscription-plan/list', [SubscriptionPlanApiController::class, 'list']);

        // user images routes
        Route::post('/user-image/store', [StockImageApiController::class, 'store']);
        Route::post('/user-image/delete', [StockImageApiController::class, 'delete']);

        // user management api
        Route::post('/user-account/delete', [UserManagementApiController::class, 'deleteUserAccount']);

        // logout api
        Route::post('/logout', [RegisterController::class, 'logout']);
    });
});
// ======================================================================================================

// only admin access routes
Route::group([
    'middleware' => 'adminToken'
], function () {
    // admin api
    Route::get('/admin/template-data', [AdminApiController::class, 'index']);
    
    Route::get('/admin/brand-kit', [BrnadKitApiController::class, 'adminBrandKit']);

    // Admin Create & update Template 
    Route::post('/template/store', [TemplateApiController::class, 'store']);
    Route::get('/admin-template/get/{id}', [TemplateApiController::class, 'getTemplate']);
    Route::post('/template/update', [TemplateApiController::class, 'update']);
    
    // post content api
    Route::post('/post-content/get/category', [PostContentApiController::class, 'getCategoryPostContent']);

    // get stock image
    Route::get('/admin/stock-image/get', [StockImageApiController::class, 'get']);
    
    // icon management api
    Route::get('/icon-management/list', [IconManagementAPiController::class, 'list']);
});
// ====================================================================================================== 

// public routes
Route::post('/store/contact-us', [ContactUsController::class, 'store']);
Route::get('/privacy-policy/get', [PrivacyPolicyApiController::class, 'get']);
Route::get('/terms-and-condition/get', [TermsAndConditionApiController::class, 'get']);



// Public routes (no authentication required - for Stripe redirects)
Route::get('/user-subscription/success', [UserSubscriptionNewApiController::class, 'success']);
Route::get('/user-subscription/cancel', [UserSubscriptionNewApiController::class, 'cancel']);

// Webhook route (no authentication, but signature verification)
// Route::post('/stripe/webhook', [UserSubscriptionNewApiController::class, 'webhook']);
Route::post('/stripe/webhook', [SubscriptionApiController::class, 'webhook']);

Route::post('/mail/beta-tester', [BetaTesterController::class, 'sendBetaTesterMail']);