<?php

// Generates 50 progressively harder "Noktaları Birleştir" levels and
// replaces whatever is currently in dot_connect_levels.
// Run with: php artisan tinker < scripts/seed_dot_connect_levels.php

use App\Models\DotConnectLevel;

function scdGenerateWalk(int $size, int $moves, int $seed): array
{
    mt_srand($seed);
    $dirs = [[0, -1], [1, 0], [0, 1], [-1, 0]]; // up, right, down, left

    for ($attempt = 0; $attempt < 400; $attempt++) {
        $start = [(int) floor($size / 2), (int) floor($size / 2)];
        // jitter the start point a bit so shapes aren't all centered the same way
        $start[0] = max(0, min($size - 1, $start[0] + mt_rand(-1, 1)));
        $start[1] = max(0, min($size - 1, $start[1] + mt_rand(-1, 1)));

        $path = [$start];
        $visited = [$start[0] . ',' . $start[1] => true];
        $lastDir = null;
        $ok = true;

        for ($step = 0; $step < $moves; $step++) {
            $order = [0, 1, 2, 3];
            shuffle($order);
            $placed = false;
            foreach ($order as $di) {
                [$dx, $dy] = $dirs[$di];
                if ($lastDir !== null && $di === (($lastDir + 2) % 4)) {
                    continue; // avoid immediately backtracking on the same edge
                }
                $cur = end($path);
                $nx = $cur[0] + $dx;
                $ny = $cur[1] + $dy;
                if ($nx < 0 || $ny < 0 || $nx >= $size || $ny >= $size) continue;
                $key = $nx . ',' . $ny;
                if (isset($visited[$key])) continue;
                $path[] = [$nx, $ny];
                $visited[$key] = true;
                $lastDir = $di;
                $placed = true;
                break;
            }
            if (! $placed) { $ok = false; break; }
        }

        if ($ok && count($path) === $moves + 1) {
            return array_map(fn ($p) => ['x' => $p[0], 'y' => $p[1]], $path);
        }
    }

    // fallback: simple deterministic zig-zag if random generation struggled
    $path = [[0, 0]];
    $x = 0; $y = 0; $dir = 1;
    for ($i = 0; $i < $moves; $i++) {
        if ($dir === 1 && $x < $size - 1) { $x++; }
        elseif ($y < $size - 1) { $y++; $dir = $dir === 1 ? -1 : 1; }
        else { $x = max(0, $x - 1); }
        $path[] = [$x, $y];
    }

    return array_map(fn ($p) => ['x' => $p[0], 'y' => $p[1]], $path);
}

function scdDirFromPoints(array $a, array $b): string
{
    if ($b['x'] > $a['x']) return 'right';
    if ($b['x'] < $a['x']) return 'left';
    if ($b['y'] > $a['y']) return 'down';
    return 'up';
}

DotConnectLevel::query()->delete();

$plan = [
    // [count, gridSize, movesFrom, movesTo, allowRepeatFrom(levelNumberWithinBlock, 0=none)]
    [10, 4, 3, 8, 0],
    [10, 5, 6, 12, 6],
    [10, 6, 9, 16, 4],
    [10, 7, 12, 20, 1],
    [10, 8, 15, 24, 1],
];

$levelNumber = 0;
$sortOrder = 0;

foreach ($plan as [$count, $gridSize, $movesFrom, $movesTo, $repeatFrom]) {
    for ($i = 1; $i <= $count; $i++) {
        $levelNumber++;
        $sortOrder++;
        $moves = (int) round($movesFrom + ($movesTo - $movesFrom) * (($i - 1) / max(1, $count - 1)));
        $moves = max(2, $moves);
        $dots = scdGenerateWalk($gridSize, $moves, $levelNumber * 977 + 13);

        $allowed = ['move_up', 'move_right', 'move_down', 'move_left'];
        if ($repeatFrom > 0 && $i >= $repeatFrom) {
            $allowed[] = 'repeat';
        }

        $xp = 10 + intdiv($levelNumber, 2);
        $maxCommands = $moves + ($repeatFrom > 0 && $i >= $repeatFrom ? 4 : 3);

        DotConnectLevel::create([
            'name' => 'Bölüm ' . $levelNumber . ' (' . $gridSize . 'x' . $gridSize . ')',
            'grid_size' => $gridSize,
            'target_dots' => $dots,
            'start_point' => $dots[0],
            'start_direction' => scdDirFromPoints($dots[0], $dots[1]),
            'allowed_commands' => $allowed,
            'max_commands' => $maxCommands,
            'xp' => $xp,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }
}

echo "Created " . DotConnectLevel::count() . " levels\n";
