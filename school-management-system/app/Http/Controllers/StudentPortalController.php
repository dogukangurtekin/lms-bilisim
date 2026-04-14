<?php

namespace App\Http\Controllers;

use App\Models\ContentProgress;
use App\Models\Avatar;
use App\Models\Badge;
use App\Models\ClassBoardPost;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\GameAssignment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentGameAssignmentProgress;
use App\Models\StudentHomeworkProgress;
use App\Models\StudentTimeStat;
use App\Services\StudentProgressReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentPortalController extends Controller
{
    public function __construct(private StudentProgressReportService $reportService)
    {
    }

    public function dashboard()
    {
        $student = $this->getStudent();
        $courses = $this->studentCourses($student)->get();
        $gameAssignments = $this->studentAssignments($student)->get();
        $courseHomeworks = $this->studentCourseHomeworks($student)->get();
        $courseSlideAssignments = $courses->filter(function ($course) {
            $slides = (array) data_get($course->lesson_payload, 'slides', []);
            return count($slides) > 0;
        });
        $totalAssignments = $gameAssignments->count() + $courseHomeworks->count() + $courseSlideAssignments->count();
        $completedCourseHomeworkCount = StudentHomeworkProgress::where('student_id', $student->id)
            ->whereIn('course_homework_id', $courseHomeworks->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();
        $completedGameAssignmentCount = StudentGameAssignmentProgress::where('student_id', $student->id)
            ->whereIn('game_assignment_id', $gameAssignments->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();
        $completedCourseSlideCount = ContentProgress::where('user_id', $student->user_id)
            ->where('completed', true)
            ->whereIn('content_id', $courseSlideAssignments->map(fn ($c) => 'course-' . $c->id)->values())
            ->count();

        $completedAssignments = $completedCourseHomeworkCount + $completedGameAssignmentCount + $completedCourseSlideCount;
        $pendingAssignments = max($totalAssignments - $completedAssignments, 0);
        $overallProgress = $totalAssignments > 0 ? (int) round(($completedAssignments / $totalAssignments) * 100) : 0;

        $gameHomeworkCount = $courseHomeworks
            ->filter(fn ($h) => in_array((string) $h->assignment_type, ['game', 'application'], true))
            ->count();
        $completedCourseGameHomeworkCount = StudentHomeworkProgress::where('student_id', $student->id)
            ->whereIn(
                'course_homework_id',
                $courseHomeworks
                    ->filter(fn ($h) => in_array((string) $h->assignment_type, ['game', 'application'], true))
                    ->pluck('id')
            )
            ->whereNotNull('completed_at')
            ->count();
        $completedGameApps = $completedGameAssignmentCount + $completedCourseGameHomeworkCount;
        $pendingGameApps = max(($gameAssignments->count() + $gameHomeworkCount) - $completedGameApps, 0);

        $lessonHomeworkCount = $courseHomeworks
            ->filter(fn ($h) => (string) $h->assignment_type === 'lesson')
            ->count();
        $completedLessonHomeworkCount = StudentHomeworkProgress::where('student_id', $student->id)
            ->whereIn(
                'course_homework_id',
                $courseHomeworks
                    ->filter(fn ($h) => (string) $h->assignment_type === 'lesson')
                    ->pluck('id')
            )
            ->whereNotNull('completed_at')
            ->count();
        $pendingCourseSlides = max($courseSlideAssignments->count() - $completedCourseSlideCount, 0);
        $pendingCourses = max($lessonHomeworkCount - $completedLessonHomeworkCount, 0) + $pendingCourseSlides;

        $xp = $this->xp($student);
        $this->syncStudentBadges($student, $xp);

        $students = Student::with(['user', 'schoolClass', 'currentAvatar'])->get();
        $xpMap = [];
        foreach ($students as $s) {
            $xpMap[$s->id] = $this->xp($s);
        }

        $schoolRank = collect($xpMap)->sortDesc()->keys()->search($student->id);
        $schoolRank = $schoolRank === false ? null : $schoolRank + 1;

        $currentClassName = $student->schoolClass?->name;
        $gradePeers = $students->filter(fn ($s) => $s->schoolClass?->name === $currentClassName);
        $gradeRankList = $gradePeers->mapWithKeys(fn ($s) => [$s->id => $xpMap[$s->id] ?? 0])->sortDesc()->keys();
        $gradeRank = $gradeRankList->search($student->id);
        $gradeRank = $gradeRank === false ? null : $gradeRank + 1;

        $topGrade = $gradePeers
            ->map(fn ($s) => [
                'name' => $s->user?->name,
                'xp' => $xpMap[$s->id] ?? 0,
                'avatar' => $s->currentAvatar?->image_path,
            ])
            ->sortByDesc('xp')
            ->take(10)
            ->values();

        $xpTrend = [];
        $taskTrend = [];
        $today = Carbon::now('Europe/Istanbul')->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $today)->addDays($i);
            $next = (clone $day)->addDay();
            $xpDaily = (int) ContentProgress::where('user_id', $student->user_id)
                ->whereBetween('created_at', [$day, $next])
                ->sum('xp_awarded');
            $completedDaily = ContentProgress::where('user_id', $student->user_id)
                ->where('completed', true)
                ->whereBetween('created_at', [$day, $next])
                ->count();
            $xpTrend[] = ['label' => $day->format('d.m'), 'value' => $xpDaily];
            $taskTrend[] = ['label' => $day->format('d.m'), 'value' => $completedDaily];
        }

        $startOfWeek = Carbon::now('Europe/Istanbul')->startOfWeek(Carbon::MONDAY)->startOfDay();
        $todayCompleted = ContentProgress::where('user_id', $student->user_id)
            ->where('completed', true)
            ->whereBetween('created_at', [$today, (clone $today)->addDay()])
            ->count();
        $weekCompleted = ContentProgress::where('user_id', $student->user_id)
            ->where('completed', true)
            ->whereBetween('created_at', [$startOfWeek, (clone $today)->addDay()])
            ->count();
        $dailyGoalTarget = 1;
        $weeklyGoalTarget = 5;
        $dailyGoalPct = (int) min(100, round(($todayCompleted / max(1, $dailyGoalTarget)) * 100));
        $weeklyGoalPct = (int) min(100, round(($weekCompleted / max(1, $weeklyGoalTarget)) * 100));
        $weekRemaining = max(0, $weeklyGoalTarget - $weekCompleted);
        $xpPerTaskGoal = 20;
        $xpToGoal = $weekRemaining * $xpPerTaskGoal;
        $xpWeekEarned = (int) ContentProgress::where('user_id', $student->user_id)
            ->whereBetween('created_at', [$startOfWeek, (clone $today)->addDay()])
            ->sum('xp_awarded');

        $heatmapDays = collect(range(6, 0))->map(function ($offset) use ($student, $today) {
            $day = (clone $today)->subDays($offset);
            $next = (clone $day)->addDay();
            $completed = ContentProgress::where('user_id', $student->user_id)
                ->where('completed', true)
                ->whereBetween('created_at', [$day, $next])
                ->count();
            $xp = (int) ContentProgress::where('user_id', $student->user_id)
                ->whereBetween('created_at', [$day, $next])
                ->sum('xp_awarded');
            $level = 0;
            if ($completed >= 1) $level = 1;
            if ($completed >= 2) $level = 2;
            if ($completed >= 4) $level = 3;
            return [
                'label' => $day->format('d.m'),
                'completed' => $completed,
                'xp' => $xp,
                'level' => $level,
            ];
        })->values();

        $timeStat = StudentTimeStat::where('student_id', $student->id)->first();
        $systemSeconds = (int) ($timeStat?->total_seconds ?? 0);

        return view('student-portal.dashboard', [
            'student' => $student,
            'courses' => $courses,
            'assignments' => $gameAssignments,
            'courseHomeworks' => $courseHomeworks,
            'totalAssignments' => $totalAssignments,
            'xp' => $xp,
            'avg' => round((float) Grade::where('student_id', $student->id)->avg('score'), 1),
            'completedAssignments' => $completedAssignments,
            'pendingAssignments' => $pendingAssignments,
            'completedGameApps' => $completedGameApps,
            'pendingGameApps' => $pendingGameApps,
            'pendingCourses' => $pendingCourses,
            'overallProgress' => $overallProgress,
            'schoolRank' => $schoolRank,
            'gradeRank' => $gradeRank,
            'topGrade' => $topGrade,
            'xpTrend' => $xpTrend,
            'taskTrend' => $taskTrend,
            'dailyGoalTarget' => $dailyGoalTarget,
            'weeklyGoalTarget' => $weeklyGoalTarget,
            'todayCompleted' => $todayCompleted,
            'weekCompleted' => $weekCompleted,
            'dailyGoalPct' => $dailyGoalPct,
            'weeklyGoalPct' => $weeklyGoalPct,
            'weekRemaining' => $weekRemaining,
            'xpToGoal' => $xpToGoal,
            'xpWeekEarned' => $xpWeekEarned,
            'heatmapDays' => $heatmapDays,
            'systemSeconds' => $systemSeconds,
            'currentAvatarPath' => $student->currentAvatar?->image_path,
        ]);
    }

    public function avatars()
    {
        $student = $this->getStudent();
        $xp = $this->xp($student);
        $this->syncStudentBadges($student, $xp);
        $spent = (int) ($student->avatar_xp_spent ?? 0);
        $availableXp = max(0, $xp - $spent);
        $avatars = Avatar::where('is_active', true)->orderBy('required_xp')->get();
        $owned = $student->avatars()->pluck('avatars.id')->all();

        return view('student-portal.avatars', compact('student', 'avatars', 'xp', 'spent', 'availableXp', 'owned'));
    }

    public function badges()
    {
        $student = $this->getStudent();
        $xp = $this->xp($student);
        [$items, $earnedCount] = $this->syncStudentBadges($student, $xp);

        return view('student-portal.badges', compact('student', 'items', 'earnedCount'));
    }

    public function buyAvatar(Avatar $avatar)
    {
        $student = $this->getStudent();
        if (! $avatar->is_active) {
            return back()->withErrors(['avatar' => 'Bu avatar kullanima acik degil.']);
        }

        $alreadyOwned = $student->avatars()->where('avatars.id', $avatar->id)->exists();
        if ($alreadyOwned) {
            return redirect()->route('student.portal.avatars')->with('ok', 'Bu avatar zaten sizde var.');
        }

        $totalXp = $this->xp($student);
        $cost = max(0, (int) $avatar->required_xp);
        $spent = (int) ($student->avatar_xp_spent ?? 0);
        $availableXp = max(0, $totalXp - $spent);
        if ($availableXp < $cost) {
            return back()->withErrors(['avatar' => 'Yetersiz XP. Bu avatar icin gereken XP: ' . $cost]);
        }

        DB::transaction(function () use ($student, $avatar, $cost) {
            $student->avatars()->syncWithoutDetaching([$avatar->id => ['unlocked_at' => now()]]);
            $student->avatar_xp_spent = (int) ($student->avatar_xp_spent ?? 0) + $cost;
            $student->current_avatar_id = $avatar->id;
            $student->save();
        });

        return redirect()->route('student.portal.avatars')->with('ok', 'Avatar satin alindi ve aktif edildi.');
    }

    public function equipAvatar(Avatar $avatar)
    {
        $student = $this->getStudent();
        $owned = $student->avatars()->where('avatars.id', $avatar->id)->exists();
        if (! $owned) {
            return back()->withErrors(['avatar' => 'Bu avatari once satin almalisiniz.']);
        }
        $student->current_avatar_id = $avatar->id;
        $student->save();

        return redirect()->route('student.portal.avatars')->with('ok', 'Avatar aktif edildi.');
    }

    public function courses()
    {
        $student = $this->getStudent();
        $courses = $this->studentCourses($student)->paginate(20);
        $courseProgress = ContentProgress::where('user_id', $student->user_id)
            ->where('content_id', 'like', 'course-%')
            ->get()
            ->keyBy('content_id');

        return view('student-portal.courses', compact('student', 'courses', 'courseProgress'));
    }

    public function courseShow(Course $course)
    {
        $student = $this->getStudent();
        abort_if(! $this->canStudentAccessCourse($student, $course), 403);
        $courseProgress = ContentProgress::where('user_id', $student->user_id)
            ->where('content_id', 'course-' . $course->id)
            ->first();
        if ($courseProgress?->completed) {
            return redirect()->route('student.portal.courses')->with('ok', 'Bu dersi tamamladiniz. Tekrar acilamaz.');
        }

        return view('student-portal.course-show', compact('student', 'course', 'courseProgress'));
    }

    public function completeCourse(Course $course)
    {
        $student = $this->getStudent();
        abort_if(! $this->canStudentAccessCourse($student, $course), 403);
        $validated = request()->validate([
            'earned_xp' => ['nullable', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);
        $existing = ContentProgress::where('content_id', 'course-' . $course->id)
            ->where('user_id', $student->user_id)
            ->first();
        if ($existing?->completed) {
            return redirect()->route('student.portal.courses')->with('ok', 'Bu ders zaten tamamlandi.');
        }

        $slides = (array) data_get($course->lesson_payload, 'slides', []);
        $slideXp = collect($slides)->sum(fn ($s) => max(0, (int) data_get($s, 'xp', 0)));
        $earnedXp = isset($validated['earned_xp']) ? max(0, (int) $validated['earned_xp']) : max(0, (int) $slideXp);
        $durationSeconds = max(0, (int) ($validated['duration_seconds'] ?? 0));

        ContentProgress::updateOrCreate(
            ['content_id' => 'course-' . $course->id, 'user_id' => $student->user_id],
            [
                'completed' => true,
                'xp_awarded' => $earnedXp,
                'payload' => [
                    'source' => 'course_slide',
                    'course_name' => $course->name,
                    'duration_seconds' => $durationSeconds,
                    'slide_count' => count($slides),
                ],
            ]
        );

        return redirect()->route('student.portal.dashboard')->with('ok', 'Ders tamamlandi. Kazanilan XP: ' . $earnedXp);
    }

    public function assignments()
    {
        $student = $this->getStudent();
        $assignments = $this->studentAssignments($student)->paginate(20, ['*'], 'game_page');
        $courseHomeworks = $this->studentCourseHomeworks($student)->paginate(20, ['*'], 'course_page');
        $progress = StudentHomeworkProgress::where('student_id', $student->id)->get()->keyBy('course_homework_id');
        $gameProgress = StudentGameAssignmentProgress::where('student_id', $student->id)->get()->keyBy('game_assignment_id');

        return view('student-portal.assignments', compact('student', 'assignments', 'courseHomeworks', 'progress', 'gameProgress'));
    }

    public function friends()
    {
        $student = $this->getStudent();

        $classmates = Student::with(['user', 'currentAvatar'])
            ->where('school_class_id', $student->school_class_id)
            ->where('id', '!=', $student->id)
            ->get();

        $gradeXpByStudentId = Grade::query()
            ->selectRaw('student_id, ROUND(SUM(score)) as total_score')
            ->whereIn('student_id', $classmates->pluck('id'))
            ->groupBy('student_id')
            ->pluck('total_score', 'student_id');

        $contentXpByUserId = ContentProgress::query()
            ->selectRaw('user_id, SUM(xp_awarded) as total_xp')
            ->whereIn('user_id', $classmates->pluck('user_id'))
            ->groupBy('user_id')
            ->pluck('total_xp', 'user_id');

        $friends = $classmates
            ->map(function (Student $classmate) use ($gradeXpByStudentId, $contentXpByUserId) {
                $fullName = trim((string) ($classmate->user?->name ?? ''));
                $nameParts = preg_split('/\s+/', $fullName, 2) ?: [];
                $firstName = trim((string) ($nameParts[0] ?? ''));
                $lastName = trim((string) ($nameParts[1] ?? ''));

                return [
                    'first_name' => $firstName !== '' ? $firstName : '-',
                    'last_name' => $lastName !== '' ? $lastName : '-',
                    'avatar_path' => $classmate->currentAvatar?->image_path,
                    'xp' => max(
                        0,
                        (int) ($gradeXpByStudentId[$classmate->id] ?? 0)
                        + (int) ($contentXpByUserId[$classmate->user_id] ?? 0)
                    ),
                ];
            })
            ->sortByDesc('xp')
            ->values();

        return view('student-portal.friends', compact('student', 'friends'));
    }

    public function classBoard()
    {
        $student = $this->getStudent();
        $messages = $this->classBoardMessages();

        $posts = ClassBoardPost::with(['student.user', 'student.currentAvatar'])
            ->where('school_class_id', $student->school_class_id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(function (ClassBoardPost $post) {
                $fullName = trim((string) ($post->student?->user?->name ?? ''));
                $parts = preg_split('/\s+/', $fullName, 2) ?: [];

                return [
                    'avatar_path' => $post->student?->currentAvatar?->image_path,
                    'first_name' => trim((string) ($parts[0] ?? '-')) ?: '-',
                    'last_name' => trim((string) ($parts[1] ?? '-')) ?: '-',
                    'message' => (string) $post->message,
                    'shared_at' => optional($post->created_at)->timezone('Europe/Istanbul')->format('d.m.Y H:i'),
                ];
            })
            ->values();

        return view('student-portal.class-board', compact('student', 'messages', 'posts'));
    }

    public function storeClassBoardPost(Request $request)
    {
        $student = $this->getStudent();
        $messages = $this->classBoardMessages();

        $validated = $request->validate([
            'message_key' => ['required', 'string', Rule::in(array_keys($messages))],
        ]);

        $messageKey = (string) $validated['message_key'];

        ClassBoardPost::create([
            'school_class_id' => $student->school_class_id,
            'student_id' => $student->id,
            'message_key' => $messageKey,
            'message' => $messages[$messageKey],
        ]);

        return redirect()->route('student.portal.class-board')->with('ok', 'Mesaj sinif panosunda paylasildi.');
    }

    public function progress()
    {
        $student = $this->getStudent();
        $rows = ContentProgress::where('user_id', $student->user_id)->latest()->paginate(30);
        $xp = $this->xp($student);
        $avg = round((float) Grade::where('student_id', $student->id)->avg('score'), 1);
        $contentLabels = $this->resolveContentLabels($rows->getCollection()->pluck('content_id')->all());

        return view('student-portal.progress', compact('student', 'rows', 'xp', 'avg', 'contentLabels'));
    }

    public function progressReport()
    {
        $student = $this->getStudent();
        $report = $this->reportService->build($student);

        return view('reports.student-progress-report', [
            'student' => $student,
            'report' => $report,
            'viewer' => 'student',
        ]);
    }

    private function getStudent(): Student
    {
        return Student::with(['user', 'schoolClass', 'currentAvatar', 'badges'])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    private function studentCourses(Student $student)
    {
        return Course::with(['teacher.user', 'schoolClass'])
            ->where(function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id)
                    ->orWhereExists(function ($sq) use ($student) {
                        $sq->select(DB::raw(1))
                            ->from('course_homeworks')
                            ->whereColumn('course_homeworks.course_id', 'courses.id')
                            ->where('course_homeworks.school_class_id', $student->school_class_id)
                            ->where('course_homeworks.assignment_type', 'lesson')
                            ->whereNull('course_homeworks.deleted_at');
                    });
            })
            ->orderBy('name');
    }

    private function canStudentAccessCourse(Student $student, Course $course): bool
    {
        if ((int) $course->school_class_id === (int) $student->school_class_id) {
            return true;
        }

        return CourseHomework::query()
            ->where('course_id', $course->id)
            ->where('school_class_id', $student->school_class_id)
            ->where('assignment_type', 'lesson')
            ->exists();
    }

    private function studentAssignments(Student $student)
    {
        $completedIds = StudentGameAssignmentProgress::query()
            ->where('student_id', $student->id)
            ->pluck('game_assignment_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return GameAssignment::withTrashed()
            ->with('levels')
            ->where(function ($q) use ($student, $completedIds) {
                $q->whereHas('classes', fn ($c) => $c->where('school_classes.id', $student->school_class_id));
                if (!empty($completedIds)) {
                    $q->orWhereIn('id', $completedIds);
                }
            })
            ->latest();
    }

    private function xp(Student $student): int
    {
        $gradeXp = (int) round((float) Grade::where('student_id', $student->id)->sum('score'));
        $contentXp = (int) ContentProgress::where('user_id', $student->user_id)->sum('xp_awarded');

        return max(0, $gradeXp + $contentXp);
    }

    private function classBoardMessages(): array
    {
        return [
            'm01' => 'Bugun kod yazmaya hazirim, hedefim bir adim daha ileri gitmek.',
            'm02' => 'Robotik projede sabirli olursam mutlaka sonuca ulasirim.',
            'm03' => 'Kucuk hatalar beni durdurmaz, her biri bana yeni bir sey ogretir.',
            'm04' => 'Takim olarak calisinca kodlar daha guclu olur.',
            'm05' => 'Her gun biraz pratik, yazilimda buyuk fark yaratir.',
            'm06' => 'Algoritma dusunmeyi gelistirdikce problemler kolaylasir.',
            'm07' => 'Denemekten korkmadan yeni cozumler bulabilirim.',
            'm08' => 'Bugun bir arkadasima kodda yardim edecegim.',
            'm09' => 'Hedefim temiz kod yazmak ve duzenli ilerlemek.',
            'm10' => 'Robotumu daha akilli hale getirmek icin calisiyorum.',
            'm11' => 'Yazilim ogrenmek sabir ister, ben hazirim.',
            'm12' => 'Basari, vazgecmeden tekrar denemekle gelir.',
            'm13' => 'Sinif olarak birbirimizi motive ederek daha hizli gelisiriz.',
            'm14' => 'Bugun ogrendigim her satir kod gelecegime yatirimdir.',
            'm15' => 'Kodlama ve robotikte her gun yeni bir kesif var.',
        ];
    }

        private function badgeDefinitions(): array
    {
        return [
            ['name' => 'Ilk Adim', 'icon' => '🚀', 'description' => 'En az 1 gorevi tamamla.', 'metric' => 'completed_total', 'target' => 1],
            ['name' => 'Odev Ustasi', 'icon' => '📘', 'description' => 'Toplam 5 odev tamamla.', 'metric' => 'completed_total', 'target' => 5],
            ['name' => 'Oyun Avcisi', 'icon' => '🎮', 'description' => '5 oyun/uygulama odevi tamamla.', 'metric' => 'completed_games', 'target' => 5],
            ['name' => 'Ders Kesifi', 'icon' => '📚', 'description' => '3 ders/slayt icerigi bitir.', 'metric' => 'completed_slides', 'target' => 3],
            ['name' => 'XP 100', 'icon' => '⭐', 'description' => '100 XP seviyesine ulas.', 'metric' => 'xp', 'target' => 100],
            ['name' => 'XP 300', 'icon' => '💎', 'description' => '300 XP seviyesine ulas.', 'metric' => 'xp', 'target' => 300],
            ['name' => 'Maratoncu', 'icon' => '⏱️', 'description' => 'Sistemde 120 dakika gecir.', 'metric' => 'minutes', 'target' => 120],
            ['name' => 'Sinif Birincisi', 'icon' => '🥇', 'description' => 'Sinifinda XP siralamasinda 1. ol.', 'metric' => 'class_rank', 'target' => 1],
            ['name' => 'Okul Birincisi', 'icon' => '🏆', 'description' => 'Okul genelinde XP siralamasinda 1. ol.', 'metric' => 'school_rank', 'target' => 1],
            ['name' => 'Efsane Tamamlayici', 'icon' => '👑', 'description' => 'Toplam 15 gorev tamamla.', 'metric' => 'completed_total', 'target' => 15],
            ['name' => 'Gorev Serisi 10', 'icon' => '🔥', 'description' => 'Toplam 10 gorev tamamla.', 'metric' => 'completed_total', 'target' => 10],
            ['name' => 'Gorev Serisi 25', 'icon' => '🏅', 'description' => 'Toplam 25 gorev tamamla.', 'metric' => 'completed_total', 'target' => 25],
            ['name' => 'Ders Ustasi', 'icon' => '🧠', 'description' => '10 ders/slayt icerigi bitir.', 'metric' => 'completed_slides', 'target' => 10],
            ['name' => 'Ders Efsanesi', 'icon' => '🎓', 'description' => '20 ders/slayt icerigi bitir.', 'metric' => 'completed_slides', 'target' => 20],
            ['name' => 'Oyun Uzmani', 'icon' => '🕹️', 'description' => '10 oyun/uygulama odevi tamamla.', 'metric' => 'completed_games', 'target' => 10],
            ['name' => 'Oyun Sampiyonu', 'icon' => '🎯', 'description' => '20 oyun/uygulama odevi tamamla.', 'metric' => 'completed_games', 'target' => 20],
            ['name' => 'XP 500', 'icon' => '🌟', 'description' => '500 XP seviyesine ulas.', 'metric' => 'xp', 'target' => 500],
            ['name' => 'XP 1000', 'icon' => '🚀', 'description' => '1000 XP seviyesine ulas.', 'metric' => 'xp', 'target' => 1000],
            ['name' => 'Disiplinli Calisma', 'icon' => '🗂️', 'description' => 'Sistemde 300 dakika gecir.', 'metric' => 'minutes', 'target' => 300],
            ['name' => 'Panel Ustasi', 'icon' => '📈', 'description' => 'Sistemde 600 dakika gecir.', 'metric' => 'minutes', 'target' => 600],
            ['name' => 'Istikrar Madalyasi', 'icon' => '🥈', 'description' => 'Toplam 40 gorev tamamla.', 'metric' => 'completed_total', 'target' => 40],
            ['name' => 'Tamamlama Zirvesi', 'icon' => '🏔️', 'description' => 'Toplam 60 gorev tamamla.', 'metric' => 'completed_total', 'target' => 60],
        ];
    }
    private function syncStudentBadges(Student $student, int $xp): array
    {
        $courseHomeworks = $this->studentCourseHomeworks($student)->get();
        $gameAssignments = $this->studentAssignments($student)->get();
        $gameHomeworkIds = $courseHomeworks
            ->filter(fn ($h) => in_array((string) $h->assignment_type, ['game', 'application'], true))
            ->pluck('id');

        $completedCourseHomework = StudentHomeworkProgress::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();
        $completedGameAssignments = StudentGameAssignmentProgress::where('student_id', $student->id)
            ->whereIn('game_assignment_id', $gameAssignments->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();
        $completedGameHomework = StudentHomeworkProgress::where('student_id', $student->id)
            ->whereIn('course_homework_id', $gameHomeworkIds)
            ->whereNotNull('completed_at')
            ->count();

        $completedSlides = ContentProgress::where('user_id', $student->user_id)
            ->where('content_id', 'like', 'course-%')
            ->where('completed', true)
            ->count();

        $timeStat = StudentTimeStat::where('student_id', $student->id)->first();
        $minutes = (int) floor(((int) ($timeStat?->total_seconds ?? 0)) / 60);

        $allStudents = Student::with('schoolClass')->get();
        $xpMap = [];
        foreach ($allStudents as $s) {
            $xpMap[$s->id] = $this->xp($s);
        }
        $schoolRankPos = collect($xpMap)->sortDesc()->keys()->search($student->id);
        $schoolRank = $schoolRankPos === false ? 999 : ($schoolRankPos + 1);
        $gradePeers = $allStudents->filter(fn ($s) => $s->schoolClass?->name === $student->schoolClass?->name);
        $gradeRankPos = $gradePeers->mapWithKeys(fn ($s) => [$s->id => $xpMap[$s->id] ?? 0])->sortDesc()->keys()->search($student->id);
        $classRank = $gradeRankPos === false ? 999 : ($gradeRankPos + 1);

        $metrics = [
            'completed_total' => $completedCourseHomework + $completedGameAssignments + $completedSlides,
            'completed_games' => $completedGameAssignments + $completedGameHomework,
            'completed_slides' => $completedSlides,
            'xp' => $xp,
            'minutes' => $minutes,
            'school_rank' => $schoolRank === 1 ? 1 : 0,
            'class_rank' => $classRank === 1 ? 1 : 0,
        ];

        $items = [];
        $earnedBadgeIds = [];
        foreach ($this->badgeDefinitions() as $def) {
            $current = (int) ($metrics[$def['metric']] ?? 0);
            $target = (int) $def['target'];
            $earned = $current >= $target;

            $badge = Badge::updateOrCreate(
                ['name' => $def['name']],
                [
                    'icon' => $def['icon'],
                    'description' => $def['description'],
                    'xp_threshold' => 0,
                ]
            );

            if ($earned) {
                $earnedBadgeIds[$badge->id] = ['awarded_at' => now()];
            }

            $items[] = [
                'id' => $badge->id,
                'name' => $def['name'],
                'icon' => $def['icon'],
                'description' => $def['description'],
                'current' => min($current, $target),
                'target' => $target,
                'earned' => $earned,
            ];
        }

        if (! empty($earnedBadgeIds)) {
            $student->badges()->syncWithoutDetaching($earnedBadgeIds);
        }

        $earnedCount = collect($items)->where('earned', true)->count();
        return [$items, $earnedCount];
    }

    private function studentCourseHomeworks(Student $student)
    {
        $completedIds = StudentHomeworkProgress::query()
            ->where('student_id', $student->id)
            ->pluck('course_homework_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return CourseHomework::withTrashed()
            ->with(['course', 'schoolClass'])
            ->where(function ($q) use ($student, $completedIds) {
                $q->where('school_class_id', $student->school_class_id);
                if (!empty($completedIds)) {
                    $q->orWhereIn('id', $completedIds);
                }
            })
            ->latest();
    }

    private function resolveContentLabels(array $contentIds): array
    {
        $labels = [];
        $courseIds = [];
        $homeworkIds = [];
        $gameAssignmentIds = [];
        foreach ($contentIds as $cid) {
            $cid = (string) $cid;
            if (preg_match('/^course-(\d+)$/', $cid, $m)) {
                $courseIds[] = (int) $m[1];
            } elseif (preg_match('/^homework-(\d+)$/', $cid, $m)) {
                $homeworkIds[] = (int) $m[1];
            } elseif (preg_match('/^game-assignment-(\d+)$/', $cid, $m)) {
                $gameAssignmentIds[] = (int) $m[1];
            }
        }

        $courses = Course::whereIn('id', array_unique($courseIds))->get()->keyBy('id');
        $homeworks = CourseHomework::withTrashed()->with('course')->whereIn('id', array_unique($homeworkIds))->get()->keyBy('id');
        $gameAssignments = GameAssignment::withTrashed()->whereIn('id', array_unique($gameAssignmentIds))->get()->keyBy('id');

        foreach ($contentIds as $cid) {
            $cid = (string) $cid;
            if (preg_match('/^course-(\d+)$/', $cid, $m)) {
                $item = $courses->get((int) $m[1]);
                $labels[$cid] = $item ? ('Ders: ' . $item->name) : $cid;
                continue;
            }
            if (preg_match('/^homework-(\d+)$/', $cid, $m)) {
                $item = $homeworks->get((int) $m[1]);
                $labels[$cid] = $item ? ('Ders Ã–devi: ' . $item->title) : $cid;
                continue;
            }
            if (preg_match('/^game-assignment-(\d+)$/', $cid, $m)) {
                $item = $gameAssignments->get((int) $m[1]);
                $labels[$cid] = $item ? ('Oyun/Uygulama: ' . $item->game_name . ' - ' . $item->title) : $cid;
                continue;
            }
            $labels[$cid] = $cid;
        }

        return $labels;
    }

    public function openHomework(CourseHomework $homework)
    {
        $student = $this->getStudent();
        abort_if($homework->school_class_id !== $student->school_class_id, 403);
        $existing = StudentHomeworkProgress::where('course_homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();
        if ($existing?->completed_at) {
            return redirect()->route('student.portal.assignments')->with('ok', 'Bu odev tamamlandi. Tekrar acilamaz.');
        }

        $progress = StudentHomeworkProgress::firstOrCreate(
            ['course_homework_id' => $homework->id, 'student_id' => $student->id],
            ['started_at' => now(), 'xp_awarded' => 0]
        );
        if (! $progress->started_at) {
            $progress->started_at = now();
            $progress->save();
        }

        $gameUrl = null;
        $gameSlug = null;
        if (in_array($homework->assignment_type, ['game', 'application'], true) && $homework->target_slug) {
            $games = ActivityController::games();
            if (isset($games[$homework->target_slug])) {
                $from = (int) ($homework->level_from ?? 1);
                $to = (int) ($homework->level_to ?? ($homework->level_from ?? 1));
                request()->session()->put('runner_grant', [
                    'slug' => $homework->target_slug,
                    'from' => $from,
                    'to' => $to,
                    'homework_id' => $homework->id,
                    'expires_at' => now()->addHours(3)->timestamp,
                ]);
                $gameSlug = $homework->target_slug;
                $query = http_build_query([
                    'from' => $from,
                    'to' => $to,
                    'levelStart' => $from,
                    'levelEnd' => $to,
                ]);
                $gameUrl = url($games[$homework->target_slug]['url']) . '?' . $query;
            }
        }

        return view('student-portal.homework-play', compact('homework', 'student', 'gameUrl', 'gameSlug'));
    }

    public function completeHomework(CourseHomework $homework)
    {
        $student = $this->getStudent();
        abort_if($homework->school_class_id !== $student->school_class_id, 403);
        $validated = request()->validate([
            'reached_level' => ['nullable', 'integer', 'min:1'],
            'earned_xp' => ['nullable', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'completed_level_ids' => ['nullable', 'string'],
            'exit_to_panel' => ['nullable', 'integer'],
        ]);
        $reachedLevel = $validated['reached_level'] ?? null;
        if ($homework->level_from && $homework->level_to) {
            if ($reachedLevel === null || $reachedLevel < $homework->level_from || $reachedLevel > $homework->level_to) {
                return back()->withErrors(['reached_level' => 'Sadece ogretmenin verdigi level araliginda tamamlama yapabilirsiniz.']);
            }
            if ((int) $reachedLevel !== (int) $homework->level_to) {
                return back()->withErrors(['reached_level' => 'Odevi tamamlamak icin verilen level araligini bitirmeniz gerekir.']);
            }
        }

        $xpAward = 20;
        if (isset($validated['earned_xp'])) {
            $xpAward = max(0, (int) $validated['earned_xp']);
        } elseif ($homework->level_points && $reachedLevel !== null) {
            $xpAward = 0;
            for ($lvl = (int) $homework->level_from; $lvl <= (int) $reachedLevel; $lvl++) {
                $xpAward += (int) ($homework->level_points[(string) $lvl] ?? 0);
            }
        } elseif ($homework->level_from && $homework->level_to && $homework->level_to >= $homework->level_from) {
            $xpAward = ($homework->level_to - $homework->level_from + 1) * 10;
        }

        $progress = StudentHomeworkProgress::firstOrCreate(
            ['course_homework_id' => $homework->id, 'student_id' => $student->id],
            ['started_at' => now()]
        );
        $durationSeconds = (int) ($validated['duration_seconds'] ?? 0);
        if ($durationSeconds <= 0 && $progress->started_at) {
            $durationSeconds = max(0, $progress->started_at->diffInSeconds(now()));
        }
        $completedLevelIds = [];
        if (! empty($validated['completed_level_ids'])) {
            $completedLevelIds = collect(explode(',', (string) $validated['completed_level_ids']))
                ->map(fn ($v) => (int) trim($v))
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
        }
        $progress->completed_at = now();
        $progress->xp_awarded = max($progress->xp_awarded, $xpAward);
        $progress->level_from = $homework->level_from ? (int) $homework->level_from : null;
        $progress->level_to = $homework->level_to ? (int) $homework->level_to : null;
        $progress->reached_level = $reachedLevel ? (int) $reachedLevel : null;
        $progress->completion_seconds = max((int) $progress->completion_seconds, $durationSeconds);
        $progress->completion_payload = [
            'homework_title' => $homework->title,
            'target_slug' => $homework->target_slug,
            'completed_level_ids' => $completedLevelIds,
        ];
        $progress->save();

        ContentProgress::updateOrCreate(
            ['content_id' => 'homework-' . $homework->id, 'user_id' => $student->user_id],
            ['completed' => true, 'xp_awarded' => $progress->xp_awarded, 'payload' => ['source' => 'course_homework']]
        );
        request()->session()->forget('runner_grant');

        if ((int) ($validated['exit_to_panel'] ?? 0) === 1) {
            return redirect()->route('student.portal.dashboard')->with('ok', 'Odev kaydedildi. Kazanilan XP: '.$progress->xp_awarded);
        }

        return redirect()->route('student.portal.homework.success', $homework)->with('earned_xp', $progress->xp_awarded);
    }

    public function homeworkSuccess(CourseHomework $homework)
    {
        $student = $this->getStudent();
        abort_if($homework->school_class_id !== $student->school_class_id, 403);
        $progress = StudentHomeworkProgress::where('course_homework_id', $homework->id)->where('student_id', $student->id)->first();

        return view('student-portal.homework-success', [
            'homework' => $homework,
            'student' => $student,
            'earnedXp' => session('earned_xp', $progress?->xp_awarded ?? 0),
        ]);
    }

    public function openGameAssignment(GameAssignment $assignment)
    {
        $student = $this->getStudent();
        $belongs = $assignment->classes()->where('school_classes.id', $student->school_class_id)->exists();
        abort_if(! $belongs, 403);
        $existing = StudentGameAssignmentProgress::where('game_assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();
        if ($existing?->completed_at) {
            return redirect()->route('student.portal.assignments')->with('ok', 'Bu odev tamamlandi. Tekrar acilamaz.');
        }

        $progress = StudentGameAssignmentProgress::firstOrCreate(
            ['game_assignment_id' => $assignment->id, 'student_id' => $student->id],
            ['started_at' => now(), 'xp_awarded' => 0]
        );
        if (! $progress->started_at) {
            $progress->started_at = now();
            $progress->save();
        }

        $from = (int) ($assignment->level_from ?? 1);
        $to = (int) ($assignment->level_to ?? ($assignment->level_from ?? 1));
        request()->session()->put('runner_grant', [
            'slug' => $assignment->game_slug,
            'from' => $from,
            'to' => $to,
            'homework_id' => 'game-assignment-' . $assignment->id,
            'expires_at' => now()->addHours(3)->timestamp,
        ]);

        $query = http_build_query([
            'from' => $from,
            'to' => $to,
            'levelStart' => $from,
            'levelEnd' => $to,
        ]);

        $gameUrl = url('/' . $assignment->game_slug) . '?' . $query;

        return view('student-portal.game-assignment-play', compact('assignment', 'student', 'gameUrl'));
    }

    public function completeGameAssignment(GameAssignment $assignment)
    {
        $student = $this->getStudent();
        $belongs = $assignment->classes()->where('school_classes.id', $student->school_class_id)->exists();
        abort_if(! $belongs, 403);

        $validated = request()->validate([
            'reached_level' => ['nullable', 'integer', 'min:1'],
            'earned_xp' => ['nullable', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'completed_level_ids' => ['nullable', 'string'],
        ]);

        $from = (int) ($assignment->level_from ?? 1);
        $to = (int) ($assignment->level_to ?? $from);
        $reachedLevel = (int) ($validated['reached_level'] ?? $to);
        if ($reachedLevel < $from || $reachedLevel > $to || $reachedLevel !== $to) {
            return back()->withErrors(['reached_level' => 'Odevi tamamlamak icin verilen level araligini bitirmeniz gerekir.']);
        }

        $xpAward = 0;
        if (isset($validated['earned_xp'])) {
            $xpAward = max(0, (int) $validated['earned_xp']);
        } else {
            $pointsByLevel = $assignment->levels->keyBy('level');
            for ($lvl = $from; $lvl <= $to; $lvl++) {
                $xpAward += (int) ($pointsByLevel->get($lvl)?->points ?? 10);
            }
        }

        $progress = StudentGameAssignmentProgress::firstOrCreate(
            ['game_assignment_id' => $assignment->id, 'student_id' => $student->id],
            ['started_at' => now()]
        );
        $durationSeconds = (int) ($validated['duration_seconds'] ?? 0);
        if ($durationSeconds <= 0 && $progress->started_at) {
            $durationSeconds = max(0, $progress->started_at->diffInSeconds(now()));
        }
        $completedLevelIds = [];
        if (! empty($validated['completed_level_ids'])) {
            $completedLevelIds = collect(explode(',', (string) $validated['completed_level_ids']))
                ->map(fn ($v) => (int) trim($v))
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
        }
        $progress->completed_at = now();
        $progress->xp_awarded = max((int) $progress->xp_awarded, $xpAward);
        $progress->level_from = $from;
        $progress->level_to = $to;
        $progress->reached_level = $reachedLevel;
        $progress->completion_seconds = max((int) $progress->completion_seconds, $durationSeconds);
        $progress->completion_payload = [
            'assignment_title' => $assignment->title,
            'game_slug' => $assignment->game_slug,
            'completed_level_ids' => $completedLevelIds,
        ];
        $progress->save();

        ContentProgress::updateOrCreate(
            ['content_id' => 'game-assignment-' . $assignment->id, 'user_id' => $student->user_id],
            [
                'completed' => true,
                'xp_awarded' => $progress->xp_awarded,
                'payload' => [
                    'source' => 'game_assignment',
                    'assignment_title' => $assignment->title,
                    'level_from' => $from,
                    'level_to' => $to,
                    'duration_seconds' => $progress->completion_seconds,
                ],
            ]
        );
        request()->session()->forget('runner_grant');

        return redirect()->route('student.portal.dashboard')->with('ok', 'Odev kaydedildi. Kazanilan XP: '.$progress->xp_awarded);
    }

    public function pingTime(Request $request)
    {
        $student = $this->getStudent();
        $totalSeconds = DB::transaction(function () use ($student) {
            $stat = StudentTimeStat::where('student_id', $student->id)->lockForUpdate()->first();
            if (! $stat) {
                $stat = StudentTimeStat::create([
                    'student_id' => $student->id,
                    'total_seconds' => 0,
                    'last_seen_at' => now(),
                ]);

                return (int) $stat->total_seconds;
            }

            $now = now();
            $delta = 0;
            if ($stat->last_seen_at) {
                $delta = max(0, $stat->last_seen_at->diffInSeconds($now));
                // Tek pingte sapma olmasin diye artis kontrollu.
                $delta = min($delta, 120);
            }

            $stat->total_seconds = (int) $stat->total_seconds + $delta;
            $stat->last_seen_at = $now;
            $stat->save();

            return (int) $stat->total_seconds;
        });

        return response()->json(['ok' => true, 'total_seconds' => $totalSeconds]);
    }
}

