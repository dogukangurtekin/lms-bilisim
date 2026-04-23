<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Score;

class GameController extends Controller
{
    public function game()
    {
        return view('flamestone.game');
    }

    public function editor()
    {
        return view('flamestone.editor');
    }

    public function leaderboard()
    {
        $scores = Score::query()
            ->with(['user:id,name', 'level:id,name'])
            ->orderBy('moves')
            ->orderByDesc('completed_at')
            ->limit(100)
            ->get();

        $levels = Level::query()->orderBy('name')->get(['id', 'name']);

        return view('flamestone.leaderboard', [
            'scores' => $scores,
            'levels' => $levels,
        ]);
    }
}
