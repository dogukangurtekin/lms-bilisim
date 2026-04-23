<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = Score::query()
            ->with(['user:id,name', 'level:id,name'])
            ->orderBy('moves')
            ->orderByDesc('completed_at')
            ->limit(100)
            ->get();

        return response()->json([
            'ok' => true,
            'items' => $rows->map(fn (Score $s) => [
                'id' => (int) $s->id,
                'moves' => (int) $s->moves,
                'duration_seconds' => (int) ($s->duration_seconds ?? 0),
                'completed_at' => optional($s->completed_at)->toIso8601String(),
                'user' => $s->user?->name,
                'level' => $s->level?->name,
                'level_id' => (int) $s->level_id,
            ])->values()->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'moves' => ['required', 'integer', 'min:1', 'max:100000'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ]);

        $userId = (int) auth()->id();
        $levelId = (int) $data['level_id'];
        $moves = (int) $data['moves'];
        $durationSeconds = max(0, (int) ($data['duration_seconds'] ?? 0));

        Level::query()->findOrFail($levelId);

        $score = Score::query()->firstOrNew([
            'user_id' => $userId,
            'level_id' => $levelId,
        ]);

        $isNew = ! $score->exists;
        $prevMoves = (int) ($score->moves ?? PHP_INT_MAX);
        $prevDuration = (int) ($score->duration_seconds ?? PHP_INT_MAX);
        $isBetter = $isNew
            || $moves < $prevMoves
            || ($moves === $prevMoves && $durationSeconds > 0 && $durationSeconds < $prevDuration);

        if ($isBetter) {
            $score->moves = $moves;
            $score->duration_seconds = $durationSeconds;
            $score->completed_at = now();
            $score->save();
        }

        return response()->json([
            'ok' => true,
            'saved' => $isBetter,
            'best_moves' => (int) $score->moves,
            'best_duration_seconds' => (int) ($score->duration_seconds ?? 0),
        ]);
    }
}
