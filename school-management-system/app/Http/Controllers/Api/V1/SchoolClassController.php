<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use App\Services\Domain\SchoolClassService;
use Illuminate\Http\JsonResponse;

class SchoolClassController extends Controller
{
    public function __construct(private SchoolClassService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(SchoolClass::query()->latest()->paginate(20));
    }

    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $created = $this->service->create($request->validated());
        return response()->json($created, 201);
    }

    public function show(SchoolClass $model): JsonResponse
    {
        return response()->json($model);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $model): JsonResponse
    {
        $this->service->update($model, $request->validated());
        return response()->json($model->refresh());
    }

    public function destroy(SchoolClass $model): JsonResponse
    {
        $this->service->delete($model);
        return response()->json([], 204);
    }
}
