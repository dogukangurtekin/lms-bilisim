<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\User;

class UserObserver
{
    public function saved(User $user): void
    {
        $user->loadMissing('role');

        if (! $user->hasRole('student')) {
            return;
        }

        Student::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['student_no' => $this->generateStudentNo()]
        );
    }

    private function generateStudentNo(): string
    {
        do {
            $value = 'ST' . now()->format('ymd') . random_int(1000, 9999);
        } while (Student::query()->where('student_no', $value)->exists());

        return $value;
    }
}

