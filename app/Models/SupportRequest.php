<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportRequest extends Model
{
    protected $fillable = [
        'sender_user_id',
        'guest_name',
        'guest_email',
        'source',
        'recipient_user_id',
        'subject',
        'message',
        'category',
        'priority',
        'status',
        'attachment_path',
        'read_at',
        'closed_at',
        'archived_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_user_id'); }
    public function recipient(): BelongsTo { return $this->belongsTo(User::class, 'recipient_user_id'); }
    public function replies(): HasMany { return $this->hasMany(SupportRequestReply::class)->orderBy('id'); }
}
