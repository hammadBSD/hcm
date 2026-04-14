<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        // Run every hour to catch shift start times
        $schedule->command('tasks:auto-assign')
            ->hourly()
            ->before(function () {
                \Log::info('Scheduler: tasks:auto-assign is about to run', [
                    'timestamp' => now()->toDateTimeString(),
                ]);
            })
            ->after(function () {
                \Log::info('Scheduler: tasks:auto-assign completed', [
                    'timestamp' => now()->toDateTimeString(),
                ]);
            });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
