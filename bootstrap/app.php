<?php

use App\Http\Middleware\AuthenticateClientApi;
use App\Http\Middleware\CheckForcedLogout;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecureHeaders::class);
        $middleware->web(append: [CheckForcedLogout::class]);
        // Not: giris formlari zaten @csrf iceriyor; bu route'lari CSRF
        // dogrulamasindan muaf tutmanin bilinen bir gerekcesi yoktu ve
        // "login CSRF" saldirilarina (saldirganin kendi hesabina otomatik
        // giris yaptirip kurbani kandirmasi) acik kapi birakiyordu.
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'client.auth' => AuthenticateClientApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Bir giris formu eski bir sekmeden ya da art arda iki kez gonderilirse
        // ikinci istek, ilk istegin session ID/CSRF token yenilemesinden sonra
        // 419'a dusebilir. CSRF kontrolunu kaldirmiyoruz; yalnizca login POST'u
        // icin taze formu aciyoruz. Ilk istek girisi tamamlamissa `guest`
        // middleware kullaniciyi zaten dashboard'a yonlendirir.
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419
                && $exception->getPrevious() instanceof TokenMismatchException
                && $request->isMethod('post')
                && $request->is('login')) {
                return redirect()->route('login', ['expired' => 1]);
            }

            return null;
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('notifications:attendance-reminders')->everyMinute();
    })
    ->create();
