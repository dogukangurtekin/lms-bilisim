<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherGameAssignment;
use App\Models\GameAssignment;
use App\Models\Student;
use App\Services\StudentGameAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        private StudentGameAccessService $gameAccess
    ) {
    }

    public static function games(): array
    {
        return [
            'block-grid-runner' => ['name' => 'Blok Kodlama', 'image' => 'blok-kodlama.png', 'url' => '/block-grid-runner'],
            'block-3d-runner' => ['name' => '3D Blok Kodlama', 'image' => '3d-blok-kodlama.png', 'url' => '/block-3d-runner'],
            'compute-it-runner' => ['name' => 'Compute It Runner', 'image' => 'compute-it.png', 'url' => '/compute-it-runner'],
            'lightbot-runner' => ['name' => 'Lightbot Runner', 'image' => 'code-robot.png', 'url' => '/lightbot-runner'],
            'line-trace-runner' => ['name' => 'Line Trace Runner', 'image' => 'cizgi-oyunu.png', 'url' => '/line-trace-runner'],
            'silent-teacher-runner' => ['name' => 'Silent Teacher Python', 'image' => 'python.png', 'url' => '/silent-teacher-runner'],
            'flamestone-game' => ['name' => 'Flamestone Puzzle', 'image' => 'flamestone-puzzle.png', 'url' => '/flamestone-game'],
            'keyboard-race' => ['name' => 'Klavye Yarismasi', 'image' => 'keyboard-runner.png', 'url' => '/keyboard-race'],
            'block-builder-studio' => ['name' => '3D Grid Tasarim', 'image' => '3d-blok-grid-runner.png', 'url' => '/block-builder-studio'],
            'connect-the-dots-runner' => ['name' => 'Noktaları Birleştir', 'image' => 'connect-the-dots.svg', 'url' => '/connect-the-dots-runner'],
            'python-editor' => ['name' => 'Python Kod Editörü', 'image' => 'python.png', 'url' => '/python-editor'],
        ];
    }

    public function index()
    {
        $user = auth()->user();
        $isAdmin = (bool) $user?->hasRole('admin');
        $isTeacher = (bool) $user?->hasRole('teacher');
        $isStudent = (bool) ($user?->student);
        $assignedGameActivities = collect();
        $teacherGameAssignmentsByTeacher = collect();
        $visibleGames = self::games();
        if ($isStudent && $user?->student instanceof Student) {
            $allowedSlugs = $this->gameAccess->allowedSlugsForStudent($user->student);
            $visibleGames = array_intersect_key(self::games(), array_flip($allowedSlugs));
        }
        if ($isTeacher && $user?->teacher) {
            $assignedGameActivities = TeacherGameAssignment::query()
                ->with(['assignedBy'])
                ->where('teacher_id', $user->teacher->id)
                ->orderBy('game_name')
                ->get();
        }
        if ($isAdmin) {
            $teacherGameAssignmentsByTeacher = TeacherGameAssignment::query()
                ->orderBy('teacher_id')
                ->orderBy('game_slug')
                ->get(['teacher_id', 'game_slug'])
                ->groupBy('teacher_id')
                ->map(fn ($rows) => $rows->pluck('game_slug')->values()->all());
        }

        return view('activities.index', [
            'games' => $visibleGames,
            'isAdmin' => $isAdmin,
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
            'assignedGameActivities' => $assignedGameActivities,
            'teacherGameAssignmentsByTeacher' => $teacherGameAssignmentsByTeacher,
            'teachers' => Teacher::query()->with('user:id,name')->orderBy('id')->get(['id', 'user_id']),
        ]);
    }

    public function assignTeacherBulk(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $games = self::games();
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'game_slugs' => ['required', 'array', 'min:1'],
            'game_slugs.*' => ['string', 'max:100'],
        ]);

        $teacherId = (int) $validated['teacher_id'];
        $slugs = collect($validated['game_slugs'])
            ->map(fn ($slug) => trim((string) $slug))
            ->filter(fn ($slug) => $slug !== '' && isset($games[$slug]))
            ->unique()
            ->values();

        if ($slugs->isEmpty()) {
            return back()->with('error', 'Gecerli oyun secimi bulunamadi.');
        }

        foreach ($slugs as $slug) {
            TeacherGameAssignment::query()->updateOrCreate(
                ['teacher_id' => $teacherId, 'game_slug' => $slug],
                [
                    'game_name' => $games[$slug]['name'],
                    'assigned_by' => auth()->id(),
                ]
            );
        }

        return back()->with('ok', count($slugs) . ' oyun/etkinlik ogretmene atandi.');
    }

    public function unassignTeacherBulk(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ]);

        $deleted = TeacherGameAssignment::query()
            ->where('teacher_id', (int) $data['teacher_id'])
            ->delete();

        return back()->with('ok', $deleted > 0
            ? 'Seçili öğretmenden tüm atamalar kaldırıldı.'
            : 'Seçili öğretmende atanmış oyun/etkinlik bulunamadı.');
    }

    public function play(Request $request)
    {
        $target = (string) $request->query('target', '');
        $target = trim($target);

        if ($target === '' || $target[0] !== '/' || str_contains($target, '://')) {
            abort(404);
        }

        $path = trim((string) parse_url($target, PHP_URL_PATH), '/');
        $segments = $path === '' ? [] : explode('/', $path);
        $games = self::games();

        $slug = null;
        if (($segments[0] ?? '') === 'runner-open' && isset($segments[1])) {
            $slug = $segments[1];
        } elseif (isset($segments[0])) {
            $slug = $segments[0];
        }

        if ($slug === null || ! isset($games[$slug])) {
            abort(404);
        }

        return view('activities.play', [
            'src' => url($target),
            'title' => $games[$slug]['name'],
        ]);
    }

    public function studentAllowedSlug(string $slug): bool
    {
        $user = auth()->user();
        if (! $user?->student) {
            return true;
        }

        $student = $user->student;
        if (! $student) {
            return false;
        }

        return $this->gameAccess->canPlay($student, $slug);
    }
}
