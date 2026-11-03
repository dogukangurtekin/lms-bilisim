<?php

namespace App\Http\Controllers;

use App\Models\StudentCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Öğrencinin ilk girişte zorunlu, tek seferlik şifre yenileme ekranı. */
class ForcePasswordController extends Controller
{
    private const TOO_SIMPLE = ['123456', '1234567', '12345678', '123456789', '111111', '000000', '123123', 'password', 'qwerty', 'sifre', 'şifre'];

    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user->password_change_required) {
            return redirect()->route('dashboard');
        }

        // Geri tuşu / önbellek eski CSRF belirteciyle açılmasın.
        return response()->view('auth.force-password', ['user' => $user])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /** Form gönderilmeden hemen önce güncel CSRF belirtecini verir (sayfa uzun süre açık kalsa da uyuşmazlık olmaz). */
    public function token(Request $request): JsonResponse
    {
        return response()->json(['token' => csrf_token()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->password_change_required) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ], [
            'password.required' => 'Yeni şifreni yaz.',
            'password.min' => 'Şifre en az 6 karakter olmalı.',
            'password.max' => 'Şifre en fazla 72 karakter olabilir.',
            'password.confirmed' => 'Şifre ve şifre tekrarı aynı olmalı.',
        ]);
        $new = (string) $data['password'];

        $username = Str::lower((string) Str::before((string) $user->email, '@'));
        $error = null;
        if (Hash::check($new, (string) $user->password)) {
            $error = 'Yeni şifren eski şifrenle aynı olamaz.';
        } elseif (Str::lower($new) === $username) {
            $error = 'Şifren kullanıcı adınla aynı olamaz.';
        } elseif (in_array(Str::lower($new), self::TOO_SIMPLE, true)) {
            $error = 'Bu şifre çok kolay tahmin edilir. Daha zor bir şifre seç.';
        }
        if ($error !== null) {
            return back()->withErrors(['password' => $error]);
        }

        DB::transaction(function () use ($user, $new, $username): void {
            $user->password = $new;
            $user->password_change_required = false;
            $user->password_changed_at = now();
            $user->save();

            // Giriş kartları ve öğrenci verileri ekranındaki kayıt güncel şifreyle eşit kalsın.
            $student = $user->student()->first();
            if ($student) {
                $credential = StudentCredential::query()->firstOrNew(['student_id' => $student->id]);
                if (! $credential->exists) {
                    $credential->username = $username;
                }
                $credential->plain_password = $new;
                $credential->save();
            }
        });

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Şifren güncellendi. Artık yeni şifrenle giriş yapabilirsin.');
    }
}
