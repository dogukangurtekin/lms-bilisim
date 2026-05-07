<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ref = new ReflectionClass(App\Http\Controllers\UserManagementController::class);
$controller = $ref->newInstanceWithoutConstructor();
$method = $ref->getMethod('downloadTemplate');
$method->setAccessible(true);
$response = $method->invoke($controller, 'test.xlsx', ['Ad','Soyad','Kullanici Adi','Sifre','Sinif','Sube'], ['Ali','Yilmaz','ali.yilmaz','123456','6','A']);
ob_start();
($response->getCallback())();
$data = ob_get_clean();
file_put_contents(dirname(__DIR__) . '/storage/test-template.xlsx', $data);
echo strlen($data);
