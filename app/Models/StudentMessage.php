<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMessage extends Model
{
    protected $fillable = [
        'student_id', 'student_user_id', 'recipient_type', 'recipient_user_id',
        'body', 'reply_body', 'replied_by', 'replied_at', 'student_read_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'student_read_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /** Admin tüm mesajları, öğretmen yalnızca kendisine yazılanları görür. */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where('recipient_type', 'teacher')->where('recipient_user_id', $user->id);
    }

    /** Öğrencinin cevap bekleyen (cevaplanmamış) mesajı var mı? */
    public static function hasPendingFor(int $studentId): bool
    {
        return static::query()->where('student_id', $studentId)->whereNull('replied_at')->exists();
    }
}
