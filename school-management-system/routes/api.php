<?php

use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\SchoolClassController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\ExecutionController;
use App\Http\Controllers\Api\FlowchartController;
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

Route::prefix('race')->group(function () {
    Route::get('/rooms/active', [RaceController::class, 'active']);
    Route::get('/my-runs', [RaceController::class, 'myRuns']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{room:code}', [RoomController::class, 'show']);
    Route::post('/rooms/{room:code}/join', [RoomController::class, 'join']);

    Route::post('/rooms/{room:code}/start', [RaceController::class, 'start']);
    Route::post('/rooms/{room:code}/end', [RaceController::class, 'end']);
    Route::post('/rooms/{room:code}/finish', [RaceController::class, 'finish']);
    Route::get('/rooms/{room:code}/leaderboard', [RaceController::class, 'leaderboard']);
    Route::get('/rooms/{room:code}/report', [RaceController::class, 'report']);
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/flowcharts', [FlowchartController::class, 'store']);
    Route::get('/flowcharts/{id}', [FlowchartController::class, 'show']);
    Route::put('/flowcharts/{id}', [FlowchartController::class, 'update']);
    Route::delete('/flowcharts/{id}', [FlowchartController::class, 'destroy']);
    Route::post('/execute', [ExecutionController::class, 'execute']);
});
