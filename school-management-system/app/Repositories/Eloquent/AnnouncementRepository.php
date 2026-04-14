<?php

namespace App\Repositories\Eloquent;

use App\Models\Announcement;

class AnnouncementRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Announcement::class;
    }
}
