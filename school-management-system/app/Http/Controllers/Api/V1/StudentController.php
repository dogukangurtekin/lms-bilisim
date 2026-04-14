<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Services\Domain\StudentService;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function __construct(private StudentService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(Student::query()->latest()->paginate(20));
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $created = $this->service->create($request->validated());
        return response()->json($created, 201);
    }

    public function show(Student $model): JsonResponse
    {
        return response()->json($model);
    }

    public function update(UpdateStudentRequest $request, Student $model): JsonResponse
    {
        $this->service->update($model, $request->validated());
        return response()->json($model->refresh());
    }

    public function destroy(Student $model): JsonResponse
    {
        $this->service->delete($model);
        return response()->json([], 204);
    }
}
