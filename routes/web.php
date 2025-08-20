<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PostTemplateController;
use App\Http\Controllers\SubscriptionPlansController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\BrnadconfigurationController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\IconManagementController;
use App\Http\Controllers\IconMangementsController;
use App\Http\Controllers\ImageStockManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostContentController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\ProfileManagementController;
use App\Http\Controllers\ProjectTestController;
use App\Http\Controllers\TermsAndConditionController;
use App\Http\Controllers\UserSubscriptionController;
use App\Http\Controllers\VideoStockController;
use App\Http\Controllers\TestController;

Route::middleware('guest')->group(function () {
    Route::redirect('/', '/login');
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    // Route::get('/register', [RegisterController::class,'index'])->name('register');
    // Route::post('/register/check', [RegisterController::class,'register'])->name('register.check');
});

Route::middleware('auth')->group(function () {
    // Route::get('/home', [HomePage::class, 'index'])->name('pages-home');
    Route::get('/dashboard', [HomePage::class, 'dashboard'])->name('dashboard');

    Route::get('/uploadMissingImages', [TestController::class, 'uploadMissingImages'])->name('uploadMissingImages');

    // notification controller
    Route::get('/notification/datatable', [NotificationController::class, 'dataTable'])->name('notification.data-table');
    Route::post('/notification/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notification.mark-as-read');

    // encode & decode 
    Route::get('/encryption/encode/{value}', [HomePage::class, 'encode'])->name('encryption.encode');
    Route::get('/encryption/decode/{value}', [HomePage::class, 'decode'])->name('encryption.decode');

    // Stock image Management
    Route::get('/stock-image-management', [ImageStockManagementController::class, 'index'])->name('stock-image-management');
    Route::get('/stock-image-management/get/saved-topics', [ImageStockManagementController::class, 'getSavedTopics'])->name('stock-image-management.get.saved-topics');
    Route::post('/get-image-management', [ImageStockManagementController::class, 'GetImages']);
    Route::post('/image-management/store', [ImageStockManagementController::class, 'imagesStore']);
    Route::get('/image-management/get/saved-images', [ImageStockManagementController::class, 'savedImages'])->name('image-management.get.saved-images');
    Route::post('/image-management/delete/saved-images', [ImageStockManagementController::class, 'deleteSavedImages'])->name('image-management.delete.saved-images');

    // extra pages
    Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
    Route::get('/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
    // Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
    // Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

    Route::get('/download/document/{id}', [ProjectTestController::class, 'downloadDocument'])->name('download.document');

    // User Management Controller
    Route::get('/user', [UserManagementController::class, 'index'])->name('user');
    Route::get('/user/datatable', [UserManagementController::class, 'userDataTable'])->name('user.data-table');
    Route::get('/user/edit/{id}', [UserManagementController::class, 'edit'])->name('user.edit');
    Route::post('/user/update', [UserManagementController::class, 'update'])->name('user.update');
    Route::post('/user/delete', [UserManagementController::class, 'destroy'])->name('user.delete');
    Route::post('/user/account-status', [UserManagementController::class, 'accountStatus'])->name('user.account-status');
    Route::post('/user/export', [UserManagementController::class, 'export'])->name('user.export');

    // Profile Management Controller
    Route::get('/profile-management', [ProfileManagementController::class, 'index'])->name('profile-management');
    Route::post('/profile-management/update', [ProfileManagementController::class, 'update'])->name('profile-management.update');

    // Only Admin access routes
    Route::middleware('checkRole:admin')->group(function () {
        // subscription plan
        Route::get('/subscription-plan', [SubscriptionPlansController::class, 'index'])->name('subscription-plan');

        // users subscription routes
        Route::get('/subscription-management', [UserSubscriptionController::class, 'index'])->name('subscription-management');
        Route::get('/subscription-management/datatable', [UserSubscriptionController::class, 'dataTable'])->name('subscription-management.data-table');
        Route::get('/subscription-management/show/{id}', [UserSubscriptionController::class, 'show'])->name('subscription-management.show');
        Route::post('/subscription-management/delete', [UserSubscriptionController::class, 'destroy'])->name('subscription-management.delete');
        Route::post('/subscription-management/export', [UserSubscriptionController::class, 'exportSubscriptionDetails'])->name('subscription-management.export');
        Route::post('/subscription-management/generate-invoice', [UserSubscriptionController::class, 'generateSubscriptionInvoice'])->name('subscription-management.generate-invoice');

        // icon management controller
        Route::get('/icon-management', [IconManagementController::class, 'index'])->name('icon-management');
        Route::post('/icon-management/store', [IconManagementController::class, 'store'])->name('icon-management.store');
        Route::get('/icon-management/get/saved-icon', [IconManagementController::class, 'getSavedIcon'])->name('icon-management.get.saved-icon');
        Route::get('/icon-management/get/saved-tag', [IconManagementController::class, 'getSavedTag'])->name('icon-management.get.saved-tag');
        Route::post('/icon-management/delete', [IconManagementController::class, 'destroy'])->name('icon-management.delete');
        Route::post('/icon-management/delete/saved-icon', [IconManagementController::class, 'deleteSavedIcon'])->name('icon-management.delete.saved-icon');
        
        // categories controller
        Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');
        Route::post('/categories/store', [CategoriesController::class, 'store'])->name('categories.store');
        Route::get('/categories/datatable', [CategoriesController::class, 'categoriesDataTable'])->name('categories.data-table');
        Route::get('/categories/edit/{id}', [CategoriesController::class, 'edit'])->name('categories.edit');
        Route::post('/categories/update', [CategoriesController::class, 'update'])->name('categories.update');
        Route::post('/categories/delete', [CategoriesController::class, 'destroy'])->name('categories.delete');
        Route::post('/categories/account-status', [CategoriesController::class, 'changeStatus'])->name('categories.change-status');
        Route::get('/categories/export', [CategoriesController::class, 'export'])->name('categories.export');

        // video stocks
        Route::get('/stock-video-management', [VideoStockController::class, 'index'])->name('stock-video-management');
        Route::post('/stock-video-management/search', [VideoStockController::class, 'GetVideos']);
        Route::get('/stock-video-management/get/saved-videos', [VideoStockController::class, 'getSavedVideos'])->name('stock-video-management.get.saved-videos');
        Route::post('/stock-video-management/store', [VideoStockController::class, 'store']);
        Route::post('/stock-video-management/delete/saved-videos', [VideoStockController::class, 'destroy'])->name('stock-video-management.delete.saved-videos');
        
        // brand configuration controller
        Route::get('/brand-configuration', [BrnadconfigurationController::class, 'index'])->name('brand-configuration');
        Route::get('/brand-configuration/show/{id}', [BrnadconfigurationController::class, 'show'])->name('brand-configuration.show');
        Route::get('/brand-configuration/edit/{id}', [BrnadconfigurationController::class, 'edit'])->name('brand-configuration.edit');
        Route::post('/brand-configuration/store', [BrnadconfigurationController::class, 'store'])->name('brand-configuration.store');
        Route::post('/brand-configuration/update', [BrnadconfigurationController::class, 'update'])->name('brand-configuration.update');
        Route::get('/brand-configuration/datatable', [BrnadconfigurationController::class, 'dataTable'])->name('brand-configuration.data-table');
        Route::post('/brand-configuration/delete', [BrnadconfigurationController::class, 'destroy'])->name('brand-configuration.delete');

        Route::get('/brand-configuration/update-json-data', [BrnadconfigurationController::class, 'updateJsonData'])->name('brand-configuration.update-json-data');
        // user feedback-management controller
        Route::get('/feedback-management', [ContactUsController::class, 'index'])->name('feedback-management');
        Route::get('/feedback-management/datatable', [ContactUsController::class, 'contactUsDataTable'])->name('feedback-management.data-table');
        Route::post('/feedback-management/delete', [ContactUsController::class, 'destroy'])->name('feedback-management.delete');
        Route::get('/feedback-management/mail-preview', [ContactUsController::class, 'mailPreview'])->name('feedback-management.mail-preview');

        // post content controller
        Route::get('/post-content', [PostContentController::class, 'index'])->name('post-content');
        Route::get('/post-content/sub-category/get/data', [PostContentController::class, 'subCategoryData'])->name('post-content.sub-category.get.data');
        Route::get('/post-content/create', [PostContentController::class, 'create'])->name('post-content.create');
        Route::post('/post-content/store', [PostContentController::class, 'store'])->name('post-content.store');
        Route::get('/post-content/datatable', [PostContentController::class, 'dataTable'])->name('post-content.data-table');
        Route::get('/post-content/edit/{id}', [PostContentController::class, 'edit'])->name('post-content.edit');
        Route::post('/post-content/update', [PostContentController::class, 'update'])->name('post-content.update');
        Route::post('/post-content/delete', [PostContentController::class, 'destroy'])->name('post-content.delete');
        Route::post('/post-content/import', [PostContentController::class, 'import'])->name('post-content.import');

        // post template controller
        Route::get('/post-template', [PostTemplateController::class, 'index'])->name('post-template');
        Route::get('/post-template/datatable', [PostTemplateController::class, 'dataTable'])->name('post-template.data-table');
        Route::post('/post-template/delete', [PostTemplateController::class, 'destroy'])->name('post-template.delete');
        Route::post('/post-template/account-status', [PostTemplateController::class, 'changeStatus'])->name('post-template.change-status');
        Route::post('/post-template/create/duplicate', [PostTemplateController::class, 'duplicate'])->name('post-template.create-duplicate');

        // privacy policy controller
        // Route::get('/privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');
        Route::get('/privacy-policy/create', [PrivacyPolicyController::class, 'create'])->name('privacy-policy.create');
        Route::post('/privacy-policy/store', [PrivacyPolicyController::class, 'store'])->name('privacy-policy.store');
        Route::get('/privacy-policy/edit/{id}', [PrivacyPolicyController::class, 'edit'])->name('privacy-policy.edit');
        Route::post('/privacy-policy/update', [PrivacyPolicyController::class, 'update'])->name('privacy-policy.update');
        Route::get('/privacy-policy/datatable', [PrivacyPolicyController::class, 'dataTable'])->name('privacy-policy.data-table');
        Route::post('/privacy-policy/delete', [PrivacyPolicyController::class, 'destroy'])->name('privacy-policy.delete');

        // terms and condition controller
        // Route::get('/terms-and-condition', [TermsAndConditionController::class, 'index'])->name('terms-and-condition');
        Route::get('/terms-and-condition/create', [TermsAndConditionController::class, 'create'])->name('terms-and-condition.create');
        Route::post('/terms-and-condition/store', [TermsAndConditionController::class, 'store'])->name('terms-and-condition.store');
        Route::get('/terms-and-condition/edit/{id}', [TermsAndConditionController::class, 'edit'])->name('terms-and-condition.edit');
        Route::post('/terms-and-condition/update', [TermsAndConditionController::class, 'update'])->name('terms-and-condition.update');
        Route::get('/terms-and-condition/datatable', [TermsAndConditionController::class, 'dataTable'])->name('terms-and-condition.data-table');
        Route::post('/terms-and-condition/delete', [TermsAndConditionController::class, 'destroy'])->name('terms-and-condition.delete');
    });
    
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/get/user', [RegisterController::class, 'GetAllUser']);
    Route::get('invoice', function() {
        return view('content.pages.user-subscription.invoice');
    });
});
