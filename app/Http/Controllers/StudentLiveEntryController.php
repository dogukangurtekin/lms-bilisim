<?php

namespace App\Http\Controllers;

use App\Models\CompetitionRoom;
use App\Models\LiveQuizSession;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Öğrenciler için tek giriş noktası: Canlı Quiz, Canlı Yarışma (oyun) ve
 * Klavye Yarışması odalarına aynı sayfadan, aynı kodla katılınır. Kodun hangi
 * türe ait olduğu burada bulunup ilgili akışa yönlendirilir.
 */
class StudentLiveEntryController extends Controller
{
    public function form()
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);

        return view('student-portal.live-entry');
    }

    public function join(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);

        $code = strtoupper(preg_replace('/\s+/', '', (string) $request->input('join_code', '')));
        $request->merge(['join_code' => $code]);
        $request->validate(['join_code' => ['required', 'string', 'size:6']], [
            'join_code.required' => 'Lütfen katılım kodunu yaz.',
            'join_code.size' => 'Katılım kodu 6 karakter olmalı.',
        ]);

        // Aynı kod birden fazla türde aktifse en yeni oluşturulan seçilir.
        $candidates = [];
        $quiz = LiveQuizSession::query()->where('join_code', $code)->whereIn('status', ['lobby', 'live'])->latest('id')->first();
        if ($quiz) {
            $candidates[] = ['type' => 'quiz', 'at' => $quiz->created_at?->timestamp ?? 0];
        }
        $comp = CompetitionRoom::query()->where('join_code', $code)->whereIn('status', ['lobby', 'live'])->latest('id')->first();
        if ($comp) {
            $candidates[] = ['type' => 'competition', 'at' => $comp->created_at?->timestamp ?? 0];
        }
        $race = Room::query()->where('code', $code)->whereIn('status', ['waiting', 'active'])->latest('id')->first();
        if ($race) {
            $candidates[] = ['type' => 'race', 'at' => $race->created_at?->timestamp ?? 0];
        }

        if ($candidates === []) {
            return back()->withInput()->withErrors(['join_code' => 'Bu kodla aktif bir quiz veya yarışma bulunamadı. Kodu kontrol et ya da öğretmeninden yenisini iste.']);
        }

        usort($candidates, fn ($a, $b) => $b['at'] <=> $a['at']);

        return match ($candidates[0]['type']) {
            'quiz' => app(LiveQuizController::class)->studentJoin($request),
            'competition' => app(CompetitionController::class)->studentJoin($request),
            default => $this->joinRace($race),
        };
    }

    private function joinRace(Room $race): RedirectResponse
    {
        return redirect()->route('keyboard-race.index', ['room' => $race->code]);
    }
}
