<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::find(1);
$teacher = App\Models\Teacher::firstOrCreate(['user_id' => $user->id], ['branch' => null, 'phone' => null, 'hire_date' => null]);
echo json_encode(['teacher_id' => $teacher->id, 'user_id' => $teacher->user_id], JSON_UNESCAPED_UNICODE) . PHP_EOL;
