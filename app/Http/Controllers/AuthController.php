<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentTimeStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = strtolower(trim($credentials['email']));
        $email = str_contains($login, '@') ? $login : ($login . '@school.local');

        $user = User::query()
            ->with('role')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->orWhereRaw("LOWER(SUBSTRING_INDEX(email, '@', 1)) = ?", [$login])
            ->first();

        if (! $user || ! $this->matchesPassword($user, (string) $credentials['password'])) {
            return back()->withErrors(['email' => 'Giris bilgileri hatali'])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user?->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
            if (! $student) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => 'Bu ogrenci hesabi icin ogrenci kaydi bulunamadi.'])->onlyInput('email');
            }

            $stat = StudentTimeStat::firstOrCreate(
                ['student_id' => $student->id],
                ['total_seconds' => 0, 'last_seen_at' => now()]
            );
            $stat->last_seen_at = now();
            $stat->save();
        }

        return redirect()->route('dashboard');
    }

    private function matchesPassword(User $user, string $plainPassword): bool
    {
        $hashedInfo = password_get_info((string) $user->password);
        $isHashed = ($hashedInfo['algo'] ?? null) !== null && ($hashedInfo['algo'] ?? 0) !== 0;

        if ($isHashed) {
            return Hash::check($plainPassword, (string) $user->password);
        }

        if (hash_equals((string) $user->password, $plainPassword)) {
            $user->password = Hash::make($plainPassword);
            $user->save();
            return true;
        }

        return false;
    }

    public function gameLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
            'game_won' => ['required', 'boolean'],
        ]);

        if (! (bool) $validated['game_won']) {
            return response()->json(['message' => 'Oyun kazanılmadan giriş yapılamaz.'], 422);
        }

        $login = strtolower(trim($validated['email']));
        $email = str_contains($login, '@') ? $login : $login . '@school.local';

        $user = User::with('role')->where('email', $email)->first();
        if (! $user || ! $user->hasRole('student')) {
            return response()->json(['message' => 'Bu yöntem yalnızca öğrenci hesapları için kullanılabilir.'], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $student = Student::where('user_id', $user->id)->first();
        if (! $student) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => 'Bu ogrenci hesabi icin ogrenci kaydi bulunamadi.'], 422);
        }

        $stat = StudentTimeStat::firstOrCreate(
            ['student_id' => $student->id],
            ['total_seconds' => 0, 'last_seen_at' => now()]
        );
        $stat->last_seen_at = now();
        $stat->save();

        return response()->json([
            'ok' => true,
            'message' => 'Tebrikler, giriş yapıyorsunuz.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user?->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $stat = StudentTimeStat::firstOrCreate(
                    ['student_id' => $student->id],
                    ['total_seconds' => 0, 'last_seen_at' => now()]
                );
                if ($stat->last_seen_at) {
                    $diff = max(0, $stat->last_seen_at->diffInSeconds(now()));
                    if ($diff > 0) {
                        $diff = min($diff, 120);
                        $stat->total_seconds = (int) $stat->total_seconds + $diff;
                    }
                }
                $stat->last_seen_at = null;
                $stat->save();
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
