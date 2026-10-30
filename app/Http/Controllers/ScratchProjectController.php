<?php

namespace App\Http\Controllers;

use App\Models\ScratchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Blok Kodlama Stüdyosu projeleri: kullanıcı başına en fazla LIMIT proje, sunucuda saklanır.
 * Sahibi okur/yazar/siler; admin ve ilgili sınıfın öğretmeni yalnızca okur (inceleme).
 */
class ScratchProjectController extends Controller
{
    public const LIMIT = 10;
    private const MAX_BYTES = 1048576;

    private function summary(ScratchProject $p): array
    {
        return ['id' => $p->id, 'name' => $p->name, 'client_ts' => (int) $p->client_ts, 'updated_at' => $p->updated_at?->toIso8601String(), 'size' => (int) $p->size];
    }

    private function listFor(int $userId): array
    {
        return ScratchProject::query()->where('user_id', $userId)->orderBy('updated_at')->get(['id', 'name', 'client_ts', 'updated_at', 'size'])
            ->map(fn (ScratchProject $p) => $this->summary($p))->all();
    }

    private function canView(ScratchProject $p): bool
    {
        $user = auth()->user();
        if ((int) $p->user_id === (int) $user?->id || $user?->hasRole('admin')) {
            return true;
        }
        if ($user?->hasRole('teacher') && $user->teacher) {
            $classId = (int) optional($p->user?->student)->school_class_id;

            return $classId > 0 && $user->teacher->classes()->where('school_classes.id', $classId)->exists();
        }

        return false;
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'data' => ['required', 'string'],
            'client_ts' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['name'] = trim(preg_replace('/\s+/u', ' ', $data['name']));
        abort_if($data['name'] === '', 422, 'Proje adı boş olamaz.');
        abort_if(strlen($data['data']) > self::MAX_BYTES, 413, 'Proje çok büyük (en fazla 1 MB).');
        $decoded = json_decode($data['data'], true);
        abort_unless(is_array($decoded) && isset($decoded['sprites']) && is_array($decoded['sprites']), 422, 'Geçersiz proje verisi.');
        $data['client_ts'] = (int) ($data['client_ts'] ?? 0);

        return $data;
    }

    public function index(): JsonResponse
    {
        return response()->json(['limit' => self::LIMIT, 'projects' => $this->listFor((int) auth()->id())]);
    }

    public function show(ScratchProject $project): JsonResponse
    {
        abort_unless($this->canView($project), 403);

        return response()->json($this->summary($project) + [
            'owner' => $project->user?->name,
            'data' => json_decode($project->data, true),
        ]);
    }

    /** Aynı adlı proje varsa günceller; yoksa yeni oluşturur (limit dolmuşsa 422 + silinebilecek projeler). */
    public function store(Request $request): JsonResponse
    {
        $d = $this->validated($request);
        $userId = (int) auth()->id();
        $existing = ScratchProject::query()->where('user_id', $userId)->where('name', $d['name'])->first();
        if (! $existing && ScratchProject::query()->where('user_id', $userId)->count() >= self::LIMIT) {
            return response()->json(['error' => 'limit', 'limit' => self::LIMIT, 'projects' => $this->listFor($userId)], 422);
        }
        $payload = ['data' => $d['data'], 'size' => strlen($d['data']), 'client_ts' => $d['client_ts']];
        $project = $existing ?? new ScratchProject(['user_id' => $userId, 'name' => $d['name']]);
        $project->fill($payload)->save();

        return response()->json($this->summary($project->refresh()));
    }

    public function update(Request $request, ScratchProject $project): JsonResponse
    {
        abort_unless((int) $project->user_id === (int) auth()->id(), 403);
        $d = $this->validated($request);
        $clash = ScratchProject::query()->where('user_id', $project->user_id)->where('name', $d['name'])->where('id', '!=', $project->id)->exists();
        if ($clash) {
            return response()->json(['error' => 'name_taken'], 422);
        }
        $project->fill(['name' => $d['name'], 'data' => $d['data'], 'size' => strlen($d['data']), 'client_ts' => $d['client_ts']])->save();

        return response()->json($this->summary($project->refresh()));
    }

    public function destroy(ScratchProject $project): JsonResponse
    {
        abort_unless((int) $project->user_id === (int) auth()->id(), 403);
        $project->delete();

        return response()->json(['ok' => true, 'projects' => $this->listFor((int) auth()->id())]);
    }

    /** Öğretmen/admin: öğrencilerin kayıtlı projeleri. */
    public function staff()
    {
        $user = auth()->user();
        $classIds = null;
        if (! $user?->hasRole('admin')) {
            abort_unless($user?->teacher, 403);
            $classIds = $user->teacher->classes()->pluck('school_classes.id')->all();
        }
        $projects = ScratchProject::query()
            ->with('user.student.schoolClass')
            ->whereHas('user.student', fn ($q) => $classIds === null ? $q : $q->whereIn('school_class_id', $classIds))
            ->latest('updated_at')
            ->paginate(50);

        return view('scratch.staff-projects', compact('projects'));
    }
}
