<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    use ChecksRole;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool { return $this->isTeacher($user); }
    public function view(User $user, Announcement $model): bool { return $this->isTeacher($user); }
    public function create(User $user): bool { return $this->isTeacher($user); }
    public function update(User $user, Announcement $model): bool { return $this->isTeacher($user); }
    public function delete(User $user, Announcement $model): bool { return $this->isTeacher($user); }
}
