<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Models\StudentTimeStat;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackStudentActiveTime
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $stat = StudentTimeStat::firstOrCreate(
                    ['student_id' => $student->id],
                    ['total_seconds' => 0, 'last_seen_at' => now()]
                );
                $now = now();
                if ($stat->last_seen_at) {
                    $diff = max(0, $stat->last_seen_at->diffInSeconds($now));
                    if ($diff > 0 && $diff <= 900) {
                        $stat->total_seconds = (int) $stat->total_seconds + $diff;
                    }
                }
                $stat->last_seen_at = $now;
                $stat->save();
            }
        }

        return $next($request);
    }
}

