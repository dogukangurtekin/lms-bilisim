<?php

use App\Http\Middleware\AuthenticateClientApi;
use App\Http\Middleware\CheckForcedLogout;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\StudentRequestGuard;
use App\Http\Middleware\TrackStudentActiveTime;
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
        $middleware->web(append: [
            CheckMaintenanceMode::class,
            CheckForcedLogout::class,
            StudentRequestGuard::class,
            TrackStudentActiveTime::class,
        ]);
        // Not: giris formlari zaten @csrf iceriyor; bu route'lari CSRF
        // dogrulamasindan muaf tutmanin bilinen bir gerekcesi yoktu ve
        // "login CSRF" saldirilarina (saldirganin kendi hesabina otomatik
        // giris yaptirip kurbani kandirmasi) acik kapi birakiyordu.
        $middleware->api(append: [CheckMaintenanceMode::class]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'exam.access' => \App\Http\Middleware\ExamBuilderAccess::class,
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
        // Komut günde bir kez gönderir (bugün gönderildiyse çıkar); her dakika yerine saatlik çalışması yeterli ve süreç sayısını 60 kat azaltır.
        // Komutlar süreç açmadan (aynı süreçte) çalışır: zamanlayıcı hem cron'dan hem de web adresinden (/api/cron/...) tetiklenebilir.
        $schedule->call(fn () => \Illuminate\Support\Facades\Artisan::call('notifications:attendance-reminders'))
            ->name('attendance-reminders')->hourly()->withoutOverlapping(10);
        $schedule->call(fn () => \Illuminate\Support\Facades\Artisan::call('activity-logs:purge'))
            ->name('activity-logs-purge')->dailyAt('00:30')->withoutOverlapping(30);
        // Zamanlayıcının (cron) gerçekten çalıştığını gösteren işaret: son çalışma zamanı önbelleğe yazılır.
        $schedule->call(fn () => \Illuminate\Support\Facades\Cache::put('scheduler_heartbeat', now()->toIso8601String(), 7200))
            ->name('scheduler-heartbeat')->everyFiveMinutes();
    })
    ->create();
