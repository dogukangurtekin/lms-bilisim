<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\SchoolClass::query()->select('id','name','section','academic_year')->orderBy('name')->orderBy('section')->limit(50)->get() as $c) {
    echo $c->id . '|' . $c->name . '|' . $c->section . '|' . ($c->academic_year ?? 'NULL') . PHP_EOL;
}
