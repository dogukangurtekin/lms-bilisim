<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;

class StudentRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Student::class;
    }
}
