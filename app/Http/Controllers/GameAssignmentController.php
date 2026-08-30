<?php

namespace App\Http\Controllers;

use App\Models\TeacherGameAssignment;
use App\Models\GameAssignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GameAssignmentController extends Controller
{
    public function __construct(private PushNotificationService $pushService)
    {
    }

    public function create(string $gameSlug)
    {
        $user = auth()->user();
        $isAdmin = (bool) $user?->hasRole('admin');
        $isTeacher = (bool) $user?->hasRole('teacher');
        if (! $isAdmin) {
            abort_unless(
                $isTeacher
                && $user?->teacher
                && TeacherGameAssignment::query()
                    ->where('teacher_id', $user->teacher->id)
                    ->where('game_slug', $gameSlug)
                    ->exists(),
                403
            );
        }
        $games = ActivityController::games();
        abort_unless(isset($games[$gameSlug]), 404);

        $ownerFilter = (string) request()->query('owner', $isAdmin ? 'admin' : 'teacher');
        $ownerFilter = in_array($ownerFilter, ['admin', 'teacher', 'all'], true) ? $ownerFilter : ($isAdmin ? 'admin' : 'teacher');
        $classFilterId = (int) request()->query('class_id', 0);
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $recentAssignments = GameAssignment::with(['classes', 'levels', 'creator.role'])
            ->where('game_slug', $gameSlug)
            ->when(! $isAdmin, function ($query) use ($user) {
                $query->where('created_by', $user?->id);
            })
            ->when($classFilterId > 0, function ($query) use ($classFilterId) {
                $query->whereHas('classes', fn ($classQuery) => $classQuery->where('school_classes.id', $classFilterId));
            })
            ->when($isAdmin && $ownerFilter === 'admin', function ($query) {
                $query->whereHas('creator.role', fn ($roleQuery) => $roleQuery->where('slug', 'admin'));
            })
            ->when($isAdmin && $ownerFilter === 'teacher', function ($query) {
                $query->whereHas('creator.role', fn ($roleQuery) => $roleQuery->where('slug', 'teacher'));
            })
            ->latest()
            ->limit(10)
            ->get();

        $ownerLabels = [
            'admin' => 'Admin ödevleri',
            'teacher' => 'Öğretmen ödevleri',
            'all' => 'Tüm ödevler',
        ];

        return view('activities.assignments.create', [
            'gameSlug' => $gameSlug,
            'game' => $games[$gameSlug],
            'classes' => $classes,
            'recentAssignments' => $recentAssignments,
            'ownerFilter' => $ownerFilter,
            'ownerLabels' => $ownerLabels,
        ]);
    }

    public function store(Request $request, string $gameSlug)
    {
        $user = auth()->user();
        $isAdmin = (bool) $user?->hasRole('admin');
        $isTeacher = (bool) $user?->hasRole('teacher');
        if (! $isAdmin) {
            abort_unless(
                $isTeacher
                && $user?->teacher
                && TeacherGameAssignment::query()
                    ->where('teacher_id', $user->teacher->id)
                    ->where('game_slug', $gameSlug)
                    ->exists(),
                403
            );
        }
        $games = ActivityController::games();
        abort_unless(isset($games[$gameSlug]), 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'level_from' => ['nullable', 'integer', 'min:1', 'max:999'],
            'level_to' => ['nullable', 'integer', 'min:1', 'max:999', 'gte:level_from'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', Rule::exists('school_classes', 'id')],
        ]);

        // Ogretmenin sayfada tek tek level puani girmesine gerek yok;
        // her level icin sabit bir varsayilan puan atanir.
        $defaultLevelPoints = 10;

        DB::transaction(function () use ($validated, $games, $gameSlug, $defaultLevelPoints) {
            $assignment = GameAssignment::create([
                'game_slug' => $gameSlug,
                'game_name' => $games[$gameSlug]['name'],
                'title' => $validated['title'],
                'due_date' => $validated['due_date'] ?? null,
                'level_from' => $validated['level_from'] ?? null,
                'level_to' => $validated['level_to'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $assignment->classes()->sync($validated['class_ids']);

            $levelFrom = $validated['level_from'] ?? null;
            $levelTo = $validated['level_to'] ?? null;
            if ($levelFrom !== null && $levelTo !== null) {
                for ($level = $levelFrom; $level <= $levelTo; $level++) {
                    $assignment->levels()->create([
                        'level' => $level,
                        'points' => $defaultLevelPoints,
                    ]);
                }
            }
        });

        $studentUserIds = Student::query()
            ->whereIn('school_class_id', $validated['class_ids'])
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($x) => (int) $x)
            ->all();

        $classNames = SchoolClass::query()
            ->whereIn('id', $validated['class_ids'])
            ->orderBy('name')->orderBy('section')
            ->get()
            ->map(fn ($c) => trim($c->name . ' ' . ($c->section ?? '')))
            ->implode(', ');

        $this->pushService->notifyAssignment(
            $studentUserIds,
            'assignment_created',
            'Yeni Etkinlik Ödevi',
            $games[$gameSlug]['name'] . ' - ' . $validated['title'],
            url($games[$gameSlug]['url']),
            'Yeni Etkinlik Ödevi Atandı',
            sprintf('%s sınıfına "%s" (%s) ödevi atandı (%d öğrenci).', $classNames, $validated['title'], $games[$gameSlug]['name'], count($studentUserIds)),
            url('/odevler'),
            ['game_slug' => $gameSlug]
        );

        return redirect()
            ->route('activities.assignments.create', $gameSlug)
            ->with('ok', 'Odev basariyla olusturuldu.');
    }
}
