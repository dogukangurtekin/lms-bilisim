<?php

namespace App\Http\Middleware;

use App\Support\ExamBuilderSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamBuilderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(ExamBuilderSetting::allowedFor($request->user()), 403);

        return $next($request);
    }
}
