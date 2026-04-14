<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    use ChecksRole;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool { return $this->isTeacher($user); }
    public function view(User $user, Teacher $model): bool { return $this->isTeacher($user); }
    public function create(User $user): bool { return $this->isTeacher($user); }
    public function update(User $user, Teacher $model): bool { return $this->isTeacher($user); }
    public function delete(User $user, Teacher $model): bool { return $this->isTeacher($user); }
}
