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

        return $classTeacherId > 0
            ? TeacherGameAssignment::query()
                ->where('teacher_id', $classTeacherId)
                ->pluck('game_slug')
                ->map(fn ($slug) => trim((string) $slug))
                ->filter()
                ->unique()
                ->values()
                ->all()
            : [];
    }

    public function canPlay(Student $student, string $slug): bool
    {
        return in_array($slug, $this->allowedSlugsForStudent($student), true);
    }
}
