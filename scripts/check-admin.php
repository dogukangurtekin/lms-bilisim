<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@school.local')->first();

if (! $user) {
    echo "missing\n";
    exit(0);
}

echo json_encode([
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'password' => $user->password,
    'role_id' => $user->role_id,
], JSON_UNESCAPED_UNICODE) . PHP_EOL;
