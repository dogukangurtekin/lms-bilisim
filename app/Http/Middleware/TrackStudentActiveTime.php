<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Models\StudentActivityLog;
use App\Models\StudentTimeStat;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackStudentActiveTime
{
    // Route adı → okunabilir Türkçe etiket
    private const ROUTE_LABELS = [
        'student.portal.dashboard'        => 'Panele baktı',
        'student.portal.courses'          => 'Dersler listesini açtı',
        'student.portal.course.show'      => 'Bir dersi açtı',
        'student.portal.assignments'      => 'Ödevler sayfasını açtı',
        'student.portal.progress'         => 'Gelişim karnesini açtı',
        'student.portal.friends'          => 'Arkadaşlar sayfasını açtı',
        'student.portal.class-board'      => 'Sınıf panosunu açtı',
        'student.portal.avatars'          => 'Avatar sayfasını açtı',
        'student.portal.badges'           => 'Rozetler sayfasını açtı',
        'student.portal.time.ping'        => null, // ping — kaydetme
        'activities.index'                => 'Oyun & Etkinlikler listesine girdi',
        'activities.show'                 => 'Bir etkinliği açtı',
        'keyboard-race.show'              => 'Klavye yarışı oynadı',
        'student.live-quiz.join.form'     => 'Canlı quiz katılım formunu açtı',
        'student.live-quiz.active'        => 'Aktif canlı quiz oturumuna baktı',
        'student.live-quiz.play'          => 'Canlı quiz oynadı',
        'student.live-quiz.answer'        => 'Canlı quizde cevap verdi',
        'profile.edit'                    => 'Profil sayfasını açtı',
        'login'                           => null,
        'logout.get'                      => null,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();

            if ($student) {
                // 1) Süre takibi (mevcut mantık)
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

                // 2) Aktivite logu — sadece GET istekleri, ping ve ajax hariç
                $routeName = $request->route()?->getName();
                $shouldLog = $request->isMethod('GET')
                    && !$request->ajax()
                    && !$request->wantsJson()
                    && $routeName !== 'student.portal.time.ping';

                if (!$shouldLog && $request->isMethod('POST')) {
                    // POST'larda sadece anlamlı olanları logla
                    $postRoutes = [
                        'student.live-quiz.answer',
                        'student.live-quiz.join',
                    ];
                    $shouldLog = in_array($routeName, $postRoutes);
                }

                if ($shouldLog) {
                    // null etiketli route'ları kaydetme
                    $label = array_key_exists($routeName, self::ROUTE_LABELS)
                        ? self::ROUTE_LABELS[$routeName]
                        : $this->buildGenericLabel($request);

                    if ($label !== null) {
                        StudentActivityLog::create([
                            'student_id'   => $student->id,
                            'url'          => $request->path(),
                            'route_name'   => $routeName,
                            'action_label' => $label,
                            'method'       => $request->method(),
                            'ip'           => $request->ip(),
                            'logged_at'    => $now,
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }

    private function buildGenericLabel(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'ders') || str_contains($path, 'course')) {
            return 'Ders içeriğine baktı';
        }
        if (str_contains($path, 'etkinlik') || str_contains($path, 'activit')) {
            return 'Etkinlik sayfasında gezindi';
        }
        if (str_contains($path, 'odev') || str_contains($path, 'assignment')) {
            return 'Ödev detayına baktı';
        }

        return 'Sayfayı ziyaret etti (' . $path . ')';
    }
}
