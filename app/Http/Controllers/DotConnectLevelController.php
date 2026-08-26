<?php

namespace App\Http\Controllers;

use App\Models\DotConnectLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DotConnectLevelController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $levels = DotConnectLevel::query()->ordered()->get();

        return view('dot-connect.manage', [
            'levels' => $levels,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $data = $this->validated($request);
        $data['created_by'] = auth()->id();
        $data['sort_order'] = (int) (DotConnectLevel::query()->max('sort_order') ?? 0) + 1;

        DotConnectLevel::query()->create($data);

        return back()->with('ok', 'Yeni bölüm eklendi.');
    }

    public function update(Request $request, DotConnectLevel $level): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $level->update($this->validated($request));

        return back()->with('ok', 'Bölüm güncellendi.');
    }

    public function destroy(DotConnectLevel $level): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $level->delete();

        return back()->with('ok', 'Bölüm silindi.');
    }

    public function toggle(DotConnectLevel $level): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $level->update(['is_active' => ! $level->is_active]);

        return back()->with('ok', $level->is_active ? 'Bölüm aktif edildi.' : 'Bölüm pasif edildi.');
    }

    public function reorder(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:dot_connect_levels,id'],
        ]);

        foreach (array_values($data['ids']) as $index => $id) {
            DotConnectLevel::query()->where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * JSON feed consumed by the connect-the-dots-runner game engine.
     */
    public function feed(): JsonResponse
    {
        $levels = DotConnectLevel::query()
            ->active()
            ->ordered()
            ->get()
            ->map(function (DotConnectLevel $level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name ?: ('Bölüm ' . $level->id),
                    'gridSize' => (int) $level->grid_size,
                    'targetDots' => $level->target_dots,
                    'targetSegments' => $level->targetSegments(),
                    'start' => $level->start_point,
                    'startDirection' => $level->start_direction,
                    'allowedCommands' => $level->allowed_commands,
                    'maxCommands' => (int) $level->max_commands,
                    'xp' => (int) $level->xp,
                ];
            })
            ->values();

        return response()->json(['levels' => $levels]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'grid_size' => ['required', 'integer', 'min:3', 'max:8'],
            'target_dots' => ['required', 'array', 'min:2'],
            'target_dots.*.x' => ['required', 'integer', 'min:0'],
            'target_dots.*.y' => ['required', 'integer', 'min:0'],
            'start_point' => ['required', 'array'],
            'start_point.x' => ['required', 'integer', 'min:0'],
            'start_point.y' => ['required', 'integer', 'min:0'],
            'start_direction' => ['required', 'in:up,right,down,left'],
            'allowed_commands' => ['required', 'array', 'min:1'],
            'allowed_commands.*' => ['in:move_up,move_right,move_down,move_left,repeat'],
            'max_commands' => ['required', 'integer', 'min:2', 'max:30'],
            'xp' => ['required', 'integer', 'min:0', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['target_dots'] = array_values($data['target_dots']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
