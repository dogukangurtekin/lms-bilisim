<?php

namespace App\Http\Controllers;

use App\Models\CourseHomework;
use App\Models\GameAssignment;
use App\Services\LessonPresentation\SlidePresentationService;
use App\Support\PublicHomework;
use Illuminate\Support\Facades\Storage;

/**
 * Herkese açık ödev linki (/acik-odev/{token}): giriş gerektirmez, yalnızca linki bilen kişiler ulaşır.
 * İlerleme/XP kaydı yoktur; öğrenci ödevi görür, etkinliği oynar, ek dosyayı indirir.
 */
class PublicHomeworkController extends Controller
{
    /** @return array{0: 'course'|'game', 1: CourseHomework|GameAssignment} */
    private function resolve(string $token): array
    {
        abort_unless(PublicHomework::available() && strlen($token) >= 20, 404);

        $course = CourseHomework::query()->with('course')
            ->where('public_token', $token)->where('is_public', true)->oldest('id')->first();
        if ($course) {
            return ['course', $course];
        }

        $game = GameAssignment::query()
            ->where('public_token', $token)->where('is_public', true)->first();
        abort_unless($game, 404);

        return ['game', $game];
    }

    public function show(string $token)
    {
        [$kind, $hw] = $this->resolve($token);
        $games = ActivityController::games();

        $data = [
            'title' => (string) $hw->title,
            'details' => $kind === 'course' ? (string) $hw->details : '',
            'due' => $hw->due_date,
            'overdue' => $hw->due_date && $hw->due_date->endOfDay()->isPast(),
            'typeLabel' => 'Ödev',
            'contentName' => null,
            'levels' => null,
            'attachment' => null,
            'action' => null,
            'note' => null,
        ];

        if ($kind === 'game') {
            $data['typeLabel'] = 'Etkinlik ödevi';
            $data['contentName'] = $hw->game_name;
            $data['levels'] = [$hw->level_from, $hw->level_to];
            $data['action'] = ['Ödeve Başla', route('public.homework.play', $token)];
        } else {
            $type = (string) $hw->assignment_type;
            if ($type === 'lesson' && $hw->course) {
                $data['typeLabel'] = 'Ders ödevi';
                $data['contentName'] = $hw->course->name;
                $data['action'] = ['Dersi Aç', route('public.homework.lesson', $token)];
            } elseif (in_array($type, ['game', 'application'], true)) {
                $data['typeLabel'] = $type === 'game' ? 'Oyun ödevi' : 'Uygulama ödevi';
                $data['contentName'] = $games[$hw->target_slug]['name'] ?? null;
                $data['levels'] = [$hw->level_from, $hw->level_to];
                $data['action'] = ['Ödeve Başla', route('public.homework.play', $token)];
            }
            if ($hw->attachment_path) {
                // Uzantı URL'de görünür; önizleyici dosya türünü buradan anlar. Dosya indirilmez, sayfada gösterilir.
                $ext = strtolower(pathinfo((string) $hw->attachment_path, PATHINFO_EXTENSION));
                $data['attachment'] = [
                    $hw->attachment_original_name ?: 'Ek dosya',
                    route('public.homework.attachment', [$token, 'ek.' . $ext]),
                ];
            }
        }

        return view('public-homework', $data);
    }

    public function play(string $token)
    {
        [$kind, $hw] = $this->resolve($token);
        $slug = $kind === 'game' ? $hw->game_slug : ($hw->target_slug ?: null);
        abort_unless(PublicHomework::slugIsPlayablePublicly($slug), 404);

        // Bu oyun yalnızca bu ziyaretçi için (oturum bazlı) açılır; ödevin son level'ına kadar oynanır.
        session()->put('public_homework', [
            'slug' => $slug,
            'limit' => (int) ($hw->level_to ?? 0),
            'token' => $token,
        ]);

        return redirect()->route('public.game', $slug);
    }

    public function lesson(string $token, SlidePresentationService $presentation)
    {
        [$kind, $hw] = $this->resolve($token);
        abort_unless($kind === 'course' && (string) $hw->assignment_type === 'lesson' && $hw->course, 404);

        return view('student-portal.course-show', [
            'student' => null,
            'course' => $hw->course,
            'courseProgress' => null,
            'slides' => $presentation->prepareCourseSlides($hw->course, true),
            'previewMode' => true,
            'publicHomeworkToken' => $token,
        ]);
    }

    /** Ek dosyayı önizleme için satır içi (inline) sunar; indirme penceresi açtırmaz. */
    public function attachment(string $token, string $file)
    {
        [$kind, $hw] = $this->resolve($token);
        abort_unless($kind === 'course' && $hw->attachment_path && Storage::disk('public')->exists($hw->attachment_path), 404);

        $ext = strtolower(pathinfo($hw->attachment_path, PATHINFO_EXTENSION));
        abort_unless(strtolower(pathinfo($file, PATHINFO_EXTENSION)) === $ext, 404);

        $types = [
            'pdf' => 'application/pdf',
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        abort_unless(isset($types[$ext]), 404);

        return response()->file(Storage::disk('public')->path($hw->attachment_path), [
            'Content-Type' => $types[$ext],
            'Content-Disposition' => 'inline; filename="ek.' . $ext . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
