<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;

class AttendanceRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Attendance::class;
    }
}
