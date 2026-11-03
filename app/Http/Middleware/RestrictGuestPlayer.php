<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Giriş yapmadan kod + ad soyad ile canlı quize katılan misafir hesapları yalnızca quiz ekranını kullanabilir;
 * sistemin başka hiçbir sayfasına ya da API'sine erişemez.
 */
class RestrictGuestPlayer
{
    private const ALLOWED = [
        'student.live-quiz.play', 'student.live-quiz.status', 'student.live-quiz.answer', 'student.live-quiz.game-level',
        'logout', 'logout.get', 'maintenance.status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('guest')) {
            return $next($request);
        }

        $name = (string) ($request->route()?->getName() ?? '');
        if (in_array($name, self::ALLOWED, true) || str_starts_with($name, 'public.live-quiz.')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Bu alan misafir katılımcılara kapalıdır.'], 403);
        }

        return redirect()->route('public.live-quiz.join');
    }
}
