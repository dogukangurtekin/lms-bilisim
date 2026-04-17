<?php

namespace App\Http\Controllers;

use App\Models\Avatar;
use App\Models\Badge;
use App\Models\ContentProgress;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCredential;
use App\Services\StudentProgressReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class StudentDataController extends Controller
{
    public function __construct(private StudentProgressReportService $reportService)
    {
    }

    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());
        $name = trim($request->string('name')->toString());
        $className = trim($request->string('class_name')->toString());
        $section = trim($request->string('section')->toString());
        $search = $q !== '' ? $q : $name;
        $listRequested = (string) $request->input('list', '') === '1';
        $hasFilter = $listRequested || $search !== '' || $className !== '' || $section !== '';

        $studentsQuery = Student::with(['user', 'schoolClass', 'currentAvatar', 'credential'])
            ->withCount('badges')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->whereHas('user', fn ($u) => $u
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                    )
                    ->orWhereHas('credential', fn ($c) => $c->where('username', 'like', "%{$search}%"))
                    ->orWhere('student_no', 'like', "%{$search}%")
                    ->orWhereHas('schoolClass', fn ($c) => $c
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('section', 'like', "%{$search}%")
                    );
                });
            })
            ->when($className !== '', fn ($query) => $query->whereHas('schoolClass', fn ($c) => $c->where('name', $className)))
            ->when($section !== '', fn ($query) => $query->whereHas('schoolClass', fn ($c) => $c->where('section', $section)))
            ->orderByDesc('id');

        $students = $hasFilter ? $studentsQuery->simplePaginate(50)->withQueryString() : collect();
        $studentItems = method_exists($students, 'getCollection') ? $students->getCollection() : $students;
        $studentIds = $studentItems->pluck('id')->all();
        $userIds = $studentItems->pluck('user_id')->all();

        $gradeXpByStudent = Grade::query()
            ->selectRaw('student_id, ROUND(SUM(score)) as xp')
            ->when(!empty($studentIds), fn ($q) => $q->whereIn('student_id', $studentIds))
            ->groupBy('student_id')
            ->pluck('xp', 'student_id');

        $contentXpByUser = ContentProgress::query()
            ->selectRaw('user_id, SUM(xp_awarded) as xp')
            ->when(!empty($userIds), fn ($q) => $q->whereIn('user_id', $userIds))
            ->groupBy('user_id')
            ->pluck('xp', 'user_id');

        $stats = $studentItems->mapWithKeys(function (Student $student) use ($gradeXpByStudent, $contentXpByUser) {
            $gradeXp = (int) ($gradeXpByStudent[$student->id] ?? 0);
            $contentXp = (int) ($contentXpByUser[$student->user_id] ?? 0);
            $xp = max(0, $gradeXp + $contentXp);

            return [
                $student->id => [
                    'xp' => $xp,
                    'badges' => (int) ($student->badges_count ?? 0),
                ],
            ];
        })->all();

        $classes = SchoolClass::query()
            ->select('name', 'section')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
        $classNames = $classes->pluck('name')->unique()->values();
        $sections = $classes->pluck('section')->unique()->values();

        $schoolClasses = SchoolClass::query()
            ->select('id', 'name', 'section')
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        return view('student-data.index', compact('students', 'stats', 'q', 'name', 'className', 'section', 'classNames', 'sections', 'schoolClasses'));
    }

    public function loginCards()
    {
        $students = Student::with(['user', 'schoolClass', 'credential'])->orderBy('id')->get();
        foreach ($students as $student) {
            $this->safeSyncRewardsAndCredentials($student, $this->calculateXp($student));
        }
        $students = Student::with(['user', 'schoolClass', 'credential'])->orderBy('id')->get();

        return view('student-data.login-cards', compact('students'));
    }

    public function resetAllPasswords(): RedirectResponse
    {
        $plain = '123456';

        DB::transaction(function () use ($plain) {
            $students = Student::with(['user', 'credential'])->get();

            foreach ($students as $student) {
                $user = $student->user;
                if (! $user) {
                    continue;
                }

                $user->password = Hash::make($plain, ['rounds' => 10]);
                $user->save();

                $username = $student->credential?->username
                    ?: Str::before((string) $user->email, '@');

                StudentCredential::query()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'username' => $username,
                        'plain_password' => $plain,
                    ]
                );
            }
        });

        return redirect()->route('student-data.index')->with('ok', 'Tum ogrenci sifreleri 123456 yapildi.');
    }

    public function resetAllPasswordsStart(): JsonResponse
    {
        $studentIds = Student::query()->orderBy('id')->pluck('id')->all();
        $task = [
            'id' => (string) Str::uuid(),
            'student_ids' => $studentIds,
            'total' => count($studentIds),
            'processed' => 0,
            'created_at' => now()->toIso8601String(),
            'completed_at' => null,
        ];
        $this->writePasswordTask($task['id'], $task);

        return response()->json([
            'task_id' => $task['id'],
            'total' => $task['total'],
        ]);
    }

    public function resetAllPasswordsStep(string $taskId): JsonResponse
    {
        $task = $this->readPasswordTask($taskId);
        if (! $task) {
            return response()->json(['message' => 'Gorev bulunamadi.'], 404);
        }

        if (($task['completed_at'] ?? null) !== null) {
            return response()->json($this->passwordTaskStatusPayload($task));
        }

        $batchSize = 20;
        $plain = '123456';
        $start = (int) ($task['processed'] ?? 0);
        $ids = array_slice((array) ($task['student_ids'] ?? []), $start, $batchSize);

        if ($ids !== []) {
            $students = Student::with(['user', 'credential'])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($ids as $id) {
                $student = $students->get($id);
                if (! $student || ! $student->user) {
                    $task['processed'] = (int) $task['processed'] + 1;
                    continue;
                }

                $student->user->password = Hash::make($plain, ['rounds' => 10]);
                $student->user->save();

                $username = $student->credential?->username
                    ?: Str::before((string) $student->user->email, '@');

                StudentCredential::query()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'username' => $username,
                        'plain_password' => $plain,
                    ]
                );

                $task['processed'] = (int) $task['processed'] + 1;
            }
        }

        if ((int) $task['processed'] >= (int) $task['total']) {
            $task['completed_at'] = now()->toIso8601String();
        }

        $this->writePasswordTask($taskId, $task);

        return response()->json($this->passwordTaskStatusPayload($task));
    }

    public function certificate(Student $student)
    {
        $xp = $this->calculateXp($student);
        $this->safeSyncRewardsAndCredentials($student, $xp);
        $student->refresh()->load(['user', 'schoolClass']);

        return view('student-data.certificate', [
            'student' => $student,
            'xp' => $xp,
            'teacherName' => env('SCHOOL_TEACHER_NAME', 'Ders Ogretmeni'),
            'principalName' => env('SCHOOL_PRINCIPAL_NAME', 'Okul Muduru'),
        ]);
    }

    public function progressReport(Student $student)
    {
        $xp = $this->calculateXp($student);
        $this->safeSyncRewardsAndCredentials($student, $xp);
        $student->refresh()->load(['user', 'schoolClass', 'badges', 'currentAvatar', 'avatars']);
        $report = $this->reportService->build($student);

        return view('reports.student-progress-report', [
            'student' => $student,
            'report' => $report,
            'viewer' => 'teacher',
        ]);
    }

    public function parentProgressReport(Student $student)
    {
        $student->load(['user', 'schoolClass', 'badges', 'currentAvatar', 'avatars']);
        $report = $this->reportService->build($student);

        return view('reports.student-progress-report', [
            'student' => $student,
            'report' => $report,
            'viewer' => 'parent',
        ]);
    }

    public function bulkProgressPreview()
    {
        return redirect()->route('student-data.index')->with('ok', 'Toplu rapor icin yeni ilerleme butonunu kullanin.');
    }

    public function bulkProgressDownload()
    {
        return redirect()->route('student-data.index')->with('ok', 'Toplu rapor icin yeni ilerleme butonunu kullanin.');
    }

    public function bulkProgressStart(Request $request): JsonResponse
    {
        $request->validate([
            'mode' => ['required', 'in:preview,download'],
        ]);

        $studentIds = Student::query()->orderBy('id')->pluck('id')->all();
        $task = [
            'id' => (string) Str::uuid(),
            'mode' => (string) $request->input('mode'),
            'student_ids' => $studentIds,
            'total' => count($studentIds),
            'processed' => 0,
            'reports' => [],
            'created_at' => now()->toIso8601String(),
            'completed_at' => null,
        ];
        $this->writeBulkTask($task['id'], $task);

        return response()->json([
            'task_id' => $task['id'],
            'total' => $task['total'],
        ]);
    }

    public function bulkProgressStep(string $taskId): JsonResponse
    {
        $task = $this->readBulkTask($taskId);
        if (! $task) {
            return response()->json(['message' => 'Gorev bulunamadi.'], 404);
        }

        if (($task['completed_at'] ?? null) !== null) {
            return response()->json($this->bulkTaskStatusPayload($task));
        }

        $batchSize = 4;
        $start = (int) ($task['processed'] ?? 0);
        $ids = array_slice((array) ($task['student_ids'] ?? []), $start, $batchSize);

        if ($ids !== []) {
            $students = Student::with(['user', 'schoolClass', 'badges', 'currentAvatar', 'avatars'])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($ids as $id) {
                $student = $students->get($id);
                if (! $student) {
                    $task['processed'] = (int) $task['processed'] + 1;
                    continue;
                }
                $task['reports'][(string) $id] = $this->reportService->build($student);
                $task['processed'] = (int) $task['processed'] + 1;
            }
        }

        if ((int) $task['processed'] >= (int) $task['total']) {
            $task['completed_at'] = now()->toIso8601String();
        }

        $this->writeBulkTask($taskId, $task);

        return response()->json($this->bulkTaskStatusPayload($task));
    }

    public function bulkProgressPreviewTask(string $taskId)
    {
        $task = $this->readBulkTask($taskId);
        if (! $task || ($task['completed_at'] ?? null) === null) {
            abort(404);
        }

        $reports = $this->buildBulkTaskViewData($task);
        $downloadUrl = route('student-data.reports.bulk-download.task', $taskId);
        return view('reports.students-progress-bulk', compact('reports', 'downloadUrl'));
    }

    public function bulkProgressDownloadTask(string $taskId): Response
    {
        $task = $this->readBulkTask($taskId);
        if (! $task || ($task['completed_at'] ?? null) === null) {
            abort(404);
        }

        $reports = $this->buildBulkTaskViewData($task);
        $html = view('reports.students-progress-bulk', compact('reports'))->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, 'tum-ogrenci-gelisim-raporlari.html', ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function calculateXp(Student $student): int
    {
        $gradeXp = (int) round((float) Grade::where('student_id', $student->id)->sum('score'));
        $contentXp = (int) ContentProgress::where('user_id', $student->user_id)->sum('xp_awarded');

        return max(0, $gradeXp + $contentXp);
    }

    private function syncRewardsAndCredentials(Student $student, int $xp): void
    {
        $credential = $student->credential;
        if (! $credential) {
            $username = Str::before((string) $student->user?->email, '@');
            $plain = (string) random_int(100000, 999999);

            StudentCredential::create([
                'student_id' => $student->id,
                'username' => $username,
                'plain_password' => $plain,
            ]);

            if ($student->user) {
                $student->user->password = Hash::make($plain, ['rounds' => 10]);
                $student->user->save();
            }
        }

        // Satin alma modelinde avatarlar otomatik acilmaz; sadece varsayilan avatar tanimlanir.
        $baseAvatar = Avatar::where('is_active', true)->orderBy('required_xp')->first();
        if ($baseAvatar) {
            $student->avatars()->syncWithoutDetaching([$baseAvatar->id => ['unlocked_at' => now()]]);
            if (! $student->current_avatar_id) {
                $student->current_avatar_id = $baseAvatar->id;
                $student->save();
            }
        }

        // Rozetler ogrenci panelindeki gorev ilerlemesine gore verilir.
    }

    private function safeSyncRewardsAndCredentials(Student $student, int $xp): void
    {
        try {
            $this->syncRewardsAndCredentials($student, $xp);
        } catch (QueryException|Throwable $e) {
            Log::warning('Student rewards sync skipped due DB issue', [
                'student_id' => $student->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function bulkTaskPath(string $taskId): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9\\-]/', '', $taskId) ?: 'task';
        return storage_path('app/reports/bulk-progress-' . $safe . '.json');
    }

    private function readBulkTask(string $taskId): ?array
    {
        $path = $this->bulkTaskPath($taskId);
        if (! is_file($path)) {
            return null;
        }
        $json = @file_get_contents($path);
        $data = is_string($json) ? json_decode($json, true) : null;
        return is_array($data) ? $data : null;
    }

    private function writeBulkTask(string $taskId, array $task): void
    {
        $path = $this->bulkTaskPath($taskId);
        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($task, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function bulkTaskStatusPayload(array $task): array
    {
        $total = max(1, (int) ($task['total'] ?? 1));
        $processed = min($total, max(0, (int) ($task['processed'] ?? 0)));
        $percent = (int) floor(($processed / $total) * 100);
        $completed = ($task['completed_at'] ?? null) !== null;
        $taskId = (string) ($task['id'] ?? '');

        return [
            'task_id' => $taskId,
            'processed' => $processed,
            'total' => $total,
            'percent' => $percent,
            'completed' => $completed,
            'preview_url' => route('student-data.reports.bulk-preview.task', $taskId),
            'download_url' => route('student-data.reports.bulk-download.task', $taskId),
        ];
    }

    private function buildBulkTaskViewData(array $task): array
    {
        $ids = (array) ($task['student_ids'] ?? []);
        $reportsMap = (array) ($task['reports'] ?? []);

        $students = Student::with(['user', 'schoolClass', 'badges', 'currentAvatar', 'avatars'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $out = [];
        foreach ($ids as $id) {
            $student = $students->get($id);
            if (! $student) {
                continue;
            }
            $out[] = [
                'student' => $student,
                'report' => (array) ($reportsMap[(string) $id] ?? []),
            ];
        }

        return $out;
    }

    private function passwordTaskPath(string $taskId): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9\\-]/', '', $taskId) ?: 'task';
        return storage_path('app/reports/password-reset-' . $safe . '.json');
    }

    private function readPasswordTask(string $taskId): ?array
    {
        $path = $this->passwordTaskPath($taskId);
        if (! is_file($path)) {
            return null;
        }
        $json = @file_get_contents($path);
        $data = is_string($json) ? json_decode($json, true) : null;
        return is_array($data) ? $data : null;
    }

    private function writePasswordTask(string $taskId, array $task): void
    {
        $path = $this->passwordTaskPath($taskId);
        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($task, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function passwordTaskStatusPayload(array $task): array
    {
        $total = max(1, (int) ($task['total'] ?? 1));
        $processed = min($total, max(0, (int) ($task['processed'] ?? 0)));
        $percent = (int) floor(($processed / $total) * 100);
        $completed = ($task['completed_at'] ?? null) !== null;
        $taskId = (string) ($task['id'] ?? '');

        return [
            'task_id' => $taskId,
            'processed' => $processed,
            'total' => $total,
            'percent' => $percent,
            'completed' => $completed,
            'message' => $completed ? 'Tum ogrenci sifreleri 123456 yapildi.' : 'Sifreler guncelleniyor...',
        ];
    }
}
