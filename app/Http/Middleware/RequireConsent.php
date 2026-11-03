<?php

namespace App\Http\Middleware;

use App\Support\ConsentPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aydınlatma metni / kullanım şartları / çerez politikasını henüz onaylamamış (ya da yeni sürümü onaylamamış)
 * kullanıcılar, onay ekranı dışında hiçbir sayfayı kullanamaz. Onay verilince ekran bir daha gelmez (sürüm değişmedikçe).
 */
class RequireConsent
{
    private const ALLOWED = [
        'consent.show', 'consent.accept',
        'password.force.show', 'password.force.update', 'password.force.token',
        'legal.privacy', 'legal.terms', 'legal.cookies',
        'logout', 'logout.get', 'login', 'maintenance.status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! ConsentPolicy::required($request->user())) {
            return $next($request);
        }

        $name = (string) ($request->route()?->getName() ?? '');
        if (in_array($name, self::ALLOWED, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Devam etmek için önce metinleri okuyup onaylayın.',
                'redirect' => route('consent.show'),
            ], 423);
        }

        // Onaydan sonra kullanıcı gitmek istediği sayfaya döner.
        if ($request->isMethod('GET')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('consent.show');
    }
}
