<?php

namespace App\Services\Course;

use Illuminate\Support\Str;

/**
 * Finds any inline `data:image/...;base64,...` blobs embedded in a course's
 * lesson_payload (typically pasted/exported images) and moves them out to
 * real files under public/ders-gorselleri/, replacing them in place with a
 * plain URL. This keeps page payloads small (a few KB instead of several MB)
 * and lets browsers cache the images across visits.
 */
class CourseImageExtractor
{
    private const PATTERN = '/data:image\/(png|jpe?g|gif|webp);base64,([A-Za-z0-9+\/=]+)/i';

    /**
     * @return array{payload: array, extracted: int, bytesRemoved: int}
     */
    public function extract(array $payload): array
    {
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($raw === false || ! str_contains($raw, 'base64,')) {
            return ['payload' => $payload, 'extracted' => 0, 'bytesRemoved' => 0];
        }

        $sizeBefore = strlen($raw);
        $extracted = 0;
        $outDir = public_path('ders-gorselleri');

        $rewritten = preg_replace_callback(self::PATTERN, function (array $m) use (&$extracted, $outDir) {
            $ext = strtolower($m[1]) === 'jpg' ? 'jpg' : strtolower($m[1]);
            $binary = base64_decode($m[2], true);
            if ($binary === false || $binary === '') {
                return $m[0];
            }

            if (! is_dir($outDir)) {
                @mkdir($outDir, 0775, true);
            }
            if (! is_dir($outDir) || ! is_writable($outDir)) {
                return $m[0];
            }

            $filename = (string) Str::uuid() . '.' . $ext;
            if (file_put_contents($outDir . '/' . $filename, $binary) === false) {
                return $m[0];
            }

            $extracted++;

            return asset('ders-gorselleri/' . $filename);
        }, $raw);

        if ($rewritten === null) {
            return ['payload' => $payload, 'extracted' => 0, 'bytesRemoved' => 0];
        }

        $decoded = json_decode($rewritten, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            // Never risk corrupting the course content; bail out untouched.
            return ['payload' => $payload, 'extracted' => 0, 'bytesRemoved' => 0];
        }

        return [
            'payload' => $decoded,
            'extracted' => $extracted,
            'bytesRemoved' => max(0, $sizeBefore - strlen($rewritten)),
        ];
    }
}
