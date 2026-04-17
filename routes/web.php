<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityRunnerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseHomeworkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlowchartPageController;
use App\Http\Controllers\GameAssignmentController;
use App\Http\Controllers\LiveQuizController;
use App\Http\Controllers\ParentWhatsappController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentDataController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherAssignmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('/login/game', [AuthController::class, 'gameLogin'])->name('login.game');
});

Route::get('/veli/gelisim-raporu/{student}', [StudentDataController::class, 'parentProgressReport'])
    ->middleware('signed')
    ->name('parent.progress-report');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/etkinlikler', [ActivityController::class, 'index'])->name('activities.index');

    Route::get('/block-3d-runner', [ActivityRunnerController::class, 'block3d']);
    Route::get('/block-grid-runner', [ActivityRunnerController::class, 'blockGrid']);
    Route::get('/compute-it-runner', [ActivityRunnerController::class, 'computeIt']);
    Route::get('/lightbot-runner', [ActivityRunnerController::class, 'lightbot']);
    Route::get('/line-trace-runner', [ActivityRunnerController::class, 'lineTrace']);
    Route::get('/silent-teacher-runner', [ActivityRunnerController::class, 'silentTeacher']);
    Route::get('/runner-open/{slug}', [ActivityRunnerController::class, 'open'])->name('runner.open');
    Route::get('/runner-grant/{slug}', [ActivityRunnerController::class, 'grant'])->name('runner.grant');
    Route::view('/keyboard-race', 'keyboard-race.index')->name('keyboard-race.index');
    Route::view('/block-builder-studio', 'block-builder.index')->name('block-builder.index');
    Route::get('/flowchart-programming', [FlowchartPageController::class, 'index'])->name('flowchart.editor');
    Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.detail');
    Route::get('/course-covers/{path}', [CourseController::class, 'cover'])->where('path', '.*')->name('courses.cover');

    Route::middleware('role:admin,teacher')->group(function () {
        Route::post('/veli-bildirim/whatsapp/baslat', [ParentWhatsappController::class, 'start'])->name('parent-whatsapp.start');
        Route::post('/veli-bildirim/whatsapp/adim/{taskId}', [ParentWhatsappController::class, 'step'])->name('parent-whatsapp.step');
        Route::get('/veli-bildirim/siniflar', [ParentWhatsappController::class, 'classes'])->name('parent-whatsapp.classes');

        Route::get('/canli-quiz', [LiveQuizController::class, 'index'])->name('live-quiz.index');
        Route::post('/canli-quiz', [LiveQuizController::class, 'store'])->name('live-quiz.store');
        Route::post('/canli-quiz/{quiz}/baslat', [LiveQuizController::class, 'start'])->name('live-quiz.start');
        Route::get('/canli-quiz/oturum/{session}', [LiveQuizController::class, 'showSession'])->name('live-quiz.session.show');
        Route::post('/canli-quiz/oturum/{session}/sonraki', [LiveQuizController::class, 'next'])->name('live-quiz.session.next');
        Route::post('/canli-quiz/oturum/{session}/kilit', [LiveQuizController::class, 'toggleLock'])->name('live-quiz.session.lock');
        Route::post('/canli-quiz/oturum/{session}/bitir', [LiveQuizController::class, 'finish'])->name('live-quiz.session.finish');

        Route::get('/courses/{course}/odev-ver', [CourseHomeworkController::class, 'create'])->name('courses.homeworks.create');
        Route::post('/courses/{course}/odev-ver', [CourseHomeworkController::class, 'store'])->name('courses.homeworks.store');
        Route::get('/ogrenci-verileri', [StudentDataController::class, 'index'])->name('student-data.index');
        Route::post('/ogrenci-verileri/sifreleri-sifirla', [StudentDataController::class, 'resetAllPasswords'])->name('student-data.passwords.reset-all');
        Route::post('/ogrenci-verileri/sifreleri-sifirla/baslat', [StudentDataController::class, 'resetAllPasswordsStart'])->name('student-data.passwords.reset-all.start');
        Route::post('/ogrenci-verileri/sifreleri-sifirla/adim/{taskId}', [StudentDataController::class, 'resetAllPasswordsStep'])->name('student-data.passwords.reset-all.step');
        Route::get('/ogrenci-verileri/giris-kartlari', [StudentDataController::class, 'loginCards'])->name('student-data.login-cards');
        Route::get('/ogrenci-verileri/gelisim-raporlari/toplu-onizleme', [StudentDataController::class, 'bulkProgressPreview'])->name('student-data.reports.bulk-preview');
        Route::get('/ogrenci-verileri/gelisim-raporlari/toplu-indir', [StudentDataController::class, 'bulkProgressDownload'])->name('student-data.reports.bulk-download');
        Route::post('/ogrenci-verileri/gelisim-raporlari/toplu-baslat', [StudentDataController::class, 'bulkProgressStart'])->name('student-data.reports.bulk-start');
        Route::post('/ogrenci-verileri/gelisim-raporlari/toplu-adim/{taskId}', [StudentDataController::class, 'bulkProgressStep'])->name('student-data.reports.bulk-step');
        Route::get('/ogrenci-verileri/gelisim-raporlari/toplu-onizleme/{taskId}', [StudentDataController::class, 'bulkProgressPreviewTask'])->name('student-data.reports.bulk-preview.task');
        Route::get('/ogrenci-verileri/gelisim-raporlari/toplu-indir/{taskId}', [StudentDataController::class, 'bulkProgressDownloadTask'])->name('student-data.reports.bulk-download.task');
        Route::get('/ogrenci-verileri/{student}/sertifika', [StudentDataController::class, 'certificate'])->name('student-data.certificate');
        Route::get('/ogrenci-verileri/{student}/gelisim-karnesi', [StudentDataController::class, 'progressReport'])->name('student-data.progress-report');
        Route::get('/etkinlikler/{gameSlug}/odev-ver', [GameAssignmentController::class, 'create'])->name('activities.assignments.create');
        Route::post('/etkinlikler/{gameSlug}/odev-ver', [GameAssignmentController::class, 'store'])->name('activities.assignments.store');
        Route::get('/odevler', [TeacherAssignmentController::class, 'index'])->name('teacher.assignments.index');
        Route::post('/odevler/odev-ver', [TeacherAssignmentController::class, 'storeHomework'])->name('teacher.assignments.homework.store');
        Route::get('/odevler/ders/{homework}', [TeacherAssignmentController::class, 'showCourseHomework'])->name('teacher.assignments.course.show');
        Route::get('/odevler/ders/{homework}/duzenle', [TeacherAssignmentController::class, 'editCourseHomework'])->name('teacher.assignments.course.edit');
        Route::put('/odevler/ders/{homework}', [TeacherAssignmentController::class, 'updateCourseHomework'])->name('teacher.assignments.course.update');
        Route::delete('/odevler/ders/{homework}', [TeacherAssignmentController::class, 'destroyCourseHomework'])->name('teacher.assignments.course.destroy');
        Route::get('/odevler/oyun/{assignment}', [TeacherAssignmentController::class, 'showGameAssignment'])->name('teacher.assignments.game.show');
        Route::get('/odevler/oyun/{assignment}/duzenle', [TeacherAssignmentController::class, 'editGameAssignment'])->name('teacher.assignments.game.edit');
        Route::put('/odevler/oyun/{assignment}', [TeacherAssignmentController::class, 'updateGameAssignment'])->name('teacher.assignments.game.update');
        Route::delete('/odevler/oyun/{assignment}', [TeacherAssignmentController::class, 'destroyGameAssignment'])->name('teacher.assignments.game.destroy');
        Route::get('/students/bulk/template', [StudentController::class, 'downloadBulkTemplate'])->name('students.bulk.template');
        Route::post('/students/bulk', [StudentController::class, 'bulkStore'])->name('students.bulk.store');
        Route::delete('/students/all', [StudentController::class, 'destroyAll'])->name('students.destroyAll');
        Route::resource('students', StudentController::class);
        Route::resource('classes', SchoolClassController::class);
        Route::post('/courses/upload-cover', [CourseController::class, 'uploadCover'])->name('courses.upload-cover');
        Route::resource('courses', CourseController::class);

    });

    Route::middleware('role:student')->group(function () {
        Route::get('/ogrenci/canli-quiz', [LiveQuizController::class, 'studentJoinForm'])->name('student.live-quiz.join.form');
        Route::post('/ogrenci/canli-quiz', [LiveQuizController::class, 'studentJoin'])->name('student.live-quiz.join');
        Route::get('/ogrenci/canli-quiz/anlik-katil/{session}', [LiveQuizController::class, 'studentInstantJoin'])->name('student.live-quiz.instant-join');
        Route::get('/ogrenci/canli-quiz/aktif-oturum', [LiveQuizController::class, 'studentActiveSession'])->name('student.live-quiz.active');
        Route::get('/ogrenci/canli-quiz/{session}', [LiveQuizController::class, 'studentPlay'])->name('student.live-quiz.play');
        Route::post('/ogrenci/canli-quiz/{session}/cevap', [LiveQuizController::class, 'studentAnswer'])->name('student.live-quiz.answer');

        Route::get('/ogrenci/panelim', [StudentPortalController::class, 'dashboard'])->name('student.portal.dashboard');
        Route::get('/ogrenci/derslerim', [StudentPortalController::class, 'courses'])->name('student.portal.courses');
        Route::get('/ogrenci/derslerim/{course}', [StudentPortalController::class, 'courseShow'])->name('student.portal.course-show');
        Route::post('/ogrenci/derslerim/{course}/tamamla', [StudentPortalController::class, 'completeCourse'])->name('student.portal.course.complete');
        Route::get('/ogrenci/odevlerim', [StudentPortalController::class, 'assignments'])->name('student.portal.assignments');
        Route::get('/ogrenci/arkadaslarim', [StudentPortalController::class, 'friends'])->name('student.portal.friends');
        Route::get('/ogrenci/sinif-panosu', [StudentPortalController::class, 'classBoard'])->name('student.portal.class-board');
        Route::post('/ogrenci/sinif-panosu/paylas', [StudentPortalController::class, 'storeClassBoardPost'])->name('student.portal.class-board.store');
        Route::get('/ogrenci/avatarlarim', [StudentPortalController::class, 'avatars'])->name('student.portal.avatars');
        Route::get('/ogrenci/rozetlerim', [StudentPortalController::class, 'badges'])->name('student.portal.badges');
        Route::post('/ogrenci/avatarlarim/{avatar}/satinal', [StudentPortalController::class, 'buyAvatar'])->name('student.portal.avatars.buy');
        Route::post('/ogrenci/avatarlarim/{avatar}/sec', [StudentPortalController::class, 'equipAvatar'])->name('student.portal.avatars.equip');
        Route::get('/ogrenci/odevlerim/{homework}/ac', [StudentPortalController::class, 'openHomework'])->name('student.portal.homework.open');
        Route::post('/ogrenci/odevlerim/{homework}/tamamla', [StudentPortalController::class, 'completeHomework'])->name('student.portal.homework.complete');
        Route::get('/ogrenci/odevlerim/{homework}/basarili', [StudentPortalController::class, 'homeworkSuccess'])->name('student.portal.homework.success');
        Route::get('/ogrenci/etkinlik-odevleri/{assignment}/ac', [StudentPortalController::class, 'openGameAssignment'])->name('student.portal.game-assignment.open');
        Route::post('/ogrenci/etkinlik-odevleri/{assignment}/tamamla', [StudentPortalController::class, 'completeGameAssignment'])->name('student.portal.game-assignment.complete');
        Route::post('/ogrenci/sure/ping', [StudentPortalController::class, 'pingTime'])->name('student.portal.time.ping');
        Route::get('/ogrenci/gelisim-karnem', [StudentPortalController::class, 'progress'])->name('student.portal.progress');
        Route::get('/ogrenci/gelisim-raporum', [StudentPortalController::class, 'progressReport'])->name('student.portal.progress-report');
    });
});
