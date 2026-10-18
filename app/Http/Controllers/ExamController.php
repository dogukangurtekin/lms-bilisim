<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $exams = Exam::query()
            ->with('owner:id,name')
            ->withCount('questions')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('updated_at')
            ->get();

        return view('exams.index', [
            'exams' => $exams,
            'isAdmin' => $isAdmin,
            'templates' => Exam::TEMPLATES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'subject' => ['nullable', 'string', 'max:120'],
            'grade_level' => ['nullable', 'integer', 'between:1,12'],
            'term' => ['nullable', 'string', 'max:80'],
            'exam_date' => ['nullable', 'date'],
            'duration_min' => ['nullable', 'integer', 'between:1,600'],
            'school_name' => ['nullable', 'string', 'max:200'],
            'template' => ['required', Rule::in(array_keys(Exam::TEMPLATES))],
            'columns' => ['required', 'integer', 'in:1,2'],
        ]);

        $exam = Exam::query()->create($data + ['user_id' => $request->user()->id, 'status' => 'draft']);

        return redirect()->route('exams.edit', $exam)->with('ok', '"'.$exam->title.'" sınavı oluşturuldu. Soruları ekleyebilirsiniz.');
    }

    public const QUESTION_TYPES = ['multiple_choice', 'true_false', 'fill_blank', 'open_ended', 'matching'];

    public const DEFAULT_SETTINGS = [
        'font_size' => 11,
        'font_family' => 'serif',
        'margin_mm' => 15,
        'show_points' => true,
        'student_box' => true,
        'page_numbers' => true,
        'instructions' => '',
    ];

    public function edit(Request $request, Exam $exam)
    {
        $this->authorizeOwner($request, $exam);

        $questions = $exam->questions()->get()->map(fn (ExamQuestion $q) => [
            'type' => $q->type,
            'body' => $q->body,
            'options' => $q->options ?? [],
            'answer' => $q->answer ?? '',
            'points' => $q->points,
            'section_title' => $q->section_title ?? '',
            'answer_lines' => (int) $q->answer_lines,
            'bank_item_id' => $q->bank_item_id,
        ])->values();

        return view('exams.edit', [
            'exam' => $exam,
            'templates' => Exam::TEMPLATES,
            'payload' => [
                'exam' => [
                    'title' => $exam->title,
                    'subject' => $exam->subject ?? '',
                    'grade_level' => $exam->grade_level,
                    'class_label' => $exam->class_label ?? '',
                    'term' => $exam->term ?? '',
                    'exam_date' => optional($exam->exam_date)->format('Y-m-d') ?? '',
                    'duration_min' => $exam->duration_min,
                    'school_name' => $exam->school_name ?? '',
                    'template' => $exam->template,
                    'columns' => (int) $exam->columns,
                    'status' => $exam->status,
                ],
                'settings' => array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings),
                'questions' => $questions,
            ],
        ]);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);

        $data = $request->validate([
            'exam.title' => ['required', 'string', 'max:200'],
            'exam.subject' => ['nullable', 'string', 'max:120'],
            'exam.grade_level' => ['nullable', 'integer', 'between:1,12'],
            'exam.class_label' => ['nullable', 'string', 'max:60'],
            'exam.term' => ['nullable', 'string', 'max:80'],
            'exam.exam_date' => ['nullable', 'date'],
            'exam.duration_min' => ['nullable', 'integer', 'between:1,600'],
            'exam.school_name' => ['nullable', 'string', 'max:200'],
            'exam.template' => ['required', Rule::in(array_keys(Exam::TEMPLATES))],
            'exam.columns' => ['required', 'integer', 'in:1,2'],
            'exam.status' => ['required', 'in:draft,final'],
            'settings' => ['required', 'array'],
            'settings.font_size' => ['required', 'integer', 'between:9,16'],
            'settings.font_family' => ['required', 'in:serif,sans'],
            'settings.margin_mm' => ['required', 'integer', 'between:8,30'],
            'settings.show_points' => ['required', 'boolean'],
            'settings.student_box' => ['required', 'boolean'],
            'settings.page_numbers' => ['required', 'boolean'],
            'settings.instructions' => ['nullable', 'string', 'max:1000'],
            'questions' => ['present', 'array', 'max:300'],
            'questions.*.type' => ['required', Rule::in(self::QUESTION_TYPES)],
            'questions.*.body' => ['nullable', 'string', 'max:6000'],
            'questions.*.options' => ['nullable', 'array', 'max:10'],
            'questions.*.answer' => ['nullable', 'string', 'max:2000'],
            'questions.*.points' => ['nullable', 'numeric', 'between:0,1000'],
            'questions.*.section_title' => ['nullable', 'string', 'max:200'],
            'questions.*.answer_lines' => ['nullable', 'integer', 'between:0,20'],
            'questions.*.bank_item_id' => ['nullable', 'integer', 'exists:question_bank_items,id'],
        ]);

        DB::transaction(function () use ($exam, $data): void {
            $meta = $data['exam'];
            $exam->fill([
                'title' => $meta['title'],
                'subject' => $meta['subject'] ?? null,
                'grade_level' => $meta['grade_level'] ?? null,
                'class_label' => $meta['class_label'] ?? null,
                'term' => $meta['term'] ?? null,
                'exam_date' => $meta['exam_date'] ?? null,
                'duration_min' => $meta['duration_min'] ?? null,
                'school_name' => $meta['school_name'] ?? null,
                'template' => $meta['template'],
                'columns' => (int) $meta['columns'],
                'status' => $meta['status'],
                'settings' => $data['settings'],
            ])->save();

            $exam->questions()->delete();
            foreach ($data['questions'] as $i => $q) {
                $options = self::cleanOptions((array) ($q['options'] ?? []));
                ExamQuestion::query()->create([
                    'exam_id' => $exam->id,
                    'sort_order' => $i + 1,
                    'type' => $q['type'],
                    'body' => (string) ($q['body'] ?? ''),
                    'options' => $options,
                    'answer' => $q['answer'] ?? null,
                    'points' => $q['points'] ?? 0,
                    'section_title' => $q['section_title'] ?? null,
                    'answer_lines' => (int) ($q['answer_lines'] ?? 0),
                    'bank_item_id' => $q['bank_item_id'] ?? null,
                ]);
            }
        });

        return response()->json(['ok' => true, 'saved_at' => now()->format('H:i:s')]);
    }

    /** Seçenek / eşleştirme çiftlerini temizler (kırpma + uzunluk sınırı). */
    public static function cleanOptions(array $options): array
    {
        $clean = static fn ($v): string => mb_substr(trim((string) $v), 0, 500);
        $out = [];
        foreach ($options as $opt) {
            $out[] = is_array($opt)
                ? ['l' => $clean($opt['l'] ?? ''), 'r' => $clean($opt['r'] ?? '')]
                : $clean($opt);
        }

        return $out;
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeOwner($request, $exam);
        $exam->delete();

        return redirect()->route('exams.index')->with('ok', 'Sınav silindi.');
    }

    private function authorizeOwner(Request $request, Exam $exam): void
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin') || $exam->user_id === $user->id, 403);
    }
}
