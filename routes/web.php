<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityRunnerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseHomeworkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlowchartPageController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameAssignmentController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\LiveQuizController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentWhatsappController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\StudentDataController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\QrLoginController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\TeacherClassAssignmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
Route::any('/public', fn () => redirect('/'));
Route::any('/public/', fn () => redirect('/'));
Route::get('/public/index.php/{asset}', function (Request $request, string $asset) {
    $runnerSlugs = [
        'block-3d-runner',
        'block-grid-runner',
        'compute-it-runner',
        'lightbot-runner',
        'line-trace-runner',
        'silent-teacher-runner',
    ];

    $asset = ltrim($asset, '/');
    if (!in_array($asset, ['app.js', 'style.css'], true)) {
        abort(404);
    }

    if ($asset === 'style.css') {
        $css = implode("\n", array_map(
            fn (string $slug) => '@import url("/runner-assets/' . $slug . '/style.css");',
            $runnerSlugs
        ));

        return response($css, 200, ['Content-Type' => 'text/css; charset=UTF-8']);
    }

    $js = <<<'JS'
const runnerSlugs = [
  "block-3d-runner",
  "block-grid-runner",
  "compute-it-runner",
  "lightbot-runner",
  "line-trace-runner",
  "silent-teacher-runner"
];

const path = window.location.pathname || "";
const slug = runnerSlugs.find((item) => path.includes("/" + item));

if (slug) {
  const script = document.createElement("script");
  script.src = "/runner-assets/" + slug + "/app.js";
  if (slug === "block-3d-runner") {
    script.type = "module";
  }
  document.head.appendChild(script);
}
JS;

    return response($js, 200, ['Content-Type' => 'application/javascript; charset=UTF-8']);
})->where('asset', 'app\.js|style\.css');
Route::any('/index.php/{path}', fn (string $path) => redirect('/' . ltrim($path, '/')))
    ->where('path', '.*');
Route::any('/public/index.php/{path}', fn (string $path) => redirect('/' . ltrim($path, '/')))
    ->where('path', '.*');
Route::any('/public/{path}', fn (string $path) => redirect('/' . ltrim($path, '/')))
    ->where('path', '.*');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('/login/game', [AuthController::class, 'gameLogin'])->name('login.game');
    Route::post('/qr/guest/generate', [QrLoginController::class, 'generateGuest'])->name('qr.guest.generate');
    Route::get('/qr/guest/status/{token}', [QrLoginController::class, 'status'])->name('qr.guest.status');
});
Route::get('/qr-login/{token}', [QrLoginController::class, 'consume'])->name('qr.login.consume');

Route::get('/veli/gelisim-raporu/{student}', [StudentDataController::class, 'parentProgressReport'])
    ->middleware('signed')
    ->name('parent.progress-report');

Route::middleware('auth')->group(function () {
    Route::get('/profilim', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profilim', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/etkinlikler', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/qr-giris', [QrLoginController::class, 'menuPage'])->middleware('role:admin,teacher')->name('qr.login.menu');
    Route::get('/qr-okut/{student}', [QrLoginController::class, 'scannerPage'])->middleware('role:admin,teacher')->name('qr.login.scanner');
    Route::post('/qr/verify', [QrLoginController::class, 'verify'])->middleware('role:admin,teacher')->name('qr.verify');

    Route::get('/block-3d-runner', [ActivityRunnerController::class, 'block3d']);
    Route::get('/block-grid-runner', [ActivityRunnerController::class, 'blockGrid']);
    Route::get('/compute-it-runner', [ActivityRunnerController::class, 'computeIt']);
    Route::get('/lightbot-runner', [ActivityRunnerController::class, 'lightbot']);
    Route::get('/line-trace-runner', [ActivityRunnerController::class, 'lineTrace']);
    Route::get('/silent-teacher-runner', [ActivityRunnerController::class, 'silentTeacher']);
    Route::get('/flamestone-game', [GameController::class, 'game'])->name('flamestone.game');
    Route::get('/oyunlar', [GameController::class, 'game'])->name('flamestone.game.alias');
    Route::get('/editor', [GameController::class, 'editor'])->name('flamestone.editor');
    Route::get('/skorlar', [GameController::class, 'leaderboard'])->name('flamestone.leaderboard');
    Route::get('/runner-open/{slug}', [ActivityRunnerController::class, 'open'])->name('runner.open');
    Route::get('/runner-grant/{slug}', [ActivityRunnerController::class, 'grant'])->name('runner.grant');
    Route::get('/runner-grant/{slug}/', [ActivityRunnerController::class, 'grant']);
    Route::view('/keyboard-race', 'keyboard-race.index')->name('keyboard-race.index');
    Route::view('/block-builder-studio', 'block-builder.index')->name('block-builder.index');
    Route::get('/flowchart-programming', [FlowchartPageController::class, 'index'])->name('flowchart.editor');
    Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.detail');
    Route::get('/course-covers/{path}', [CourseController::class, 'cover'])->where('path', '.*')->name('courses.cover');

    Route::middleware('role:admin,teacher')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/kullanici-yonetimi', [UserManagementController::class, 'index'])->name('users.index');
            Route::post('/kullanici-yonetimi', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('/kullanici-yonetimi/toplu/ogrenci-sablon', [UserManagementController::class, 'downloadStudentBulkTemplate'])->name('users.bulk.students.template');
            Route::post('/kullanici-yonetimi/toplu/ogrenci-yukle', [UserManagementController::class, 'bulkStoreStudents'])->name('users.bulk.students.store');
            Route::get('/kullanici-yonetimi/toplu/ogretmen-sablon', [UserManagementController::class, 'downloadTeacherBulkTemplate'])->name('users.bulk.teachers.template');
            Route::post('/kullanici-yonetimi/toplu/ogretmen-yukle', [UserManagementController::class, 'bulkStoreTeachers'])->name('users.bulk.teachers.store');
            Route::delete('/kullanici-yonetimi/ogrenciler/secili', [UserManagementController::class, 'destroySelectedStudents'])->name('users.students.destroy-selected');
            Route::delete('/kullanici-yonetimi/ogrenciler/tumu', [UserManagementController::class, 'destroyAllStudents'])->name('users.students.destroy-all');
            Route::delete('/kullanici-yonetimi/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
            Route::get('/kullanici-yonetimi/ogretmen/{teacher}/sinif-ata', [TeacherClassAssignmentController::class, 'edit'])->name('users.teachers.classes.edit');
            Route::post('/kullanici-yonetimi/ogretmen/{teacher}/sinif-ata/kademe', [TeacherClassAssignmentController::class, 'assignByLevel'])->name('users.teachers.classes.assign-level');
            Route::post('/kullanici-yonetimi/ogretmen/{teacher}/sinif-ata/siniflar', [TeacherClassAssignmentController::class, 'assignByClasses'])->name('users.teachers.classes.assign-classes');
        });
        Route::get('/bildirimler', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/app-notifications/send', [NotificationController::class, 'sendMessage'])->name('notifications.send');
        Route::post('/app-notifications/{log}/resend', [NotificationController::class, 'resend'])->name('notifications.resend');
        Route::delete('/app-notifications/{log}', [NotificationController::class, 'destroyLog'])->name('notifications.logs.destroy');
        Route::delete('/app-notifications', [NotificationController::class, 'destroyAllLogs'])->name('notifications.logs.destroy-all');
        Route::post('/app-notifications/{log}/delete', [NotificationController::class, 'destroyLog'])->name('notifications.logs.destroy.post');
        Route::post('/app-notifications/delete-all', [NotificationController::class, 'destroyAllLogs'])->name('notifications.logs.destroy-all.post');
        Route::post('/classes/secili', [SchoolClassController::class, 'destroySelected'])->name('classes.destroy-selected');
        Route::post('/classes/tumu', [SchoolClassController::class, 'destroyAll'])->name('classes.destroy-all');
        Route::get('/classes/secili-sil', [SchoolClassController::class, 'destroySelectedGet'])->name('classes.destroy-selected.get');
        Route::get('/classes/tumu-sil', [SchoolClassController::class, 'destroyAllGet'])->name('classes.destroy-all.get');
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
        Route::post('/courses/{course}/assign-teacher', [CourseController::class, 'assignTeacher'])->name('courses.assign-teacher');
        Route::post('/courses/{course}/assign-classes', [CourseController::class, 'assignClasses'])->name('courses.assign-classes');
        Route::post('/courses/{course}/assign-level', [CourseController::class, 'assignByLevel'])->name('courses.assign-level');
        Route::post('/courses/{course}/delete', [CourseController::class, 'destroyPost'])->name('courses.destroy.post');
        Route::get('/courses/{course}/delete-now', [CourseController::class, 'destroyNow'])->name('courses.destroy.now');
        Route::get('/courses/delete/{id}', [CourseController::class, 'destroyById'])->name('courses.destroy.by-id');
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
        Route::get('/ogrenci/etkinlik-odevleri/{assignment}/ac', [StudentPortalController::class, 'openGameAssignment'])
            ->name('student.portal.game-assignment.open')
            ->missing(fn () => redirect()->route('student.portal.assignments'));
        Route::post('/ogrenci/etkinlik-odevleri/{assignment}/tamamla', [StudentPortalController::class, 'completeGameAssignment'])->name('student.portal.game-assignment.complete');
        Route::post('/ogrenci/sure/ping', [StudentPortalController::class, 'pingTime'])->name('student.portal.time.ping');
        Route::get('/ogrenci/gelisim-karnem', [StudentPortalController::class, 'progress'])->name('student.portal.progress');
        Route::get('/ogrenci/gelisim-raporum', [StudentPortalController::class, 'progressReport'])->name('student.portal.progress-report');
    });

    Route::get('/webpush/public-key', [NotificationController::class, 'publicKey'])->name('notifications.public-key');
    Route::post('/webpush/subscribe', [NotificationController::class, 'subscribe'])->name('notifications.subscribe');
    Route::post('/webpush/unsubscribe', [NotificationController::class, 'unsubscribe'])->name('notifications.unsubscribe');
    Route::post('/webpush/device-status', [NotificationController::class, 'syncDeviceStatus'])->name('notifications.device-status');
    Route::post('/app-notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');
    Route::post('/app-notifications/{log}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/app-notifications/mine', [NotificationController::class, 'myLogs'])->name('notifications.mine');
    Route::delete('/app-notifications/mine/{log}', [NotificationController::class, 'destroyMyLog'])->name('notifications.mine.destroy');
    Route::delete('/app-notifications/mine', [NotificationController::class, 'destroyAllMyLogs'])->name('notifications.mine.destroy-all');

    Route::prefix('/api/flamestone')->group(function () {
        Route::get('/levels', [LevelController::class, 'index'])->name('flamestone.levels.index');
        Route::post('/levels', [LevelController::class, 'store'])->name('flamestone.levels.store');
        Route::get('/scores', [ScoreController::class, 'index'])->name('flamestone.scores.index');
        Route::post('/scores', [ScoreController::class, 'store'])->name('flamestone.scores.store');
    });
});
