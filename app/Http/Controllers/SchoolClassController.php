<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use App\Services\Domain\SchoolClassService;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function __construct(private SchoolClassService $service)
    {
    }

    public function index(Request $request)
    {
        $className = trim($request->string('class_name')->toString());
        $section = trim($request->string('section')->toString());

        $items = SchoolClass::with('teacher.user')
            ->when($className !== '', fn ($query) => $query->where('name', 'like', "%{$className}%"))
            ->when($section !== '', fn ($query) => $query->where('section', 'like', "%{$section}%"))
            ->orderBy('name')
            ->orderBy('section')
            ->paginate(50)
            ->withQueryString();

        return view('school-classes.index', compact('items', 'className', 'section'));
    }

    public function create() { return view('school-classes.create'); }
    public function store(StoreSchoolClassRequest $request) { $model = $this->service->create($request->validated()); return $request->expectsJson() ? response()->json($model, 201) : redirect()->route('classes.index')->with('ok', 'Sinif eklendi'); }
    public function show(SchoolClass $class) { return view('school-classes.show', ['classroom' => $class]); }
    public function edit(SchoolClass $class) { return view('school-classes.edit', ['classroom' => $class]); }
    public function update(UpdateSchoolClassRequest $request, SchoolClass $class) { $this->service->update($class, $request->validated()); return $request->expectsJson() ? response()->json($class->refresh()) : redirect()->route('classes.index')->with('ok', 'Sinif guncellendi'); }
    public function destroy(SchoolClass $class) { $this->service->delete($class); return request()->expectsJson() ? response()->json([], 204) : redirect()->route('classes.index')->with('ok', 'Sinif silindi'); }
}
