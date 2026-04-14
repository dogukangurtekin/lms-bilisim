<?php

namespace App\Policies;

use App\Models\User;

trait ChecksRole
{
    private function isAdmin(User $user): bool
    {
        return $user->role?->slug === 'admin';
    }

    private function isTeacher(User $user): bool
    {
        return $user->role?->slug === 'teacher';
    }
}
