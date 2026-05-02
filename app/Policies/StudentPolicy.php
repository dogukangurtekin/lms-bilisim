<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class StudentPolicy
{
    use ChecksRole;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool { return $this->isTeacher($user); }
    public function create(User $user): bool { return $this->isTeacher($user); }

    public function view(User $user, Student $model): bool
    {
        return $this->teacherOwnsClass($user, (int) $model->school_class_id);
    }

    public function update(User $user, Student $model): bool
    {
        return $this->teacherOwnsClass($user, (int) $model->school_class_id);
    }

    public function delete(User $user, Student $model): bool
    {
        return $this->teacherOwnsClass($user, (int) $model->school_class_id);
    }

    private function teacherOwnsClass(User $user, int $classId): bool
    {
        if (! $this->isTeacher($user) || $classId <= 0) {
            return false;
        }

        return Teacher::query()
            ->where('user_id', $user->id)
            ->whereHas('classes', fn ($q) => $q->whereKey($classId))
            ->exists();
    }
}
