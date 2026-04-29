<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherClassAssignmentController extends Controller
{
    public function edit(Teacher $teacher)
    {
        $classes = SchoolClass::query()->orderBy('grade_level')->orderBy('name')->orderBy('section')->get();
        $assignedClassIds = $classes->where('teacher_id', $teacher->id)->pluck('id')->all();
        $levels = $classes->pluck('grade_level')->unique()->sort()->values();
        return view('users.assign-classes', compact('teacher', 'classes', 'assignedClassIds', 'levels'));
    }

    public function assignByLevel(Request $request, Teacher $teacher)
    {
        $data = $request->validate(['grade_level' => ['required', 'integer', 'between:1,12']]);
        SchoolClass::query()->where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
        SchoolClass::query()->where('grade_level', (int) $data['grade_level'])->update(['teacher_id' => $teacher->id]);
        return back()->with('ok', 'Kademe bazli sinif atamasi guncellendi.');
    }

    public function assignByClasses(Request $request, Teacher $teacher)
    {
        $data = $request->validate(['class_ids' => ['array'], 'class_ids.*' => ['integer', 'exists:school_classes,id']]);
        $ids = collect($data['class_ids'] ?? [])->map(fn ($v) => (int) $v)->unique()->values()->all();
        SchoolClass::query()->where('teacher_id', $teacher->id)->whereNotIn('id', $ids)->update(['teacher_id' => null]);
        if (!empty($ids)) {
            SchoolClass::query()->whereIn('id', $ids)->update(['teacher_id' => $teacher->id]);
        }
        return back()->with('ok', 'Sinif bazli atama guncellendi.');
    }
}

