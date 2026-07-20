<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$course = App\Models\Course::query()->whereRaw("JSON_EXTRACT(lesson_payload, '$.cover_image') IS NOT NULL")->first();
if (! $course) { echo "no-course\n"; exit; }
$controller = app(App\Http\Controllers\CourseController::class);
$rc = new ReflectionClass($controller);
$build = $rc->getMethod('buildCoursePackage');
$build->setAccessible(true);
$decode = $rc->getMethod('decodeCourseImportPayload');
$decode->setAccessible(true);
$exportCover = $rc->getMethod('exportCoverBinary');
$exportCover->setAccessible(true);
$mime = $rc->getMethod('exportCoverMime');
$mime->setAccessible(true);
$pkg = $build->invoke($controller, ['exported_at'=>now()->toIso8601String(),'course'=>['name'=>$course->name,'code'=>$course->code,'weekly_hours'=>$course->weekly_hours,'lesson_payload'=>(array)$course->lesson_payload]], $exportCover->invoke($controller, $course), $mime->invoke($controller, $course));
file_put_contents('storage/app/test.coursepkg', $pkg);
$coverBinary = null;
$args = [$pkg, &$coverBinary];
$decoded = $decode->invokeArgs($controller, $args);
echo json_encode(['decoded' => is_array($decoded), 'cover_len' => strlen((string)$coverBinary), 'course_name' => $decoded['course']['name'] ?? null], JSON_UNESCAPED_UNICODE) . PHP_EOL;
