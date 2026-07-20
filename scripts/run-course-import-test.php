<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

$user = App\Models\User::find(1);
Auth::login($user);

$controller = app(App\Http\Controllers\CourseController::class);
$path = __DIR__ . '/../storage/app/test-coursepkg.coursepkg';
$file = new UploadedFile($path, 'test-coursepkg.coursepkg', 'application/octet-stream', null, true);
$request = Request::create('/courses/yukle', 'POST', [], [], ['course_json' => [$file]], ['HTTP_ACCEPT' => 'application/json']);
$request->setUserResolver(fn() => $user);
$request->setLaravelSession(app('session')->driver());

$response = $controller->import($request);
$latest = App\Models\Course::orderByDesc('id')->first();
$cover = $latest ? data_get($latest->lesson_payload, 'cover_image') : null;
$exists = $cover ? is_file(public_path($cover)) : false;

echo json_encode([
    'response_class' => is_object($response) ? get_class($response) : gettype($response),
    'latest_id' => $latest?->id,
    'latest_name' => $latest?->name,
    'cover' => $cover,
    'cover_exists' => $exists,
], JSON_UNESCAPED_UNICODE) . PHP_EOL;
