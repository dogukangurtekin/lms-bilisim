<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCodingActivityRequest;
use App\Http\Requests\UpdateCodingActivityRequest;
use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\DailyActivityAssignment;
use App\Models\QuestionOption;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CodingActivityManagementController extends Controller
{
    public function index()
    {
        $activities = CodingActivity::withCount('questions')->latest()->paginate(20);
        $todayAssignment = DailyActivityAssignment::with('activity')->whereDate('assignment_date', Carbon::today('Europe/Istanbul'))->first();
        $selectedId = (int) request()->integer('edit');
        $editingActivity = $selectedId > 0 ? CodingActivity::with('questions.options')->find($selectedId) : null;

        return view('coding-activities.manage', compact('activities', 'todayAssignment', 'editingActivity'));
    }

    public function store(StoreCodingActivityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $activity = CodingActivity::create([
                'created_by' => auth()->id(),
                'title' => $data['title'],
                'type' => $data['type'],
                'instruction' => $data['instruction'] ?? null,
                'lesson_pages' => array_values(array_filter($data['lesson_pages'] ?? [])),
                'base_xp' => $data['base_xp'] ?? 20,
                'is_active' => true,
                'is_random_pool' => (bool) ($data['is_random_pool'] ?? true),
            ]);

            $this->syncQuestions($activity, $data['questions'] ?? []);
        });

        return back()->with('ok', 'Etkinlik oluşturuldu.');
    }

    public function update(UpdateCodingActivityRequest $request, CodingActivity $activity): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($activity, $data): void {
            $activity->update([
                'title' => $data['title'],
                'type' => $data['type'],
                'instruction' => $data['instruction'] ?? null,
                'lesson_pages' => array_values(array_filter($data['lesson_pages'] ?? [])),
                'base_xp' => $data['base_xp'] ?? 20,
                'is_random_pool' => (bool) ($data['is_random_pool'] ?? false),
            ]);

            $activity->questions()->each(function (ActivityQuestion $question): void {
                $question->options()->delete();
                $question->delete();
            });

            $this->syncQuestions($activity, $data['questions'] ?? []);
        });

        return redirect()->route('coding.activities.manage', ['edit' => $activity->id])->with('ok', 'Etkinlik güncellendi.');
    }

    public function destroy(CodingActivity $activity): RedirectResponse
    {
        DB::transaction(function () use ($activity): void {
            DailyActivityAssignment::query()->where('coding_activity_id', $activity->id)->delete();
            $activity->questions()->each(function (ActivityQuestion $question): void {
                $question->options()->delete();
                $question->delete();
            });
            $activity->delete();
        });

        return redirect()->route('coding.activities.manage')->with('ok', 'Etkinlik silindi.');
    }

    public function assignToday(CodingActivity $activity): RedirectResponse
    {
        DailyActivityAssignment::updateOrCreate(
            ['assignment_date' => Carbon::today('Europe/Istanbul')->toDateString(), 'target_role' => 'student'],
            ['coding_activity_id' => $activity->id, 'assigned_by' => auth()->id()]
        );

        return back()->with('ok', 'Bugünün etkinliği atandı.');
    }

    private function syncQuestions(CodingActivity $activity, array $questions): void
    {
        foreach ($questions as $index => $q) {
            if (empty($q['prompt']) || empty($q['question_type'])) {
                continue;
            }

            $question = ActivityQuestion::create([
                'coding_activity_id' => $activity->id,
                'question_type' => $q['question_type'],
                'prompt' => $q['prompt'],
                'points' => (int) ($q['points'] ?? 10),
                'order_no' => $index + 1,
                'answer_key' => $q['question_type'] === 'multi_choice'
                    ? ['correct' => array_values($q['correct_options'] ?? [])]
                    : ($q['question_type'] === 'single_choice'
                        ? [
                            'correct' => array_values($q['correct_options'] ?? []),
                            'answer' => (string) (collect(array_values($q['options'] ?? []))
                                ->values()
                                ->map(fn ($label, $optIndex) => [
                                    'key' => chr(65 + $optIndex),
                                    'label' => (string) $label,
                                ])
                                ->firstWhere('key', (string) ($q['correct_options'][0] ?? ''))['label'] ?? ($q['answer'] ?? '')
                            ),
                        ]
                        : ['answer' => (string) ($q['answer'] ?? '')]),
            ]);

            if (in_array($q['question_type'], ['single_choice', 'multi_choice'], true)) {
                foreach (array_values($q['options'] ?? []) as $optIndex => $label) {
                    if (trim((string) $label) === '') {
                        continue;
                    }
                    QuestionOption::create([
                        'activity_question_id' => $question->id,
                        'option_key' => chr(65 + $optIndex),
                        'label' => $label,
                        'is_correct' => in_array(chr(65 + $optIndex), $q['correct_options'] ?? [], true),
                        'order_no' => $optIndex + 1,
                    ]);
                }
            }
        }
    }
}
