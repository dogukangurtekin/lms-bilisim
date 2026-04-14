<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;

class TeacherRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Teacher::class;
    }
}
