<?php

namespace App\Http\Controllers;

use App\Models\QuestionBankItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionBankController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request)
    {
        $subjects = $this->visibleQuery($request)->whereNotNull('subject')->where('subject', '!=', '')
            ->distinct()->orderBy('subject')->pluck('subject');

        return view('exams.bank', [
            'subjects' => $subjects,
            'isAdmin' => $request->user()->hasRole('admin'),
        ]);
    }

    /** Filtreli, sayfalı arama (editördeki havuz paneli ve havuz sayfası ortak kullanır). */
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = $this->visibleQuery($request)->with('owner:id,name');

        if ($request->query('scope') === 'mine') {
            $q->where('owner_id', $user->id);
        }
        if (($s = trim((string) $request->query('subject', ''))) !== '') {
            $q->where('subject', 'like', '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s).'%');
        }
        if (($g = (int) $request->query('grade', 0)) > 0) {
            $q->where('grade_level', $g);
        }
        if (in_array($d = (string) $request->query('difficulty', ''), ['kolay', 'orta', 'zor'], true)) {
            $q->where('difficulty', $d);
        }
        if (in_array($t = (string) $request->query('type', ''), ExamController::QUESTION_TYPES, true)) {
            $q->where('type', $t);
        }
        if (($term = trim((string) $request->query('q', ''))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
            $q->where(fn ($w) => $w->where('body', 'like', $like)->orWhere('unit', 'like', $like)->orWhere('tags', 'like', $like));
        }

        $page = $q->orderByDesc('id')->paginate(self::PER_PAGE);

        return response()->json([
            'data' => $page->getCollection()->map(fn (QuestionBankItem $i) => $this->present($i, $user->id))->values(),
            'has_more' => $page->hasMorePages(),
            'total' => $page->total(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $item = QuestionBankItem::query()->create($data + ['owner_id' => $request->user()->id]);

        return response()->json(['ok' => true, 'item' => $this->present($item->load('owner:id,name'), $request->user()->id)], 201);
    }

    public function update(Request $request, QuestionBankItem $item): JsonResponse
    {
        $this->authorizeOwner($request, $item);
        $item->fill($this->validated($request))->save();

        return response()->json(['ok' => true, 'item' => $this->present($item->load('owner:id,name'), $request->user()->id)]);
    }

    public function destroy(Request $request, QuestionBankItem $item): JsonResponse
    {
        $this->authorizeOwner($request, $item);
        $item->delete(); // sınavlardaki kopyalar kalır (bank_item_id boşalır)

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        $d = $request->validate([
            'subject' => ['nullable', 'string', 'max:120'],
            'grade_level' => ['nullable', 'integer', 'between:1,12'],
            'unit' => ['nullable', 'string', 'max:160'],
            'difficulty' => ['required', Rule::in(['kolay', 'orta', 'zor'])],
            'tags' => ['nullable', 'string', 'max:255'],
            'is_shared' => ['nullable', 'boolean'],
            'type' => ['required', Rule::in(ExamController::QUESTION_TYPES)],
            'body' => ['required', 'string', 'max:6000'],
            'options' => ['nullable', 'array', 'max:10'],
            'answer' => ['nullable', 'string', 'max:2000'],
        ]);
        $d['options'] = ExamController::cleanOptions($d['options'] ?? []);
        $d['is_shared'] = (bool) ($d['is_shared'] ?? false);

        return $d;
    }

    /** Admin hepsini; öğretmen kendi + paylaşılan soruları görür. */
    private function visibleQuery(Request $request)
    {
        $user = $request->user();

        return QuestionBankItem::query()->when(
            ! $user->hasRole('admin'),
            fn ($q) => $q->where(fn ($w) => $w->where('owner_id', $user->id)->orWhere('is_shared', true))
        );
    }

    private function authorizeOwner(Request $request, QuestionBankItem $item): void
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin') || $item->owner_id === $user->id, 403);
    }

    private function present(QuestionBankItem $i, int $userId): array
    {
        return [
            'id' => $i->id,
            'type' => $i->type,
            'body' => $i->body,
            'options' => $i->options ?? [],
            'answer' => $i->answer ?? '',
            'subject' => $i->subject ?? '',
            'grade_level' => $i->grade_level,
            'unit' => $i->unit ?? '',
            'difficulty' => $i->difficulty,
            'tags' => $i->tags ?? '',
            'is_shared' => (bool) $i->is_shared,
            'owner' => $i->owner?->name ?? '',
            'mine' => $i->owner_id === $userId,
        ];
    }
}
