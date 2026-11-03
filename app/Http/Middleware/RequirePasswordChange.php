<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Şifresini henüz yenilememiş öğrenci hesapları, şifre yenileme ekranı dışında hiçbir sayfayı kullanamaz.
 * Şifre yenilenince bayrak kalkar ve ekran bir daha gelmez.
 */
class RequirePasswordChange
{
    private const ALLOWED = [
        'password.force.show', 'password.force.update', 'logout', 'logout.get', 'login', 'maintenance.status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->password_change_required || ! $user->hasRole('student')) {
            return $next($request);
        }

        $name = (string) ($request->route()?->getName() ?? '');
        if (in_array($name, self::ALLOWED, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Devam etmek için önce şifrenizi güncelleyin.',
                'redirect' => route('password.force.show'),
            ], 423);
        }

        return redirect()->route('password.force.show');
    }
}
