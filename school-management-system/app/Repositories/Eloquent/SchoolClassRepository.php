<?php

namespace App\Repositories\Eloquent;

use App\Models\SchoolClass;

class SchoolClassRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return SchoolClass::class;
    }
}
