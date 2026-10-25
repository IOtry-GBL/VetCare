<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('appointments:send-reminders')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('appointments:update-missed')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('queue:work database --queue=emails --stop-when-empty --max-time=50 --timeout=45 --tries=3')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
            'super-admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'pet-owner.verified' => \App\Http\Middleware\EnsurePetOwnerIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (is_dir(dirname(__DIR__).'/public_html')) {
    $app->usePublicPath(dirname(__DIR__).'/public_html');
}

return $app;
