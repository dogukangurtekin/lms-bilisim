<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
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

        return redirect()->route('exams.index')->with('ok', '"'.$exam->title.'" sınavı oluşturuldu. Soru editörü bir sonraki adımda eklenecek.');
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
