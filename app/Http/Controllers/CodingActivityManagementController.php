<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCodingActivityRequest;
use App\Http\Requests\UpdateCodingActivityRequest;
use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\DailyActivityAssignment;
use App\Models\Teacher;
use App\Models\QuestionOption;
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
        $hasTeacherColumn = $this->canUseTeacherColumn();
        $hasAdminLockColumn = $this->canUseAdminLockColumn();
        $activities = CodingActivity::query()
            ->withCount('questions')
            ->when($user?->hasRole('teacher') && $hasTeacherColumn, fn ($query) => $query->where('teacher_id', $teacherId))
            ->latest()
            ->paginate(20);
        $todayAssignment = DailyActivityAssignment::with(['activity' => fn ($query) => $query->when($user?->hasRole('teacher'), fn ($q) => $q->where('teacher_id', $teacherId))])
            ->whereDate('assignment_date', Carbon::today('Europe/Istanbul'))
            ->where('target_role', 'student')
            ->when($user?->hasRole('teacher') && $hasTeacherColumn, fn ($query) => $query->whereHas('activity', fn ($q) => $q->where('teacher_id', $teacherId)))
            ->first();
        $selectedId = (int) request()->integer('edit');
        $editingActivity = $selectedId > 0 ? CodingActivity::with('questions.options')->find($selectedId) : null;
        $isAdmin = (bool) auth()->user()?->hasRole('admin');
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

        return view('coding-activities.manage', compact('activities', 'todayAssignment', 'editingActivity', 'isAdmin', 'teachers', 'assignableActivities'));
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

        return back()->with('ok', 'Etkinlik oluşturuldu.');
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
            return redirect()->route('coding.activities.manage')->with('ok', 'Etkinlik sadece hesabinizdan kaldirildi.');
        }

        DB::transaction(function () use ($activity): void {
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
        DailyActivityAssignment::updateOrCreate(
            ['assignment_date' => Carbon::today('Europe/Istanbul')->toDateString(), 'target_role' => 'student'],
            ['coding_activity_id' => $activity->id, 'assigned_by' => auth()->id()]
        );

        if ($this->canUseTeacherColumn()) {
            $activity->forceFill(['teacher_id' => $activity->teacher_id ?: null])->save();
        }

        return back()->with('ok', 'Bugünün etkinliği atandı.');
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
        abort_unless(auth()->user()?->hasRole('admin'), 403);

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

                $activity = CodingActivity::create([
                    'created_by' => auth()->id(),
                    'title' => $title,
                    'type' => (string) ($activityData['type'] ?? 'daily_task'),
                    'instruction' => $activityData['instruction'] ?? null,
                    'lesson_pages' => array_values(array_filter((array) ($activityData['lesson_pages'] ?? []), fn ($v) => $v !== null && $v !== '')),
                    'base_xp' => (int) ($activityData['base_xp'] ?? 20),
                    'is_active' => (bool) ($activityData['is_active'] ?? true),
                    'is_random_pool' => (bool) ($activityData['is_random_pool'] ?? true),
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
                ->pluck('id');

            if ($deletableIds->isEmpty()) {
                return;
            }

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
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        abort_unless($this->canUseTeacherColumn(), 404);

        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ]);

        $teacherId = (int) $data['teacher_id'];
        $updated = CodingActivity::query()
            ->where('teacher_id', $teacherId)
            ->update([
                'teacher_id' => null,
                'admin_locked' => false,
            ]);

        DailyActivityAssignment::query()
            ->whereHas('activity', fn ($q) => $q->where('teacher_id', $teacherId))
            ->delete();

        return redirect()->route('coding.activities.manage')->with('ok', $updated > 0
            ? 'Seçili öğretmenden tüm günlük çalışmalar kaldırıldı.'
            : 'Seçili öğretmende atanmış günlük çalışma bulunamadı.');
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
