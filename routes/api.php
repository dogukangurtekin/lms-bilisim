<?php

use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\SchoolClassController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\ClientBlockBuilderController;
use App\Http\Controllers\ClientDataController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,teacher'])->prefix('v1')->name('api.')->group(function () {
    Route::apiResource('students', StudentController::class);
    Route::apiResource('classes', SchoolClassController::class);
    Route::apiResource('courses', CourseController::class);
});

Route::prefix('client')->group(function () {
    Route::middleware('throttle:1000,1')->group(function () {
        Route::post('/auth/login', [ClientDataController::class, 'login']);
        Route::post('/auth/register', [ClientDataController::class, 'register']);
    });

    Route::middleware(['client.auth', 'throttle:12000,1'])->group(function () {
        Route::post('/auth/logout', [ClientDataController::class, 'logout']);
        Route::post('/auth/update-password', [ClientDataController::class, 'updatePassword']);
        Route::post('/auth/delete-user', [ClientDataController::class, 'deleteUser']);

        Route::get('/docs/get', [ClientDataController::class, 'getDoc']);
        Route::post('/docs/set', [ClientDataController::class, 'setDoc']);
        Route::post('/docs/update', [ClientDataController::class, 'updateDoc']);
        Route::post('/docs/delete', [ClientDataController::class, 'deleteDoc']);
        Route::post('/docs/add', [ClientDataController::class, 'addDoc']);
        Route::post('/docs/query', [ClientDataController::class, 'queryDocs']);
        Route::post('/docs/batch', [ClientDataController::class, 'batch']);

        Route::post('/callable/{name}', [ClientDataController::class, 'callable']);

        Route::prefix('/block-builder')->group(function () {
            Route::get('/designs/latest', [ClientBlockBuilderController::class, 'latest']);
            Route::get('/designs/{id}', [ClientBlockBuilderController::class, 'show'])->whereNumber('id');
            Route::post('/designs', [ClientBlockBuilderController::class, 'store']);
        });
    });
});

// user_id istemciden değil oturumdan alınır; aksi halde herhangi biri bir
// öğretmenin id'sini göndererek oda başlatıp bitirebilirdi.
// Canlı yarışma lobisi: anahtarlı, oturumsuz, 3 sn CDN önbelleği (yüzlerce öğrenci tek istek gibi görünür).
Route::get('/competitions/lobby/{room}/{key}', [\App\Http\Controllers\CompetitionController::class, 'publicLobby'])
    ->where(['room' => '[0-9]+', 'key' => '[a-f0-9]{24}'])
    ->middleware('throttle:120,1');

// Zamanlayıcıyı web üzerinden tetikleme (hosting cron'u PHP 7.4 dosyası çalıştırabilir ama PHP 8.2 komutu çalıştıramıyorsa):
// cron -> domains/bilisimkod.com/cron-ping.php -> bu adres. Anahtar APP_KEY'den türetilir; aynı anda tek çalışma (kilit), dakikada en fazla 6 istek.
Route::get('/cron/{token}', function (string $token) {
    $expected = substr(hash_hmac('sha256', 'scheduler-cron', (string) config('app.key')), 0, 40);
    abort_unless(hash_equals($expected, $token), 404);

    $lock = \Illuminate\Support\Facades\Cache::lock('cron-web-run', 240);
    if (! $lock->get()) {
        return response('busy', 200)->header('Content-Type', 'text/plain');
    }
    try {
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        $out = trim(\Illuminate\Support\Facades\Artisan::output());
    } finally {
        $lock->release();
    }

    return response('ok '.now()->toIso8601String().' '.$out, 200)->header('Content-Type', 'text/plain');
})->where('token', '[a-f0-9]{40}')->middleware('throttle:6,1');

Route::prefix('race')->middleware(['web', 'auth', \App\Http\Middleware\UseAuthenticatedUserId::class])->group(function () {
    Route::get('/rooms/active', [RaceController::class, 'active']);
    Route::get('/my-runs', [RaceController::class, 'myRuns']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{room:code}', [RoomController::class, 'show']);
    Route::post('/rooms/{room:code}/join', [RoomController::class, 'join']);

    Route::post('/rooms/{room:code}/start', [RaceController::class, 'start']);
    Route::post('/rooms/{room:code}/end', [RaceController::class, 'end']);
    Route::post('/rooms/{room:code}/progress', [RaceController::class, 'progress']);
    Route::post('/rooms/{room:code}/finish', [RaceController::class, 'finish']);
    Route::get('/rooms/{room:code}/leaderboard', [RaceController::class, 'leaderboard']);
    Route::get('/rooms/{room:code}/report', [RaceController::class, 'report']);
});
