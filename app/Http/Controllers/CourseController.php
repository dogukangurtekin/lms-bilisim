<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\Domain\CourseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $items = Course::with(['teacher.user', 'schoolClass'])
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))
            ->when($category !== '' && $category !== 'Tumu', fn ($query) => $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.category')) = ?", [$category]))
            ->orderBy($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        return view('courses.index', compact('items', 'q', 'category', 'sort', 'dir'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('courses.create', compact('teachers', 'classes'));
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
    public function destroy(Course $course) { $this->service->delete($course); return request()->expectsJson() ? response()->json([], 204) : redirect()->route('courses.index')->with('ok', 'Ders silindi'); }

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

        $path = $this->storeCoverAsWebp($request->file('cover_image_file'));
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
        if ($magick === null) {
            throw new \RuntimeException('ImageMagick bulunamadi. Kapak gorseli islenemedi.');
        }

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

        if (! $process->isSuccessful() || !is_file($outputPath)) {
            throw new \RuntimeException('Kapak gorseli webp formata donusturulemedi.');
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
            $probe = new Process(['where', $bin]);
            $probe->setTimeout(5);
            $probe->run();
            if ($probe->isSuccessful()) {
                return $bin;
            }
        }

        return null;
    }
}
