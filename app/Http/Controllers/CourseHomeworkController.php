<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ActivityController;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseHomeworkController extends Controller
{
    public function __construct(private PushNotificationService $pushService)
    {
    }

    private function assignedClassIds(): ?array
    {
        $user = auth()->user();
        if ($user?->hasRole('admin')) {
            return null;
        }

        $teacher = $user?->teacher;
        abort_unless($user?->hasRole('teacher') && $teacher, 403);

        return $teacher->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all();
    }

    public function create(Course $course)
    {
        $assignedClassIds = $this->assignedClassIds();
        $classes = SchoolClass::query()
            ->when($assignedClassIds !== null, fn ($query) => $query->whereIn('id', $assignedClassIds))
            ->orderBy('name')
            ->orderBy('section')
            ->get();
        $homeworks = CourseHomework::with('schoolClass')->where('course_id', $course->id)->latest()->limit(20)->get();
        $games = ActivityController::games();

        return view('courses.homeworks.create', compact('course', 'classes', 'homeworks', 'games'));
    }

    public function store(Request $request, Course $course)
    {
        $assignedClassIds = $this->assignedClassIds();
        $classIdRule = Rule::exists('school_classes', 'id');
        if ($assignedClassIds !== null) {
            $classIdRule->whereIn('id', $assignedClassIds);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'details' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', $classIdRule],
            'assignment_type' => ['required', 'in:lesson,game,application'],
            'target_slug' => ['nullable', 'string', 'max:120'],
            'level_from' => ['nullable', 'integer', 'min:1'],
            'level_to' => ['nullable', 'integer', 'min:1', 'gte:level_from'],
            'level_points' => ['nullable', 'array'],
            'level_points.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $points = [];
        if (! empty($validated['level_points']) && isset($validated['level_from'], $validated['level_to'])) {
            for ($lvl = (int) $validated['level_from']; $lvl <= (int) $validated['level_to']; $lvl++) {
                $points[(string) $lvl] = (int) ($validated['level_points'][$lvl] ?? 0);
            }
        }

        $classIds = array_values(array_unique(array_map('intval', $validated['class_ids'])));

        foreach ($classIds as $classId) {
            CourseHomework::create([
                'course_id' => $course->id,
                'school_class_id' => $classId,
                'assignment_type' => $validated['assignment_type'],
                'target_slug' => $validated['target_slug'] ?? null,
                'title' => $validated['title'],
                'details' => $validated['details'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'level_from' => $validated['level_from'] ?? null,
                'level_to' => $validated['level_to'] ?? null,
                'level_points' => $points !== [] ? $points : null,
                'created_by' => auth()->id(),
            ]);
        }

        $studentUserIds = Student::query()
            ->whereIn('school_class_id', $classIds)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($x) => (int) $x)
            ->all();

        $classNames = SchoolClass::query()
            ->whereIn('id', $classIds)
            ->orderBy('name')->orderBy('section')
            ->get()
            ->map(fn ($c) => trim($c->name . ' ' . ($c->section ?? '')))
            ->implode(', ');

        $this->pushService->notifyAssignment(
            $studentUserIds,
            'assignment_created',
            'Yeni Odev Eklendi',
            $validated['title'],
            url('/ogrenci/odevlerim'),
            'Yeni Odev Atandi',
            sprintf('%s sinifina "%s" dersi icin "%s" odevi atandi (%d ogrenci).', $classNames, $course->name, $validated['title'], count($studentUserIds)),
            url('/odevler'),
            ['course_id' => $course->id]
        );

        return redirect()->route('courses.homeworks.create', $course)->with('ok', count($classIds) . ' sinif icin odev olusturuldu.');
    }
}
