<?php

use App\Http\Middleware\AuthenticateClientApi;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecureHeaders::class);
        $middleware->validateCsrfTokens(except: [
            'login',
            'login/game',
            'public/index.php/login',
            'public/index.php/login/game',
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'client.auth' => AuthenticateClientApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('notifications:attendance-reminders')->everyMinute();
    })
    ->create();
