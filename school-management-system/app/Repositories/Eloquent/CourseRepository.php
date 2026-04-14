<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Course::class;
    }
}
