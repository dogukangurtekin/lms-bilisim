<?php

namespace App\Http\Middleware;

use App\Support\MaintenanceMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bakım modu açıkken yalnızca admin sisteme erişebilir. Öğrenci, öğretmen ve
 * ziyaretçiler login ekranı yerine bakım sayfasını görür. Admin'in giriş
 * yapabilmesi için /login?yonetici=1 ve login POST'u açık bırakılır
 * (POST'ta admin olmayan hesap AuthController içinde reddedilir).
 */
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! MaintenanceMode::active()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        return self::respond($request);
    }

    public static function respond(Request $request): Response
    {
        $state = MaintenanceMode::state();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'maintenance' => true,
                'message' => 'Sistem bakım çalışmasındadır, yakın zamanda açılacaktır.',
            ], 503, ['Retry-After' => 300]);
        }

        return response()
            ->view('maintenance', ['note' => (string) ($state['message'] ?? '')], 503)
            ->header('Retry-After', '300')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function isAllowed(Request $request): bool
    {
        if ($request->is('up', '__deploy/*', 'bakim-durumu', 'logout', 'gizlilik-politikasi', 'kullanim-sartlari', 'cerez-politikasi', 'build/*')) {
            return true;
        }

        if ($request->is('login')) {
            return $request->isMethod('post') || $request->boolean('yonetici');
        }

        return false;
    }
}
