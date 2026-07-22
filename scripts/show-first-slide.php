<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$course = App\Models\Course::query()->orderByDesc('id')->first();
$payload = (array) ($course?->lesson_payload ?? []);
echo json_encode($payload['slides'][0] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
?>
