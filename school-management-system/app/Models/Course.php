<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['name', 'code', 'teacher_id', 'school_class_id', 'weekly_hours', 'lesson_payload'];

    public function getLessonPayloadAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode((string) $value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        // Gecmiste cift encode edilen kayitlari da okuyabilmek icin.
        if (is_string($decoded)) {
            $decodedAgain = json_decode($decoded, true);
            return is_array($decodedAgain) ? $decodedAgain : [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function setLessonPayloadAttribute($value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (! is_array($value)) {
            $value = [];
        }

        $this->attributes['lesson_payload'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
