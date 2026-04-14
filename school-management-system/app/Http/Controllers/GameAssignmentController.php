<?php

namespace App\Http\Controllers;

use App\Models\GameAssignment;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GameAssignmentController extends Controller
{
    public function create(string $gameSlug)
    {
        $games = ActivityController::games();
        abort_unless(isset($games[$gameSlug]), 404);

        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $recentAssignments = GameAssignment::with(['classes', 'levels'])
            ->where('game_slug', $gameSlug)
            ->latest()
            ->limit(10)
            ->get();

        return view('activities.assignments.create', [
            'gameSlug' => $gameSlug,
            'game' => $games[$gameSlug],
            'classes' => $classes,
            'recentAssignments' => $recentAssignments,
        ]);
    }

    public function store(Request $request, string $gameSlug)
    {
        $games = ActivityController::games();
        abort_unless(isset($games[$gameSlug]), 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'level_from' => ['nullable', 'integer', 'min:1', 'max:999'],
            'level_to' => ['nullable', 'integer', 'min:1', 'max:999', 'gte:level_from'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', Rule::exists('school_classes', 'id')],
            'points' => ['nullable', 'array'],
            'points.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        DB::transaction(function () use ($validated, $games, $gameSlug) {
            $assignment = GameAssignment::create([
                'game_slug' => $gameSlug,
                'game_name' => $games[$gameSlug]['name'],
                'title' => $validated['title'],
                'due_date' => $validated['due_date'] ?? null,
                'level_from' => $validated['level_from'] ?? null,
                'level_to' => $validated['level_to'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $assignment->classes()->sync($validated['class_ids']);

            $levelFrom = $validated['level_from'] ?? null;
            $levelTo = $validated['level_to'] ?? null;
            $points = $validated['points'] ?? [];
            if ($levelFrom !== null && $levelTo !== null) {
                for ($level = $levelFrom; $level <= $levelTo; $level++) {
                    $assignment->levels()->create([
                        'level' => $level,
                        'points' => (int) ($points[$level] ?? 0),
                    ]);
                }
            }
        });

        return redirect()
            ->route('activities.assignments.create', $gameSlug)
            ->with('ok', 'Odev basariyla olusturuldu.');
    }
}

