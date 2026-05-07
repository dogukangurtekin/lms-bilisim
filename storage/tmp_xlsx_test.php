<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ref = new ReflectionClass(App\Http\Controllers\UserManagementController::class);
$controller = $ref->newInstanceWithoutConstructor();
$method = $ref->getMethod('extractRowsFromUpload');
$method->setAccessible(true);
$path = getenv('TEMP') . DIRECTORY_SEPARATOR . 'ogrenci-toplu-kayit-sablonu.xlsx';
$rows = $method->invoke($controller, $path, 'xlsx');
var_export($rows);
