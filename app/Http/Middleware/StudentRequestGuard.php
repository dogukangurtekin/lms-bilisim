<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Öğrenci hesapları için kullanıcı başına istek sınırı (IP'ye göre değil: okullar aynı IP'yi paylaşır).
 *
 * Amaç: art arda F5 ve tarayıcı konsolundan yazılan döngü/betik istekleri sunucuyu yormasın.
 * Konsoldan atılan istek oturum ve CSRF belirteciyle normal istekten ayırt edilemez; bu yüzden
 * savunma, istek hızını sınırlamak ve XP gibi değerleri sunucuda doğrulamaktır.
 *
 * Canlı yarışma ve canlı quiz yolları sık yoklama yaptığı için ayrı, geniş bir kovadadır.
 */
class StudentRequestGuard
{
    private const LIMITS = [
        'live' => 600,  // canlı yarışma / canlı quiz (sık yoklama)
        'nav' => 40,    // sayfa açma / yenileme (F5)
        'mut' => 90,    // POST/PUT/PATCH/DELETE
        'all' => 300,   // diğer tüm istekler (AJAX, yoklama vb.)
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('student')) {
            return $next($request);
        }

        $name = (string) ($request->route()?->getName() ?? '');
        if (in_array($name, ['logout', 'logout.get', 'login', 'maintenance.status'], true)) {
            return $next($request);
        }

        $bucket = $this->bucket($request, $name);

        // Ardışık doğrulamalardan sonra verilen dinlenme süresi (yalnızca sayfa açma/yenileme).
        if ($bucket === 'nav' && ($until = (int) Cache::get('guard_cooldown:'.$user->id, 0)) > time()) {
            return $this->cooldownPage($until - time());
        }

        $key = 'student_guard:'.$user->id.':'.$bucket;
        $max = self::LIMITS[$bucket];

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return $this->tooMany($request, $user->id, $bucket, RateLimiter::availableIn($key));
        }
        RateLimiter::hit($key, 60);

        return $next($request);
    }

    private function bucket(Request $request, string $name): string
    {
        if (Str::startsWith($name, ['student.competitions.', 'student.live-quiz.']) || $request->is('ogrenci/canli-*')) {
            return 'live';
        }
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return 'mut';
        }
        if (! $request->ajax() && ! $request->expectsJson()) {
            return 'nav';
        }

        return 'all';
    }

    private function tooMany(Request $request, int $userId, string $bucket, int $retryAfter): Response
    {
        $retryAfter = max(1, $retryAfter);

        // Aynı kullanıcı için en fazla 5 dakikada bir log: konsol döngüsü logu da şişirmesin.
        if (Cache::add('student_guard_log:'.$userId.':'.$bucket, 1, 300)) {
            Log::warning('Öğrenci istek sınırı aşıldı', ['user_id' => $userId, 'bucket' => $bucket, 'path' => $request->path()]);
        }

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['ok' => false, 'message' => 'Çok fazla istek gönderdiniz. Lütfen biraz bekleyin.'], 429, ['Retry-After' => $retryAfter]);
        }

        if ($bucket === 'nav' && $request->hasSession()) {
            return $this->challengePage($request, $retryAfter);
        }

        return $this->page('⏳', 'Biraz yavaşla', 'Çok fazla istek gönderdin. Birkaç saniye içinde tekrar deneyebilirsin.', $retryAfter, '', $retryAfter);
    }

    /** Aşırı yenileme: kısa bir toplama sorusu ve en az 3 sn bekleme ile devam edilir (harici servis yok). */
    private function challengePage(Request $request, int $retryAfter): Response
    {
        $a = random_int(2, 9);
        $b = random_int(2, 9);
        $request->session()->put('guard_challenge', [
            'answer' => $a + $b,
            'at' => time(),
            'intended' => $request->isMethod('GET') ? '/'.ltrim($request->getRequestUri(), '/') : '/ogrenci/panelim',
        ]);

        $form = '<form method="POST" action="'.e(route('student.guard.verify')).'" style="margin-top:18px">'
            .'<input type="hidden" name="_token" value="'.e(csrf_token()).'">'
            .'<label style="display:block;font-weight:700;margin-bottom:8px">'.$a.' + '.$b.' = ?</label>'
            .'<input name="answer" type="number" inputmode="numeric" autocomplete="off" required autofocus style="width:110px;padding:10px;font-size:18px;text-align:center;border:2px solid #cfd2e3;border-radius:10px"> '
            .'<button id="gb" type="submit" disabled style="padding:11px 20px;font-size:16px;font-weight:700;border:0;border-radius:10px;background:#5B3DF5;color:#fff;cursor:pointer;opacity:.5">Devam et</button>'
            .'</form><script>(function(){var b=document.getElementById("gb"),n=3;function t(){if(n>0){b.textContent="Devam et ("+n+")";n--;setTimeout(t,1000)}else{b.disabled=false;b.style.opacity=1;b.textContent="Devam et"}}t()})()</script>';

        return $this->page('🛡️', 'Küçük bir doğrulama', 'Sayfayı çok sık yeniledin. Devam etmek için sonucu yaz.', 0, $form, $retryAfter);
    }

    private function cooldownPage(int $seconds): Response
    {
        $seconds = max(1, $seconds);

        return $this->page('☕', 'Kısa bir mola', 'Çok sık yenileme yaptığın için sayfa kısa süre dinleniyor. Birazdan otomatik olarak devam edeceksin.', $seconds, '', $seconds);
    }

    private function page(string $icon, string $title, string $text, int $refresh, string $extra, int $retryAfter): Response
    {
        $meta = $refresh > 0 ? '<meta http-equiv="refresh" content="'.$refresh.'">' : '';
        $html = '<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            .$meta.'<title>'.e($title).'</title>'
            .'<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F7F6F2;color:#16182B;font:16px/1.6 system-ui,sans-serif;text-align:center;padding:24px}'
            .'.b{max-width:420px}h1{font-size:24px;margin:8px 0}p{color:#585A72;margin:0}</style></head><body><div class="b"><div style="font-size:44px">'.$icon.'</div>'
            .'<h1>'.e($title).'</h1><p>'.e($text).'</p>'.$extra.'</div></body></html>';

        return response($html, 429, ['Retry-After' => max(1, $retryAfter), 'Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
