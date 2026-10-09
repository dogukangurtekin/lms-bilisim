<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveQuizSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'live_quiz_id',
        'teacher_user_id',
        'join_code',
        'status',
        'phase',
        'current_index',
        'is_locked',
        'started_at_ms',
        'ends_at_ms',
        'finished_at_ms',
        'xp_awarded_at_ms',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LiveQuiz::class, 'live_quiz_id')->withTrashed();
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LiveQuizAnswer::class, 'live_quiz_session_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LiveQuizParticipant::class, 'live_quiz_session_id');
    }

    /**
     * Ogrencinin katildigi canli quiz oturumu sayisi. Hem katilim (participants)
     * hem cevap (answers) kayitlari sayilir; yalniz cevaplara bakmak, katilip
     * hicbir soruya cevap kaydi olusmayan ogrenciyi dusuruyordu.
     */
    public static function joinedCountForUser(int $userId): int
    {
        return (int) LiveQuizParticipant::query()
            ->where('student_user_id', $userId)
            ->select('live_quiz_session_id')
            ->union(
                LiveQuizAnswer::query()
                    ->where('student_user_id', $userId)
                    ->select('live_quiz_session_id')
            )
            ->get()
            ->unique('live_quiz_session_id')
            ->count();
    }
}
