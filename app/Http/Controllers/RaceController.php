<?php

namespace App\Http\Controllers;

use App\Models\RaceResult;
use App\Models\Room;
use App\Models\StudentReport;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RaceController extends Controller
{
    private const RACE_DURATION_SECONDS = 120;
    public const XP_PER_CORRECT_CHARACTER = 5;

    public function start(Room $room): JsonResponse
    {
        $payload = request()->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if (! $this->isTeacher((int) $payload['user_id'])) {
            return response()->json(['message' => 'Only teachers can start race.'], 403);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['message' => 'Race already started or finished.'], 422);
        }

        $room->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
        Cache::forget('race:active_room');
        Cache::forget("race:leaderboard:{$room->id}");
        Cache::forget("race:report:{$room->id}");

        $participants = RaceResult::query()
            ->where('room_id', $room->id)
            ->where('is_spectator', false)
            ->get(['user_id', 'user_name'])
            ->map(fn (RaceResult $result) => [
                'userId' => $result->user_id,
                'userName' => $result->user_name,
            ])
            ->values();

        $this->publishRaceEvent([
            'type' => 'race_started',
            'roomCode' => $room->code,
            'payload' => [
                'text' => $room->text,
                'startedAt' => $room->started_at?->toIso8601String(),
                'durationSeconds' => self::RACE_DURATION_SECONDS,
                'endsAt' => $room->started_at?->copy()->addSeconds(self::RACE_DURATION_SECONDS)->toIso8601String(),
                'participants' => $participants,
            ],
        ]);

        return response()->json([
            'message' => 'Race started.',
            'room_code' => $room->code,
        ]);
    }

    public function progress(Request $request, Room $room): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'progress' => ['required', 'numeric', 'min:0', 'max:100'],
            'wpm' => ['required', 'numeric', 'min:0', 'max:400'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:100'],
            'correct_characters' => ['required', 'integer', 'min:0', 'max:1000'],
            'elapsed_seconds' => ['required', 'integer', 'min:0', 'max:36000'],
        ]);

        if ($room->status !== 'active') {
            return response()->json(['message' => 'Race is not active.'], 422);
        }

        $result = RaceResult::query()
            ->where('room_id', $room->id)
            ->where('user_id', (int) $payload['user_id'])
            ->where('is_spectator', false)
            ->first();

        if (! $result) {
            return response()->json(['message' => 'Join the race before sending progress.'], 403);
        }

        $result->update([
            'progress' => $payload['progress'],
            'wpm' => $payload['wpm'],
            'accuracy' => $payload['accuracy'],
            'correct_characters' => $this->normalizeCorrectCharacters($room, (int) $payload['correct_characters']),
            'elapsed_seconds' => $payload['elapsed_seconds'],
        ]);

        Cache::forget("race:leaderboard:{$room->id}");
        Cache::forget("race:report:{$room->id}");

        return response()->json(['message' => 'Progress updated.']);
    }

    public function finish(Request $request, Room $room): JsonResponse
    {
        $payload = $request->validate([
            'user_name' => ['required', 'string', 'max:60'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'progress' => ['required', 'numeric', 'min:0', 'max:100'],
            'wpm' => ['required', 'numeric', 'min:0', 'max:400'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:100'],
            'correct_characters' => ['required', 'integer', 'min:0', 'max:1000'],
            'elapsed_seconds' => ['nullable', 'integer', 'min:0', 'max:36000'],
            'completion_seconds' => ['nullable', 'integer', 'min:0', 'max:36000'],
            'is_spectator' => ['sometimes', 'boolean'],
        ]);

        if ($room->status === 'waiting') {
            return response()->json(['message' => 'Race has not started yet.'], 422);
        }

        $elapsedSeconds = (int) ($payload['elapsed_seconds'] ?? 0);
        $completionSeconds = isset($payload['completion_seconds']) ? (int) $payload['completion_seconds'] : null;
        $correctCharacters = $this->normalizeCorrectCharacters($room, (int) $payload['correct_characters']);
        $xpEarned = $this->calculateXp($correctCharacters);

        DB::transaction(function () use ($room, $payload, $elapsedSeconds, $completionSeconds, $correctCharacters, $xpEarned): void {
            $result = RaceResult::query()->firstOrNew(
                [
                    'room_id' => $room->id,
                    'user_id' => $payload['user_id'] ?? null,
                ]
            );
            $previousXp = (int) ($result->xp_earned ?? 0);
            $result->fill([
                'user_name' => $payload['user_name'],
                'progress' => $payload['progress'],
                'wpm' => $payload['wpm'],
                'accuracy' => $payload['accuracy'],
                'correct_characters' => $correctCharacters,
                'elapsed_seconds' => $elapsedSeconds,
                'completion_seconds' => $completionSeconds,
                'xp_earned' => $xpEarned,
                'is_spectator' => (bool) ($payload['is_spectator'] ?? false),
                'finished_at' => Carbon::now(),
            ])->save();

            $this->syncRaceXpToStudentReport($result, max(0, $xpEarned - $previousXp));

            $allParticipantsDone = RaceResult::query()
                ->where('room_id', $room->id)
                ->where('is_spectator', false)
                ->where('progress', '<', 100)
                ->doesntExist();

            if ($allParticipantsDone && $room->status !== 'finished') {
                $room->update([
                    'status' => 'finished',
                    'finished_at' => now(),
                ]);
            }
        });
        Cache::forget('race:active_room');
        Cache::forget("race:leaderboard:{$room->id}");
        Cache::forget("race:report:{$room->id}");

        $leaderboard = $this->buildLeaderboard($room);

        if ($room->status === 'finished') {
            $this->publishRaceEvent([
                'type' => 'race_finished',
                'roomCode' => $room->code,
                'payload' => [
                    'leaderboard' => $leaderboard,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Result stored.',
            'room_status' => $room->status,
            'leaderboard' => $leaderboard,
        ]);
    }

    public function end(Request $request, Room $room): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if (! $this->isTeacher((int) $payload['user_id'])) {
            return response()->json(['message' => 'Only teachers can end race.'], 403);
        }

        if ($room->status === 'finished') {
            return response()->json([
                'message' => 'Race already finished.',
                'leaderboard' => $this->buildLeaderboard($room),
                'report' => $this->buildRoomReport($room),
            ]);
        }

        DB::transaction(function () use ($room): void {
            RaceResult::query()
                ->where('room_id', $room->id)
                ->where('is_spectator', false)
                ->lockForUpdate()
                ->get()
                ->each(function (RaceResult $result) use ($room): void {
                    $previousXp = (int) $result->xp_earned;
                    $xpEarned = $this->calculateXp(
                        $this->normalizeCorrectCharacters($room, (int) $result->correct_characters)
                    );
                    $result->update([
                        'xp_earned' => $xpEarned,
                        'finished_at' => $result->finished_at ?: now(),
                    ]);
                    $this->syncRaceXpToStudentReport($result, max(0, $xpEarned - $previousXp));
                });

            $room->update([
                'status' => 'finished',
                'finished_at' => now(),
            ]);
        });
        Cache::forget('race:active_room');
        Cache::forget("race:leaderboard:{$room->id}");
        Cache::forget("race:report:{$room->id}");

        $leaderboard = $this->buildLeaderboard($room);
        $report = $this->buildRoomReport($room);

        $this->publishRaceEvent([
            'type' => 'race_finished',
            'roomCode' => $room->code,
            'payload' => [
                'leaderboard' => $leaderboard,
                'closedByTeacher' => true,
            ],
        ]);

        return response()->json([
            'message' => 'Race finished by teacher.',
            'leaderboard' => $leaderboard,
            'report' => $report,
        ]);
    }

    private function isTeacher(int $userId): bool
    {
        $slug = (string) (User::query()->with('role')->find($userId)?->role?->slug ?? '');
        return in_array($slug, ['admin', 'teacher'], true);
    }

    public function leaderboard(Room $room): JsonResponse
    {
        $cacheKey = "race:leaderboard:{$room->id}";
        $leaderboard = Cache::remember($cacheKey, now()->addSeconds(5), function () use ($room) {
            return $this->buildLeaderboard($room);
        });
        return response()->json([
            'leaderboard' => $leaderboard,
        ]);
    }

    public function active(): JsonResponse
    {
        $room = Cache::remember('race:active_room', now()->addSeconds(5), function () {
            return Room::query()
                ->where('status', 'active')
                ->whereNotNull('started_at')
                ->where('started_at', '>=', now()->subHours(3))
                ->orderByDesc('started_at')
                ->first(['id', 'code', 'name', 'status', 'started_at', 'created_at']);
        });

        return response()->json([
            'active' => $room ? [
                'roomCode' => $room->code,
                'name' => $room->name,
                'status' => $room->status,
                'startedAt' => $room->started_at?->toIso8601String(),
                'createdAt' => $room->created_at?->toIso8601String(),
            ] : null,
        ]);
    }

    private function buildLeaderboard(Room $room): array
    {
        return RaceResult::query()
            ->where('room_id', $room->id)
            ->where('is_spectator', false)
            ->with('user.student.currentAvatar')
            ->orderByDesc('progress')
            ->orderByDesc('wpm')
            ->orderByDesc('accuracy')
            ->get(['id', 'user_id', 'user_name', 'progress', 'wpm', 'accuracy', 'correct_characters', 'elapsed_seconds', 'completion_seconds', 'xp_earned', 'finished_at'])
            ->map(function (RaceResult $result): array {
                $avatar = $result->user?->student?->currentAvatar;

                return [
                'userName' => $result->user_name,
                'progress' => (float) $result->progress,
                'wpm' => (float) $result->wpm,
                'accuracy' => (float) $result->accuracy,
                'correctCharacters' => (int) $result->correct_characters,
                'elapsedSeconds' => (int) ($result->elapsed_seconds ?? 0),
                'completionSeconds' => $result->completion_seconds !== null ? (int) $result->completion_seconds : null,
                'xpEarned' => max((int) ($result->xp_earned ?? 0), (int) $result->correct_characters * self::XP_PER_CORRECT_CHARACTER),
                'finishedAt' => $result->finished_at?->toIso8601String(),
                'avatarUrl' => $avatar?->image_path ? asset($avatar->image_path) : null,
                'avatarName' => $avatar?->name,
                ];
            })
            ->toArray();
    }

    public function myRuns(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'user_name' => ['nullable', 'string', 'max:60'],
        ]);

        $query = RaceResult::query()
            ->with('room:id,code,name,status,started_at,finished_at')
            ->where('is_spectator', false)
            ->whereNotNull('finished_at');

        $userId = $payload['user_id'] ?? null;
        $userName = trim((string) ($payload['user_name'] ?? ''));
        if ($userId !== null) {
            $query->where('user_id', (int) $userId);
        } elseif ($userName !== '') {
            $query->where('user_name', $userName);
        } else {
            return response()->json(['runs' => []]);
        }

        $runs = $query->orderByDesc('finished_at')
            ->limit(50)
            ->get()
            ->map(fn (RaceResult $result) => [
                'roomCode' => $result->room?->code,
                'roomName' => $result->room?->name,
                'status' => $result->room?->status,
                'progress' => (float) $result->progress,
                'wpm' => (float) $result->wpm,
                'accuracy' => (float) $result->accuracy,
                'elapsedSeconds' => (int) ($result->elapsed_seconds ?? 0),
                'completionSeconds' => $result->completion_seconds !== null ? (int) $result->completion_seconds : null,
                'xpEarned' => (int) ($result->xp_earned ?? 0),
                'finishedAt' => $result->finished_at?->toIso8601String(),
            ]);

        return response()->json(['runs' => $runs]);
    }

    public function report(Room $room): JsonResponse
    {
        $cacheKey = "race:report:{$room->id}";
        $report = Cache::remember($cacheKey, now()->addSeconds(5), function () use ($room) {
            return $this->buildRoomReport($room);
        });
        return response()->json([
            'report' => $report,
        ]);
    }

    private function buildRoomReport(Room $room): array
    {
        $startedAt = $room->started_at ? Carbon::parse($room->started_at) : null;
        $finishedAt = $room->finished_at ? Carbon::parse($room->finished_at) : null;
        $durationSeconds = null;
        if ($startedAt && $finishedAt) {
            $durationSeconds = max(0, (int) $startedAt->diffInSeconds($finishedAt, false));
        }

        $rows = RaceResult::query()
            ->where('room_id', $room->id)
            ->where('is_spectator', false)
            ->orderByDesc('progress')
            ->orderByDesc('wpm')
            ->orderByDesc('accuracy')
            ->get(['user_name', 'progress', 'wpm', 'accuracy', 'correct_characters', 'elapsed_seconds', 'completion_seconds', 'xp_earned', 'finished_at'])
            ->map(fn (RaceResult $r) => [
                'userName' => $r->user_name,
                'progress' => (float) $r->progress,
                'wpm' => (float) $r->wpm,
                'accuracy' => (float) $r->accuracy,
                'correctCharacters' => (int) $r->correct_characters,
                'elapsedSeconds' => (int) ($r->elapsed_seconds ?? 0),
                'completionSeconds' => $r->completion_seconds !== null ? (int) $r->completion_seconds : null,
                'xpEarned' => (int) ($r->xp_earned ?? 0),
                'finishedAt' => $r->finished_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return [
            'roomCode' => $room->code,
            'roomName' => $room->name,
            'status' => $room->status,
            'startedAt' => $startedAt?->toIso8601String(),
            'finishedAt' => $finishedAt?->toIso8601String(),
            'durationSeconds' => $durationSeconds,
            'participants' => $rows,
        ];
    }

    private function calculateXp(int $correctCharacters): int
    {
        return max(0, $correctCharacters) * self::XP_PER_CORRECT_CHARACTER;
    }

    private function normalizeCorrectCharacters(Room $room, int $correctCharacters): int
    {
        return max(0, min(mb_strlen((string) $room->text, 'UTF-8'), $correctCharacters));
    }

    private function syncRaceXpToStudentReport(RaceResult $result, int $xpDelta): void
    {
        if (! $result->user_id) {
            return;
        }
        if ($xpDelta <= 0) {
            return;
        }

        $uid = (int) $result->user_id;
        $xp = $xpDelta;
        $elapsedMs = ((int) ($result->elapsed_seconds ?? 0)) * 1000;

        $profile = UserProfile::query()->firstOrCreate(
            ['user_id' => $uid],
            ['xp' => 0]
        );
        $profile->increment('xp', $xp);
        $report = StudentReport::query()->firstOrCreate(
            ['user_id' => $uid],
            ['total_xp' => 0, 'total_duration_ms' => 0, 'completion_percent' => 0, 'meta' => []]
        );
        $report->increment('total_xp', $xp);
        $report->increment('total_duration_ms', $elapsedMs);
    }

    private function publishRaceEvent(array $event): void
    {
        try {
            Redis::publish('race_events', json_encode($event, JSON_THROW_ON_ERROR));
        } catch (\Throwable $exception) {
            Log::warning('Redis unavailable; race event publish skipped.', [
                'type' => $event['type'] ?? null,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
