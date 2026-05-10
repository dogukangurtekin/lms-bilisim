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

        $items = Course::with(['teacher.user', 'schoolClass'])
            ->when($user?->hasRole('teacher'), fn ($query) => $query->where('teacher_id', $teacherId))
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))
            ->when($category !== '' && $category !== 'Tumu', fn ($query) => $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.category')) = ?", [$category]))
            ->orderBy($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        return view('courses.index', compact('items', 'q', 'category', 'sort', 'dir', 'teachers'));
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
            'url' => route('courses.cover', ['path' => ltrim($path, '/')]),
            'path' => $path,
        ]);
    }
    public function cover(string $path)
    {
        $safePath = trim(str_replace('\\', '/', $path), '/');
        if ($safePath === '' || str_contains($safePath, '..')) {
            abort(404);
        }
        $fullPath = 'course-covers/' . ltrim(preg_replace('#^course-covers/#i', '', $safePath), '/');
        if (!Storage::disk('public')->exists($fullPath)) {
            abort(404);
        }
        return response()->file(Storage::disk('public')->path($fullPath), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
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

        $title = (string) ($course?->name ?? 'APP Inventor ile Mobil Kodlama');
        $lessonNumber = max(1, (int) ($curriculum['lesson_number'] ?? 1));
        $detailTitle = (string) ($curriculum['title'] ?? 'Mobil Dunyaya Ilk Adim: Arayuzu Kesfediyorum');
        $konu = (string) ($curriculum['konu'] ?? 'Bu derste APP Inventor arayuzunu taniyarak temel bilesenleri nasil kullandigimizi ogreniyoruz.');
        $kazanimlar = (array) ($curriculum['kazanımlar'] ?? [
            'APP Inventor ekraninda ana panelleri tanir.',
            'Bilesen ekleme ve duzenleme mantigini kavrar.',
            'Basit bir mobil arayuz tasarimini olusturur.',
            'Proje dosyasini kaydetme ve tekrar acma adimlarini uygular.',
        ]);
        $etkinlikler = (array) ($curriculum['etkinlikler'] ?? [
            'Bilesenlerle mini arayuz olusturma etkinligi',
            'Renk ve tipografi secimi alistirmasi',
            'Kisa sureli eslestirme ve dogru-yanlis etkinligi',
            'Mini proje: butonla ekran gecisi uygulamasi',
        ]);
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

    public function export(Course $course): StreamedResponse
    {
        $user = auth()->user();
        if ($user?->hasRole('teacher') && (int) $course->teacher_id !== (int) (optional($user->teacher)->id ?? 0)) {
            abort(403);
        }

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'course' => [
                'name' => (string) $course->name,
                'code' => (string) $course->code,
                'weekly_hours' => (int) $course->weekly_hours,
                'lesson_payload' => (array) ($course->lesson_payload ?? []),
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
            'courses' => $courses->map(fn (Course $c) => [
                'name' => (string) $c->name,
                'code' => (string) $c->code,
                'weekly_hours' => (int) $c->weekly_hours,
                'lesson_payload' => (array) ($c->lesson_payload ?? []),
            ])->values()->all(),
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
            'course_json.*' => ['file', 'mimes:json,txt', 'max:5120'],
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
            $raw = file_get_contents($file->getRealPath());
            $decoded = json_decode((string) $raw, true);
            if (!is_array($decoded)) continue;

            $rows = [];
            if (isset($decoded['course']) && is_array($decoded['course'])) {
                $rows[] = $decoded['course'];
            } elseif (isset($decoded['courses']) && is_array($decoded['courses'])) {
                $rows = array_values(array_filter($decoded['courses'], fn ($x) => is_array($x)));
            }

            foreach ($rows as $c) {
                $name = trim((string) ($c['name'] ?? ''));
                if ($name === '') continue;
                $rawCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($c['code'] ?? 'CRS')));
                $baseCode = substr($rawCode !== '' ? $rawCode : 'CRS', 0, 20);
                $finalCode = $baseCode . '-' . strtoupper(Str::random(6)); // max 27 char
                $created[] = Course::query()->create([
                    'name' => $name,
                    'code' => $finalCode,
                    'teacher_id' => $teacherId,
                    'school_class_id' => null,
                    'weekly_hours' => max(1, min(20, (int) ($c['weekly_hours'] ?? 2))),
                    'lesson_payload' => (array) ($c['lesson_payload'] ?? []),
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
        if (! $request->hasFile('cover_image_file')) {
            unset($data['cover_image_file']);
            return $data;
        }

        $payload = [];
        if (!empty($data['lesson_payload'])) {
            $decoded = json_decode((string) $data['lesson_payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        try {
            $path = $this->storeCoverAsWebp($request->file('cover_image_file'));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'cover_image_file' => $e->getMessage(),
            ]);
        }
        $payload['cover_image'] = ltrim($path, '/');
        $data['lesson_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        unset($data['cover_image_file']);

        return $data;
    }

    private function storeCoverAsWebp(UploadedFile $file): string
    {
        $relative = 'course-covers/' . Str::uuid() . '.webp';
        $outputPath = Storage::disk('public')->path($relative);
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $magick = $this->resolveMagickBinary();
        if ($magick !== null) {
            $process = new Process([
                $magick,
                $file->getRealPath(),
                '-auto-orient',
                '-resize', '1600x900^',
                '-gravity', 'center',
                '-extent', '1600x900',
                '-strip',
                '-quality', '78',
                '-define', 'webp:method=6',
                $outputPath,
            ]);
            $process->setTimeout(20);
            $process->run();
        }

        if (!is_file($outputPath)) {
            $this->storeCoverWithGd($file->getRealPath(), $outputPath);
        }

        if (!is_file($outputPath)) {
            throw new \RuntimeException('Kapak gorseli islenemedi. Sunucuda webp donusumu desteklenmiyor olabilir.');
        }

        return $relative;
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
        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
            throw new \RuntimeException('Kapak gorseli islenemedi. GD/webp destegi bulunamadi.');
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
        if (!@imagewebp($dst, $outputPath, 78)) {
            imagedestroy($dst);
            imagedestroy($src);
            throw new \RuntimeException('Kapak gorseli webp olarak kaydedilemedi.');
        }
        imagedestroy($dst);
        imagedestroy($src);
    }
}
