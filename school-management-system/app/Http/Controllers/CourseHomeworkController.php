<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ActivityController;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class CourseHomeworkController extends Controller
{
    public function create(Course $course)
    {
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $homeworks = CourseHomework::with('schoolClass')->where('course_id', $course->id)->latest()->limit(20)->get();
        $games = ActivityController::games();

        return view('courses.homeworks.create', compact('course', 'classes', 'homeworks', 'games'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'details' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
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

        return redirect()->route('courses.homeworks.create', $course)->with('ok', count($classIds) . ' sinif icin odev olusturuldu.');
    }
}
