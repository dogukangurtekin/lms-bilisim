<?php

namespace App\Repositories\Eloquent;

use App\Models\Grade;

class GradeRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Grade::class;
    }
}
