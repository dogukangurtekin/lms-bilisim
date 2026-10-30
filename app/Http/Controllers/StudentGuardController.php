<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Aşırı yenileme yapan öğrenci hesabına gösterilen doğrulamanın cevabı (bkz. StudentRequestGuard).
 * Kısa bir toplama sorusu + en az 3 sn bekleme: elle yenilemeyi ve konsol döngülerini yavaşlatır.
 */
class StudentGuardController extends Controller
{
    public function verify(Request $request)
    {
        $user = $request->user();
        $c = $request->session()->get('guard_challenge');
        $intended = is_array($c) ? (string) ($c['intended'] ?? '/') : '/';
        // Yalnızca site içi yola dön (open redirect olmasın).
        if (! str_starts_with($intended, '/') || str_starts_with($intended, '//')) {
            $intended = '/ogrenci/panelim';
        }

        $age = is_array($c) ? time() - (int) ($c['at'] ?? 0) : -1;
        $ok = is_array($c)
            && $age >= 3 && $age <= 600
            && (int) $request->input('answer', -999) === (int) ($c['answer'] ?? -1);

        if (! $ok) {
            // Yanlış/çok hızlı: yeni soru için sayfa tekrar çıkar (limit hâlâ aşılmış durumda).
            $request->session()->forget('guard_challenge');

            return redirect($intended);
        }

        $request->session()->forget('guard_challenge');
        RateLimiter::clear('student_guard:'.$user->id.':nav');

        // 15 dk içinde 3. kez doğrulama gerekiyorsa 5 dk dinlenme süresi: ısrarlı yenileme zinciri kırılır.
        $strikes = (int) Cache::get('guard_strikes:'.$user->id, 0) + 1;
        Cache::put('guard_strikes:'.$user->id, $strikes, 900);
        if ($strikes >= 3) {
            Cache::put('guard_cooldown:'.$user->id, time() + 300, 300);
            Cache::forget('guard_strikes:'.$user->id);
        }

        return redirect($intended);
    }
}
