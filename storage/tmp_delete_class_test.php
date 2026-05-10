<?php
$base = dirname(__DIR__);
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$countBefore = App\Models\SchoolClass::query()->count();
$c = App\Models\SchoolClass::query()->orderByDesc('id')->first();
if (!$c) { echo "NO_CLASS\n"; exit; }
$id = $c->id;
$deleted = App\Models\SchoolClass::query()->whereKey($id)->delete();
$countAfter = App\Models\SchoolClass::query()->count();
echo "DELETE_FROM_TEST_ID={$id}\n";
echo "DELETED={$deleted}\n";
echo "COUNT_BEFORE={$countBefore}\n";
echo "COUNT_AFTER={$countAfter}\n";
