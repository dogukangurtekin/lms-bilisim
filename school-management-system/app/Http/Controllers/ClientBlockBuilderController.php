<?php

namespace App\Http\Controllers;

use App\Models\BlockBuilderDesign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientBlockBuilderController extends Controller
{
    public function latest(Request $request): JsonResponse
    {
        $uid = (int) $request->attributes->get('client_uid', 0);
        if ($uid <= 0) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }

        $design = BlockBuilderDesign::query()
            ->where('user_id', $uid)
            ->latest('updated_at')
            ->first();

        if (! $design) {
            return response()->json(['design' => null]);
        }

        return response()->json(['design' => $this->serializeDesign($design)]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $uid = (int) $request->attributes->get('client_uid', 0);
        if ($uid <= 0) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }

        $design = BlockBuilderDesign::query()
            ->whereKey($id)
            ->where('user_id', $uid)
            ->first();

        if (! $design) {
            return response()->json(['message' => 'not found'], 404);
        }

        return response()->json(['design' => $this->serializeDesign($design)]);
    }

    public function store(Request $request): JsonResponse
    {
        $uid = (int) $request->attributes->get('client_uid', 0);
        if ($uid <= 0) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }

        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:120'],
            'blocks' => ['required', 'array', 'max:20000'],
            'blocks.*.x' => ['required', 'integer', 'min:-512', 'max:512'],
            'blocks.*.y' => ['required', 'integer', 'min:0', 'max:512'],
            'blocks.*.z' => ['required', 'integer', 'min:-512', 'max:512'],
            'blocks.*.color' => ['required', 'string', 'max:16'],
            'blocks.*.texture' => ['nullable', 'string', 'max:32'],
        ]);

        $name = trim((string) ($validated['name'] ?? ''));
        if ($name === '') {
            $name = 'Tasarim ' . now()->format('Y-m-d H:i');
        }

        $inputBlocks = is_array($validated['blocks']) ? $validated['blocks'] : [];
        $blocks = collect($inputBlocks)->map(function (array $block): array {
            return [
                'x' => (int) $block['x'],
                'y' => (int) $block['y'],
                'z' => (int) $block['z'],
                'color' => Str::lower(trim((string) $block['color'])),
                'texture' => Str::lower(trim((string) ($block['texture'] ?? 'plain'))),
            ];
        })->values()->all();

        $designId = (int) ($validated['id'] ?? 0);
        $design = null;
        if ($designId > 0) {
            $design = BlockBuilderDesign::query()
                ->whereKey($designId)
                ->where('user_id', $uid)
                ->first();
        }

        if (! $design) {
            $design = new BlockBuilderDesign();
            $design->user_id = $uid;
        }

        $design->name = $name;
        $design->blocks = $blocks;
        $design->save();

        return response()->json(['design' => $this->serializeDesign($design)]);
    }

    private function serializeDesign(BlockBuilderDesign $design): array
    {
        return [
            'id' => (int) $design->id,
            'name' => (string) $design->name,
            'blocks' => is_array($design->blocks) ? $design->blocks : [],
            'updatedAt' => optional($design->updated_at)?->toIso8601String(),
        ];
    }
}
