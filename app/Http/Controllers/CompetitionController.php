<?php

namespace App\Http\Controllers;

use App\Models\CompetitionParticipant;
use App\Models\CompetitionRoom;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompetitionController extends Controller
{
    /**
     * "Canlı Yarışmalar" icin uygun oyunlar: seviye (from/to) tabanli
     * ilerleyen, ActivityRunnerController'in grant mekanizmasiyla uyumlu
     * calisanlar. keyboard-race/block-builder-studio/flamestone-game/
     * python-editor seviye araligi kavramini kullanmadigindan disaridadir.
     */
    public const ELIGIBLE_SLUGS = [
        'block-grid-runner',
        'block-3d-runner',
        'compute-it-runner',
        'lightbot-runner',
        'line-trace-runner',
        'silent-teacher-runner',
        'connect-the-dots-runner',
        'bee-garden-runner',
    ];

    private function eligibleGames(): array
    {
        $all = ActivityController::games();
        return array_intersect_key($all, array_flip(self::ELIGIBLE_SLUGS));
    }

    public function index()
    {
        $user = auth()->user();
        $isAdmin = (bool) $user?->hasRole('admin');
        $teacherId = auth()->id();

        $rooms = CompetitionRoom::query()
            ->with('schoolClass')
            ->when(! $isAdmin, fn ($q) => $q->where('teacher_user_id', $teacherId))
            ->latest()
            ->limit(25)
            ->get();

        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('competitions.index', [
            'games' => $this->eligibleGames(),
            'rooms' => $rooms,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request)
    {
        $games = $this->eligibleGames();
        $data = $request->validate([
            'game_slug' => ['required', 'string'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'level_from' => ['required', 'integer', 'min:1', 'max:500'],
            'level_to' => ['required', 'integer', 'min:1', 'max:500', 'gte:level_from'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        if (! isset($games[$data['game_slug']])) {
            return back()->withErrors(['game_slug' => 'Gecersiz oyun secimi.'])->withInput();
        }

        $room = CompetitionRoom::query()->create([
            'teacher_user_id' => auth()->id(),
            'game_slug' => $data['game_slug'],
            'game_name' => $games[$data['game_slug']]['name'],
            'school_class_id' => $data['school_class_id'] ?: null,
            'level_from' => (int) $data['level_from'],
            'level_to' => (int) $data['level_to'],
            'duration_seconds' => (int) $data['duration_minutes'] * 60,
            'join_code' => strtoupper(Str::random(6)),
            'status' => 'lobby',
        ]);

        return redirect()->route('competitions.room.show', $room)->with('ok', 'Yarisma odasi olusturuldu. Ogrenciler katilinca "Herkese Baslat" butonuna basin.');
    }

    public function showRoom(CompetitionRoom $room)
    {
        abort_unless($room->teacher_user_id === auth()->id() || auth()->user()?->hasRole('admin'), 403);
        $room = $this->syncRoomByTimer($room);
        $room->load('participants.studentUser');

        return view('competitions.room', [
            'room' => $room,
            'rows' => $this->leaderboardRows($room),
        ]);
    }

    public function launch(CompetitionRoom $room)
    {
        abort_unless($room->teacher_user_id === auth()->id() || auth()->user()?->hasRole('admin'), 403);
        if ($room->status !== 'lobby') {
            return back();
        }

        $nowMs = $this->nowMs();
        $room->update([
            'status' => 'live',
            'started_at_ms' => $nowMs,
            'ends_at_ms' => $nowMs + ($room->duration_seconds * 1000),
        ]);

        return back()->with('ok', 'Yarisma herkes icin ayni anda baslatildi.');
    }

    public function finish(CompetitionRoom $room)
    {
        abort_unless($room->teacher_user_id === auth()->id() || auth()->user()?->hasRole('admin'), 403);

        if ($room->status !== 'finished') {
            $room->update([
                'status' => 'finished',
                'finished_at_ms' => $this->nowMs(),
            ]);
        }

        return redirect()->route('competitions.room.show', $room)->with('ok', 'Yarisma sonlandirildi.');
    }

    public function roomStatus(CompetitionRoom $room)
    {
        abort_unless($room->teacher_user_id === auth()->id() || auth()->user()?->hasRole('admin'), 403);
        $room = $this->syncRoomByTimer($room);

        return response()->json([
            'status' => $room->status,
            'ends_at_ms' => (int) ($room->ends_at_ms ?? 0),
            'server_now_ms' => $this->nowMs(),
            'joined' => $room->participants()->count(),
            'rows' => $this->leaderboardRows($room),
        ]);
    }

    public function studentJoinForm()
    {
        return view('student-portal.competition-join');
    }

    public function studentJoin(Request $request)
    {
        $data = $request->validate(['join_code' => ['required', 'string', 'size:6']]);
        $room = CompetitionRoom::query()
            ->where('join_code', Str::upper($data['join_code']))
            ->whereIn('status', ['lobby', 'live'])
            ->first();
        if (! $room) {
            return back()->withErrors(['join_code' => 'Aktif bir yarisma odasi bulunamadi.']);
        }

        $room = $this->syncRoomByTimer($room);
        if (! in_array($room->status, ['lobby', 'live'], true)) {
            return back()->withErrors(['join_code' => 'Bu yarisma sona ermis.']);
        }
        if (! $this->studentCanJoin($room, auth()->id())) {
            return back()->withErrors(['join_code' => 'Bu yarisma senin sinifina acik degil.']);
        }

        CompetitionParticipant::query()->updateOrCreate(
            ['competition_room_id' => $room->id, 'student_user_id' => auth()->id()],
            [
                'user_name' => (string) (auth()->user()?->name ?? 'Ogrenci'),
                'joined_at_ms' => $this->nowMs(),
                'is_spectator' => $room->status === 'live' ? false : false,
            ]
        );

        return redirect()->route('student.competitions.play', $room);
    }

    public function studentPlay(CompetitionRoom $room)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $room = $this->syncRoomByTimer($room);
        abort_unless(in_array($room->status, ['lobby', 'live', 'finished'], true), 403);
        abort_unless($this->studentCanJoin($room, auth()->id()), 403);

        $participant = CompetitionParticipant::query()
            ->where('competition_room_id', $room->id)
            ->where('student_user_id', auth()->id())
            ->first();
        abort_unless($participant, 403);

        $games = ActivityController::games();
        $game = $games[$room->game_slug] ?? null;
        abort_unless($game, 404);

        $iframeSrc = null;
        if ($room->status === 'live') {
            // Ogrenci icin ayni ActivityRunnerController::open() mantigiyla
            // dogru seviye araligini oturuma yaziyoruz; runner motoru bu
            // grant'i dogrulamadan oyunu acmiyor.
            $request = request();
            $request->session()->put('runner_grant', [
                'slug' => $room->game_slug,
                'from' => (int) $room->level_from,
                'to' => (int) $room->level_to,
                'homework_id' => 'competition-' . $room->id,
                'expires_at' => now()->addHours(6)->timestamp,
            ]);
            $iframeSrc = url("/{$room->game_slug}?from={$room->level_from}&to={$room->level_to}");
        }

        return view('student-portal.competition-play', [
            'room' => $room,
            'participant' => $participant,
            'gameName' => $game['name'],
            'iframeSrc' => $iframeSrc,
        ]);
    }

    public function studentStatus(CompetitionRoom $room)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $room = $this->syncRoomByTimer($room);
        abort_unless($this->studentCanJoin($room, auth()->id()), 403);

        $rows = $this->leaderboardRows($room);
        $myRank = 0;
        foreach ($rows as $i => $row) {
            if ((int) $row['student_user_id'] === (int) auth()->id()) {
                $myRank = $i + 1;
                break;
            }
        }

        return response()->json([
            'status' => $room->status,
            'ends_at_ms' => (int) ($room->ends_at_ms ?? 0),
            'server_now_ms' => $this->nowMs(),
            'joined' => $room->participants()->count(),
            'my_rank' => $myRank,
            'total' => count($rows),
        ]);
    }

    /**
     * Oyun iframe'i, kendi ilerleme olaylarini (GAME_UPDATE / LEVEL_COMPLETED)
     * window.parent'a postMessage ile yayinliyor; ogrenci sayfasindaki JS bunu
     * yakalayip buraya iletiyor. Boylece oyunun kendi ilerleme/level mantigi
     * degistirilmeden, hangi oyun olursa olsun ayni canli siralama besleniyor.
     */
    public function reportProgress(Request $request, CompetitionRoom $room)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $data = $request->validate([
            'progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'current_level_index' => ['nullable', 'integer', 'min:0'],
            'xp' => ['nullable', 'integer', 'min:0'],
        ]);

        $room = $this->syncRoomByTimer($room);
        if ($room->status !== 'live') {
            return response()->json(['ok' => true]);
        }

        $participant = CompetitionParticipant::query()
            ->where('competition_room_id', $room->id)
            ->where('student_user_id', auth()->id())
            ->first();
        if (! $participant || $participant->is_spectator) {
            return response()->json(['ok' => true]);
        }

        $progress = max((float) $participant->progress_percent, (float) ($data['progress_percent'] ?? 0));
        $levelIdx = max((int) $participant->current_level_index, (int) ($data['current_level_index'] ?? 0));
        $xp = (int) ($data['xp'] ?? 0);

        $update = [
            'progress_percent' => min(100, $progress),
            'current_level_index' => $levelIdx,
        ];
        if ($xp > (int) $participant->xp_earned) {
            $update['xp_earned'] = $xp;
        }
        if ($progress >= 100 && ! $participant->finished_at_ms) {
            $update['finished_at_ms'] = $this->nowMs();
        }
        $participant->update($update);

        return response()->json(['ok' => true]);
    }

    private function leaderboardRows(CompetitionRoom $room): array
    {
        return CompetitionParticipant::query()
            ->where('competition_room_id', $room->id)
            ->where('is_spectator', false)
            ->orderByDesc('progress_percent')
            ->orderByDesc('xp_earned')
            ->orderBy('finished_at_ms')
            ->get()
            ->map(fn ($p) => [
                'student_user_id' => (int) $p->student_user_id,
                'name' => $p->user_name,
                'progress_percent' => (float) $p->progress_percent,
                'current_level_index' => (int) $p->current_level_index,
                'xp_earned' => (int) $p->xp_earned,
                'finished' => $p->finished_at_ms !== null,
            ])
            ->values()
            ->all();
    }

    private function syncRoomByTimer(CompetitionRoom $room): CompetitionRoom
    {
        if ($room->status !== 'live') {
            return $room;
        }
        $endsAtMs = (int) ($room->ends_at_ms ?? 0);
        if ($endsAtMs > 0 && $this->nowMs() >= $endsAtMs) {
            $room->update([
                'status' => 'finished',
                'finished_at_ms' => $endsAtMs,
            ]);
            return $room->fresh();
        }

        return $room;
    }

    private function studentCanJoin(CompetitionRoom $room, int $studentUserId): bool
    {
        $student = Student::query()->where('user_id', $studentUserId)->first();
        if (! $student) {
            return false;
        }
        if (empty($room->school_class_id)) {
            return true;
        }

        return (int) $student->school_class_id === (int) $room->school_class_id;
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
