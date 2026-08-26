<?php

namespace App\Services;

use App\Models\GameAssignment;
use App\Models\Student;
use App\Models\TeacherGameAssignment;

class StudentGameAccessService
{
    public function allowedSlugsForStudent(Student $student): array
    {
        $classTeacherId = (int) ($student->schoolClass?->teacher_id ?? 0);

        $teacherUnlocked = $classTeacherId > 0
            ? TeacherGameAssignment::query()
                ->where('teacher_id', $classTeacherId)
                ->pluck('game_slug')
                ->all()
            : [];

        $homeworkAssigned = $student->school_class_id
            ? GameAssignment::query()
                ->whereHas('classes', function ($query) use ($student) {
                    $query->where('school_classes.id', $student->school_class_id);
                })
                ->pluck('game_slug')
                ->all()
            : [];

        return collect(array_merge($teacherUnlocked, $homeworkAssigned))
            ->map(fn ($slug) => trim((string) $slug))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function canPlay(Student $student, string $slug): bool
    {
        if (in_array($slug, $this->allowedSlugsForStudent($student), true)) {
            return true;
        }

        // A direct homework assignment (GameAssignment -> class) also grants
        // access, independent of whether the teacher separately unlocked the
        // game for their whole class via TeacherGameAssignment.
        if (! $student->school_class_id) {
            return false;
        }

        return GameAssignment::query()
            ->where('game_slug', $slug)
            ->whereHas('classes', function ($query) use ($student) {
                $query->where('school_classes.id', $student->school_class_id);
            })
            ->exists();
    }
}
