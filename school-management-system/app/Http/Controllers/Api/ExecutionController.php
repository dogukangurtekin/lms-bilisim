<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Flowchart\FlowchartExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExecutionController extends Controller
{
    public function __construct(private readonly FlowchartExecutionService $executionService)
    {
    }

    public function execute(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nodes' => ['required', 'array', 'min:2'],
            'nodes.*.id' => ['required', 'string'],
            'nodes.*.type' => ['required', 'in:start,end,process,io,decision'],
            'nodes.*.text' => ['nullable', 'string'],
            'nodes.*.code' => ['nullable', 'string'],
            'edges' => ['required', 'array'],
            'edges.*.id' => ['required', 'string'],
            'edges.*.from' => ['required', 'string'],
            'edges.*.to' => ['required', 'string'],
            'edges.*.condition' => ['nullable', 'in:yes,no'],
            'inputs' => ['nullable', 'array'],
            'step_mode' => ['nullable', 'boolean'],
        ]);

        $result = $this->executionService->execute(
            $payload['nodes'],
            $payload['edges'],
            $payload['inputs'] ?? [],
            (bool) ($payload['step_mode'] ?? false)
        );

        return response()->json($result);
    }
}

