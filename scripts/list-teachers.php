<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'teachers=' . App\Models\Teacher::count() . PHP_EOL;
foreach (App\Models\Teacher::query()->with('user:id,name,email')->orderBy('id')->limit(5)->get() as $t) {
    echo json_encode(['id'=>$t->id,'user_id'=>$t->user_id,'user'=>$t->user?->email], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
