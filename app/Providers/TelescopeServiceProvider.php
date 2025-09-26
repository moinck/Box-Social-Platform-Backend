<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment(env('APP_ENV'));

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {

            $blockedRoutes = [
                'daily-backup',
                'dashboard',
                'notification/datatable',
                'stock-image-management',
                'stock-image-management/get/saved-topics',
                'get-image-management',
                'image-management/get/saved-images',
                'user',
                'user/datatable',
                'user/edit/*',
                'user/export',
                'user/fca-numbers',
                'profile-management',
                'subscription-plan',
                'subscription-management',
                'subscription-management/datatable',
                'subscription-management/show/*',
                'subscription-management/export',
                'payment-history',
                'payment-history/datatable',
                'icon-management',
                'icon-management/get/saved-icon',
                'icon-management/get/saved-tag',
                'categories',
                'categories/datatable',
                'categories/edit/*',
                'categories/export',
                'brand-configuration',
                'brand-configuration/datatable',
                'brand-configuration/show/*',
                'brand-configuration/edit/*',
                'feedback-management',
                'feedback-management/datatable',
                'feedback-management/mail-preview',
                'post-content',
                'post-content/sub-category/get/data',
                'post-content/create',
                'post-content/edit/*',
                'post-content/datatable',
                'post-template',
                'post-template/datatable',
                'privacy-policy/create',
                'privacy-policy/datatable',
                'privacy-policy/edit/*',
                'terms-and-condition/create',
                'terms-and-condition/datatable',
                'terms-and-condition/edit/*',
                'cookie-policy',
                'email-settings',
                'email-settings/edit/*',
                'dummy-fca-number',
                'dummy-fca-number/edit/*',
                'youtube-video',
                'youtube-video/link-edit/*',
                'faq-calendar',
                'faq-calendar/edit/*',

                /** API Routes */
                'api/get/user',
                'api/category/list',
                'api/sub-category/list',
                'api/sub-category/get/*',
                'api/brandkit/get',
                'api/profile-management/get',
                'api/post-content/get/data',
                'api/template/get/*',
                'api/template/list',
                'api/text-content/template/list',
                'api/user-template/list',
                'api/user-template/get/*',
                'api/user-template/get-template/*',
                'api/user-subscription/current',
                'api/user-subscription/download-limit',
                'api/user-downloads/state',
                'api/user-downloads/increment',
                'api/saved-template-count/increment',
                'api/user-subscription/history',
                'api/subscription-plan/list',
                'api/notifications',
                'api/admin/template-data',
                'api/admin/brand-kit',
                'api/admin-template/get/*',
                'api/admin-template/get-template/*',
                'api/post-content/get/category',
                'api/admin/stock-image/get',
                'api/admin/stock-image/uploaded',
                'api/icon-management/list',
                'api/store/contact-us',
                'api/privacy-policy/get',
                'api/terms-and-condition/get',
                'api/cookie-policy',
                'api/youtube-video',
                'api/calendar-events',
            ];

            if (request()?->is(...$blockedRoutes) || ($entry->type === 'command' && $entry->content['command'] === 'list')) {
                return false;
            }

            return $isLocal ||
                   $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment(env('APP_ENV'))) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'admin.box@yopmail.com',
                'contact@fsdigitalmarketing.co.uk',
            ]);
        });
    }
}
