<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\Domain\CourseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class CourseController extends Controller
{
    public function __construct(private CourseService $service)
    {
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $category = trim($request->string('category')->toString());
        $sort = in_array($request->string('sort')->toString(), ['id', 'name', 'code', 'created_at'], true) ? $request->string('sort')->toString() : 'id';
        $dir = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';
        $user = $request->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);

        try {
            $items = Course::query()
                ->when($user?->hasRole('teacher'), fn ($query) => $query->where('teacher_id', $teacherId))
                ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))
                ->when($category !== '' && $category !== 'Tumu', fn ($query) => $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.category')) = ?", [$category]))
                ->orderBy($sort, $dir)
                ->paginate(20)
                ->withQueryString();
        } catch (\Throwable $e) {
            Log::warning('Course index fallback triggered', [
                'message' => $e->getMessage(),
            ]);
            $items = Course::query()
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString();
        }

        try {
            $teachers = Teacher::query()->orderByDesc('id')->get();
        } catch (\Throwable $e) {
            Log::warning('Course teachers fallback triggered', [
                'message' => $e->getMessage(),
            ]);
            $teachers = collect();
        }
        $canManageCourses = (bool) ($user?->hasRole('admin') || $user?->hasRole('teacher'));
        $canAssignCourses = (bool) ($user?->hasRole('admin') || $user?->hasRole('teacher'));

        return view('courses.index', compact('items', 'q', 'category', 'sort', 'dir', 'teachers', 'canManageCourses', 'canAssignCourses'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('courses.create', compact('teachers', 'classes'));
    }
    public function assignTeacher(Request $request, Course $course)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ]);
        $course->teacher_id = (int) $data['teacher_id'];
        $course->save();
        return redirect()->route('courses.index')->with('ok', 'Ders ogretmene atandi.');
    }
    public function assignClasses(Request $request, Course $course)
    {
        $data = $request->validate([
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
        ]);
        $classIds = collect($data['class_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        foreach ($classIds as $classId) {
            CourseHomework::query()->firstOrCreate([
                'course_id' => $course->id,
                'school_class_id' => $classId,
                'assignment_type' => 'lesson',
                'title' => $course->name,
            ], [
                'details' => null,
                'due_date' => null,
                'created_by' => auth()->id(),
            ]);
        }
        return redirect()->route('courses.index')->with('ok', 'Ders secilen siniflara atandi.');
    }
    public function assignByLevel(Request $request, Course $course)
    {
        $data = $request->validate([
            'grade_level' => ['required', 'integer', 'between:1,12'],
        ]);
        $classIds = SchoolClass::query()->where('grade_level', (int) $data['grade_level'])->pluck('id')->map(fn ($v) => (int) $v)->all();
        foreach ($classIds as $classId) {
            CourseHomework::query()->firstOrCreate([
                'course_id' => $course->id,
                'school_class_id' => $classId,
                'assignment_type' => 'lesson',
                'title' => $course->name,
            ], [
                'details' => null,
                'due_date' => null,
                'created_by' => auth()->id(),
            ]);
        }
        return redirect()->route('courses.index')->with('ok', 'Ders kademe bazli atandi.');
    }
    public function uploadCover(Request $request)
    {
        $validated = $request->validate([
            'cover_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $path = $this->storeCoverAsWebp($validated['cover_image']);

        return response()->json([
            'url' => route('courses.cover', ['path' => $path]),
            'path' => $path,
        ]);
    }
    public function cover(string $path)
    {
        $safePath = trim(str_replace('\\', '/', $path), '/');
        if ($safePath === '' || str_contains($safePath, '..')) {
            abort(404);
        }
        $normalized = preg_replace('#^/?storage/#i', '', $safePath);
        $normalized = preg_replace('#^/?kapak-gorseli/#i', '', (string) $normalized);
        $normalized = preg_replace('#^/?course-covers/#i', '', (string) $normalized);
        $relative = ltrim((string) $normalized, '/');
        $baseDir = $this->coverStorageDirectory();
        $baseName = pathinfo($relative, PATHINFO_FILENAME);
        $extensions = ['png', 'webp', 'jpg', 'jpeg'];
        $candidatesRelative = [];
        foreach ($extensions as $ext) {
            $candidatesRelative[] = 'kapak-gorseli/' . $baseName . '.' . $ext;
            $candidatesRelative[] = 'course-covers/' . $baseName . '.' . $ext;
        }
        $candidates = array_values(array_filter([
            $baseDir . '/' . $relative,
            $baseDir . '/' . preg_replace('/\.webp$/i', '.png', $relative),
            public_path('kapak-gorseli/' . $relative),
            public_path('kapak-gorseli/' . preg_replace('/\.webp$/i', '.png', $relative)),
            storage_path('app/public/kapak-gorseli/' . $relative),
            storage_path('app/public/kapak-gorseli/' . preg_replace('/\.webp$/i', '.png', $relative)),
            storage_path('app/public/course-covers/' . $relative),
            storage_path('app/public/course-covers/' . preg_replace('/\.png$/i', '.webp', $relative)),
            ...array_map(fn ($candidate) => public_path($candidate), $candidatesRelative),
            ...array_map(fn ($candidate) => storage_path('app/public/' . $candidate), $candidatesRelative),
        ]));

        foreach ($candidates as $fullPath) {
            if (is_file($fullPath)) {
                return response()->file($fullPath, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        abort(404);
    }
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();
        $data = $this->attachCoverImageToPayload($request, $data);
        $data['created_by'] = auth()->id();
        $model = $this->service->create($data);

        return $request->expectsJson()
            ? response()->json($model, 201)
            : redirect()->route('courses.index')->with('ok', 'Ders eklendi');
    }
    public function show($id)
    {
        $course = Course::with(['teacher.user', 'schoolClass'])->find($id);

        $payload = (array) ($course?->lesson_payload ?? []);
        $curriculum = (array) ($payload['curriculum'] ?? []);

        $title = (string) ($course?->name ?? '');
        $lessonNumber = max(1, (int) ($curriculum['lesson_number'] ?? 1));
        $detailTitle = (string) ($curriculum['title'] ?? '');
        $konu = (string) ($curriculum['konu'] ?? '');
        $kazanimlar = array_values(array_filter((array) (
            $curriculum['kazanımlar']
            ?? $curriculum['kazanÄ±mlar']
            ?? $curriculum['kazanimlar']
            ?? []
        ), fn ($item) => trim((string) $item) !== ''));
        $etkinlikler = array_values(array_filter((array) ($curriculum['etkinlikler'] ?? []), fn ($item) => trim((string) $item) !== ''));
        $progress = max(0, min(100, (int) ($curriculum['progress'] ?? 0)));
        $isCompleted = false;
        $startUrl = '#';

        if (auth()->check() && auth()->user()?->hasRole('student') && $course) {
            $isCompleted = ContentProgress::query()
                ->where('content_id', 'course-' . $course->id)
                ->where('user_id', auth()->id())
                ->where('completed', true)
                ->exists();
            $startUrl = route('student.portal.course-show', $course);
        }

        return view('course-detail', compact(
            'course',
            'title',
            'detailTitle',
            'lessonNumber',
            'konu',
            'kazanimlar',
            'etkinlikler',
            'progress',
            'isCompleted',
            'startUrl'
        ));
    }
    public function edit(Course $course)
    {
        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('courses.edit', compact('course', 'teachers', 'classes'));
    }
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $data = $request->validated();
        $data = $this->attachCoverImageToPayload($request, $data);
        $this->service->update($course, $data);

        return $request->expectsJson()
            ? response()->json($course->refresh())
            : redirect()->route('courses.index')->with('ok', 'Ders guncellendi');
    }
    public function destroy(Course $course)
    {
        $this->performDestroyById((int) $course->id);

        return request()->expectsJson()
            ? response()->json([], 204)
            : redirect()->route('courses.index')->with('ok', 'Ders silindi');
    }
    public function destroyPost(Course $course)
    {
        $this->performDestroyById((int) $course->id);
        return redirect()->route('courses.index')->with('ok', 'Ders silindi');
    }
    public function destroyNow(Course $course)
    {
        $this->performDestroyById((int) $course->id);
        return redirect()->route('courses.index')->with('ok', 'Ders silindi');
    }
    public function destroyById(int $id)
    {
        $this->performDestroyById($id);
        return redirect()->route('courses.index')->with('ok', 'Ders silindi');
    }

    public function destroyAll(Request $request)
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        $deletedCount = 0;

        DB::transaction(function () use (&$deletedCount): void {
            $courseIds = Course::query()->pluck('id')->map(fn ($v) => (int) $v)->all();

            if ($courseIds === []) {
                $deletedCount = 0;
                return;
            }

            CourseHomework::query()->whereIn('course_id', $courseIds)->delete();
            $deletedCount = Course::query()->whereIn('id', $courseIds)->delete();
        });

        return redirect()->route('courses.index')->with('ok', $deletedCount . ' ders sistemden kaldirildi.');
    }

    public function export(Course $course): StreamedResponse
    {
        $user = auth()->user();
        if ($user?->hasRole('teacher') && (int) $course->teacher_id !== (int) (optional($user->teacher)->id ?? 0)) {
            abort(403);
        }

        $lessonPayload = (array) ($course->lesson_payload ?? []);
        $lessonPayload['cover_image_data'] = $this->exportCoverDataUrl($course);
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'course' => [
                'name' => (string) $course->name,
                'code' => (string) $course->code,
                'weekly_hours' => (int) $course->weekly_hours,
                'lesson_payload' => $lessonPayload,
            ],
        ];

        $filename = 'ders-' . Str::slug((string) $course->name ?: 'course') . '-' . $course->id . '.json';
        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function exportAll(): StreamedResponse
    {
        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);

        $courses = Course::query()
            ->when($user?->hasRole('teacher'), fn ($q) => $q->where('teacher_id', $teacherId))
            ->orderBy('id')
            ->get(['id', 'name', 'code', 'weekly_hours', 'lesson_payload']);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'count' => $courses->count(),
            'courses' => $courses->map(function (Course $c) {
                $lessonPayload = (array) ($c->lesson_payload ?? []);
                $lessonPayload['cover_image_data'] = $this->exportCoverDataUrl($c);

                return [
                    'name' => (string) $c->name,
                    'code' => (string) $c->code,
                    'weekly_hours' => (int) $c->weekly_hours,
                    'lesson_payload' => $lessonPayload,
                ];
            })->values()->all(),
        ];

        $filename = 'tum-dersler-' . now()->format('Ymd-His') . '.json';
        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'course_json' => ['required'],
            'course_json.*' => ['file', 'mimes:json,txt', 'max:65536'],
        ]);

        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        if ($teacherId <= 0) {
            $teacherId = (int) Teacher::query()->value('id');
        }
        if ($teacherId <= 0) {
            return redirect()->route('courses.index')->with('error', 'Ogretmen kaydi bulunamadi.');
        }

        $files = $request->file('course_json');
        if (!$files) {
            return redirect()->route('courses.index')->with('error', 'Lutfen en az bir dosya secin.');
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        $created = [];
        foreach ($files as $file) {
            $raw = (string) file_get_contents($file->getRealPath());
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            $rows = [];
            if (isset($decoded['course']) && is_array($decoded['course'])) {
                $rows[] = $decoded['course'];
            } elseif (isset($decoded['courses']) && is_array($decoded['courses'])) {
                $rows = array_values(array_filter($decoded['courses'], fn ($x) => is_array($x)));
            } elseif (array_is_list($decoded)) {
                $rows = array_values(array_filter($decoded, fn ($x) => is_array($x)));
            } else {
                $rows[] = $decoded;
            }

            foreach ($rows as $c) {
                $courseData = is_array($c) ? $c : [];
                if (isset($courseData['course']) && is_array($courseData['course'])) {
                    $courseData = $courseData['course'];
                }
                $name = trim((string) ($courseData['name'] ?? $courseData['title'] ?? ''));
                if ($name === '') continue;
                $rawCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($courseData['code'] ?? 'CRS')));
                $baseCode = substr($rawCode !== '' ? $rawCode : 'CRS', 0, 20);
                $finalCode = $baseCode . '-' . strtoupper(Str::random(6)); // max 27 char
                $lessonPayload = (array) ($courseData['lesson_payload'] ?? $courseData['payload'] ?? []);
                if ($lessonPayload === [] && (isset($courseData['slides']) || isset($courseData['curriculum']))) {
                    $lessonPayload = array_filter([
                        'slides' => $courseData['slides'] ?? null,
                        'curriculum' => $courseData['curriculum'] ?? null,
                        'lesson_description' => $courseData['lesson_description'] ?? null,
                        'difficulty' => $courseData['difficulty'] ?? null,
                        'category' => $courseData['category'] ?? null,
                        'cover_image' => $courseData['cover_image'] ?? null,
                        'cover_image_data' => $courseData['cover_image_data'] ?? null,
                    ], fn ($v) => $v !== null && $v !== '');
                }
                if (!empty($lessonPayload['cover_image_data']) && is_string($lessonPayload['cover_image_data'])) {
                    try {
                        $lessonPayload['cover_image'] = $this->storeCoverFromDataUrl($lessonPayload['cover_image_data']);
                    } catch (\Throwable $e) {
                        Log::warning('Course cover import failed', ['message' => $e->getMessage(), 'course_name' => $name]);
                    }
                    unset($lessonPayload['cover_image_data']);
                }
                $cover = trim((string) ($lessonPayload['cover_image'] ?? ''));
                if ($cover !== '') {
                    $cover = ltrim(str_replace('\\', '/', $cover), '/');
                    $cover = preg_replace('#^storage/#i', '', $cover);
                    $cover = preg_replace('#^course-covers/#i', 'course-covers/', $cover);
                    if (!Storage::disk('public')->exists($cover)) {
                        unset($lessonPayload['cover_image']);
                    } else {
                        $lessonPayload['cover_image'] = $cover;
                    }
                }
                $created[] = Course::query()->create([
                    'name' => $name,
                    'code' => $finalCode,
                    'teacher_id' => $teacherId,
                    'school_class_id' => null,
                    'weekly_hours' => max(1, min(20, (int) ($courseData['weekly_hours'] ?? 2))),
                    'lesson_payload' => $lessonPayload,
                    'created_by' => auth()->id(),
                ]);
            }
        }

        if (count($created) < 1) {
            return redirect()->route('courses.index')->with('error', 'Yuklenen dosyalarda gecerli ders bulunamadi.');
        }
        return redirect()->route('courses.index')->with('ok', count($created) . ' ders yuklendi.');
    }

    private function performDestroyById(int $courseId): void
    {
        $user = auth()->user();
        Log::info('Course delete requested', ['course_id' => $courseId, 'user_id' => auth()->id(), 'role' => $user?->role?->slug]);

        if ($user?->hasRole('teacher')) {
            $teacherId = (int) (optional($user->teacher)->id ?? 0);
            if ($teacherId <= 0) {
                throw new \RuntimeException('Ogretmen kaydi bulunamadi.');
            }
            $course = Course::query()
                ->whereKey($courseId)
                ->where('teacher_id', $teacherId)
                ->first();
            if (! $course) {
                throw new \RuntimeException('Ders bu ogretmene atali degil veya bulunamadi.');
            }

            // Ogretmen kendi olusturdugu dersi tamamen silebilir.
            if ((int) ($course->created_by ?? 0) === (int) auth()->id()) {
                Course::query()
                    ->whereKey($courseId)
                    ->where('teacher_id', $teacherId)
                    ->delete();
                return;
            }

            // Adminin olusturdugu/atadigi derste sadece atama ogretmenden kaldirilir.
            $adminTeacherId = $this->resolveAdminTeacherId($teacherId);
            if ($adminTeacherId <= 0) {
                throw new \RuntimeException('Admin ogretmen kaydi bulunamadigi icin ders atamasi kaldirilamadi.');
            }

            $updated = Course::query()
                ->whereKey($courseId)
                ->where('teacher_id', $teacherId)
                ->update(['teacher_id' => $adminTeacherId]);
            if ($updated !== 1) {
                throw new \RuntimeException('Ders atamasi kaldirilamadi.');
            }
            return;
        }

        DB::transaction(function () use ($courseId) {
            CourseHomework::query()->where('course_id', $courseId)->delete();
            $deleted = Course::query()->whereKey($courseId)->delete();
            if ($deleted !== 1) {
                throw new \RuntimeException('Ders kaydi bulunamadi veya silinemedi.');
            }
        });
    }

    private function resolveAdminTeacherId(int $currentTeacherId): int
    {
        $adminUserIds = \App\Models\User::query()
            ->whereHas('role', fn ($q) => $q->where('slug', 'admin'))
            ->pluck('id')
            ->all();

        if ($adminUserIds === []) {
            return 0;
        }

        $adminTeacherId = Teacher::query()
            ->whereIn('user_id', $adminUserIds)
            ->where('id', '!=', $currentTeacherId)
            ->value('id');

        return (int) ($adminTeacherId ?? 0);
    }

    private function attachCoverImageToPayload(Request $request, array $data): array
    {
        $payload = [];
        if (!empty($data['lesson_payload'])) {
            $decoded = json_decode((string) $data['lesson_payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $base64 = (string) $request->input('cover_image_data', '');
        if ($base64 !== '') {
            try {
                $path = $this->storeCoverFromDataUrl($base64);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'cover_image_file' => $e->getMessage(),
                ]);
            }
            $payload['cover_image'] = $path;
            $data['lesson_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
            unset($data['cover_image_file'], $data['cover_image_data']);
            return $data;
        }

        if (! $request->hasFile('cover_image_file')) {
            unset($data['cover_image_file'], $data['cover_image_data']);
            return $data;
        }

        try {
            $path = $this->storeCoverAsWebp($request->file('cover_image_file'));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'cover_image_file' => $e->getMessage(),
            ]);
        }
        $payload['cover_image'] = $path;
        $data['lesson_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        unset($data['cover_image_file'], $data['cover_image_data']);

        return $data;
    }

    private function storeCoverFromDataUrl(string $dataUrl): string
    {
        if (! preg_match('#^data:image/(png|jpeg|jpg|webp);base64,#i', $dataUrl)) {
            throw new \RuntimeException('Kapak gorseli gecersiz formatta.');
        }

        [$meta, $encoded] = explode(',', $dataUrl, 2) + [null, null];
        if (! is_string($encoded) || $encoded === '') {
            throw new \RuntimeException('Kapak gorseli okunamadi.');
        }

        $binary = base64_decode($encoded, true);
        if ($binary === false || $binary === '') {
            throw new \RuntimeException('Kapak gorseli base64 cozulemedi.');
        }

        $outputDir = $this->coverStorageDirectory();
        if (!is_dir($outputDir)) {
            @mkdir($outputDir, 0775, true);
        }
        if (!is_dir($outputDir) || !is_writable($outputDir)) {
            throw new \RuntimeException('Kapak gorseli kayit klasoru yazilabilir degil.');
        }

        $relative = 'kapak-gorseli/' . Str::uuid() . '.png';
        $outputPath = $outputDir . '/' . basename($relative);
        if (file_put_contents($outputPath, $binary) === false || !is_file($outputPath) || filesize($outputPath) <= 0) {
            throw new \RuntimeException('Kapak gorseli kaydedilemedi.');
        }

        return $relative;
    }

    private function storeCoverAsWebp(UploadedFile $file): string
    {
        $outputDir = $this->coverStorageDirectory();
        if (!is_dir($outputDir)) {
            @mkdir($outputDir, 0775, true);
        }
        if (!is_dir($outputDir) || !is_writable($outputDir)) {
            throw new \RuntimeException('Kapak gorseli kayit klasoru yazilabilir degil.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'png';
        $relative = 'kapak-gorseli/' . Str::uuid() . '.' . $extension;
        $outputPath = $outputDir . '/' . basename($relative);
        $sourcePath = $file->getRealPath();

        $magick = $this->resolveMagickBinary();
        $canUseImagePipeline = $sourcePath && is_file($sourcePath) && ($magick || function_exists('imagecreatefromstring'));

        if ($canUseImagePipeline) {
            if ($magick) {
                $process = new Process([
                    $magick,
                    $sourcePath,
                    '-auto-orient',
                    '-resize', '1600x900^',
                    '-gravity', 'center',
                    '-extent', '1600x900',
                    '-background', 'white',
                    '-flatten',
                    $outputPath,
                ]);
                $process->setTimeout(30);
                $process->run();
                if (! $process->isSuccessful() || !is_file($outputPath) || filesize($outputPath) <= 0) {
                    $this->storeCoverWithGd($sourcePath, $outputPath);
                }
            } else {
                $this->storeCoverWithGd($sourcePath, $outputPath);
            }
        } else {
            $stream = fopen((string) $file->getRealPath(), 'rb');
            if ($stream === false) {
                throw new \RuntimeException('Kapak gorseli okunamadi.');
            }
            $target = fopen($outputPath, 'wb');
            if ($target === false) {
                fclose($stream);
                throw new \RuntimeException('Kapak gorseli yazilamadi.');
            }
            stream_copy_to_stream($stream, $target);
            fclose($stream);
            fclose($target);
        }

        if (!is_file($outputPath) || filesize($outputPath) <= 0) {
            throw new \RuntimeException('Kapak gorseli kaydedilemedi.');
        }

        return $relative;
    }

    private function coverStorageDirectory(): string
    {
        $preferred = public_path('kapak-gorseli');
        if (is_dir($preferred) || @mkdir($preferred, 0775, true) || is_dir($preferred)) {
            return $preferred;
        }

        $alt = public_path('public/kapak-gorseli');
        if (is_dir($alt) || @mkdir($alt, 0775, true) || is_dir($alt)) {
            return $alt;
        }

        return $preferred;
    }

    private function resolveMagickBinary(): ?string
    {
        $candidates = array_filter([
            env('MAGICK_BIN'),
            'magick',
            'C:\\Program Files\\ImageMagick-7.1.2-Q16-HDRI\\magick.exe',
            'C:\\Program Files\\ImageMagick-7.1.1-Q16-HDRI\\magick.exe',
        ]);

        foreach ($candidates as $bin) {
            if (str_contains($bin, '\\') || str_contains($bin, '/')) {
                if (is_file($bin)) {
                    return $bin;
                }
                continue;
            }
            $locator = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
            $probe = new Process([$locator, $bin]);
            $probe->setTimeout(5);
            $probe->run();
            if ($probe->isSuccessful()) {
                return $bin;
            }
        }

        return null;
    }

    private function storeCoverWithGd(string $sourcePath, string $outputPath): void
    {
        if (!function_exists('imagecreatefromstring') || (!function_exists('imagewebp') && !function_exists('imagepng'))) {
            throw new \RuntimeException('Kapak gorseli islenemedi. GD destegi bulunamadi.');
        }

        $raw = @file_get_contents($sourcePath);
        if ($raw === false) {
            throw new \RuntimeException('Kapak gorseli okunamadi.');
        }
        $src = @imagecreatefromstring($raw);
        if (!is_resource($src) && !($src instanceof \GdImage)) {
            throw new \RuntimeException('Kapak gorseli islenemedi.');
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $dstW = 1600;
        $dstH = 900;
        $targetRatio = $dstW / $dstH;
        $srcRatio = $srcW / max($srcH, 1);

        if ($srcRatio > $targetRatio) {
            $cropH = $srcH;
            $cropW = (int) round($srcH * $targetRatio);
            $srcX = (int) floor(($srcW - $cropW) / 2);
            $srcY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetRatio);
            $srcX = 0;
            $srcY = (int) floor(($srcH - $cropH) / 2);
        }

        $dst = imagecreatetruecolor($dstW, $dstH);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $dstW, $dstH, $cropW, $cropH);
        $saved = function_exists('imagepng')
            ? @imagepng($dst, $outputPath, 6)
            : false;
        if (! $saved) {
            imagedestroy($dst);
            imagedestroy($src);
            throw new \RuntimeException('Kapak gorseli PNG olarak kaydedilemedi.');
        }
        imagedestroy($dst);
        imagedestroy($src);
    }

    private function exportCoverDataUrl(Course $course): string
    {
        $cover = $this->resolveCoverFilePath((string) data_get($course->lesson_payload, 'cover_image', ''));
        if ($cover === '' || ! is_file($cover)) {
            return '';
        }

        $mime = match (strtolower(pathinfo($cover, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
        $binary = @file_get_contents($cover);
        if ($binary === false || $binary === '') {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }

    private function resolveCoverFilePath(string $cover): string
    {
        $cover = trim(str_replace('\\', '/', $cover));
        if ($cover === '') {
            return '';
        }

        $cover = ltrim($cover, '/');
        $relative = preg_replace('#^storage/#i', '', $cover) ?? $cover;
        $relative = preg_replace('#^public/#i', '', $relative) ?? $relative;

        $paths = [
            public_path($relative),
            public_path('public/' . $relative),
            storage_path('app/public/' . $relative),
            storage_path('app/public/kapak-gorseli/' . basename($relative)),
            public_path('kapak-gorseli/' . basename($relative)),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return '';
    }
}
