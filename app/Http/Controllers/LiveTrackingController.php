<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentActivityLog;
use Illuminate\Http\Request;

class LiveTrackingController extends Controller
{
    /**
     * Canlı Takip ana sayfası — son 2 saatte aktif olan öğrenciler
     */
    public function index(Request $request)
    {
        $since = now()->subHours(2);

        // Son 2 saatte en az 1 log girişi olan öğrencileri çek
        $students = Student::with(['user', 'schoolClass'])
            ->whereHas('activityLogs', fn($q) => $q->where('logged_at', '>=', $since))
            ->get()
            ->map(function (Student $student) use ($since) {
                $logs = StudentActivityLog::where('student_id', $student->id)
                    ->where('logged_at', '>=', $since)
                    ->orderByDesc('logged_at')
                    ->get();

                return [
                    'student'       => $student,
                    'log_count'     => $logs->count(),
                    'last_seen'     => $logs->first()?->logged_at,
                    'last_action'   => $logs->first()?->action_label,
                    'first_seen'    => $logs->last()?->logged_at,
                ];
            })
            ->sortByDesc('last_seen')
            ->values();

        return view('live-tracking.index', compact('students', 'since'));
    }

    /**
     * Öğrenci detay sayfası — son 2 saatlik adım adım akış
     */
    public function show(Student $student)
    {
        $since = now()->subHours(2);

        $logs = StudentActivityLog::where('student_id', $student->id)
            ->where('logged_at', '>=', $since)
            ->orderByDesc('logged_at')
            ->get();

        $student->load(['user', 'schoolClass']);

        return view('live-tracking.show', compact('student', 'logs', 'since'));
    }

    /**
     * AJAX — canlı güncelleme için öğrenci listesi (JSON)
     */
    public function refresh()
    {
        $since = now()->subHours(2);

        $rows = Student::with(['user', 'schoolClass'])
            ->whereHas('activityLogs', fn($q) => $q->where('logged_at', '>=', $since))
            ->get()
            ->map(function (Student $student) use ($since) {
                $logs = StudentActivityLog::where('student_id', $student->id)
                    ->where('logged_at', '>=', $since)
                    ->orderByDesc('logged_at')
                    ->limit(1)
                    ->get();

                $last = $logs->first();

                return [
                    'id'          => $student->id,
                    'name'        => $student->user->name ?? '-',
                    'class'       => $student->schoolClass->name ?? '-',
                    'last_seen'   => $last?->logged_at?->diffForHumans() ?? '-',
                    'last_action' => $last?->action_label ?? '-',
                    'detail_url'  => route('live-tracking.show', $student),
                ];
            })
            ->sortByDesc(fn($r) => $r['last_seen'])
            ->values();

        return response()->json($rows);
    }
}
