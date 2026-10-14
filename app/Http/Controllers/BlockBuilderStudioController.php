<?php

namespace App\Http\Controllers;

use App\Models\BlockBuilderDesign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Blok Tasarım Stüdyosu (3D Grid) — oturum açmış kullanıcının (öğrenci/öğretmen) kendi dünyalarını kaydedip geri yüklemesi.
 * Kayıtlar kullanıcıya bağlıdır (block_builder_designs.user_id); kimse başkasının dünyasını göremez/değiştiremez.
 */
class BlockBuilderStudioController extends Controller
{
    public const MAX_DESIGNS_PER_USER = 30;

    /** Kullanıcının dünyalarının özet listesi (bloklar olmadan). */
    public function index(): JsonResponse
    {
        $designs = BlockBuilderDesign::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->limit(self::MAX_DESIGNS_PER_USER + 20)
            ->get(['id', 'name', 'blocks', 'updated_at'])
            ->map(fn (BlockBuilderDesign $d) => [
                'id' => (int) $d->id,
                'name' => (string) $d->name,
                'blockCount' => is_array($d->blocks) ? count($d->blocks) : 0,
                'updatedAt' => optional($d->updated_at)?->toIso8601String(),
            ])
            ->values();

        return response()->json(['designs' => $designs, 'max' => self::MAX_DESIGNS_PER_USER]);
    }

    public function latest(): JsonResponse
    {
        $design = BlockBuilderDesign::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->first();

        return response()->json(['design' => $design ? $this->serialize($design) : null]);
    }

    public function show(int $id): JsonResponse
    {
        $design = BlockBuilderDesign::query()->whereKey($id)->where('user_id', Auth::id())->first();
        if (! $design) {
            return response()->json(['message' => 'Dünya bulunamadı.'], 404);
        }

        return response()->json(['design' => $this->serialize($design)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:120'],
            'blocks' => ['present', 'array', 'max:20000'],
            'blocks.*.x' => ['required', 'integer', 'min:-512', 'max:512'],
            'blocks.*.y' => ['required', 'integer', 'min:0', 'max:512'],
            'blocks.*.z' => ['required', 'integer', 'min:-512', 'max:512'],
            'blocks.*.color' => ['required', 'string', 'max:16'],
            'blocks.*.texture' => ['nullable', 'string', 'max:32'],
        ]);

        $uid = (int) Auth::id();
        $name = trim((string) ($validated['name'] ?? ''));
        if ($name === '') {
            $name = 'Dünyam '.now()->format('d.m.Y H:i');
        }

        $blocks = collect($validated['blocks'])->map(fn (array $b): array => [
            'x' => (int) $b['x'],
            'y' => (int) $b['y'],
            'z' => (int) $b['z'],
            'color' => Str::lower(trim((string) $b['color'])),
            'texture' => Str::lower(trim((string) ($b['texture'] ?? 'plain'))) ?: 'plain',
        ])->values()->all();

        $design = null;
        $designId = (int) ($validated['id'] ?? 0);
        if ($designId > 0) {
            $design = BlockBuilderDesign::query()->whereKey($designId)->where('user_id', $uid)->first();
        }

        if (! $design) {
            $count = BlockBuilderDesign::query()->where('user_id', $uid)->count();
            if ($count >= self::MAX_DESIGNS_PER_USER) {
                return response()->json([
                    'message' => 'En fazla '.self::MAX_DESIGNS_PER_USER.' dünya kaydedebilirsin. Önce eski bir dünyanı sil.',
                ], 422);
            }
            $design = new BlockBuilderDesign();
            $design->user_id = $uid;
        }

        $design->name = $name;
        $design->blocks = $blocks;
        $design->save();

        return response()->json(['design' => $this->serialize($design)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = BlockBuilderDesign::query()->whereKey($id)->where('user_id', Auth::id())->delete();
        if (! $deleted) {
            return response()->json(['message' => 'Dünya bulunamadı.'], 404);
        }

        return response()->json(['ok' => true]);
    }

    private function serialize(BlockBuilderDesign $design): array
    {
        return [
            'id' => (int) $design->id,
            'name' => (string) $design->name,
            'blocks' => is_array($design->blocks) ? $design->blocks : [],
            'updatedAt' => optional($design->updated_at)?->toIso8601String(),
        ];
    }
}
