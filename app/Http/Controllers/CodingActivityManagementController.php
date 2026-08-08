<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCodingActivityRequest;
use App\Http\Requests\UpdateCodingActivityRequest;
use App\Models\ActivityAttempt;
use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\DailyActivityAssignment;
use App\Models\Student;
use App\Models\StudentReport;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\QuestionOption;
use App\Models\UserXpLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CodingActivityManagementController extends Controller
{
    private function canUseTeacherColumn(): bool
    {
        return Schema::hasColumn('coding_activities', 'teacher_id');
    }

    private function canUseAdminLockColumn(): bool
    {
        return Schema::hasColumn('coding_activities', 'admin_locked');
    }

    private function isAssignedToTeacher(CodingActivity $activity, ?int $teacherId): bool
    {
        if (! $this->canUseTeacherColumn()) {
            return false;
        }

        return (int) ($activity->teacher_id ?? 0) > 0 && (int) $activity->teacher_id === (int) $teacherId;
    }

    public function index()
    {
        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        $isAdmin = (bool) auth()->user()?->hasRole('admin');
        $isTeacher = (bool) auth()->user()?->hasRole('teacher');
        $hasTeacherColumn = $this->canUseTeacherColumn();
        $hasAdminLockColumn = $this->canUseAdminLockColumn();
        $availableClasses = $this->availableClassesForCurrentUser($user);
        $creatorFilter = (int) request()->integer('creator_id');
        $activitiesQuery = CodingActivity::query()
            ->with(['creator:id,name', 'teacher.user:id,name'])
            ->withCount('questions')
            ->when($user?->hasRole('teacher') && $hasTeacherColumn, function ($query) use ($teacherId, $user): void {
                $query->where(function ($inner) use ($teacherId, $user): void {
                    $inner->where('teacher_id', $teacherId)
                        ->orWhere('created_by', (int) $user?->id);
                });
            })
            ->when($isAdmin && $creatorFilter > 0, fn ($query) => $query->where('created_by', $creatorFilter));
        $activities = $activitiesQuery->latest()->paginate(20)->withQueryString();
        $bulkActivities = (clone $activitiesQuery)
            ->latest('id')
            ->get();
        $activityCreators = $isAdmin
            ? CodingActivity::query()
                ->whereNotNull('created_by')
                ->with('creator:id,name')
                ->select(['created_by'])
                ->distinct()
                ->get()
                ->pluck('creator')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values()
            : collect();
        $bulkClassAssignmentMap = DailyActivityAssignment::query()
            ->with('activity:id,title,teacher_id,created_by')
            ->whereDate('assignment_date', Carbon::today('Europe/Istanbul'))
            ->where('target_role', 'student')
            ->get(['coding_activity_id', 'target_class_ids'])
            ->reduce(function (array $carry, DailyActivityAssignment $assignment): array {
                $activityId = (int) $assignment->coding_activity_id;
                foreach (array_values(array_filter((array) ($assignment->target_class_ids ?? []), fn ($value) => (int) $value > 0)) as $classId) {
                    $carry[(int) $classId] ??= [];
                    $carry[(int) $classId][] = $activityId;
                }
                return $carry;
            }, []);
        $todayAssignment = DailyActivityAssignment::with(['activity' => fn ($query) => $query->when($user?->hasRole('teacher'), fn ($q) => $q->where('teacher_id', $teacherId))])
            ->whereDate('assignment_date', Carbon::today('Europe/Istanbul'))
            ->where('target_role', 'student')
            ->when($user?->hasRole('teacher') && $hasTeacherColumn, fn ($query) => $query->whereHas('activity', fn ($q) => $q->where('teacher_id', $teacherId)))
            ->first();
        $selectedId = (int) request()->integer('edit');
        $editingActivity = $selectedId > 0 ? CodingActivity::with('questions.options')->find($selectedId) : null;
        $teachers = Teacher::query()->with('user')->orderBy('id', 'desc')->get();
        $assignableActivities = CodingActivity::query()
            ->select(array_values(array_filter([
                'id',
                'title',
                $hasTeacherColumn ? 'teacher_id' : null,
                $hasAdminLockColumn ? 'admin_locked' : null,
            ])))
            ->orderByDesc('id')
            ->get();
        $activityIdsByTeacher = $hasTeacherColumn
            ? CodingActivity::query()
                ->select(['id', 'teacher_id'])
                ->whereNotNull('teacher_id')
                ->get()
                ->groupBy(fn (CodingActivity $activity) => (int) ($activity->teacher_id ?? 0))
                ->map(fn ($group) => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all())
                ->all()
            : [];

        return view('coding-activities.manage', compact('activities', 'bulkActivities', 'bulkClassAssignmentMap', 'todayAssignment', 'editingActivity', 'isAdmin', 'isTeacher', 'teachers', 'availableClasses', 'activityIdsByTeacher', 'activityCreators', 'creatorFilter'));
    }

    public function store(StoreCodingActivityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $hasTeacherColumn = $this->canUseTeacherColumn();
        $hasAdminLockColumn = $this->canUseAdminLockColumn();

        DB::transaction(function () use ($data, $hasTeacherColumn, $hasAdminLockColumn): void {
            $payload = [
                'created_by' => auth()->id(),
                'title' => $data['title'],
                'type' => $data['type'],
                'instruction' => $data['instruction'] ?? null,
                'lesson_pages' => array_values(array_filter($data['lesson_pages'] ?? [])),
                'base_xp' => $data['base_xp'] ?? 20,
                'is_active' => true,
                'is_random_pool' => (bool) ($data['is_random_pool'] ?? true),
            ];
            if ($hasTeacherColumn) {
                $payload['teacher_id'] = auth()->user()?->hasRole('teacher') ? optional(auth()->user()?->teacher)->id : null;
            }
            if ($hasAdminLockColumn) {
                $payload['admin_locked'] = false;
            }
            $activity = CodingActivity::create($payload);

            $this->syncQuestions($activity, $data['questions'] ?? []);
        });

        return back()->with('ok', 'Etkinlik oluÅŸturuldu.');
    }

    public function update(UpdateCodingActivityRequest $request, CodingActivity $activity): RedirectResponse
    {
        $data = $request->validated();
        $hasTeacherColumn = $this->canUseTeacherColumn();
        $hasAdminLockColumn = $this->canUseAdminLockColumn();

        DB::transaction(function () use ($activity, $data, $hasTeacherColumn, $hasAdminLockColumn): void {
            $payload = [
                'title' => $data['title'],
                'type' => $data['type'],
                'instruction' => $data['instruction'] ?? null,
                'lesson_pages' => array_values(array_filter($data['lesson_pages'] ?? [])),
                'base_xp' => $data['base_xp'] ?? 20,
                'is_random_pool' => (bool) ($data['is_random_pool'] ?? false),
            ];
            if ($hasTeacherColumn) {
                $payload['teacher_id'] = $activity->teacher_id ?: (auth()->user()?->hasRole('teacher') ? optional(auth()->user()?->teacher)->id : null);
            }
            if ($hasAdminLockColumn && ! array_key_exists('admin_locked', $payload)) {
                $payload['admin_locked'] = (bool) ($activity->admin_locked ?? false);
            }
            $activity->update($payload);

            $activity->questions()->each(function (ActivityQuestion $question): void {
                $question->options()->delete();
                $question->delete();
            });

            $this->syncQuestions($activity, $data['questions'] ?? []);
        });

        return redirect()->route('coding.activities.manage', ['edit' => $activity->id])->with('ok', 'Etkinlik güncellendi.');
    }

    public function destroy(CodingActivity $activity): RedirectResponse
    {
        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        $hasTeacherColumn = $this->canUseTeacherColumn();
        $hasAdminLockColumn = $this->canUseAdminLockColumn();
        $isAssignedToTeacher = $hasTeacherColumn && $this->isAssignedToTeacher($activity, $teacherId);
        $isLocked = $hasAdminLockColumn && (bool) ($activity->admin_locked ?? false);

        if ($user?->hasRole('admin') && ($isAssignedToTeacher || $isLocked)) {
            return redirect()->route('coding.activities.manage')->with('error', 'Ogretmene atanmis gunluk calisma admin tarafindan silinemez.');
        }

        if ($user?->hasRole('teacher') && $isAssignedToTeacher) {
            DB::transaction(function () use ($activity, $teacherId, $hasTeacherColumn, $hasAdminLockColumn): void {
                $assignment = DailyActivityAssignment::query()
                    ->where('coding_activity_id', $activity->id)
                    ->whereDate('assignment_date', Carbon::today('Europe/Istanbul'))
                    ->where('target_role', 'student')
                    ->first();

                $this->purgeUnfinishedStudentDailyCodingTraces(
                    [$activity->id],
                    $this->studentUserIdsForAssignmentScope($activity, $assignment)
                );

                DailyActivityAssignment::query()
                    ->where('coding_activity_id', $activity->id)
                    ->delete();

                $update = [];
                if ($hasTeacherColumn) {
                    $update['teacher_id'] = null;
                }
                if ($hasAdminLockColumn) {
                    $update['admin_locked'] = true;
                }
                if ($update !== []) {
                    $activity->update($update);
                }
            });

            return redirect()->route('coding.activities.manage')->with('ok', 'Etkinlik sadece hesabinizdan kaldirildi.');
        }

        DB::transaction(function () use ($activity): void {
            $assignment = DailyActivityAssignment::query()
                ->where('coding_activity_id', $activity->id)
                ->whereDate('assignment_date', Carbon::today('Europe/Istanbul'))
                ->where('target_role', 'student')
                ->first();

            $this->purgeUnfinishedStudentDailyCodingTraces(
                [$activity->id],
                $this->studentUserIdsForAssignmentScope($activity, $assignment)
            );

            DailyActivityAssignment::query()->where('coding_activity_id', $activity->id)->delete();
            $activity->questions()->each(function (ActivityQuestion $question): void {
                $question->options()->delete();
                $question->delete();
            });
            $activity->delete();
        });

        return redirect()->route('coding.activities.manage')->with('ok', 'Etkinlik silindi.');
    }

    public function assignToday(CodingActivity $activity): RedirectResponse
    {
        $data = request()->validate([
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
        ]);
        $classIds = $this->normalizeAssignableClassIds($data['class_ids'] ?? []);
        if ($classIds === []) {
            return back()->with('error', 'Lutfen en az bir sinif secin.');
        }

        if (! $this->classesAreAssignableByCurrentUser($classIds)) {
            return back()->with('error', 'Secilen siniflar icin yetkiniz yok.');
        }

        DailyActivityAssignment::updateOrCreate(
            ['assignment_date' => Carbon::today('Europe/Istanbul')->toDateString(), 'target_role' => 'student'],
            [
                'coding_activity_id' => $activity->id,
                'assigned_by' => auth()->id(),
                'target_class_ids' => $classIds,
            ]
        );

        if ($this->canUseTeacherColumn()) {
            $activity->forceFill(['teacher_id' => $activity->teacher_id ?: null])->save();
        }

        return back()->with('ok', 'Bugunun etkinligi secilen siniflara atandi.');
    }

    public function unassignToday(CodingActivity $activity): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('teacher'), 403);

        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        $isTeacherOwned = $this->activityIsTeacherOwned($activity);

        if ($user?->hasRole('admin') && $isTeacherOwned) {
            return back()->with('error', '??retmene atanm?? g?nl?k ?al??ma sadece ilgili ??retmen taraf?ndan geri al?nabilir.');
        }

        if ($user?->hasRole('teacher') && $teacherId > 0 && (int) ($activity->teacher_id ?? 0) !== $teacherId) {
            return back()->with('error', 'Bu g?nl?k ?al??may? geri alma yetkiniz yok.');
        }

        $today = Carbon::today('Europe/Istanbul')->toDateString();
        $assignment = DailyActivityAssignment::query()
            ->whereDate('assignment_date', $today)
            ->where('target_role', 'student')
            ->where('coding_activity_id', $activity->id)
            ->first();

        if (! $assignment) {
            return back()->with('error', 'Bu etkinlik i?in aktif ??renci atamas? bulunamad?.');
        }

        $targetUserIds = $this->studentUserIdsForAssignmentScope($activity, $assignment);

        DB::transaction(function () use ($activity, $assignment, $targetUserIds): void {
            $this->purgeUnfinishedStudentDailyCodingTraces([$activity->id], $targetUserIds);
            $assignment->delete();
        });

        return back()->with('ok', 'Etkinlik ??rencilerden geri al?nd?.');
    }

    public function exportAll(): StreamedResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $activities = CodingActivity::with(['questions.options'])->latest()->get();
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'activities' => $activities->map(fn (CodingActivity $activity) => $this->serializeActivity($activity))->values()->all(),
        ];

        $filename = 'gunluk-calismalar-' . now()->format('Ymd-His') . '.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function export(CodingActivity $activity): StreamedResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $activity->loadMissing('questions.options');
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'activity' => $this->serializeActivity($activity),
        ];

        $filename = Str::slug($activity->title) . '-gunluk-calisma.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('teacher'), 403);

        $request->validate([
            'activity_json' => ['required'],
            'activity_json.*' => ['file', 'mimes:json,txt', 'max:65536'],
        ]);

        $files = $request->file('activity_json');
        if (!is_array($files)) {
            $files = [$files];
        }

        $created = [];
        foreach ($files as $file) {
            $raw = (string) file_get_contents($file->getRealPath());
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            $rows = [];
            if (isset($decoded['activity']) && is_array($decoded['activity'])) {
                $rows[] = $decoded['activity'];
            } elseif (isset($decoded['activities']) && is_array($decoded['activities'])) {
                $rows = array_values(array_filter($decoded['activities'], fn ($x) => is_array($x)));
            } elseif (array_is_list($decoded)) {
                $rows = array_values(array_filter($decoded, fn ($x) => is_array($x)));
            } else {
                $rows[] = $decoded;
            }

            foreach ($rows as $row) {
                $activityData = is_array($row) ? $row : [];
                $title = trim((string) ($activityData['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $importTeacherId = null;
                if ($this->canUseTeacherColumn()) {
                    $teacherId = (int) ($activityData['teacher_id'] ?? 0);
                    if (auth()->user()?->hasRole('teacher')) {
                        $importTeacherId = (int) (optional(auth()->user()?->teacher)->id ?? 0);
                    } elseif ($teacherId > 0 && Teacher::query()->whereKey($teacherId)->exists()) {
                        $importTeacherId = $teacherId;
                    }
                }

                $activity = CodingActivity::create([
                    'created_by' => auth()->id(),
                    'title' => $title,
                    'type' => (string) ($activityData['type'] ?? 'daily_task'),
                    'instruction' => $activityData['instruction'] ?? null,
                    'lesson_pages' => array_values(array_filter((array) ($activityData['lesson_pages'] ?? []), fn ($v) => $v !== null && $v !== '')),
                    'base_xp' => (int) ($activityData['base_xp'] ?? 20),
                    'is_active' => (bool) ($activityData['is_active'] ?? true),
                    'is_random_pool' => (bool) ($activityData['is_random_pool'] ?? true),
                    'teacher_id' => $importTeacherId,
                    'admin_locked' => (bool) ($activityData['admin_locked'] ?? false),
                ]);

                $this->syncQuestions($activity, (array) ($activityData['questions'] ?? []));
                $created[] = $activity->id;
            }
        }

        if ($created === []) {
            return back()->with('error', 'Yuklenen dosyalarda gecerli gunluk calisma bulunamadi.');
        }

        return back()->with('ok', count($created) . ' gunluk calisma yuklendi.');
    }

    public function destroyAll(): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        DB::transaction(function (): void {
            $deletableIds = CodingActivity::query()
                ->when($this->canUseTeacherColumn(), fn ($q) => $q->whereNull('teacher_id'))
                ->when($this->canUseAdminLockColumn(), fn ($q) => $q->where(function ($inner): void {
                    $inner->whereNull('admin_locked')->orWhere('admin_locked', false);
                }))
                ->pluck('id')
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all();

            if ($deletableIds === []) {
                return;
            }

            $this->purgeUnfinishedStudentDailyCodingTraces($deletableIds);
            DailyActivityAssignment::query()->whereIn('coding_activity_id', $deletableIds)->delete();
            ActivityQuestion::query()->whereIn('coding_activity_id', $deletableIds)->delete();
            CodingActivity::query()->whereIn('id', $deletableIds)->delete();
        });

        return redirect()->route('coding.activities.manage')->with('ok', 'Tum gunluk calismalar silindi.');
    }

    public function assignTeacherBulk(Request $request): RedirectResponse
    {
        abort_unless($this->canUseTeacherColumn(), 404);

        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'activity_ids' => ['required', 'array', 'min:1'],
            'activity_ids.*' => ['integer', 'exists:coding_activities,id'],
        ]);

        $activityIds = collect($data['activity_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $update = [];
        if ($this->canUseTeacherColumn()) {
            $update['teacher_id'] = (int) $data['teacher_id'];
        }
        if ($this->canUseAdminLockColumn()) {
            $update['admin_locked'] = true;
        }
        CodingActivity::query()->whereIn('id', $activityIds)->update($update);

        return redirect()->route('coding.activities.manage')->with('ok', count($activityIds) . ' günlük çalışma öğretmene atandı.');
    }

    public function unassignTeacherBulk(Request $request): RedirectResponse
    {
        abort_unless($this->canUseTeacherColumn(), 404);

        $data = $request->validate([
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'activity_ids' => ['required', 'array', 'min:1'],
            'activity_ids.*' => ['integer', 'exists:coding_activities,id'],
        ]);

        $teacherId = (int) ($data['teacher_id'] ?? 0);
        if ($teacherId <= 0) {
            $teacherId = (int) (optional($request->user()?->teacher)->id ?? 0);
        }
        abort_if($teacherId <= 0, 422, 'Oğretmen seçimi bulunamadı.');

        $activityIds = collect($data['activity_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $updated = 0;

        DB::transaction(function () use ($activityIds, $teacherId, &$updated): void {
            $activities = CodingActivity::query()
                ->whereIn('id', $activityIds)
                ->where('teacher_id', $teacherId)
                ->get();

            foreach ($activities as $activity) {
                if ($this->canUseTeacherColumn()) {
                    $activity->forceFill(['teacher_id' => null])->save();
                }
                if ($this->canUseAdminLockColumn()) {
                    $activity->forceFill(['admin_locked' => false])->save();
                }
                $updated++;
            }
        });

        return redirect()->route('coding.activities.manage')->with('ok', $updated . ' günlük çalışma öğretmen ataması kaldırıldı.');
    }

    public function assignClassesBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_ids' => ['required', 'array', 'min:1'],
            'activity_ids.*' => ['integer', 'exists:coding_activities,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
        ]);

        $activityIds = collect($data['activity_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $classIds = $this->normalizeAssignableClassIds($data['class_ids'] ?? []);
        if ($classIds === []) {
            return back()->with('error', 'Lutfen en az bir sinif secin.');
        }

        if (! $this->classesAreAssignableByCurrentUser($classIds)) {
            return back()->with('error', 'Secilen siniflar icin yetkiniz yok.');
        }

        foreach ($activityIds as $activityId) {
            $activity = CodingActivity::query()->find($activityId);
            if (! $activity) {
                continue;
            }

            DailyActivityAssignment::updateOrCreate(
                ['assignment_date' => Carbon::today('Europe/Istanbul')->toDateString(), 'target_role' => 'student', 'coding_activity_id' => $activity->id],
                [
                    'assigned_by' => auth()->id(),
                    'target_class_ids' => $classIds,
                ]
            );
        }

        return redirect()->route('coding.activities.manage')->with('ok', count($activityIds) . ' günlük çalışma seçilen sınıflara atandı.');
    }

    public function unassignClassesBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_ids' => ['required', 'array', 'min:1'],
            'activity_ids.*' => ['integer', 'exists:coding_activities,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
        ]);

        $activityIds = collect($data['activity_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $classIds = $this->normalizeAssignableClassIds($data['class_ids'] ?? []);
        $today = Carbon::today('Europe/Istanbul')->toDateString();
        $updated = 0;

        DB::transaction(function () use ($activityIds, $classIds, $today, &$updated): void {
            foreach ($activityIds as $activityId) {
                $assignment = DailyActivityAssignment::query()
                    ->whereDate('assignment_date', $today)
                    ->where('target_role', 'student')
                    ->where('coding_activity_id', $activityId)
                    ->first();

                if (! $assignment) {
                    continue;
                }

                $currentClassIds = collect((array) ($assignment->target_class_ids ?? []))
                    ->map(fn ($value) => (int) $value)
                    ->unique()
                    ->values()
                    ->all();

                $remaining = array_values(array_diff($currentClassIds, $classIds));
                if ($remaining === []) {
                    $assignment->delete();
                } else {
                    $assignment->forceFill(['target_class_ids' => $remaining])->save();
                }
                $updated++;
            }
        });

        return redirect()->route('coding.activities.manage')->with('ok', $updated . ' günlük çalışma-sınıf ataması kaldırıldı.');
    }

    private function purgeUnfinishedStudentDailyCodingTraces(array $activityIds, ?array $targetUserIds = null): void
    {
        $activityIds = array_values(array_unique(array_map('intval', $activityIds)));
        if ($activityIds === []) {
            return;
        }

        $targetUserIds = $targetUserIds !== null
            ? array_values(array_unique(array_map('intval', $targetUserIds)))
            : Student::query()
                ->pluck('user_id')
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0)
                ->values()
                ->all();

        if ($targetUserIds === []) {
            return;
        }

        $completedPairs = UserXpLog::query()
            ->whereIn('coding_activity_id', $activityIds)
            ->whereIn('user_id', $targetUserIds)
            ->get(['user_id', 'coding_activity_id'])
            ->map(fn (UserXpLog $log) => ((int) $log->user_id) . ':' . ((int) $log->coding_activity_id))
            ->unique()
            ->values()
            ->all();

        foreach ($activityIds as $activityId) {
            foreach ($targetUserIds as $userId) {
                if (in_array($userId . ':' . $activityId, $completedPairs, true)) {
                    continue;
                }

                ActivityAttempt::query()
                    ->where('coding_activity_id', $activityId)
                    ->where('user_id', $userId)
                    ->delete();

                DB::table('leaderboards')
                    ->where('coding_activity_id', $activityId)
                    ->where('user_id', $userId)
                    ->delete();

                StudentReport::query()
                    ->where('user_id', $userId)
                    ->get()
                    ->each(function (StudentReport $report) use ($activityId): void {
                        $meta = (array) ($report->meta ?? []);
                        $currentLogs = (array) ($meta['dailyCodingLogs'] ?? []);
                        $filteredLogs = array_values(array_filter($currentLogs, function ($log) use ($activityId) {
                            return (int) data_get($log, 'activity_id', 0) !== $activityId;
                        }));

                        if ($filteredLogs !== $currentLogs) {
                            $meta['dailyCodingLogs'] = $filteredLogs;
                            $report->meta = $meta;
                            $report->save();
                        }
                    });
            }
        }
    }

    private function studentUserIdsForAssignmentScope(CodingActivity $activity, ?DailyActivityAssignment $assignment = null): array
    {
        $targetClassIds = collect((array) ($assignment?->target_class_ids ?? []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        if ($targetClassIds !== []) {
            return Student::query()
                ->whereIn('school_class_id', $targetClassIds)
                ->pluck('user_id')
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0)
                ->values()
                ->all();
        }

        $teacherId = (int) ($activity->teacher_id ?? 0);
        if ($teacherId <= 0) {
            return Student::query()
                ->pluck('user_id')
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0)
                ->values()
                ->all();
        }

        $teacher = Teacher::query()->find($teacherId);
        if (! $teacher) {
            return [];
        }

        $classIds = $teacher->classes()
            ->pluck('school_classes.id')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();

        if ($classIds === []) {
            return [];
        }

        return Student::query()
            ->whereIn('school_class_id', $classIds)
            ->pluck('user_id')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();
    }

    private function normalizeAssignableClassIds(array $classIds): array
    {
        return collect($classIds)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function availableClassesForCurrentUser($user)
    {
        return SchoolClass::query()
            ->with('teacher.user')
            ->when($user?->hasRole('teacher') && ! $user?->hasRole('admin'), function ($query) use ($user): void {
                $teacherId = (int) (optional($user?->teacher)->id ?? 0);
                if ($teacherId > 0) {
                    $query->where('teacher_id', $teacherId);
                }
            })
            ->orderByRaw('COALESCE(grade_level, 0) asc')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    private function classesAreAssignableByCurrentUser(array $classIds): bool
    {
        $user = auth()->user();
        if ($user?->hasRole('admin')) {
            return true;
        }

        if (! $user?->hasRole('teacher')) {
            return false;
        }

        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        if ($teacherId <= 0 || $classIds === []) {
            return false;
        }

        $allowed = SchoolClass::query()
            ->where('teacher_id', $teacherId)
            ->whereIn('id', $classIds)
            ->count();

        return $allowed === count($classIds);
    }

    private function activityIsTeacherOwned(CodingActivity $activity): bool
    {
        return (int) ($activity->teacher_id ?? 0) > 0;
    }

    private function serializeActivity(CodingActivity $activity): array
    {
        $questions = $activity->questions->map(function (ActivityQuestion $question) {
            return [
                'prompt' => $question->prompt,
                'question_type' => $question->question_type,
                'points' => $question->points,
                'answer' => data_get($question->answer_key, 'answer', ''),
                'options' => $question->options->pluck('label')->values()->all(),
                'correct_options' => $question->options->where('is_correct', true)->pluck('option_key')->values()->all(),
            ];
        })->values()->all();

        return [
            'title' => $activity->title,
            'type' => $activity->type,
            'instruction' => $activity->instruction,
            'lesson_pages' => array_values((array) $activity->lesson_pages),
            'base_xp' => $activity->base_xp,
            'is_active' => $activity->is_active,
            'is_random_pool' => $activity->is_random_pool,
            'teacher_id' => $activity->teacher_id,
            'admin_locked' => $activity->admin_locked ?? false,
            'questions' => $questions,
        ];
    }

    private function syncQuestions(CodingActivity $activity, array $questions): void
    {
        foreach ($questions as $index => $q) {
            if (empty($q['prompt']) || empty($q['question_type'])) {
                continue;
            }

            $question = ActivityQuestion::create([
                'coding_activity_id' => $activity->id,
                'question_type' => $q['question_type'],
                'prompt' => $q['prompt'],
                'points' => (int) ($q['points'] ?? 10),
                'order_no' => $index + 1,
                'answer_key' => $q['question_type'] === 'multi_choice'
                    ? ['correct' => array_values($q['correct_options'] ?? [])]
                    : ($q['question_type'] === 'single_choice'
                        ? [
                            'correct' => array_values($q['correct_options'] ?? []),
                            'answer' => (string) (collect(array_values($q['options'] ?? []))
                                ->values()
                                ->map(fn ($label, $optIndex) => [
                                    'key' => chr(65 + $optIndex),
                                    'label' => (string) $label,
                                ])
                                ->firstWhere('key', (string) ($q['correct_options'][0] ?? ''))['label'] ?? ($q['answer'] ?? '')
                            ),
                        ]
                        : ['answer' => (string) ($q['answer'] ?? '')]),
            ]);

            if (in_array($q['question_type'], ['single_choice', 'multi_choice'], true)) {
                foreach (array_values($q['options'] ?? []) as $optIndex => $label) {
                    if (trim((string) $label) === '') {
                        continue;
                    }
                    QuestionOption::create([
                        'activity_question_id' => $question->id,
                        'option_key' => chr(65 + $optIndex),
                        'label' => $label,
                        'is_correct' => in_array(chr(65 + $optIndex), $q['correct_options'] ?? [], true),
                        'order_no' => $optIndex + 1,
                    ]);
                }
            }
        }
    }
}


