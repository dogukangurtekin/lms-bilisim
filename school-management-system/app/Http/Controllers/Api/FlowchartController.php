<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flowchart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlowchartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $this->validatePayload($request);
        $flowchart = DB::transaction(function () use ($request, $payload) {
            $flowchart = Flowchart::create([
                'name' => $payload['name'],
                'user_id' => (int) $request->user()->id,
            ]);
            $this->syncGraph($flowchart, $payload['nodes'], $payload['edges']);
            return $flowchart->fresh(['nodes', 'edges']);
        });

        return response()->json($this->toDto($flowchart), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $flowchart = Flowchart::with(['nodes', 'edges'])
            ->where('user_id', (int) $request->user()->id)
            ->findOrFail($id);

        return response()->json($this->toDto($flowchart));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = $this->validatePayload($request);
        $flowchart = DB::transaction(function () use ($request, $id, $payload) {
            $flowchart = Flowchart::query()
                ->where('user_id', (int) $request->user()->id)
                ->findOrFail($id);

            $flowchart->update(['name' => $payload['name']]);
            $this->syncGraph($flowchart, $payload['nodes'], $payload['edges']);
            return $flowchart->fresh(['nodes', 'edges']);
        });

        return response()->json($this->toDto($flowchart));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $flowchart = Flowchart::query()
            ->where('user_id', (int) $request->user()->id)
            ->findOrFail($id);
        $flowchart->delete();

        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'nodes' => ['required', 'array', 'min:2'],
            'nodes.*.id' => ['required', 'string', 'max:60'],
            'nodes.*.type' => ['required', 'in:start,end,process,io,decision'],
            'nodes.*.text' => ['nullable', 'string', 'max:255'],
            'nodes.*.code' => ['nullable', 'string'],
            'nodes.*.position.x' => ['required', 'numeric'],
            'nodes.*.position.y' => ['required', 'numeric'],
            'edges' => ['required', 'array'],
            'edges.*.id' => ['required', 'string', 'max:60'],
            'edges.*.from' => ['required', 'string', 'max:60'],
            'edges.*.to' => ['required', 'string', 'max:60'],
            'edges.*.condition' => ['nullable', 'in:yes,no'],
        ]);
    }

    private function syncGraph(Flowchart $flowchart, array $nodes, array $edges): void
    {
        $flowchart->nodes()->delete();
        $flowchart->edges()->delete();

        $flowchart->nodes()->createMany(array_map(static fn ($node) => [
            'node_key' => (string) $node['id'],
            'type' => (string) $node['type'],
            'text' => (string) ($node['text'] ?? ''),
            'code' => (string) ($node['code'] ?? ''),
            'position_x' => (float) ($node['position']['x'] ?? 0),
            'position_y' => (float) ($node['position']['y'] ?? 0),
        ], $nodes));

        $flowchart->edges()->createMany(array_map(static fn ($edge) => [
            'edge_key' => (string) $edge['id'],
            'from_node' => (string) $edge['from'],
            'to_node' => (string) $edge['to'],
            'condition' => $edge['condition'] ?? null,
        ], $edges));
    }

    private function toDto(Flowchart $flowchart): array
    {
        return [
            'id' => $flowchart->id,
            'name' => $flowchart->name,
            'nodes' => $flowchart->nodes->map(static fn ($node) => [
                'id' => $node->node_key,
                'type' => $node->type,
                'text' => $node->text,
                'code' => $node->code,
                'position' => [
                    'x' => (float) $node->position_x,
                    'y' => (float) $node->position_y,
                ],
            ])->values()->all(),
            'edges' => $flowchart->edges->map(static fn ($edge) => [
                'id' => $edge->edge_key,
                'from' => $edge->from_node,
                'to' => $edge->to_node,
                'condition' => $edge->condition,
            ])->values()->all(),
            'created_at' => $flowchart->created_at?->toIso8601String(),
            'updated_at' => $flowchart->updated_at?->toIso8601String(),
        ];
    }
}

