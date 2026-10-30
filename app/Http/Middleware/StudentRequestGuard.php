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

        $html = '<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<meta http-equiv="refresh" content="'.$retryAfter.'"><title>Biraz yavaşla</title>'
            .'<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F7F6F2;color:#16182B;font:16px/1.6 system-ui,sans-serif;text-align:center;padding:24px}'
            .'.b{max-width:420px}h1{font-size:24px;margin:8px 0}p{color:#585A72}</style></head><body><div class="b"><div style="font-size:44px">⏳</div>'
            .'<h1>Biraz yavaşla</h1><p>Sayfayı çok sık yeniledin. Birkaç saniye içinde otomatik olarak devam edeceksin.</p></div></body></html>';

        return response($html, 429, ['Retry-After' => $retryAfter, 'Content-Type' => 'text/html; charset=UTF-8']);
    }
}
