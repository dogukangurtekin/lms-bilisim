<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureSystemLevels();

        $query = Level::query()
            ->with('user:id,name')
            ->orderByRaw('case when user_id is null then 0 else 1 end')
            ->orderBy('id');

        $user = auth()->user();
        if ($user?->hasRole('student')) {
            $grant = session('runner_grant');
            $isGrantValid = is_array($grant)
                && ($grant['slug'] ?? null) === 'flamestone-game'
                && (($grant['expires_at'] ?? 0) >= time());

            if ($isGrantValid) {
                $from = max(1, (int) ($grant['from'] ?? 1));
                $to = max($from, (int) ($grant['to'] ?? $from));
                $query->where(function ($q) use ($from, $to) {
                    $q->whereNotNull('user_id')
                        ->orWhere(function ($sys) use ($from, $to) {
                            $sys->whereNull('user_id')
                                ->whereRaw('id BETWEEN ? AND ?', [$from, $to]);
                        });
                });
            } else {
                $query->where(function ($q) {
                    $q->whereNotNull('user_id')
                        ->orWhere(function ($sys) {
                            $sys->whereNull('user_id')->whereRaw('id BETWEEN 1 AND 2');
                        });
                });
            }
        }

        $levels = $query->get(['id', 'name', 'data', 'user_id', 'created_at']);

        return response()->json([
            'ok' => true,
            'items' => $levels->map(fn (Level $level) => [
                'id' => (int) $level->id,
                'name' => (string) $level->name,
                'data' => (array) ($level->data ?? []),
                'user_id' => $level->user_id ? (int) $level->user_id : null,
                'author' => $level->user?->name,
            ])->values()->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'data' => ['required', 'array'],
            'data.grid' => ['required', 'array', 'min:8', 'max:20'],
        ]);

        $level = Level::query()->create([
            'name' => (string) $data['name'],
            'data' => $data['data'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'item' => [
                'id' => (int) $level->id,
                'name' => (string) $level->name,
                'data' => (array) ($level->data ?? []),
                'user_id' => $level->user_id ? (int) $level->user_id : null,
            ],
        ], 201);
    }

    private function ensureSystemLevels(): void
    {
        $expected = $this->defaultLevels();
        foreach ($expected as $lv) {
            Level::query()->updateOrCreate([
                'name' => $lv['name'],
                'user_id' => null,
            ], [
                'name' => $lv['name'],
                'data' => $lv['data'],
                'user_id' => null,
            ]);
        }
    }

    private function defaultLevels(): array
    {
        $levels = [];
        for ($i = 1; $i <= 50; $i++) {
            $difficulty = $i <= 18 ? 'Kolay' : ($i <= 35 ? 'Orta' : 'Zor');
            $levels[] = [
                'name' => sprintf('%s %02d', $difficulty, $i),
                'data' => [
                    'grid' => $this->generateGridForLevel($i),
                ],
            ];
        }
        return $levels;
    }

    private function generateGridForLevel(int $levelNo): array
    {
        $size = $levelNo <= 20 ? 10 : 12;
        $grid = array_fill(0, $size, str_repeat('.', $size));

        for ($y = 0; $y < $size; $y++) {
            $row = str_split($grid[$y]);
            for ($x = 0; $x < $size; $x++) {
                if ($x === 0 || $y === 0 || $x === $size - 1 || $y === $size - 1) {
                    $row[$x] = '#';
                }
            }
            $grid[$y] = implode('', $row);
        }

        $path = [];
        $x = 1;
        $y = 1;
        $path[] = [$x, $y];
        while ($x < $size - 2) {
            $x++;
            $path[] = [$x, $y];
        }
        while ($y < $size - 2) {
            $y++;
            $path[] = [$x, $y];
        }

        $blockedCount = min(($size * $size) / 5, 8 + (int) floor($levelNo / 2));
        $seed = 31 + $levelNo * 97;
        for ($i = 0; $i < $blockedCount; $i++) {
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
            $rx = 1 + ($seed % ($size - 2));
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
            $ry = 1 + ($seed % ($size - 2));
            if ($this->isOnPath($path, $rx, $ry)) continue;
            $grid = $this->setCell($grid, $rx, $ry, '#');
        }

        $grid = $this->setCell($grid, 1, 1, 'S');
        $grid = $this->setCell($grid, $size - 2, $size - 2, 'G');

        $bx = min($size - 3, 2 + ($levelNo % max(2, $size - 4)));
        $by = min($size - 3, 2 + (int) floor(($levelNo * 3) % max(2, $size - 4)));
        if (!$this->isOnPath($path, $bx, $by)) {
            $grid = $this->setCell($grid, $bx, $by, 'B');
        }

        // Bloklar amacli olsun: her levelde en az bir blok hedef noktasi.
        $slotX = max(2, $size - 4);
        $slotY = 2;
        if ($this->getCell($grid, $slotX, $slotY) === '#') {
            $grid = $this->setCell($grid, $slotX, $slotY, '.');
        }
        if ($this->getCell($grid, $slotX, $slotY) === '.' && !($slotX === 1 && $slotY === 1) && !($slotX === $size - 2 && $slotY === $size - 2)) {
            $grid = $this->setCell($grid, $slotX, $slotY, 'X');
        }

        if ($levelNo >= 6) {
            $tx = min($size - 3, 2 + ($levelNo % max(2, $size - 5)));
            $ty = min($size - 3, 3 + ($levelNo % max(2, $size - 6)));
            if (!$this->isOnPath($path, $tx, $ty)) {
                $grid = $this->setCell($grid, $tx, $ty, 'T');
            }
        }

        // Tum bolumlerde: once blok hedefine koy, sonra anahtar-kapi-finise git.
        $kx = 2;
        $ky = $size - 3;
        $dx = $size - 3;
        $dy = $size - 2;
        if ($this->getCell($grid, $kx, $ky) === '#') {
            $grid = $this->setCell($grid, $kx, $ky, '.');
        }
        if ($this->getCell($grid, $dx, $dy) === 'G') {
            $dy = $size - 3;
        }
        if ($this->getCell($grid, $dx, $dy) === '#') {
            $grid = $this->setCell($grid, $dx, $dy, '.');
        }
        $grid = $this->setCell($grid, $kx, $ky, 'K');
        $grid = $this->setCell($grid, $dx, $dy, 'D');

        return $grid;
    }

    private function isOnPath(array $path, int $x, int $y): bool
    {
        foreach ($path as [$px, $py]) {
            if ($px === $x && $py === $y) return true;
        }
        return false;
    }

    private function setCell(array $grid, int $x, int $y, string $value): array
    {
        $row = str_split($grid[$y]);
        $row[$x] = $value;
        $grid[$y] = implode('', $row);
        return $grid;
    }

    private function getCell(array $grid, int $x, int $y): string
    {
        $row = str_split($grid[$y]);
        return (string) ($row[$x] ?? '.');
    }
}
