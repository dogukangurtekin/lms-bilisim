<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$course = App\Models\Course::query()->whereRaw("JSON_EXTRACT(lesson_payload, '$.cover_image') IS NOT NULL")->first();
if (! $course) { echo "no-course\n"; exit(1); }
echo json_encode([
    'id' => $course->id,
    'name' => $course->name,
    'cover' => data_get($course->lesson_payload, 'cover_image'),
    'cover_url' => $course->coverImageUrl(),
    'resolved' => (function($c){ $r = new ReflectionClass($c); $m = $r->getMethod('resolveCoverFilePath'); $m->setAccessible(true); return $m->invoke($c, data_get($c->lesson_payload, 'cover_image', '')); })($course),
], JSON_UNESCAPED_UNICODE) . PHP_EOL;
