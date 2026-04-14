<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\Domain\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(private CourseService $service)
    {
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $sort = in_array($request->string('sort')->toString(), ['id', 'name', 'code', 'created_at'], true) ? $request->string('sort')->toString() : 'id';
        $dir = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';

        $items = Course::with(['teacher.user', 'schoolClass'])
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->orderBy($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        return view('courses.index', compact('items', 'q', 'sort', 'dir'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('courses.create', compact('teachers', 'classes'));
    }
    public function store(StoreCourseRequest $request) { $model = $this->service->create($request->validated()); return $request->expectsJson() ? response()->json($model, 201) : redirect()->route('courses.index')->with('ok', 'Ders eklendi'); }
    public function show(Course $course) { return view('courses.show', compact('course')); }
    public function edit(Course $course)
    {
        $teachers = Teacher::with('user')->orderByDesc('id')->get();
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('courses.edit', compact('course', 'teachers', 'classes'));
    }
    public function update(UpdateCourseRequest $request, Course $course) { $this->service->update($course, $request->validated()); return $request->expectsJson() ? response()->json($course->refresh()) : redirect()->route('courses.index')->with('ok', 'Ders guncellendi'); }
    public function destroy(Course $course) { $this->service->delete($course); return request()->expectsJson() ? response()->json([], 204) : redirect()->route('courses.index')->with('ok', 'Ders silindi'); }
}
