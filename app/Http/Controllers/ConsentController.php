<?php

namespace App\Http\Controllers;

use App\Support\ConsentPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/** Giriş sonrası "Okudum, Onayladım" ekranı (herkes için, sürüm başına bir kez). */
class ConsentController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! ConsentPolicy::required($user)) {
            return redirect()->route('dashboard');
        }

        return response()->view('auth.consent', [
            'user' => $user,
            'documents' => ConsentPolicy::documents(),
            'summary' => ConsentPolicy::summary($user),
            'isStudent' => $user->hasRole('student'),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function accept(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (ConsentPolicy::required($user)) {
            $version = ConsentPolicy::version();
            $now = now();

            DB::transaction(function () use ($user, $request, $version, $now): void {
                DB::table('users')->where('id', $user->id)->update(['consent_version' => $version, 'consent_accepted_at' => $now]);
                // Onayın kanıtı: kim, hangi sürümü, ne zaman, hangi cihaz/IP'den onayladı.
                DB::table('user_consents')->insert([
                    'user_id' => $user->id,
                    'version' => $version,
                    'ip' => (string) $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                    'accepted_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        return redirect()->intended(route('dashboard'));
    }
}
