<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_user_id',
        'game_slug',
        'game_name',
        'school_class_id',
        'level_from',
        'level_to',
        'duration_seconds',
        'join_code',
        'is_open',
        'status',
        'started_at_ms',
        'ends_at_ms',
        'finished_at_ms',
    ];

    protected $casts = ['is_open' => 'boolean'];

    /**
     * Öğrencinin şu an katılabileceği kodsuz (is_open) açık oda: lobide/canlı, süresi dolmamış,
     * sınıf kısıtı uyuyor. Her sayfada görünen sağ alt bildirimi besler.
     */
    public static function openFor(?int $userId): ?self
    {
        return static::openListFor($userId)->first();
    }

    /** Öğrencinin katılabileceği tüm kodsuz açık odalar (en yeni önce). */
    public static function openListFor(?int $userId): \Illuminate\Support\Collection
    {
        if (! $userId) {
            return collect();
        }
        $student = Student::query()->where('user_id', $userId)->first(['id', 'school_class_id']);
        if (! $student) {
            return collect();
        }
        $nowMs = (int) floor(microtime(true) * 1000);

        return static::query()
            ->with('schoolClass')
            ->where('is_open', true)
            ->whereIn('status', ['lobby', 'live'])
            ->where(fn ($q) => $q->whereNull('school_class_id')->orWhere('school_class_id', $student->school_class_id))
            ->where(fn ($q) => $q->whereNull('ends_at_ms')->orWhere('ends_at_ms', '>', $nowMs))
            ->latest('id')
            ->get();
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class);
    }
}
