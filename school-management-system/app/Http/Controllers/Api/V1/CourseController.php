<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Services\Domain\CourseService;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    public function __construct(private CourseService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(Course::query()->latest()->paginate(20));
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $created = $this->service->create($request->validated());
        return response()->json($created, 201);
    }

    public function show(Course $model): JsonResponse
    {
        return response()->json($model);
    }

    public function update(UpdateCourseRequest $request, Course $model): JsonResponse
    {
        $this->service->update($model, $request->validated());
        return response()->json($model->refresh());
    }

    public function destroy(Course $model): JsonResponse
    {
        $this->service->delete($model);
        return response()->json([], 204);
    }
}
