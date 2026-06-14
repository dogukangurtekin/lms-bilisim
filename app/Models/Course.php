<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['name', 'code', 'teacher_id', 'created_by', 'school_class_id', 'weekly_hours', 'lesson_payload'];

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
            $decoded = is_array($decodedAgain) ? $decodedAgain : [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        if (!empty($decoded['cover_image']) && is_string($decoded['cover_image'])) {
            $decoded['cover_image'] = $this->normalizeCoverPath((string) $decoded['cover_image']);
        }

        return $decoded;
    }

    public function coverImageUrl(): string
    {
        $cover = $this->normalizeCoverPath((string) data_get($this->lesson_payload, 'cover_image', ''));
        if ($cover === '') {
            return '';
        }

        $cover = preg_replace('#^(?:kapak-gorseli/)+#i', 'kapak-gorseli/', $cover) ?? $cover;
        $cover = preg_replace('#^(?:course-covers/)+#i', 'kapak-gorseli/', $cover) ?? $cover;

        $relative = 'kapak-gorseli/' . ltrim($cover, '/');
        $relative = preg_replace('#^(?:kapak-gorseli/)+#i', 'kapak-gorseli/', $relative) ?? $relative;
        $publicPath = public_path($relative);
        if (is_file($publicPath)) {
            return asset($relative);
        }

        $storageFallbacks = [
            storage_path('app/public/' . $relative),
            storage_path('app/public/' . preg_replace('/\.png$/i', '.webp', $relative)),
            storage_path('app/public/course-covers/' . basename($relative)),
        ];

        foreach ($storageFallbacks as $fallbackPath) {
            if (is_file($fallbackPath)) {
                return route('courses.cover', ['path' => basename(dirname($fallbackPath)) . '/' . basename($fallbackPath)]);
            }
        }

        return asset($relative);
    }

    private function normalizeCoverPath(string $cover): string
    {
        $cover = trim(str_replace('\\', '/', $cover));
        if ($cover === '') {
            return '';
        }

        if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
            $path = (string) parse_url($cover, PHP_URL_PATH);
            $cover = trim($path, '/');
        }

        $cover = ltrim($cover, '/');
        $cover = preg_replace('#^storage/#i', '', $cover) ?? $cover;

        if (str_starts_with($cover, 'course-covers/')) {
            $cover = 'kapak-gorseli/' . substr($cover, strlen('course-covers/'));
        } elseif (str_starts_with($cover, 'kapak-gorseli/')) {
            $cover = $cover;
        } elseif (str_starts_with($cover, 'courses/cover/')) {
            $cover = 'kapak-gorseli/' . substr($cover, strlen('courses/cover/'));
        } elseif (str_contains($cover, '/course-covers/')) {
            $cover = 'kapak-gorseli/' . basename($cover);
        }

        $cover = preg_replace('#^(?:kapak-gorseli/)+#i', 'kapak-gorseli/', $cover) ?? $cover;

        return ltrim($cover, '/');
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
