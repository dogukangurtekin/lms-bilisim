<?php

namespace App\Http\Controllers;

use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use App\Support\PublicGamesSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Giriş yapmadan canlı quize katılım: yönetici "herkese açık" olarak başlattığı oturuma kod + ad soyad ile girilir.
 * Katılımcı için oturuma özel geçici bir "misafir" hesabı açılır; bu hesap yalnızca quiz ekranını kullanabilir
 * (bkz. RestrictGuestPlayer) ve XP / öğrenci raporu gibi öğrenci kayıtlarına yazılmaz.
 */
class PublicLiveQuizController extends Controller
{
    /** Tek bir herkese açık oturuma en fazla bu kadar misafir katılabilir. */
    private const MAX_GUESTS = 300;

    /** Hiçbir parolayla eşleşmeyen bcrypt özeti: misafir hesapla parola girişi yapılamaz. */
    private const UNUSABLE_HASH = '$2y$04$IidytrRDiXYNo3jbDrAhVeheifSxunvcd9cjKwnRp/Gj6J1aVAn.O';

    public function form(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($user?->hasRole('guest')) {
            $active = LiveQuizParticipant::query()
                ->where('student_user_id', $user->id)
                ->whereIn('live_quiz_session_id', LiveQuizSession::query()->where('is_public', true)->whereIn('status', ['lobby', 'live'])->select('id'))
                ->latest('id')
                ->first();
            if ($active && PublicGamesSetting::liveQuizEnabled()) {
                return redirect()->route('student.live-quiz.play', $active->live_quiz_session_id);
            }
        }

        return view('public-live-quiz', [
            'enabled' => PublicGamesSetting::liveQuizEnabled(),
            'code' => Str::upper(Str::substr((string) $request->query('kod', ''), 0, 6)),
            'signedInAsMember' => $user !== null && ! $user->hasRole('guest'),
        ]);
    }

    public function join(Request $request): RedirectResponse
    {
        if (! PublicGamesSetting::liveQuizEnabled()) {
            return back()->withInput()->withErrors(['join_code' => 'Herkese açık canlı quiz şu an kapalı.']);
        }

        $name = '/^[\pL][\pL\s\'.\-]*$/u';
        $data = $request->validate([
            'join_code' => ['required', 'string', 'size:6'],
            'first_name' => ['required', 'string', 'min:2', 'max:30', 'regex:' . $name],
            'last_name' => ['required', 'string', 'min:2', 'max:30', 'regex:' . $name],
        ], [
            'join_code.required' => 'Katılım kodunu yaz.',
            'join_code.size' => 'Katılım kodu 6 karakter olmalı.',
            'first_name.required' => 'Adını yaz.',
            'first_name.min' => 'Adın çok kısa.',
            'first_name.regex' => 'Adında yalnızca harf kullan.',
            'last_name.required' => 'Soyadını yaz.',
            'last_name.min' => 'Soyadın çok kısa.',
            'last_name.regex' => 'Soyadında yalnızca harf kullan.',
        ]);

        $auth = $request->user();
        if ($auth && ! $auth->hasRole('guest')) {
            return back()->withInput()->withErrors(['join_code' => 'Hesabınla zaten giriş yapmışsın. Öğrenciysen Canlı Yarışmalar bölümünden katıl.']);
        }

        $session = LiveQuizSession::query()
            ->where('join_code', Str::upper($data['join_code']))
            ->where('is_public', true)
            ->whereIn('status', ['lobby', 'live'])
            ->first();
        if (! $session) {
            return back()->withInput()->withErrors(['join_code' => 'Bu kodla açık bir quiz bulunamadı. Kodu kontrol et.']);
        }

        $already = $auth
            ? LiveQuizParticipant::query()->where('live_quiz_session_id', $session->id)->where('student_user_id', $auth->id)->exists()
            : false;
        if ($already) {
            return redirect()->route('student.live-quiz.play', $session);
        }

        $count = LiveQuizParticipant::query()->where('live_quiz_session_id', $session->id)->count();
        if ($count >= self::MAX_GUESTS) {
            return back()->withInput()->withErrors(['join_code' => 'Bu quiz katılımcı sınırına ulaştı.']);
        }

        $displayName = $this->uniqueName($session, $this->cleanName($data['first_name'] . ' ' . $data['last_name']));

        if ($auth) {
            $user = $auth;
            $user->forceFill(['name' => $displayName])->save();
        } else {
            $user = $this->createGuest($displayName);
            Auth::login($user);
            $request->session()->regenerate();
        }

        LiveQuizParticipant::query()->updateOrCreate(
            ['live_quiz_session_id' => $session->id, 'student_user_id' => $user->id],
            ['joined_at_ms' => (int) floor(microtime(true) * 1000)]
        );

        return redirect()->route('student.live-quiz.play', $session);
    }

    public function leave(Request $request): RedirectResponse
    {
        if ($request->user()?->hasRole('guest')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('public.live-quiz.join');
    }

    private function cleanName(string $name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $name));
    }

    /** Aynı odada aynı ad soyad varsa sıralamada karışmasın diye "(2)", "(3)" eklenir. */
    private function uniqueName(LiveQuizSession $session, string $name): string
    {
        $taken = User::query()
            ->whereIn('id', LiveQuizParticipant::query()->where('live_quiz_session_id', $session->id)->select('student_user_id'))
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower((string) $n, 'UTF-8'))
            ->all();

        $candidate = $name;
        for ($i = 2; in_array(mb_strtolower($candidate, 'UTF-8'), $taken, true); $i++) {
            $candidate = $name . ' (' . $i . ')';
        }

        return $candidate;
    }

    private function createGuest(string $name): User
    {
        $roleId = Role::query()->firstOrCreate(['slug' => 'guest'], ['name' => 'Misafir'])->id;
        $now = now();
        $id = DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'name' => $name,
            'email' => 'guest-' . Str::lower(Str::random(24)) . '@guest.invalid',
            'password' => self::UNUSABLE_HASH,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return User::query()->findOrFail($id);
    }
}
