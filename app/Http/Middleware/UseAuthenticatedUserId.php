<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * İstemciden gelen user_id değerini oturum açmış kullanıcının id'siyle ezer.
 * Aksi halde herhangi biri başka bir kullanıcının (ör. öğretmenin) id'sini
 * göndererek onun adına işlem yapabilirdi.
 */
class UseAuthenticatedUserId
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(['user_id' => $request->user()->id]);

        return $next($request);
    }
}
