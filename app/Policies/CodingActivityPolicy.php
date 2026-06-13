<?php
namespace App\Policies;
use App\Models\CodingActivity;
use App\Models\User;
class CodingActivityPolicy
{
    public function view(User $user, CodingActivity $codingActivity): bool
    {
        return $user->hasRole('student');
    }

    public function attempt(User $user, CodingActivity $codingActivity): bool
    {
        return $user->hasRole('student') && (bool) $codingActivity->is_active;
    }
}
