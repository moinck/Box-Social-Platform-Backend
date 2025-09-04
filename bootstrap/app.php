<?php

use App\Console\Commands\CleanExpiredTokens;
use App\Http\Middleware\AdminTokenMiddleware;
use App\Http\Middleware\CheckRoleMiddleware;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(LocaleMiddleware::class);
        $middleware->alias([
            'checkRole' => CheckRoleMiddleware::class,
            'verified' => EnsureEmailIsVerified::class,
            'checkUserStatus' => CheckUserStatus::class,
            'adminToken' => AdminTokenMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // clean expired auth tokens
        $schedule->command('app:clean-expired-tokens')
            ->daily()
            ->at('01:30')
            ->withoutOverlapping()
            ->runInBackground();

        // reset user's monthly downloads
        $schedule->command('downloads:reset-monthly')
                ->monthlyOn(1, '00:01')
                ->withoutOverlapping()
                ->runInBackground();

        // check user's expired subscriptions
        $schedule->command('subscriptions:check-expired')
                ->daily()
                ->at('02:00')
                ->withoutOverlapping()
                ->runInBackground();
                
        // check user's expired subscriptions
        $schedule->command('subscriptions:check-expired')
                ->daily()
                ->at('02:00')
                ->withoutOverlapping()
                ->runInBackground();

        //Free Trial Subscription Last Day mail
        $schedule->command('SendLastDaySubMail')
                ->daily()
                ->at('10:00')
                ->timezone('Europe/London')
                ->withoutOverlapping()
                ->runInBackground();

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
