<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$course = App\Models\Course::query()->orderByDesc('id')->first();
$view = view('courses.partials.builder-form', [
  'course' => $course,
  'teachers' => App\Models\Teacher::query()->orderByDesc('id')->get(),
  'classes' => App\Models\SchoolClass::query()->orderBy('name')->orderBy('section')->get(),
  'errors' => new Illuminate\Support\ViewErrorBag(),
]);
$html = $view->render();
file_put_contents(__DIR__ . '/../storage/app/builder-form-render.html', $html);
$hasSlides = substr_count($html, 'slide-list-item');
$hasPayload = strpos($html, 'lesson_builder_draft') !== false;
echo json_encode(['slide_list_items' => $hasSlides, 'has_draft' => $hasPayload, 'html_len' => strlen($html)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
?>
