<?php

namespace App\Http\Controllers;

use App\Models\CourseHomework;
use App\Models\GameAssignment;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        $courseHomeworks = CourseHomework::with(['course', 'schoolClass'])
            ->latest()
            ->paginate(20, ['*'], 'course_page');
        $gameAssignments = GameAssignment::with(['classes', 'levels'])
            ->latest()
            ->paginate(20, ['*'], 'game_page');
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('teacher-assignments.index', compact('courseHomeworks', 'gameAssignments', 'classes'));
    }

    public function storeHomework(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'details' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'all_classes' => ['nullable', 'boolean'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:10240'],
        ]);

        $attachmentPath = null;
        $attachmentOriginalName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('homeworks', 'public');
            $attachmentOriginalName = $file->getClientOriginalName();
        }

        $allClasses = filter_var($request->input('all_classes', false), FILTER_VALIDATE_BOOL);
        $targetClassIds = $allClasses
            ? SchoolClass::query()->pluck('id')->all()
            : array_values(array_unique(array_map('intval', (array) ($validated['class_ids'] ?? []))));

        if ($targetClassIds === []) {
            throw ValidationException::withMessages([
                'class_ids' => 'En az bir sınıf seçmelisiniz veya "Tüm sınıflar" seçeneğini açmalısınız.',
            ]);
        }

        foreach ($targetClassIds as $classId) {
            CourseHomework::create([
                'course_id' => null,
                'school_class_id' => (int) $classId,
                'assignment_type' => 'homework',
                'target_slug' => null,
                'title' => $validated['title'],
                'details' => $validated['details'] ?? null,
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $attachmentOriginalName,
                'due_date' => $validated['due_date'] ?? null,
                'level_from' => null,
                'level_to' => null,
                'level_points' => null,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('teacher.assignments.index')->with('ok', 'Ödev başarıyla oluşturuldu.');
    }

    public function showCourseHomework(CourseHomework $homework)
    {
        $homework->load(['course', 'schoolClass']);
        $gameUrl = null;
        $gameSlug = null;
        if (in_array((string) $homework->assignment_type, ['game', 'application'], true) && $homework->target_slug) {
            $games = ActivityController::games();
            if (isset($games[$homework->target_slug])) {
                $from = (int) ($homework->level_from ?? 1);
                $to = (int) ($homework->level_to ?? ($homework->level_from ?? 1));
                request()->session()->put('runner_grant', [
                    'slug' => $homework->target_slug,
                    'from' => $from,
                    'to' => $to,
                    'homework_id' => 'teacher-preview-homework-' . $homework->id,
                    'expires_at' => now()->addHours(3)->timestamp,
                ]);
                $gameSlug = $homework->target_slug;
                $query = http_build_query([
                    'from' => $from,
                    'to' => $to,
                    'levelStart' => $from,
                    'levelEnd' => $to,
                    'preview' => 1,
                ]);
                $gameUrl = url($games[$homework->target_slug]['url']) . '?' . $query;
            }
        }

        return view('teacher-assignments.show-course-homework', compact('homework', 'gameUrl', 'gameSlug'));
    }

    public function editCourseHomework(CourseHomework $homework)
    {
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $games = ActivityController::games();
        return view('teacher-assignments.edit-course-homework', compact('homework', 'classes', 'games'));
    }

    public function updateCourseHomework(Request $request, CourseHomework $homework)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'details' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'assignment_type' => ['required', 'in:lesson,game,application,homework'],
            'target_slug' => ['nullable', 'string', 'max:120'],
            'level_from' => ['nullable', 'integer', 'min:1'],
            'level_to' => ['nullable', 'integer', 'min:1', 'gte:level_from'],
            'level_points' => ['nullable', 'array'],
            'level_points.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:10240'],
        ]);
        $points = [];
        if (! empty($validated['level_points']) && isset($validated['level_from'], $validated['level_to'])) {
            for ($lvl = (int) $validated['level_from']; $lvl <= (int) $validated['level_to']; $lvl++) {
                $points[(string) $lvl] = (int) ($validated['level_points'][$lvl] ?? 0);
            }
        }
        $attachmentPath = $homework->attachment_path;
        $attachmentOriginalName = $homework->attachment_original_name;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('homeworks', 'public');
            $attachmentOriginalName = $file->getClientOriginalName();
        }
        $homework->update([
            'school_class_id' => (int) $validated['school_class_id'],
            'assignment_type' => $validated['assignment_type'],
            'target_slug' => $validated['target_slug'] ?? null,
            'title' => $validated['title'],
            'details' => $validated['details'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'due_date' => $validated['due_date'] ?? null,
            'level_from' => $validated['level_from'] ?? null,
            'level_to' => $validated['level_to'] ?? null,
            'level_points' => $points !== [] ? $points : null,
        ]);

        return redirect()->route('teacher.assignments.index')->with('ok', 'Ders odevi guncellendi.');
    }

    public function destroyCourseHomework(CourseHomework $homework)
    {
        $homework->delete();
        return redirect()->route('teacher.assignments.index')->with('ok', 'Ders odevi silindi. Ogrenci kayitlari korunur.');
    }

    public function showGameAssignment(GameAssignment $assignment)
    {
        $assignment->load(['classes', 'levels']);
        $from = (int) ($assignment->level_from ?? 1);
        $to = (int) ($assignment->level_to ?? ($assignment->level_from ?? 1));
        request()->session()->put('runner_grant', [
            'slug' => $assignment->game_slug,
            'from' => $from,
            'to' => $to,
            'homework_id' => 'teacher-preview-assignment-' . $assignment->id,
            'expires_at' => now()->addHours(3)->timestamp,
        ]);
        $query = http_build_query([
            'from' => $from,
            'to' => $to,
            'levelStart' => $from,
            'levelEnd' => $to,
            'preview' => 1,
        ]);
        $gameUrl = url('/' . $assignment->game_slug) . '?' . $query;

        return view('teacher-assignments.show-game-assignment', compact('assignment', 'gameUrl'));
    }

    public function editGameAssignment(GameAssignment $assignment)
    {
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        return view('teacher-assignments.edit-game-assignment', compact('assignment', 'classes'));
    }

    public function updateGameAssignment(Request $request, GameAssignment $assignment)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'due_date' => ['nullable', 'date'],
            'level_from' => ['nullable', 'integer', 'min:1', 'max:999'],
            'level_to' => ['nullable', 'integer', 'min:1', 'max:999', 'gte:level_from'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
            'points' => ['nullable', 'array'],
            'points.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);
        $assignment->update([
            'title' => $validated['title'],
            'due_date' => $validated['due_date'] ?? null,
            'level_from' => $validated['level_from'] ?? null,
            'level_to' => $validated['level_to'] ?? null,
        ]);
        $assignment->classes()->sync($validated['class_ids']);
        $assignment->levels()->delete();
        $from = $validated['level_from'] ?? null;
        $to = $validated['level_to'] ?? null;
        if ($from !== null && $to !== null) {
            for ($level = $from; $level <= $to; $level++) {
                $assignment->levels()->create([
                    'level' => $level,
                    'points' => (int) (($validated['points'] ?? [])[$level] ?? 0),
                ]);
            }
        }

        return redirect()->route('teacher.assignments.index')->with('ok', 'Oyun/uygulama odevi guncellendi.');
    }

    public function destroyGameAssignment(GameAssignment $assignment)
    {
        $assignment->delete();
        return redirect()->route('teacher.assignments.index')->with('ok', 'Oyun/uygulama odevi silindi. Ogrenci kayitlari korunur.');
    }
}
