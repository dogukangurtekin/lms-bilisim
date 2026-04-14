<?php

namespace App\Http\Controllers;

use App\Models\RaceResult;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'text' => ['required', 'string', 'min:30', 'max:1000'],
            'user_name' => ['required', 'string', 'max:60'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if (! $this->isTeacher((int) $payload['user_id'])) {
            return response()->json(['message' => 'Only teachers can create rooms.'], 403);
        }

        $normalizedText = mb_strtolower((string) $payload['text'], 'UTF-8');
        $normalizedText = preg_replace('/[^a-zçğıöşü\s]/u', ' ', $normalizedText) ?? '';
        $normalizedText = preg_replace('/\s+/u', ' ', trim($normalizedText)) ?: '';
        if (mb_strlen($normalizedText) < 30) {
            return response()->json(['message' => 'Race text must be at least 30 characters.'], 422);
        }

        $room = Room::create([
            'code' => strtoupper(Str::random(6)),
            'name' => $payload['name'],
            'text' => $normalizedText,
            'status' => 'waiting',
            'created_by' => $payload['user_id'] ?? null,
        ]);

        RaceResult::create([
            'room_id' => $room->id,
            'user_id' => $payload['user_id'] ?? null,
            'user_name' => $payload['user_name'],
            'progress' => 0,
            'wpm' => 0,
            'accuracy' => 100,
            'is_spectator' => false,
        ]);

        $this->publishRaceEvent([
            'type' => 'room_created',
            'roomCode' => $room->code,
            'payload' => [
                'name' => $room->name,
            ],
        ]);

        return response()->json([
            'room' => $room,
        ], 201);
    }

    public function show(Room $room): JsonResponse
    {
        $room->load(['raceResults' => fn ($query) => $query->orderByDesc('wpm')]);

        return response()->json([
            'room' => $room,
            'results' => $room->raceResults,
        ]);
    }

    public function join(Request $request, Room $room): JsonResponse
    {
        $payload = $request->validate([
            'user_name' => ['required', 'string', 'max:60'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if (! $this->isStudent((int) $payload['user_id'])) {
            return response()->json(['message' => 'Only students can join rooms.'], 403);
        }

        $existing = RaceResult::where('room_id', $room->id)
            ->where('user_id', (int) $payload['user_id'])
            ->first();

        $isSpectator = $room->status !== 'waiting';

        if (! $existing) {
            $existing = RaceResult::create([
                'room_id' => $room->id,
                'user_id' => $payload['user_id'] ?? null,
                'user_name' => $payload['user_name'],
                'progress' => 0,
                'wpm' => 0,
                'accuracy' => 100,
                'is_spectator' => $isSpectator,
            ]);
        }

        $this->publishRaceEvent([
            'type' => 'user_joined',
            'roomCode' => $room->code,
            'payload' => [
                'userName' => $existing->user_name,
                'userId' => $existing->user_id,
                'spectator' => (bool) $existing->is_spectator,
            ],
        ]);

        return response()->json([
            'room_code' => $room->code,
            'user' => [
                'id' => $existing->user_id,
                'name' => $existing->user_name,
                'spectator' => (bool) $existing->is_spectator,
            ],
            'race_text' => $room->text,
            'status' => $room->status,
        ]);
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

    private function isTeacher(int $userId): bool
    {
        $slug = (string) (User::query()->with('role')->find($userId)?->role?->slug ?? '');
        return in_array($slug, ['admin', 'teacher'], true);
    }

    private function isStudent(int $userId): bool
    {
        $slug = (string) (User::query()->with('role')->find($userId)?->role?->slug ?? '');
        if (in_array($slug, ['student'], true)) {
            return true;
        }

        return Student::query()->where('user_id', $userId)->exists();
    }
}
