<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportRequestReply extends Model
{
    protected $fillable = [
        'support_request_id',
        'sender_user_id',
        'message',
        'internal_note',
    ];

    protected $casts = [
        'internal_note' => 'boolean',
    ];

    public function request(): BelongsTo { return $this->belongsTo(SupportRequest::class, 'support_request_id'); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_user_id'); }
}
