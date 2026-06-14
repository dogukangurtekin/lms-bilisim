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
            $cover = trim((string) $decoded['cover_image']);
            $cover = str_replace('\\', '/', $cover);

            if (preg_match('#^https?://#i', $cover)) {
                if (preg_match('#/(?:course-covers|kapak-gorseli)/([^/?#]+)#i', $cover, $match)) {
                    $cover = 'kapak-gorseli/' . $match[1];
                } else {
                    $decoded['cover_image'] = $cover;
                    return $decoded;
                }
            }

            $cover = preg_replace('#^/?storage/#i', 'storage/', $cover);
            $cover = preg_replace('#^/?course-covers/#i', 'kapak-gorseli/', $cover);
            $cover = preg_replace('#^/?kapak-gorseli/#i', 'kapak-gorseli/', $cover);
            $cover = preg_replace('#^/?courses/cover/#i', 'kapak-gorseli/', $cover);
            $cover = preg_replace('#^/?courses/.*?/course-covers/#i', 'kapak-gorseli/', $cover);

            $decoded['cover_image'] = $cover;
        }

        return $decoded;
    }

    public function coverImageUrl(): string
    {
        $cover = trim((string) data_get($this->lesson_payload, 'cover_image', ''));
        if ($cover === '') {
            return '';
        }

        $cover = str_replace('\\', '/', $cover);
        if (preg_match('#^https?://#i', $cover)) {
            if (preg_match('#/(?:course-covers|kapak-gorseli)/([^/?#]+)#i', $cover, $match)) {
                $cover = 'kapak-gorseli/' . $match[1];
            } else {
                return $cover;
            }
        }
        $cover = preg_replace('#^/?storage/#i', '', $cover);
        $cover = preg_replace('#^/?course-covers/#i', '', $cover);
        $cover = preg_replace('#^/?kapak-gorseli/#i', '', $cover);
        $cover = preg_replace('#^/?courses/cover/#i', '', $cover);

        if ($cover === '') {
            return '';
        }

        $relative = 'kapak-gorseli/' . ltrim($cover, '/');
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
