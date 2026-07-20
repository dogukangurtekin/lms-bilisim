<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = app(App\Http\Controllers\CourseController::class);
$rc = new ReflectionClass($controller);
$method = $rc->getMethod('decodeCourseImportPayload');
$method->setAccessible(true);
$pkg = file_get_contents(__DIR__ . '/../storage/app/test-coursepkg.coursepkg');
$coverBinary = null;
$decoded = $method->invokeArgs($controller, [$pkg, &$coverBinary]);
echo json_encode(['decoded' => is_array($decoded), 'name' => $decoded['course']['name'] ?? null, 'cover_len' => strlen((string)$coverBinary)], JSON_UNESCAPED_UNICODE) . PHP_EOL;
