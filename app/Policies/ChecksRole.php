<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

trait ChecksRole
{
    private function isAdmin(User $user): bool
    {
        return $this->resolveRoleSlug($user) === 'admin';
    }

    private function isTeacher(User $user): bool
    {
        return $this->resolveRoleSlug($user) === 'teacher';
    }

    private function resolveRoleSlug(User $user): string
    {
        $slug = (string) ($user->role?->slug ?? '');

        if ($slug !== '') {
            return $slug;
        }

        if (! empty($user->role_id)) {
            return (string) (Role::query()->whereKey($user->role_id)->value('slug') ?? '');
        }

        return '';
    }
}
