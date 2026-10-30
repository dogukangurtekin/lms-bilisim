<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScratchProject extends Model
{
    protected $fillable = ['user_id', 'name', 'data', 'size', 'client_ts'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
