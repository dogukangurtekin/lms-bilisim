<?php

// Bulk version of the one-course test: walks every course, and for any
// lesson_payload still containing inline base64 images, extracts them to
// public/ders-gorselleri/ and rewrites the JSON. Safe to re-run — courses
// with no base64 content are left untouched (no write happens).
//
// Usage: php artisan tinker --execute="require base_path('scripts/extract_all_course_images.php');"

$extractor = app(\App\Services\Course\CourseImageExtractor::class);

$total = 0;
$changed = 0;
$imagesExtracted = 0;
$bytesRemoved = 0;
$errors = [];

App\Models\Course::query()->chunkById(20, function ($courses) use ($extractor, &$total, &$changed, &$imagesExtracted, &$bytesRemoved, &$errors) {
    foreach ($courses as $course) {
        $total++;
        $payload = $course->lesson_payload;
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($raw === false || ! str_contains($raw, 'base64,')) {
            continue;
        }

        try {
            $result = $extractor->extract($payload);
            if ($result['extracted'] > 0) {
                $course->lesson_payload = $result['payload'];
                $course->timestamps = false; // don't disturb updated_at for a pure cleanup pass
                $course->save();
                $changed++;
                $imagesExtracted += $result['extracted'];
                $bytesRemoved += $result['bytesRemoved'];
                echo "Course {$course->id} ({$course->name}): {$result['extracted']} images, " . round($result['bytesRemoved'] / 1024) . " KB removed\n";
            }
        } catch (\Throwable $e) {
            $errors[] = "Course {$course->id}: " . $e->getMessage();
            echo "  ERROR on course {$course->id}: " . $e->getMessage() . "\n";
        }
    }
});

echo "\n--- Summary ---\n";
echo "Courses scanned: {$total}\n";
echo "Courses changed: {$changed}\n";
echo "Images extracted: {$imagesExtracted}\n";
echo "Total bytes removed: " . round($bytesRemoved / 1024 / 1024, 2) . " MB\n";
if ($errors !== []) {
    echo "Errors: " . count($errors) . "\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
}
