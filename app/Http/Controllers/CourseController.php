<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\CourseFavorite;
use App\Models\CourseHomework;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Support\Utf8Text;
use App\Models\Teacher;
use App\Services\Domain\CourseService;
use App\Services\LessonPresentation\SlidePresentationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $service,
        private SlidePresentationService $presentation
    )
    {
    }

    public function toggleFavorite(Request $request, Course $course)
    {
        $userId = (int) $request->user()->id;

        $existing = CourseFavorite::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            CourseFavorite::query()->create(['user_id' => $userId, 'course_id' => $course->id]);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $category = trim($request->string('category')->toString());
        $difficulty = trim($request->string('difficulty')->toString());
        $educationStage = trim($request->string('education_stage')->toString());
        $favoritesOnly = $request->boolean('favorites_only');
        if ($favoritesOnly) {
            // Favoriler seçiliyken kademe/seviye/kategori filtrelerini yok say;
            // sadece favori dersler, seviye/kademe fark etmeksizin gösterilsin.
            $category = '';
            $difficulty = '';
            $educationStage = '';
        }
        $sort = in_array($request->string('sort')->toString(), ['id', 'name', 'code', 'created_at'], true) ? $request->string('sort')->toString() : 'id';
        $dir = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';
        $user = $request->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        $isAdmin = (bool) $user?->hasRole('admin');
        $isTeacher = (bool) $user?->hasRole('teacher');
        $hasTeacherProfile = $teacherId > 0;
        $ownerFilter = trim((string) $request->string('owner')->toString());
        $ownerFilter = $isAdmin ? $ownerFilter : ($isTeacher ? (string) $user?->id : '');
        $applyOwnerFilter = function ($query) use ($isAdmin, $isTeacher, $hasTeacherProfile, $ownerFilter, $teacherId, $user): void {
            if ($isAdmin) {
                if ($ownerFilter === '' || $ownerFilter === 'all') {
                    return;
                }

                if ($ownerFilter === 'system_admin') {
                    $adminUserIds = User::query()
                        ->whereHas('role', fn ($roleQuery) => $roleQuery->where('slug', 'admin'))
                        ->pluck('id')
                        ->all();
                    $query->whereIn('created_by', $adminUserIds);
                    return;
                }

                if (is_numeric($ownerFilter)) {
                    $selectedUserId = (int) $ownerFilter;
                    $selectedTeacherId = (int) (Teacher::query()
                        ->where('user_id', $selectedUserId)
                        ->value('id') ?? 0);

                    $query->where(function ($subQuery) use ($selectedUserId, $selectedTeacherId): void {
                        $subQuery->where('created_by', $selectedUserId);
                        if ($selectedTeacherId > 0) {
                            $subQuery->orWhere('teacher_id', $selectedTeacherId);
                        }
                    });
                }

                return;
            }

            if (($isTeacher || $hasTeacherProfile) && $teacherId > 0) {
                $currentUserId = (int) ($user?->id ?? 0);
                $query->where(function ($subQuery) use ($teacherId, $currentUserId): void {
                    $subQuery->where('teacher_id', $teacherId);
                    if ($currentUserId > 0) {
                        $subQuery->orWhere('created_by', $currentUserId);
                    }
                });
            }
        };

        try {
            $itemsQuery = Course::query()
                ->with(['schoolClass:id,name,section,grade_level', 'creator:id,name'])
                ->whereNull('parent_course_id')
                ->when($isTeacher && $teacherId > 0, function ($query) use ($teacherId, $user): void {
                    $query->where(function ($subQuery) use ($teacherId, $user): void {
                        $subQuery->where('teacher_id', $teacherId);
                        $currentUserId = (int) ($user?->id ?? 0);
                        if ($currentUserId > 0) {
                            $subQuery->orWhere('created_by', $currentUserId);
                        }
                    });
                })
                ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))
                ->when($category !== '' && $category !== 'Tumu', fn ($query) => $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.category')) = ?", [$category]))
                ->when($difficulty !== '' && $difficulty !== 'Tumu', fn ($query) => $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.difficulty')) = ?", [$difficulty]))
                ->when($educationStage !== '' && $educationStage !== 'Tumu', fn ($query) => $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.education_stage')) = ?", [$educationStage]))
                ->when($favoritesOnly && $user, fn ($query) => $query->whereHas('favorites', fn ($fav) => $fav->where('user_id', $user->id)))
                ;
            $applyOwnerFilter($itemsQuery);

            $items = $itemsQuery
                ->orderBy($sort, $dir)
                ->paginate(20)
                ->withQueryString();
        } catch (\Throwable $e) {
            Log::warning('Course index fallback triggered', [
                'message' => $e->getMessage(),
            ]);
            $itemsQuery = Course::query()
                ->with(['schoolClass:id,name,section,grade_level', 'creator:id,name'])
                ->whereNull('parent_course_id')
                ->when($isTeacher && $teacherId > 0, function ($query) use ($teacherId, $user): void {
                    $query->where(function ($subQuery) use ($teacherId, $user): void {
                        $subQuery->where('teacher_id', $teacherId);
                        $currentUserId = (int) ($user?->id ?? 0);
                        if ($currentUserId > 0) {
                            $subQuery->orWhere('created_by', $currentUserId);
                        }
                    });
                })
                ;
            $applyOwnerFilter($itemsQuery);

            $items = $itemsQuery
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString();
        }

        try {
            $teachers = Teacher::query()->with('user:id,name')->orderByDesc('id')->get();
        } catch (\Throwable $e) {
            Log::warning('Course teachers fallback triggered', [
                'message' => $e->getMessage(),
            ]);
            $teachers = collect();
        }
        $classAssignmentsByTeacher = [];
        $teacherVisibleClasses = collect();
        $teacherVisibleGradeLevels = collect();
        try {
            $classAssignmentsByTeacher = SchoolClass::query()
                ->select(['id', 'teacher_id', 'grade_level'])
                ->whereNotNull('teacher_id')
                ->orderBy('grade_level')
                ->orderBy('name')
                ->orderBy('section')
                ->get()
                ->groupBy(fn (SchoolClass $class) => (int) ($class->teacher_id ?? 0))
                ->map(function ($group): array {
                    return [
                        'class_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                        'grade_levels' => $group->pluck('grade_level')->filter(fn ($grade) => $grade !== null)->map(fn ($grade) => (int) $grade)->unique()->values()->all(),
                    ];
                })
                ->all();

            if ($isTeacher && $teacherId > 0) {
                $teacherVisibleClasses = SchoolClass::query()
                    ->where('teacher_id', $teacherId)
                    ->orderBy('grade_level')
                    ->orderBy('name')
                    ->orderBy('section')
                    ->get();
                $teacherVisibleGradeLevels = $teacherVisibleClasses
                    ->pluck('grade_level')
                    ->filter(fn ($grade) => $grade !== null)
                    ->map(fn ($grade) => (int) $grade)
                    ->unique()
                    ->values();
            }
        } catch (\Throwable $e) {
            Log::warning('Course class assignments fallback triggered', [
                'message' => $e->getMessage(),
            ]);
        }
        try {
            $assignableCourses = Course::query()
                ->select(['id', 'name', 'teacher_id', 'parent_course_id'])
                ->orderByDesc('id')
                ->whereNull('parent_course_id')
                ->when(
                    $isTeacher && $teacherId > 0,
                    fn ($query) => $query->where('teacher_id', $teacherId)
                )
                ->get();
        } catch (\Throwable $e) {
            Log::warning('Course assignable courses fallback triggered', [
                'message' => $e->getMessage(),
            ]);
            $assignableCourses = collect();
        }
        $courseIdsByTeacher = Course::query()
            ->select(['id', 'teacher_id'])
            ->whereNotNull('teacher_id')
            ->get()
            ->groupBy(fn (Course $course) => (int) ($course->teacher_id ?? 0))
            ->map(fn ($group) => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all())
            ->all();
        $courseIdsByClass = CourseHomework::query()
            ->select(['course_id', 'school_class_id'])
            ->where('assignment_type', 'lesson')
            ->whereNotNull('school_class_id')
            ->get()
            ->groupBy(fn (CourseHomework $assignment) => (int) ($assignment->school_class_id ?? 0))
            ->map(fn ($group) => $group->pluck('course_id')->map(fn ($id) => (int) $id)->unique()->values()->all())
            ->all();
        $canManageCourses = (bool) ($user?->hasRole('admin') || $user?->hasRole('teacher'));
        $canAssignCourses = (bool) ($user?->hasRole('admin') || $user?->hasRole('teacher'));
        $courseOwners = collect();
        if ($isAdmin) {
            $teacherOwners = Teacher::query()
                ->with('user')
                ->orderBy('id')
                ->get()
                ->map(function (Teacher $teacher): array {
                    return [
                        'value' => (string) ($teacher->user?->id ?? ''),
                        'label' => $teacher->user?->name ?: ('Öğretmen #' . $teacher->id),
                    ];
                })
                ->filter(fn ($item) => $item['value'] !== '')
                ->values();

            $courseOwners = collect([
                ['value' => 'all', 'label' => 'Öğretmen Seçiniz'],
            ])->concat($teacherOwners)->values();
        }

        $favoriteCourseIds = $user
            ? CourseFavorite::query()->where('user_id', $user->id)->pluck('course_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return view('courses.index', compact('items', 'q', 'category', 'difficulty', 'educationStage', 'favoritesOnly', 'favoriteCourseIds', 'sort', 'dir', 'teachers', 'assignableCourses', 'courseIdsByTeacher', 'courseIdsByClass', 'classAssignmentsByTeacher', 'teacherVisibleClasses', 'teacherVisibleGradeLevels', 'canManageCourses', 'canAssignCourses', 'courseOwners', 'ownerFilter', 'isAdmin', 'isTeacher'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $parentCourse = null;
        if (request()->filled('parent_course_id')) {
            $parentCourse = Course::query()->find(request()->integer('parent_course_id'));
        }

        return view('courses.create', compact('teachers', 'classes', 'parentCourse'));
    }
    public function assignTeacher(Request $request, Course $course)
    {
        $data = $request->validate([
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
        ]);
        $course->teacher_id = (int) $data['teacher_id'];
        $course->save();
        $this->forgetDashboardCacheForTeacher((int) $data['teacher_id']);
        return redirect()->route('courses.index')->with('ok', 'Ders ogretmene atandi.');
    }

    public function unassignTeacher(Request $request, Course $course)
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        if ((int) ($course->teacher_id ?? 0) <= 0) {
            return redirect()->route('courses.index')->with('error', 'Bu ders zaten herhangi bir ogretmene atanmis degil.');
        }

        DB::transaction(function () use ($course): void {
            CourseHomework::query()
                ->where('course_id', $course->id)
                ->where('assignment_type', 'lesson')
                ->delete();

            $course->forceFill([
                'teacher_id' => null,
            ])->save();
        });
        $this->forgetDashboardCacheForCourse($course);

        return redirect()->route('courses.index')->with('ok', 'Ders atamasi kaldirildi.');
    }

    public function assignTeacherBulk(Request $request)
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
        $data = $request->validate([
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $courseIds = collect($data['course_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        if ($teacherId <= 0) {
            $teacherId = (int) (optional($request->user()?->teacher)->id ?? 0);
        }
        abort_if($teacherId <= 0, 422, "\u{00d6}\u{011f}retmen se\u{00e7}imi bulunamad\u{0131}.");
        Course::query()
            ->whereIn('id', $courseIds)
            ->update(['teacher_id' => $teacherId]);

        return redirect()->route('courses.index')->with('ok', count($courseIds) . ' ders ' . "\u{00f6}\u{011f}retmene atand\u{0131}.");
    }

    public function unassignTeacherBulk(Request $request)
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
        $data = $request->validate([
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'], 
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $courseIds = collect($data['course_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        if ($teacherId <= 0) {
            $teacherId = (int) (optional($request->user()?->teacher)->id ?? 0);
        }
        abort_if($teacherId <= 0, 422, "\u{00d6}\u{011f}retmen se\u{00e7}imi bulunamad\u{0131}.");
        $updated = 0;

        DB::transaction(function () use ($courseIds, $teacherId, &$updated): void {
            $courses = Course::query()
                ->whereIn('id', $courseIds)
                ->where('teacher_id', $teacherId)
                ->get();

            foreach ($courses as $course) {
                CourseHomework::query()
                    ->where('course_id', $course->id)
                    ->where('assignment_type', 'lesson')
                    ->delete();

                $course->forceFill([
                    'teacher_id' => null,
                ])->save();

                $updated++;
            }
        });
        $this->forgetDashboardCacheForTeacher($teacherId);

        return redirect()->route('courses.index')->with('ok', $updated . ' dersin ' . "\u{00f6}\u{011f}retmen atamas\u{0131} kald\u{0131}r\u{0131}ld\u{0131}.");
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

    public function assignClassesBulk(Request $request)
    {
        $data = $request->validate([
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
        ]);

        $courseIds = collect($data['course_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $classIds = collect($data['class_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();

        foreach ($courseIds as $courseId) {
            $course = Course::query()->find($courseId);
            if (! $course) {
                continue;
            }

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
        }

        return redirect()->route('courses.index')->with('ok', count($courseIds) . ' ders secilen siniflara atandi.');
    }

    public function unassignClassesBulk(Request $request)
    {
        $data = $request->validate([
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
        ]);

        $courseIds = collect($data['course_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $classIds = collect($data['class_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $updated = 0;

        DB::transaction(function () use ($courseIds, $classIds, &$updated): void {
            foreach ($courseIds as $courseId) {
                foreach ($classIds as $classId) {
                    $deleted = CourseHomework::query()
                        ->where('course_id', $courseId)
                        ->where('school_class_id', $classId)
                        ->where('assignment_type', 'lesson')
                        ->delete();
                    $updated += (int) $deleted;
                }
            }
        });

        return redirect()->route('courses.index')->with('ok', $updated . ' ders-sinif atamasi kaldirildi.');
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

    private function forgetDashboardCacheForTeacher(int $teacherId): void
    {
        if ($teacherId <= 0) {
            return;
        }

        $userId = (int) Teacher::query()->whereKey($teacherId)->value('user_id');
        if ($userId <= 0) {
            return;
        }

        $classIds = [0];
        try {
            $classIds = array_merge($classIds, SchoolClass::query()->pluck('id')->map(fn ($id) => (int) $id)->all());
        } catch (\Throwable) {
            // Best-effort cache temizliği
        }

        foreach (array_values(array_unique($classIds)) as $classId) {
            Cache::forget('dashboard.teacher.' . $userId . '.class.' . $classId);
        }
    }

    private function forgetDashboardCacheForCourse(Course $course): void
    {
        $teacherId = (int) ($course->teacher_id ?? 0);
        if ($teacherId > 0) {
            $this->forgetDashboardCacheForTeacher($teacherId);
        }
    }

    public function uploadCover(Request $request)
    {
        $validated = $request->validate([
            'cover_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $path = $this->storeCoverAsWebp($validated['cover_image']);

        return response()->json([
            'url' => asset('storage/' . ltrim($path, '/')),
            'path' => $path,
        ]);
    }

    public function uploadMedia(Request $request)
    {
        $validated = $request->validate([
            'media' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'data_url' => ['nullable', 'string'],
        ]);

        if (empty($validated['media']) && empty($validated['data_url'])) {
            return response()->json([
                'message' => 'Yuklenecek gorsel bulunamadi.',
            ], 422);
        }

        if (! empty($validated['data_url'])) {
            $path = $this->storeCoverFromDataUrl($validated['data_url']);
        } else {
            $path = $this->storeCoverAsWebp($validated['media']);
        }

        return response()->json([
            'url' => asset('storage/' . ltrim($path, '/')),
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

        $fallback = public_path('logo.png');
        if (is_file($fallback)) {
            return response()->file($fallback, [
                'Cache-Control' => 'public, max-age=300',
            ]);
        }

        abort(404);
    }
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();
        $data['parent_course_id'] = !empty($data['parent_course_id']) ? (int) $data['parent_course_id'] : null;
        $data['sort_order'] = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data = $this->attachCoverImageToPayload($request, $data);
        $data['created_by'] = auth()->id();
        $model = $this->service->create($data);

        return $request->expectsJson()
            ? response()->json($model, 201)
            : redirect()->route('courses.index')->with('ok', 'Ders eklendi');
    }
    public function preview(Course $course)
    {
        $slides = $this->presentation->prepareCourseSlides($course, true);

        return view('student-portal.course-show', [
            'student' => null,
            'course' => $course,
            'courseProgress' => null,
            'slides' => $slides,
            'previewMode' => true,
        ]);
    }

    public function show($id)
    {
        $course = Course::with(['teacher.user', 'schoolClass', 'subCourses.teacher.user', 'subCourses.schoolClass'])->find($id);

        $payload = (array) ($course?->lesson_payload ?? []);
        $curriculum = (array) ($payload['curriculum'] ?? []);

        $title = (string) ($course?->name ?? '');
        $lessonNumber = max(1, (int) ($curriculum['lesson_number'] ?? 1));
        $detailTitle = (string) ($curriculum['title'] ?? '');
        $konu = (string) ($curriculum['konu'] ?? '');
        $kazanimlar = array_values(array_filter((array) (
            $curriculum['kazanımlar'] ?? $curriculum['kazanimlar'] ?? []
        ), fn ($item) => trim((string) $item) !== ''));
        $etkinlikler = array_values(array_filter((array) ($curriculum['etkinlikler'] ?? []), fn ($item) => trim((string) $item) !== ''));
        $progress = max(0, min(100, (int) ($curriculum['progress'] ?? 0)));
        $isCompleted = false;
        $startUrl = '#';

        if (auth()->check() && $course) {
            $startUrl = route('student.portal.course-show', $course);
        }

        if (auth()->check() && auth()->user()?->hasRole('student') && $course) {
            $isCompleted = ContentProgress::query()
                ->where('content_id', 'course-' . $course->id)
                ->where('user_id', auth()->id())
                ->where('completed', true)
                ->exists();
        }

        $slides = $course ? $this->presentation->prepareCourseSlides($course, true) : [];
        $subCourses = $course?->subCourses ?? collect();
        $subCourseProgress = collect();
        if (auth()->check() && auth()->user()?->hasRole('student') && $course) {
            $student = $this->getStudent();
            $subCourseProgress = ContentProgress::query()
                ->where('user_id', $student->user_id)
                ->whereIn('content_id', $subCourses->map(fn ($sub) => 'course-' . $sub->id)->values())
                ->get()
                ->keyBy('content_id');
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
            'startUrl',
            'slides',
            'subCourses',
            'subCourseProgress'
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
        $data['parent_course_id'] = !empty($data['parent_course_id']) ? (int) $data['parent_course_id'] : null;
        $data['sort_order'] = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data = $this->attachCoverImageToPayload($request, $data, $course);
        $this->service->update($course, $data);

        return $request->expectsJson()
            ? response()->json($course->refresh())
            : redirect()->route('courses.index')->with('ok', 'Ders guncellendi');
    }

    public function updatePost(UpdateCourseRequest $request, Course $course)
    {
        return $this->update($request, $course);
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

    private function getStudent(): Student
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $student = Student::query()
            ->with(['user', 'schoolClass', 'currentAvatar'])
            ->where('user_id', $user->id)
            ->first();

        abort_unless($student, 403);

        return $student;
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

        $subCourses = $course->subCourses()->orderBy('sort_order')->orderBy('id')->get();
        $package = $this->buildCoursePackage([
            'exported_at' => now()->toIso8601String(),
            'course' => [
                'id' => (int) $course->id,
                'name' => (string) $course->name,
                'code' => (string) $course->code,
                'teacher_id' => (int) $course->teacher_id,
                'school_class_id' => $course->school_class_id !== null ? (int) $course->school_class_id : null,
                'weekly_hours' => (int) $course->weekly_hours,
                'parent_course_id' => $course->parent_course_id !== null ? (int) $course->parent_course_id : null,
                'sort_order' => (int) ($course->sort_order ?? 0),
                'is_active' => (bool) ($course->is_active ?? true),
                'lesson_payload' => (array) ($course->lesson_payload ?? []),
                'created_by' => $course->created_by !== null ? (int) $course->created_by : null,
                'slides' => (array) data_get($course->lesson_payload, 'slides', []),
                'curriculum' => (array) data_get($course->lesson_payload, 'curriculum', []),
                'lesson_description' => (string) data_get($course->lesson_payload, 'lesson_description', ''),
                'category' => (string) data_get($course->lesson_payload, 'category', ''),
                'difficulty' => (string) data_get($course->lesson_payload, 'difficulty', ''),
                'cover_image' => (string) data_get($course->lesson_payload, 'cover_image', ''),
                'cover_image_data' => $this->exportCoverDataUrl($course),
                'cover_image_mime' => $this->exportCoverMime($course),
            ],
            'sub_courses' => $subCourses->map(function (Course $sub) {
                return [
                    'name' => (string) $sub->name,
                    'code' => (string) $sub->code,
                    'teacher_id' => $sub->teacher_id !== null ? (int) $sub->teacher_id : null,
                    'school_class_id' => $sub->school_class_id !== null ? (int) $sub->school_class_id : null,
                    'weekly_hours' => (int) $sub->weekly_hours,
                    'parent_course_id' => (int) $sub->parent_course_id,
                    'sort_order' => (int) ($sub->sort_order ?? 0),
                    'is_active' => (bool) ($sub->is_active ?? true),
                    'lesson_payload' => (array) ($sub->lesson_payload ?? []),
                    'created_by' => $sub->created_by !== null ? (int) $sub->created_by : null,
                    'slides' => (array) data_get($sub->lesson_payload, 'slides', []),
                    'curriculum' => (array) data_get($sub->lesson_payload, 'curriculum', []),
                    'lesson_description' => (string) data_get($sub->lesson_payload, 'lesson_description', ''),
                    'category' => (string) data_get($sub->lesson_payload, 'category', ''),
                    'difficulty' => (string) data_get($sub->lesson_payload, 'difficulty', ''),
                    'cover_image' => (string) data_get($sub->lesson_payload, 'cover_image', ''),
                ];
            })->values()->all(),
        ], $this->exportCoverBinary($course), $this->exportCoverMime($course));

        $filename = 'ders-' . Str::slug((string) $course->name ?: 'course') . '-' . $course->id . '.coursepkg';
        return response()->streamDownload(function () use ($package) {
            echo $package;
        }, $filename, ['Content-Type' => 'application/octet-stream']);
    }

    public function exportAll(): StreamedResponse
    {
        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);

        $courses = Course::query()
            ->with(['subCourses' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->when($user?->hasRole('teacher'), fn ($q) => $q->where('teacher_id', $teacherId))
            ->whereNull('parent_course_id')
            ->orderBy('id')
            ->get();

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'count' => $courses->count(),
            'courses' => $courses->map(function (Course $c) {
                $lessonPayload = (array) ($c->lesson_payload ?? []);
                $lessonPayload['cover_image_data'] = $this->exportCoverDataUrl($c);
                $lessonPayload['cover_image_mime'] = $this->exportCoverMime($c);

                return [
                    'id' => (int) $c->id,
                    'name' => (string) $c->name,
                    'code' => (string) $c->code,
                    'teacher_id' => $c->teacher_id !== null ? (int) $c->teacher_id : null,
                    'school_class_id' => $c->school_class_id !== null ? (int) $c->school_class_id : null,
                    'weekly_hours' => (int) $c->weekly_hours,
                    'created_by' => $c->created_by !== null ? (int) $c->created_by : null,
                    'lesson_payload' => $lessonPayload,
                    'slides' => (array) data_get($lessonPayload, 'slides', []),
                    'curriculum' => (array) data_get($lessonPayload, 'curriculum', []),
                    'lesson_description' => (string) data_get($lessonPayload, 'lesson_description', ''),
                    'category' => (string) data_get($lessonPayload, 'category', ''),
                    'difficulty' => (string) data_get($lessonPayload, 'difficulty', ''),
                    'cover_image' => (string) data_get($lessonPayload, 'cover_image', ''),
                    'cover_image_data' => (string) data_get($lessonPayload, 'cover_image_data', ''),
                    'cover_image_mime' => (string) data_get($lessonPayload, 'cover_image_mime', 'image/png'),
                    'sub_courses' => $c->subCourses->map(function (Course $sub) {
                        $subPayload = (array) ($sub->lesson_payload ?? []);
                        $subPayload['cover_image_data'] = $this->exportCoverDataUrl($sub);
                        $subPayload['cover_image_mime'] = $this->exportCoverMime($sub);

                        return [
                            'name' => (string) $sub->name,
                            'code' => (string) $sub->code,
                            'teacher_id' => $sub->teacher_id !== null ? (int) $sub->teacher_id : null,
                            'school_class_id' => $sub->school_class_id !== null ? (int) $sub->school_class_id : null,
                            'weekly_hours' => (int) $sub->weekly_hours,
                            'parent_course_id' => (int) $sub->parent_course_id,
                            'sort_order' => (int) ($sub->sort_order ?? 0),
                            'is_active' => (bool) ($sub->is_active ?? true),
                            'lesson_payload' => $subPayload,
                            'created_by' => $sub->created_by !== null ? (int) $sub->created_by : null,
                            'slides' => (array) data_get($subPayload, 'slides', []),
                            'curriculum' => (array) data_get($subPayload, 'curriculum', []),
                            'lesson_description' => (string) data_get($subPayload, 'lesson_description', ''),
                            'category' => (string) data_get($subPayload, 'category', ''),
                            'difficulty' => (string) data_get($subPayload, 'difficulty', ''),
                            'cover_image' => (string) data_get($subPayload, 'cover_image', ''),
                            'cover_image_data' => (string) data_get($subPayload, 'cover_image_data', ''),
                            'cover_image_mime' => (string) data_get($subPayload, 'cover_image_mime', 'image/png'),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];

        $filename = 'tum-dersler-' . now()->format('Ymd-His') . '.coursepkg';
        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/octet-stream']);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'course_json' => ['required'],
            'course_json.*' => ['file', 'mimetypes:application/json,text/plain,application/octet-stream', 'max:65536'],
            'parent_course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ]);

        $user = auth()->user();
        $teacherId = (int) (optional($user?->teacher)->id ?? 0);
        if ($teacherId <= 0 && $user?->hasRole('teacher')) {
            $teacher = Teacher::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['branch' => null, 'phone' => null, 'hire_date' => null]
            );
            $teacherId = (int) $teacher->id;
        }
        if ($teacherId <= 0 && $user?->hasRole('teacher')) {
            return redirect()->route('courses.index')->with('error', 'Ogretmen kaydi bulunamadi.');
        }

        $attachToParentCourseId = !empty($data['parent_course_id']) ? (int) $data['parent_course_id'] : null;
        $attachToParentCourse = $attachToParentCourseId ? Course::query()->find($attachToParentCourseId) : null;
        if ($attachToParentCourseId !== null && ! $attachToParentCourse) {
            return redirect()->route('courses.index')->with('error', 'Ana ders bulunamadi.');
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
            $decoded = $this->decodeCourseImportPayload((string) file_get_contents($file->getRealPath()), $coverBinary);
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
                $name = trim((string) Utf8Text::normalize($courseData['name'] ?? $courseData['title'] ?? ''));
                if ($name === '') continue;
                $rawCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($courseData['code'] ?? 'CRS')));
                $baseCode = substr($rawCode !== '' ? $rawCode : 'CRS', 0, 20);
                $finalCode = $baseCode . '-' . strtoupper(Str::random(6)); // max 27 char
                $lessonPayloadRaw = $courseData['lesson_payload'] ?? $courseData['payload'] ?? [];
                if (is_string($lessonPayloadRaw)) {
                    $decodedPayload = json_decode($lessonPayloadRaw, true);
                    $lessonPayload = is_array($decodedPayload) ? $decodedPayload : [];
                } else {
                    $lessonPayload = (array) $lessonPayloadRaw;
                }
                $lessonPayload = $this->normalizeCourseImportData($lessonPayload);
                $topLevelPayload = array_filter([
                    'slides' => $courseData['slides'] ?? null,
                    'curriculum' => $courseData['curriculum'] ?? null,
                    'lesson_description' => $courseData['lesson_description'] ?? null,
                    'difficulty' => $courseData['difficulty'] ?? null,
                    'category' => $courseData['category'] ?? null,
                    'cover_image' => $courseData['cover_image'] ?? null,
                    'cover_image_data' => $courseData['cover_image_data'] ?? null,
                ], fn ($v) => $v !== null && $v !== '');
                $lessonPayload = array_replace_recursive($lessonPayload, $topLevelPayload);
                $lessonPayload = $this->normalizeCourseImportData($lessonPayload);
                if (empty($lessonPayload['slides']) && is_array(data_get($lessonPayload, 'lesson_pages'))) {
                    $lessonPayload['slides'] = array_values(array_map(function ($text, $idx) {
                        return [
                            'title' => 'Sayfa ' . ($idx + 1),
                            'xp' => 0,
                            'kind' => 'topic',
                            'interaction_type' => 'none',
                            'points' => 5,
                            'time_limit' => 10,
                            'double_points' => false,
                            'content' => (string) $text,
                            'instructions' => '',
                            'image_url' => '',
                            'video_url' => '',
                            'file_url' => '',
                            'code' => '',
                            'question_prompt' => '',
                            'question' => ['options' => [], 'pairs' => [], 'items' => []],
                        ];
                    }, (array) $lessonPayload['lesson_pages'], array_keys((array) $lessonPayload['lesson_pages'])));
                }
                if (empty($lessonPayload['slides'])) {
                    $legacyText = trim((string) (
                        $courseData['content']
                        ?? $courseData['text']
                        ?? $courseData['body']
                        ?? $courseData['description']
                        ?? data_get($lessonPayload, 'content')
                        ?? data_get($lessonPayload, 'text')
                        ?? data_get($lessonPayload, 'body')
                        ?? data_get($lessonPayload, 'description')
                        ?? ''
                    ));
                    $legacyCode = trim((string) (
                        $courseData['code']
                        ?? data_get($lessonPayload, 'code')
                        ?? data_get($lessonPayload, 'html')
                        ?? data_get($lessonPayload, 'source_code')
                        ?? data_get($lessonPayload, 'script')
                        ?? ''
                    ));
                    $legacyQuestion = trim((string) (
                        $courseData['question_prompt']
                        ?? data_get($lessonPayload, 'question_prompt')
                        ?? data_get($lessonPayload, 'prompt')
                        ?? data_get($lessonPayload, 'questionText')
                        ?? ''
                    ));
                    if ($legacyText !== '' || $legacyCode !== '' || $legacyQuestion !== '') {
                        $lessonPayload['slides'] = [[
                            'title' => (string) (data_get($lessonPayload, 'title') ?: $courseData['name'] ?: 'Sayfa 1'),
                            'xp' => (int) (data_get($lessonPayload, 'xp') ?: 0),
                            'kind' => (string) (data_get($lessonPayload, 'kind') ?: 'topic'),
                            'interaction_type' => (string) (data_get($lessonPayload, 'interaction_type') ?: 'none'),
                            'points' => (int) (data_get($lessonPayload, 'points') ?: 5),
                            'time_limit' => (int) (data_get($lessonPayload, 'time_limit') ?: 10),
                            'double_points' => (bool) data_get($lessonPayload, 'double_points', false),
                            'content' => $legacyText,
                            'instructions' => (string) (data_get($lessonPayload, 'instructions') ?: ''),
                            'image_url' => (string) (data_get($lessonPayload, 'image_url') ?: ''),
                            'video_url' => (string) (data_get($lessonPayload, 'video_url') ?: ''),
                            'file_url' => (string) (data_get($lessonPayload, 'file_url') ?: ''),
                            'code' => $legacyCode,
                            'question_prompt' => $legacyQuestion,
                            'question' => data_get($lessonPayload, 'question') ?: ['options' => [], 'pairs' => [], 'items' => []],
                        ]];
                    }
                }
                if (! isset($lessonPayload['slides']) || ! is_array($lessonPayload['slides'])) {
                    $lessonPayload['slides'] = [];
                }
                if (! isset($lessonPayload['curriculum']) || ! is_array($lessonPayload['curriculum'])) {
                    $lessonPayload['curriculum'] = [];
                }
                if (isset($coverBinary) && $coverBinary !== '' && empty($lessonPayload['cover_image_data'])) {
                    $lessonPayload['cover_image_data'] = $this->binaryToDataUrl($coverBinary, $courseData['cover_image_mime'] ?? 'image/png');
                }
                if (!empty($lessonPayload['cover_image_data']) && is_string($lessonPayload['cover_image_data'])) {
                    try {
                        $lessonPayload['cover_image'] = $this->storeCoverFromDataUrl($lessonPayload['cover_image_data']);
                    } catch (\Throwable $e) {
                        Log::warning('Course cover import failed', ['message' => $e->getMessage(), 'course_name' => $name]);
                    }
                    unset($lessonPayload['cover_image_data']);
                } elseif (!empty($lessonPayload['cover_image']) && is_string($lessonPayload['cover_image'])) {
                    try {
                        $lessonPayload['cover_image'] = $this->storeCoverFromUrlOrPath((string) $lessonPayload['cover_image']);
                    } catch (\Throwable $e) {
                        Log::warning('Course cover import fallback failed', ['message' => $e->getMessage(), 'course_name' => $name]);
                        unset($lessonPayload['cover_image']);
                    }
                }
                $cover = trim((string) ($lessonPayload['cover_image'] ?? ''));
                if ($cover !== '') {
                    $cover = ltrim(str_replace('\\', '/', $cover), '/');
                    $cover = preg_replace('#^storage/#i', '', $cover);
                    $cover = preg_replace('#^course-covers/#i', 'course-covers/', $cover);
                    $resolvedCoverPath = $this->resolveCoverFilePath($cover);
                    if ($resolvedCoverPath === '' || !is_file($resolvedCoverPath)) {
                        unset($lessonPayload['cover_image']);
                    } else {
                        $lessonPayload['cover_image'] = $cover;
                    }
                }
                $importTeacherId = null;
                if ($user?->hasRole('teacher')) {
                    $candidateTeacherId = (int) ($courseData['teacher_id'] ?? 0);
                    if ($candidateTeacherId > 0 && Teacher::query()->whereKey($candidateTeacherId)->exists()) {
                        $importTeacherId = $candidateTeacherId;
                    } elseif ($teacherId > 0) {
                        $importTeacherId = $teacherId;
                    }
                }

                $importClassId = $courseData['school_class_id'] ?? null;
                $importClassId = is_numeric($importClassId) ? (int) $importClassId : null;
                if ($importClassId !== null && !SchoolClass::query()->whereKey($importClassId)->exists()) {
                    $importClassId = null;
                }

                $importCreatedBy = (int) auth()->id();

                $parentCourse = Course::query()->create([
                    'name' => $name,
                    'code' => $finalCode,
                    'teacher_id' => $importTeacherId,
                    'school_class_id' => $importClassId,
                    'weekly_hours' => max(1, min(20, (int) ($courseData['weekly_hours'] ?? 2))),
                    'parent_course_id' => $attachToParentCourseId ?? (!empty($courseData['parent_course_id']) ? (int) $courseData['parent_course_id'] : null),
                    'sort_order' => (int) ($courseData['sort_order'] ?? 0),
                    'is_active' => array_key_exists('is_active', $courseData) ? (bool) $courseData['is_active'] : true,
                    'lesson_payload' => $lessonPayload,
                    'created_by' => $importCreatedBy,
                ]);
                $created[] = $parentCourse;

                $subCoursesData = array_values(array_filter((array) ($courseData['sub_courses'] ?? $decoded['sub_courses'] ?? []), fn ($x) => is_array($x)));
                foreach ($subCoursesData as $subData) {
                    $subName = trim((string) Utf8Text::normalize($subData['name'] ?? $subData['title'] ?? ''));
                    if ($subName === '') {
                        continue;
                    }
                    $subCodeBase = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($subData['code'] ?? $subName)));
                    $subCodeBase = substr($subCodeBase !== '' ? $subCodeBase : 'SUB', 0, 20);
                    $subFinalCode = $subCodeBase . '-' . strtoupper(Str::random(6));
                    $subPayloadRaw = $subData['lesson_payload'] ?? $subData['payload'] ?? [];
                    $subPayload = is_string($subPayloadRaw) ? (json_decode($subPayloadRaw, true) ?: []) : (array) $subPayloadRaw;
                    $subPayload = $this->normalizeCourseImportData($subPayload);
                    $subPayload = array_replace_recursive($subPayload, array_filter([
                        'slides' => $subData['slides'] ?? null,
                        'curriculum' => $subData['curriculum'] ?? null,
                        'lesson_description' => $subData['lesson_description'] ?? null,
                        'difficulty' => $subData['difficulty'] ?? null,
                        'category' => $subData['category'] ?? null,
                        'cover_image' => $subData['cover_image'] ?? null,
                    ], fn ($v) => $v !== null && $v !== ''));
                    $subPayload = $this->normalizeCourseImportData($subPayload);
                    if (! isset($subPayload['slides']) || ! is_array($subPayload['slides'])) {
                        $subPayload['slides'] = [];
                    }
                    if (! isset($subPayload['curriculum']) || ! is_array($subPayload['curriculum'])) {
                        $subPayload['curriculum'] = [];
                    }
                    $subCourseTeacherId = $importTeacherId;
                    if ($user?->hasRole('teacher')) {
                        $candidateSubTeacherId = (int) ($subData['teacher_id'] ?? 0);
                        if ($candidateSubTeacherId > 0 && Teacher::query()->whereKey($candidateSubTeacherId)->exists()) {
                            $subCourseTeacherId = $candidateSubTeacherId;
                        }
                    }
                    $subCourseClassId = $subData['school_class_id'] ?? $parentCourse->school_class_id;
                    $subCourseClassId = is_numeric($subCourseClassId) ? (int) $subCourseClassId : null;
                    if ($subCourseClassId !== null && !SchoolClass::query()->whereKey($subCourseClassId)->exists()) {
                        $subCourseClassId = $parentCourse->school_class_id;
                    }
                    Course::query()->create([
                        'name' => $subName,
                        'code' => $subFinalCode,
                        'teacher_id' => $subCourseTeacherId,
                        'school_class_id' => $subCourseClassId,
                        'weekly_hours' => max(1, min(20, (int) ($subData['weekly_hours'] ?? $parentCourse->weekly_hours ?? 2))),
                        'parent_course_id' => $parentCourse->id,
                        'sort_order' => (int) ($subData['sort_order'] ?? 0),
                        'is_active' => array_key_exists('is_active', $subData) ? (bool) $subData['is_active'] : true,
                        'lesson_payload' => $subPayload,
                        'created_by' => $importCreatedBy,
                    ]);
                }
            }
        }

        if (count($created) < 1) {
            return redirect()->route('courses.index')->with('error', 'Yüklenen dosyalarda geçerli ders bulunamadı. .coursepkg dosyası indirildiği şekilde yüklenmeli.');
        }

        if ($attachToParentCourse) {
            return redirect()->route('course.detail', ['id' => $attachToParentCourse->id])
                ->with('ok', count($created) . ' alt ders yuklendi.');
        }

        return redirect()->route('courses.index')->with('ok', count($created) . ' ders yuklendi. Kapak verisi varsa da geri yuklendi.');
    }

    private function buildCoursePackage(array $payload, string $coverBinary = '', string $coverMime = 'image/png'): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new \RuntimeException('Ders paketi olusturulamadi.');
        }

        $boundary = 'COURSEPKG-' . Str::lower(Str::random(24));
        $parts = [];
        $parts[] = "--{$boundary}\r\nContent-Type: application/json; charset=UTF-8\r\nContent-Disposition: form-data; name=\"manifest\"; filename=\"manifest.json\"\r\n\r\n{$json}\r\n";

        if ($coverBinary !== '') {
            $courseName = (string) data_get($payload, 'course.name', 'course');
            $base = Str::slug($courseName ?: 'course');
            $coverName = $base !== '' ? $base . '-cover.' . $this->mimeToExtension($coverMime) : 'cover.' . $this->mimeToExtension($coverMime);
            $parts[] = "--{$boundary}\r\nContent-Type: {$coverMime}\r\nContent-Disposition: form-data; name=\"cover\"; filename=\"{$coverName}\"\r\n\r\n{$coverBinary}\r\n";
        }

        $parts[] = "--{$boundary}--\r\n";
        return "COURSEPKG2\r\nBOUNDARY:{$boundary}\r\n\r\n" . implode('', $parts);
    }

    private function decodeCourseImportPayload(string $raw, ?string &$coverBinary = null): ?array
    {
        $coverBinary = null;
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;

        if (str_starts_with($raw, 'COURSEPKG2')) {
            $split = preg_split("/\r?\n\r?\n/", $raw, 2);
            if (! is_array($split) || count($split) !== 2) {
                return null;
            }

            [$header, $body] = $split;
            if (!preg_match('/^BOUNDARY:(.+)$/m', $header, $m)) {
                return null;
            }
            $boundary = trim($m[1]);
            $parts = preg_split('/\r?\n--' . preg_quote($boundary, '/') . '(?:--)?\r?\n/', "\r\n" . $body);
            if (! is_array($parts) || count($parts) < 2) {
                return null;
            }

            $manifest = '';
            $coverBinary = '';
            foreach ($parts as $part) {
                if (trim($part) === '') {
                    continue;
                }
                [$partHeaders, $partBody] = preg_split("/\r?\n\r?\n/", $part, 2) + [null, null];
                if (! is_string($partHeaders) || ! is_string($partBody)) {
                    continue;
                }
                if (str_contains($partHeaders, 'application/json')) {
                    $manifest = trim($partBody);
                } elseif (preg_match('/Content-Type:\s*([^\r\n]+)/i', $partHeaders, $typeMatch)) {
                    $coverBinary = rtrim($partBody, "\r\n");
                    $coverMime = trim($typeMatch[1]);
                    $coverBinary = $coverBinary;
                }
            }

            if ($manifest === '') {
                return null;
            }

            $decoded = json_decode($manifest, true);
            return is_array($decoded) ? $decoded : null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function exportCoverBinary(Course $course): string
    {
        $cover = $this->resolveCoverFilePath((string) data_get($course->lesson_payload, 'cover_image', ''));
        if ($cover !== '' && is_file($cover)) {
            $binary = (string) @file_get_contents($cover);
            if ($binary !== '') {
                return $binary;
            }
        }

        $coverUrl = $course->coverImageUrl();
        if ($coverUrl !== '') {
            $binary = (string) @file_get_contents($coverUrl);
            return $binary;
        }

        return '';
    }

    private function exportCoverMime(Course $course): string
    {
        $cover = $this->resolveCoverFilePath((string) data_get($course->lesson_payload, 'cover_image', ''));
        if ($cover === '' || ! is_file($cover)) {
            $cover = $course->coverImageUrl();
            $path = (string) parse_url($cover, PHP_URL_PATH);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        } else {
            $ext = strtolower(pathinfo($cover, PATHINFO_EXTENSION));
        }

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }

    private function mimeToExtension(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };
    }

    private function binaryToDataUrl(string $binary, string $mime = 'image/png'): string
    {
        $mime = trim($mime) !== '' ? $mime : 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($binary);
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

            // Ogretmen kendi olusturdugu dersi tamamen silebilir (alt dersleriyle birlikte).
            if ((int) ($course->created_by ?? 0) === (int) auth()->id()) {
                $courseIds = $this->collectCourseIdsWithDescendants($courseId);
                DB::transaction(function () use ($courseIds) {
                    CourseHomework::query()->whereIn('course_id', $courseIds)->delete();
                    Course::query()->whereIn('id', $courseIds)->delete();
                });
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

        $courseIds = $this->collectCourseIdsWithDescendants($courseId);

        DB::transaction(function () use ($courseId, $courseIds) {
            CourseHomework::query()->whereIn('course_id', $courseIds)->delete();
            $deleted = Course::query()->whereIn('id', $courseIds)->delete();
            if ($deleted < 1 || ! in_array($courseId, $courseIds, true)) {
                throw new \RuntimeException('Ders kaydi bulunamadi veya silinemedi.');
            }
        });
    }

    /**
     * Verilen ders id'si ile birlikte tüm alt derslerinin (ve varsa onların da alt derslerinin)
     * id'lerini toplar, böylece bir ders silinirken alt dersleri de yetim kalmadan silinir.
     *
     * @return array<int, int>
     */
    private function collectCourseIdsWithDescendants(int $courseId): array
    {
        $ids = [$courseId];
        $queue = [$courseId];

        while ($queue !== []) {
            $childIds = Course::query()
                ->whereIn('parent_course_id', $queue)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $childIds = array_values(array_diff($childIds, $ids));
            if ($childIds === []) {
                break;
            }

            $ids = array_merge($ids, $childIds);
            $queue = $childIds;
        }

        return $ids;
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

    private function attachCoverImageToPayload(Request $request, array $data, ?Course $existingCourse = null): array
    {
        $payload = [];
        if (!empty($data['lesson_payload'])) {
            $rawPayload = (string) $data['lesson_payload'];
            $decodedBase64 = $this->decodeBase64PayloadString($rawPayload);
            if ($decodedBase64 !== null) {
                $rawPayload = $decodedBase64;
            }
            $decoded = json_decode($rawPayload, true);
            if (!is_array($decoded)) {
                $decoded = json_decode(html_entity_decode($rawPayload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), true);
            }
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        $payload = $this->sanitizeLessonPayloadForStorage($payload);
        $existingCover = '';
        if ($existingCourse) {
            $existingCover = trim((string) data_get($existingCourse->lesson_payload, 'cover_image', ''));
            if ($existingCover === '') {
                $existingCover = trim((string) $existingCourse->coverImageUrl());
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
            if ($existingCover !== '' && empty($payload['cover_image'])) {
                $payload['cover_image'] = $existingCover;
                $data['lesson_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
            }
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

    private function decodeBase64PayloadString(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $decoded = base64_decode($trimmed, true);
        if (! is_string($decoded) || $decoded === '') {
            return null;
        }

        $reencoded = base64_encode($decoded);
        if ($reencoded !== preg_replace('/\s+/', '', $trimmed)) {
            return null;
        }

        return $decoded;
    }

    private function sanitizeLessonPayloadForStorage(array $payload): array
    {
        $walk = function ($value) use (&$walk) {
            if (is_array($value)) {
                $clean = [];
                foreach ($value as $key => $item) {
                    $clean[$key] = $walk($item);
                }

                return $clean;
            }

            if (! is_string($value)) {
                return $value;
            }

            return Utf8Text::normalize($value);
        };

        $payload = $walk($payload);
        if (is_array($payload)) {
            unset($payload['cover_image_data']);
        }

        return is_array($payload) ? $payload : [];
    }

    private function normalizeCourseImportData(array $payload): array
    {
        $payload = Utf8Text::sanitizeArray($payload);

        foreach (['slides', 'curriculum', 'lesson_pages'] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                continue;
            }

            $payload[$key] = Utf8Text::sanitizeArray($payload[$key]);
        }

        if (! empty($payload['lesson_description']) && is_string($payload['lesson_description'])) {
            $payload['lesson_description'] = (string) Utf8Text::normalize($payload['lesson_description']);
        }
        if (! empty($payload['category']) && is_string($payload['category'])) {
            $payload['category'] = (string) Utf8Text::normalize($payload['category']);
        }
        if (! empty($payload['difficulty']) && is_string($payload['difficulty'])) {
            $payload['difficulty'] = (string) Utf8Text::normalize($payload['difficulty']);
        }

        return $payload;
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
        // storage/app/public survives this host's git-based deploys (deploy
        // resets public/ to whatever is committed, wiping anything written
        // there at runtime). Files here are reachable via the public/storage
        // symlink created by `php artisan storage:link`.
        $preferred = storage_path('app/public/kapak-gorseli');
        if (is_dir($preferred) || @mkdir($preferred, 0775, true) || is_dir($preferred)) {
            return $preferred;
        }

        $alt = public_path('kapak-gorseli');
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
        $binary = '';
        $mime = 'image/png';

        if ($cover !== '' && is_file($cover)) {
            $binary = (string) @file_get_contents($cover);
            $mime = match (strtolower(pathinfo($cover, PATHINFO_EXTENSION))) {
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => 'image/png',
            };
        } else {
            $coverUrl = $course->coverImageUrl();
            if ($coverUrl !== '') {
                $path = (string) parse_url($coverUrl, PHP_URL_PATH);
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };

                try {
                    $binary = (string) @file_get_contents($coverUrl);
                } catch (\Throwable $e) {
                    $binary = '';
                }
            }
        }

        if ($binary === '') {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }

    private function storeCoverFromUrlOrPath(string $cover): string
    {
        $cover = trim($cover);
        if ($cover === '') {
            throw new \RuntimeException('Kapak gorseli bos.');
        }

        if (str_starts_with($cover, 'data:image/')) {
            return $this->storeCoverFromDataUrl($cover);
        }

        $path = $this->resolveCoverFilePath($cover);
        if ($path !== '' && is_file($path)) {
            $binary = (string) @file_get_contents($path);
            if ($binary !== '') {
                $relative = 'kapak-gorseli/' . Str::uuid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
                $outputPath = $this->coverStorageDirectory() . '/' . basename($relative);
                if (file_put_contents($outputPath, $binary) === false) {
                    throw new \RuntimeException('Kapak gorseli yazilamadi.');
                }
                return $relative;
            }
        }

        if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
            $binary = @file_get_contents($cover);
            if ($binary !== false && $binary !== '') {
                $ext = strtolower(pathinfo((string) parse_url($cover, PHP_URL_PATH), PATHINFO_EXTENSION));
                $ext = in_array($ext, ['png', 'webp', 'jpg', 'jpeg'], true) ? $ext : 'png';
                $relative = 'kapak-gorseli/' . Str::uuid() . '.' . $ext;
                $outputPath = $this->coverStorageDirectory() . '/' . basename($relative);
                if (file_put_contents($outputPath, $binary) === false) {
                    throw new \RuntimeException('Kapak gorseli yazilamadi.');
                }
                return $relative;
            }
        }

        throw new \RuntimeException('Kapak gorseli alinamadi.');
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
