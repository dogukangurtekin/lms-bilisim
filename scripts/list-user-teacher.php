<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\User::with('teacher','role')->orderBy('id')->get() as $u) {
    echo json_encode(['id'=>$u->id,'email'=>$u->email,'role'=>$u->role?->slug,'teacher_id'=>$u->teacher?->id], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
