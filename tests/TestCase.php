<?php

namespace Tests;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\StudentController;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Route;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('students.index')) {
            Route::middleware(['web', 'auth'])->group(function (): void {
                Route::get('/', fn () => redirect('/login'));
                Route::get('/students', [StudentController::class, 'index'])->name('students.index');
                Route::post('/students', [StudentController::class, 'store'])->name('students.store');
                Route::get('/students/bulk/template', [StudentController::class, 'downloadBulkTemplate'])->name('students.bulk.template');
                Route::get('/classes', fn () => response('ok', 200));
                Route::get('/courses', [CourseController::class, 'index']);
                Route::get('/ogrenci-verileri', fn () => response('ok', 200));
                Route::get('/odevler', fn () => response('ok', 200));
            });
        }
    }
}