<?php

namespace App\Repositories\Eloquent;

use App\Models\User;

class UserRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return User::class;
    }
}
