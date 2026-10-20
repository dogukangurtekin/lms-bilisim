<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=(self), camera=(self)');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        // Hosting katmani (Hostinger) sadece zayif bir "upgrade-insecure-requests"
        // CSP'si ekliyor; bu neredeyse hicbir XSS/veri-sizdirma korumasi saglamiyor.
        // Uygulama genelinde cok sayida satir-ici <script> kullanildigindan
        // 'unsafe-inline'/'unsafe-eval' korunuyor (mevcut islevi bozmamak icin),
        // ama betik/stil/yazi tipi kaynaklari yalnizca kendi alan adimizla
        // sinirlandirilarak gercek bir zarar azaltma katmani ekleniyor
        // (harici CDN kullanilmiyor; KVKK/gizlilik metinleriyle uyumlu).
        if (! $request->is('api/*')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "font-src 'self' data:",
                "img-src 'self' data: blob: https:",
                "media-src 'self' data: blob:",
                "connect-src 'self'",
                $request->routeIs('admin.hyperframes') ? "frame-src 'self' https://www.hyperframes.dev" : "frame-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                'upgrade-insecure-requests',
            ]));
        }
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        if ($request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
