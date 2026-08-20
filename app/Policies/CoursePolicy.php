<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    use ChecksRole;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool { return $this->isTeacher($user) || $this->isAdmin($user); }

    public function view(User $user, Course $model): bool
    {
        return $this->isTeacher($user) || $this->isAdmin($user) || (int) ($model->created_by ?? 0) === (int) $user->id;
    }

    public function create(User $user): bool { return $this->isTeacher($user) || $this->isAdmin($user); }

    public function update(User $user, Course $model): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isTeacher($user)) {
            $teacherId = (int) (optional($user->teacher)->id ?? 0);
            if ($teacherId > 0 && (int) ($model->teacher_id ?? 0) === $teacherId) {
                return true;
            }
        }

        return (int) ($model->created_by ?? 0) === (int) $user->id;
    }

    public function delete(User $user, Course $model): bool
    {
        return $this->update($user, $model);
    }
}
