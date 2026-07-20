<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@school.local')->first();
if (! $user) {
    echo "missing\n";
    exit(1);
}

$candidates = ['123456', 'Admin1234!', 'admin1234!', 'password', 'Admin123456'];

foreach ($candidates as $candidate) {
    $ok = Illuminate\Support\Facades\Hash::check($candidate, $user->password);
    echo $candidate . ':' . ($ok ? 'yes' : 'no') . PHP_EOL;
}
