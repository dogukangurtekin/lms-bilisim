<?php

namespace App\Http\Controllers;

use App\Models\DocumentRecord;
use App\Models\GameState;
use App\Models\StudentReport;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientDataController extends Controller
{
    private const NATIVE = ['users', 'gameStates', 'studentReports'];

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['nullable', 'string', 'max:190'],
            'email' => ['nullable', 'string', 'max:190'],
            'password' => ['required', 'string', 'min:1', 'max:128'],
        ]);
        $identifier = trim((string) ($validated['identifier'] ?? $validated['email'] ?? ''));
        $password = (string) ($validated['password'] ?? '');
        if ($identifier === '') {
            throw ValidationException::withMessages(['identifier' => 'identifier or email is required']);
        }
        $user = User::query()->where('email', $identifier)->orWhere('name', $identifier)->first();
        if (! $user || ! Hash::check($password, (string) $user->password)) {
            return response()->json(['message' => 'invalid credentials'], 401);
        }
        $token = $this->issueClientToken((string) $user->id);
        return response()->json(['user' => ['uid' => (string) $user->id, 'id' => (string) $user->id, 'email' => $user->email, 'displayName' => $user->name, 'token' => $token]]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'password' => ['required', 'string', 'min:6', 'max:128'],
            'username' => ['nullable', 'string', 'min:3', 'max:60'],
        ]);
        $email = trim((string) ($validated['email'] ?? ''));
        $password = (string) ($validated['password'] ?? '');
        if ($email === '' || $password === '') {
            return response()->json(['message' => 'email and password are required'], 422);
        }
        if (User::query()->where('email', $email)->exists()) {
            return response()->json(['message' => 'email already exists'], 409);
        }
        $username = trim((string) ($validated['username'] ?? (Str::before($email, '@') ?: 'user_'.Str::lower(Str::random(6)))));
        $user = User::query()->create(['name' => $username, 'email' => $email, 'password' => Hash::make($password)]);
        UserProfile::query()->updateOrCreate(['user_id' => $user->id], ['username' => $username, 'role' => 'student', 'xp' => 0, 'meta' => []]);
        $token = $this->issueClientToken((string) $user->id);
        return response()->json(['user' => ['uid' => (string) $user->id, 'id' => (string) $user->id, 'email' => $user->email, 'displayName' => $user->name, 'token' => $token]], 201);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('client_uid', '');
        $user = User::query()->find($uid);
        $validated = $request->validate([
            'newPassword' => ['required', 'string', 'min:6', 'max:128'],
        ]);
        $newPassword = (string) ($validated['newPassword'] ?? '');
        if (! $user || $newPassword === '') {
            return response()->json(['message' => 'invalid input'], 422);
        }
        $user->password = Hash::make($newPassword);
        $user->save();
        return response()->json(['ok' => true]);
    }

    public function deleteUser(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('client_uid', '');
        if ($uid === '') return response()->json(['message' => 'unauthenticated'], 401);
        if ($this->isProtectedUser($uid)) {
            return response()->json(['message' => 'teacher/admin account is protected'], 403);
        }
        User::query()->whereKey($uid)->delete();
        UserProfile::query()->where('user_id', $uid)->delete();
        GameState::query()->where('user_id', $uid)->delete();
        StudentReport::query()->where('user_id', $uid)->delete();
        DocumentRecord::query()->where('path', "users/{$uid}")->orWhere('path', 'like', "%/{$uid}")->delete();
        Cache::forget("client_api_token:{$uid}");
        return response()->json(['ok' => true]);
    }

    public function logout(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('client_uid', '');
        if ($uid !== '') {
            Cache::forget("client_api_token:{$uid}");
        }
        return response()->json(['ok' => true]);
    }

    public function setDoc(Request $request): JsonResponse
    {
        $doc = $this->upsertDoc((string) $request->input('path', ''), (array) $request->input('data', []), (bool) $request->input('merge', false));
        return response()->json(['doc' => $doc]);
    }

    public function updateDoc(Request $request): JsonResponse
    {
        $doc = $this->upsertDoc((string) $request->input('path', ''), (array) $request->input('data', []), true);
        return response()->json(['doc' => $doc]);
    }

    public function getDoc(Request $request): JsonResponse
    {
        $doc = $this->findDoc((string) $request->query('path', ''));
        return $doc ? response()->json(['exists' => true, 'doc' => $doc]) : response()->json(['exists' => false]);
    }

    public function deleteDoc(Request $request): JsonResponse
    {
        $path = trim((string) $request->input('path', ''), '/');
        [$parent, $collection, $id] = $this->splitDocPath($path);
        if ($parent === null && in_array($collection, self::NATIVE, true)) {
            if ($collection === 'users') {
                if ($this->isProtectedUser($id)) {
                    return response()->json(['message' => 'teacher/admin account is protected'], 403);
                }
                User::query()->whereKey($id)->delete();
                UserProfile::query()->where('user_id', $id)->delete();
            } elseif ($collection === 'gameStates') {
                GameState::query()->where('user_id', $id)->delete();
            } elseif ($collection === 'studentReports') {
                StudentReport::query()->where('user_id', $id)->delete();
            }
            return response()->json(['ok' => true]);
        }
        DocumentRecord::query()->where('path', $path)->delete();
        return response()->json(['ok' => true]);
    }

    public function addDoc(Request $request): JsonResponse
    {
        $collectionPath = trim((string) $request->input('collectionPath', ''), '/');
        $id = Str::lower(Str::random(20));
        $path = "{$collectionPath}/{$id}";
        $doc = $this->upsertDoc($path, (array) $request->input('data', []), false);
        return response()->json(['doc' => $doc], 201);
    }

    public function queryDocs(Request $request): JsonResponse
    {
        $source = (array) $request->input('source', []);
        $constraints = (array) $request->input('constraints', []);
        if (count($constraints) > 20) {
            return response()->json(['message' => 'too many constraints'], 422);
        }
        $type = (string) ($source['type'] ?? 'collection');
        if ($type === 'collectionGroup') {
            $name = (string) ($source['name'] ?? '');
            if (strlen($name) > 120) {
                return response()->json(['message' => 'invalid source'], 422);
            }
            $q = DocumentRecord::query()->where('collection_name', $name);
            $this->applyDocumentRecordConstraints($q, $constraints);
            $docs = $q->get()->map(fn ($d) => $this->docPayload($d))->values()->all();
            return response()->json(['docs' => $this->applyConstraints($docs, $constraints)]);
        }

        $collectionPath = trim((string) ($source['path'] ?? ''), '/');
        if (strlen($collectionPath) > 255) {
            return response()->json(['message' => 'invalid source'], 422);
        }
        [$parent, $collection] = $this->splitCollectionPath($collectionPath);
        if ($parent === null && $collection === 'users') {
            $q = User::query()
                ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->select(['users.*', 'user_profiles.role', 'user_profiles.xp', 'user_profiles.meta', 'user_profiles.total_time_seconds', 'user_profiles.class_name', 'user_profiles.section', 'user_profiles.selected_avatar_id']);
            $this->applyNativeUserConstraints($q, $constraints);
            $rows = $q->get();
            $docs = $rows->map(function ($r) {
                $meta = [];
                if (is_array($r->meta)) $meta = $r->meta;
                elseif (is_string($r->meta) && $r->meta !== '') {
                    $decoded = json_decode($r->meta, true);
                    if (is_array($decoded)) $meta = $decoded;
                }
                $data = array_merge($meta, [
                    'username' => $r->name, 'email' => $r->email, 'role' => $r->role ?: 'student', 'xp' => (int) ($r->xp ?? 0),
                    'totalTimeSeconds' => (int) ($r->total_time_seconds ?? 0), 'class' => $r->class_name, 'section' => $r->section, 'selectedAvatarId' => $r->selected_avatar_id,
                    'createdAt' => optional($r->created_at)?->toIso8601String(), 'updatedAt' => optional($r->updated_at)?->toIso8601String(),
                ]);
                return ['id' => (string) $r->id, 'path' => "users/{$r->id}", 'parentPath' => null, 'collection' => 'users', 'data' => $data, 'createdAt' => optional($r->created_at)?->toIso8601String(), 'updatedAt' => optional($r->updated_at)?->toIso8601String()];
            })->values()->all();
            return response()->json(['docs' => $this->applyConstraints($docs, $constraints)]);
        }

        $q = DocumentRecord::query()->where('collection_name', $collection)->where('parent_path', $parent);
        $this->applyDocumentRecordConstraints($q, $constraints);
        $docs = $q->get()->map(fn ($d) => $this->docPayload($d))->values()->all();
        return response()->json(['docs' => $this->applyConstraints($docs, $constraints)]);
    }

    public function batch(Request $request): JsonResponse
    {
        $ops = (array) $request->input('ops', []);
        if (count($ops) > 500) {
            return response()->json(['message' => 'too many operations'], 422);
        }
        foreach ($ops as $op) {
            $kind = (string) ($op['kind'] ?? '');
            if ($kind === 'set') $this->upsertDoc((string) ($op['path'] ?? ''), (array) ($op['data'] ?? []), (bool) ($op['merge'] ?? false));
            if ($kind === 'update') $this->upsertDoc((string) ($op['path'] ?? ''), (array) ($op['data'] ?? []), true);
            if ($kind === 'delete') DocumentRecord::query()->where('path', trim((string) ($op['path'] ?? ''), '/'))->delete();
        }
        return response()->json(['ok' => true]);
    }

    public function callable(Request $request, string $name): JsonResponse
    {
        $data = (array) $request->input('data', []);
        if ($name === 'deleteUserByAdmin') {
            $requesterUid = (string) $request->attributes->get('client_uid', '');
            $targetUid = (string) ($data['uid'] ?? $data['userId'] ?? '');
            if ($requesterUid === '' || $targetUid === '') {
                return response()->json(['data' => ['ok' => false, 'message' => 'invalid user']], 422);
            }

            $targetProfile = UserProfile::query()->where('user_id', $targetUid)->first();
            $targetRole = Str::lower(Str::ascii(trim((string) ($targetProfile?->role ?? 'student'))));
            $isTeacherLike = in_array($targetRole, ['teacher', 'admin', 'administrator', 'ogretmen'], true);

            // Başka bir öğretmen/admin hesabını bu endpoint ile silmeye izin verme.
            if ($isTeacherLike) {
                return response()->json(['data' => ['ok' => false, 'message' => 'teacher/admin account is protected']], 403);
            }

            User::query()->whereKey($targetUid)->delete();
            UserProfile::query()->where('user_id', $targetUid)->delete();
            GameState::query()->where('user_id', $targetUid)->delete();
            StudentReport::query()->where('user_id', $targetUid)->delete();
            return response()->json(['data' => ['ok' => true]]);
        }
        if ($name === 'setUserPasswordByAdmin') {
            $uid = (string) ($data['uid'] ?? $data['userId'] ?? '');
            $user = User::query()->find($uid);
            if ($user && isset($data['newPassword'])) {
                $user->password = Hash::make((string) $data['newPassword']);
                $user->save();
            }
            return response()->json(['data' => ['ok' => true]]);
        }
        if ($name === 'studentAiAssistant') {
            return response()->json(['data' => ['reply' => 'AI assistant is not configured on Laravel API yet.']]);
        }
        if ($name === 'getMyStats') {
            $uid = (string) $request->attributes->get('client_uid', '');
            if ($uid === '') {
                return response()->json(['data' => ['ok' => false, 'message' => 'unauthenticated']], 401);
            }
            return response()->json(['data' => $this->buildMyStatsSummary($uid)]);
        }
        return response()->json(['data' => ['ok' => false, 'message' => "Unknown callable: {$name}"]], 404);
    }

    private function buildMyStatsSummary(string $uid): array
    {
        $profile = UserProfile::query()->where('user_id', $uid)->first();
        $meta = $this->normalizeMeta($profile?->meta);
        $ownerTeacherId = (string) ($meta['ownerTeacherId'] ?? $meta['createdBy'] ?? '');
        $studentClass = (string) ($profile?->class_name ?? '');
        $studentSection = (string) ($profile?->section ?? '');

        $matchesOwner = function (array $payload) use ($ownerTeacherId): bool {
            $recordUserId = (string) ($payload['userId'] ?? '');
            if ($ownerTeacherId !== '' && $recordUserId !== '' && $recordUserId !== $ownerTeacherId) return false;
            return true;
        };
        $matchesTarget = function (array $payload, bool $requireClassWhenTargetEmpty) use ($studentClass, $studentSection): bool {
            $targetClass = (string) ($payload['targetClass'] ?? '');
            $targetSection = (string) ($payload['targetSection'] ?? '');
            if ($targetClass === '') {
                return $requireClassWhenTargetEmpty ? ($studentClass !== '') : true;
            }
            if ($studentClass === '') return false;
            if ($targetSection !== '') return $targetClass === $studentClass && $targetSection === $studentSection;
            return $targetClass === $studentClass;
        };

        $activityDocs = DocumentRecord::query()
            ->where('collection_name', 'activities')
            ->get(['payload']);
        $studentTasks = $activityDocs->filter(function ($row) use ($matchesOwner, $matchesTarget) {
            $payload = (array) ($row->payload ?? []);
            if (! $matchesOwner($payload)) return false;
            return $matchesTarget($payload, true);
        });
        $totalTasks = $studentTasks->count();

        $completions = DocumentRecord::query()
            ->where('collection_name', 'completions')
            ->where('payload->userId', $uid)
            ->get(['payload']);
        $approvedManualCount = DocumentRecord::query()
            ->where('collection_name', 'bookTaskProgress')
            ->where('payload->userId', $uid)
            ->where('payload->approved', true)
            ->count();
        $completedCount = $completions->count() + $approvedManualCount;
        $totalScore = 0;
        foreach ($completions as $row) {
            $p = (array) ($row->payload ?? []);
            $correct = (float) ($p['correctAnswers'] ?? 0);
            $totalQ = (float) ($p['totalQuestions'] ?? 1);
            $totalScore += ($correct / max(1, $totalQ)) * 100;
        }
        $avgScore = $completedCount > 0 ? (int) round($totalScore / $completedCount) : 0;
        $taskRate = $totalTasks > 0 ? (int) round(($completedCount / $totalTasks) * 100) : 0;

        $contentAssignments = DocumentRecord::query()
            ->where('collection_name', 'contentAssignments')
            ->get(['payload', 'document_id']);
        $assignmentsForStudent = $contentAssignments->filter(function ($row) use ($matchesOwner, $matchesTarget) {
            $payload = (array) ($row->payload ?? []);
            if (! $matchesOwner($payload)) return false;
            return $matchesTarget($payload, false);
        });
        $contentProgressDocs = DocumentRecord::query()
            ->where('collection_name', 'contentProgress')
            ->where('payload->userId', $uid)
            ->get(['payload']);
        $contentProgressByContentId = [];
        foreach ($contentProgressDocs as $row) {
            $p = (array) ($row->payload ?? []);
            $contentId = (string) ($p['contentId'] ?? '');
            if ($contentId !== '') $contentProgressByContentId[$contentId] = $p;
        }
        $activityCompleted = 0;
        foreach ($assignmentsForStudent as $row) {
            $payload = (array) ($row->payload ?? []);
            $contentId = (string) ($payload['contentId'] ?? '');
            $progress = $contentId !== '' ? ($contentProgressByContentId[$contentId] ?? null) : null;
            if (! $progress) continue;
            $appUsage = (array) ($progress['appUsage'] ?? []);
            $done = false;
            foreach ($appUsage as $usage) {
                $u = (array) $usage;
                if ((float) ($u['percent'] ?? 0) > 0 || (float) ($u['seconds'] ?? 0) > 0) { $done = true; break; }
            }
            if ($done) $activityCompleted++;
        }
        $totalActivities = $assignmentsForStudent->count();
        $activityRate = $totalActivities > 0 ? (int) round(($activityCompleted / $totalActivities) * 100) : 0;

        $lessons = DocumentRecord::query()
            ->where('collection_name', 'lessons')
            ->get(['payload', 'document_id']);
        $lessonsForStudent = $lessons->filter(function ($row) use ($matchesOwner, $matchesTarget) {
            $payload = (array) ($row->payload ?? []);
            if (($payload['isPublished'] ?? null) === false) return false;
            if (! $matchesOwner($payload)) return false;
            return $matchesTarget($payload, false);
        });
        $lessonProgressDocs = DocumentRecord::query()
            ->where('collection_name', 'lessonProgress')
            ->where('payload->userId', $uid)
            ->get(['payload']);
        $lessonProgressByLessonId = [];
        foreach ($lessonProgressDocs as $row) {
            $p = (array) ($row->payload ?? []);
            $lessonId = (string) ($p['lessonId'] ?? '');
            if ($lessonId !== '') $lessonProgressByLessonId[$lessonId] = $p;
        }
        $lessonCompleted = 0;
        foreach ($lessonsForStudent as $row) {
            $payload = (array) ($row->payload ?? []);
            $lessonId = (string) ($row->document_id ?? '');
            $progress = $lessonId !== '' ? ($lessonProgressByLessonId[$lessonId] ?? null) : null;
            if ($progress) $lessonCompleted++;
        }
        $lessonTotal = $lessonsForStudent->count();
        $lessonRate = $lessonTotal > 0 ? (int) round(($lessonCompleted / $lessonTotal) * 100) : 0;

        $blockAssignments = DocumentRecord::query()
            ->where('collection_name', 'blockAssignments')
            ->get(['payload', 'document_id']);
        $blockAssignmentsForStudent = $blockAssignments->filter(function ($row) use ($matchesOwner, $matchesTarget) {
            $payload = (array) ($row->payload ?? []);
            if (! empty($payload['isDeleted'])) return false;
            if (! $matchesOwner($payload)) return false;
            return $matchesTarget($payload, false);
        });
        $blockProgressDocs = DocumentRecord::query()
            ->where('collection_name', 'blockAssignmentProgress')
            ->where('payload->userId', $uid)
            ->get(['payload']);
        $blockProgressByAssignmentId = [];
        foreach ($blockProgressDocs as $row) {
            $p = (array) ($row->payload ?? []);
            $assignmentId = (string) ($p['assignmentId'] ?? '');
            if ($assignmentId !== '') $blockProgressByAssignmentId[$assignmentId] = $p;
        }
        $isBlockDone = function (array $progress): bool {
            $completedLevels = max(0, (int) ($progress['completedLevels'] ?? 0));
            $totalLevels = max(0, (int) ($progress['totalLevels'] ?? 0));
            $percent = max(0, (float) ($progress['percent'] ?? 0));
            return ! empty($progress['completed']) || $percent >= 100 || ($totalLevels > 0 && $completedLevels >= $totalLevels);
        };
        $block2DTotal = 0; $block2DCompleted = 0; $block3DTotal = 0; $block3DCompleted = 0; $pythonQuizTotal = 0; $pythonQuizCompleted = 0;
        foreach ($blockAssignmentsForStudent as $row) {
            $payload = (array) ($row->payload ?? []);
            $assignmentId = (string) ($row->document_id ?? '');
            $progress = $assignmentId !== '' ? ($blockProgressByAssignmentId[$assignmentId] ?? []) : [];
            $type = (string) ($payload['assignmentType'] ?? '');
            if ($type === 'block3d') {
                $block3DTotal++;
                if ($progress && $isBlockDone($progress)) $block3DCompleted++;
            } elseif ($type === 'silentteacher') {
                $pythonQuizTotal++;
                if ($progress && $isBlockDone($progress)) $pythonQuizCompleted++;
            } elseif ($type === 'flowchart' || $type === 'lightbot') {
                // ignore from block2d stats
            } else {
                $block2DTotal++;
                if ($progress && $isBlockDone($progress)) $block2DCompleted++;
            }
        }
        $block2DRate = $block2DTotal > 0 ? (int) round(($block2DCompleted / $block2DTotal) * 100) : 0;
        $block3DRate = $block3DTotal > 0 ? (int) round(($block3DCompleted / $block3DTotal) * 100) : 0;
        $pythonQuizRate = $pythonQuizTotal > 0 ? (int) round(($pythonQuizCompleted / $pythonQuizTotal) * 100) : 0;

        $computeAssignments = DocumentRecord::query()
            ->where('collection_name', 'computeAssignments')
            ->get(['payload', 'document_id']);
        $computeAssignmentsForStudent = $computeAssignments->filter(function ($row) use ($matchesOwner, $matchesTarget) {
            $payload = (array) ($row->payload ?? []);
            if (! empty($payload['isDeleted'])) return false;
            if (! $matchesOwner($payload)) return false;
            return $matchesTarget($payload, false);
        });
        $computeProgressDocs = DocumentRecord::query()
            ->where('collection_name', 'computeAssignmentProgress')
            ->where('payload->userId', $uid)
            ->get(['payload']);
        $computeProgressByAssignmentId = [];
        foreach ($computeProgressDocs as $row) {
            $p = (array) ($row->payload ?? []);
            $assignmentId = (string) ($p['assignmentId'] ?? '');
            if ($assignmentId !== '') $computeProgressByAssignmentId[$assignmentId] = $p;
        }
        $isComputeDone = function (array $progress, array $assignment): bool {
            $percent = max(0, (float) ($progress['percent'] ?? 0));
            $completedLevels = max(0, (int) ($progress['completedLevels'] ?? 0));
            $levelStart = (int) ($assignment['levelStart'] ?? 1);
            $levelEnd = (int) ($assignment['levelEnd'] ?? $levelStart);
            $assignmentTotalLevels = max(1, $levelEnd - $levelStart + 1);
            $totalLevels = max((int) ($progress['totalLevels'] ?? 0), $assignmentTotalLevels);
            $completedIds = is_array($progress['completedLevelIds'] ?? null) ? $progress['completedLevelIds'] : [];
            $completedByIds = count($completedIds);
            $currentIndex = max(0, (int) ($progress['currentLevelIndex'] ?? 0));
            $completedCount = max($completedLevels, $completedByIds, $currentIndex);
            return ! empty($progress['completed']) || $percent >= 100 || ($totalLevels > 0 && $completedCount >= $totalLevels);
        };
        $computeTotal = 0; $computeCompleted = 0;
        foreach ($computeAssignmentsForStudent as $row) {
            $payload = (array) ($row->payload ?? []);
            $assignmentId = (string) ($row->document_id ?? '');
            $progress = $assignmentId !== '' ? ($computeProgressByAssignmentId[$assignmentId] ?? []) : [];
            $computeTotal++;
            if ($progress && $isComputeDone($progress, $payload)) $computeCompleted++;
        }
        $computeRate = $computeTotal > 0 ? (int) round(($computeCompleted / $computeTotal) * 100) : 0;

        $totalXP = (int) ($profile?->xp ?? 0);

        $schoolRank = null;
        $classRank = null;
        $baseQuery = UserProfile::query()
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('student_reports', 'student_reports.user_id', '=', 'user_profiles.user_id')
            ->where(function ($q) {
                $q->whereNull('user_profiles.role')
                    ->orWhereNotIn('user_profiles.role', ['teacher', 'admin', 'administrator', 'ogretmen']);
            })
            ->select([
                'user_profiles.user_id',
                'user_profiles.xp',
                'user_profiles.class_name',
                'user_profiles.section',
                'user_profiles.meta',
                'student_reports.total_xp',
            ]);

        $rows = $baseQuery->get();
        $rankRows = $rows->map(function ($row) {
            $primaryXp = (int) ($row->xp ?? 0);
            $fallbackXp = (int) ($row->total_xp ?? 0);
            $row->rank_xp = $primaryXp > 0 ? $primaryXp : $fallbackXp;
            return $row;
        })->values()->all();
        usort($rankRows, function ($a, $b) {
            $diff = (int) ($b->rank_xp <=> $a->rank_xp);
            if ($diff !== 0) return $diff;
            return (int) ((int) $a->user_id <=> (int) $b->user_id);
        });
        $rank = 0;
        $lastXp = null;
        foreach ($rankRows as $index => $row) {
            $xp = (int) ($row->rank_xp ?? 0);
            if ($lastXp === null || $xp !== $lastXp) {
                $rank = $index + 1;
            }
            if ((string) $row->user_id === $uid) {
                $schoolRank = $rank;
                break;
            }
            $lastXp = $xp;
        }

        $normalizedClass = Str::lower(Str::ascii(trim($studentClass)));
        $normalizedSection = Str::lower(Str::ascii(trim($studentSection)));
        if ($normalizedClass !== '') {
            $classRows = collect($rankRows)->filter(function ($row) use ($normalizedClass, $normalizedSection) {
                $rowClass = Str::lower(Str::ascii(trim((string) ($row->class_name ?? ''))));
                $rowSection = Str::lower(Str::ascii(trim((string) ($row->section ?? ''))));
                if ($rowClass !== $normalizedClass) return false;
                if ($normalizedSection !== '' && $rowSection !== $normalizedSection) return false;
                return true;
            })->values();
            $classRankIndex = null;
            $rank = 0;
            $lastXp = null;
            foreach ($classRows as $index => $row) {
                $xp = (int) ($row->rank_xp ?? 0);
                if ($lastXp === null || $xp !== $lastXp) {
                    $rank = $index + 1;
                }
                if ((string) $row->user_id === $uid) {
                    $classRankIndex = $rank;
                    break;
                }
                $lastXp = $xp;
            }
            $classRank = $classRankIndex;
        }

        return [
            'ok' => true,
            'totalTasks' => $totalTasks,
            'completedCount' => $completedCount,
            'avgScore' => $avgScore,
            'taskRate' => $taskRate,
            'totalActivities' => $totalActivities,
            'activityCompleted' => $activityCompleted,
            'activityRate' => $activityRate,
            'lessonTotal' => $lessonTotal,
            'lessonCompleted' => $lessonCompleted,
            'lessonRate' => $lessonRate,
            'block2DTotal' => $block2DTotal,
            'block2DCompleted' => $block2DCompleted,
            'block2DRate' => $block2DRate,
            'block3DTotal' => $block3DTotal,
            'block3DCompleted' => $block3DCompleted,
            'block3DRate' => $block3DRate,
            'pythonQuizTotal' => $pythonQuizTotal,
            'pythonQuizCompleted' => $pythonQuizCompleted,
            'pythonQuizRate' => $pythonQuizRate,
            'computeTotal' => $computeTotal,
            'computeCompleted' => $computeCompleted,
            'computeRate' => $computeRate,
            'totalXP' => $totalXP,
            'classRank' => $classRank,
            'schoolRank' => $schoolRank,
        ];
    }

    private function normalizeMeta(mixed $meta): array
    {
        if (is_array($meta)) return $meta;
        if (is_string($meta) && $meta !== '') {
            $decoded = json_decode($meta, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function isProtectedUser(string $uid): bool
    {
        if ($uid === '') {
            return false;
        }
        $role = (string) (UserProfile::query()->where('user_id', $uid)->value('role') ?? '');
        $normalized = Str::lower(Str::ascii(trim($role)));
        return in_array($normalized, ['teacher', 'admin', 'administrator', 'ogretmen'], true);
    }

    private function upsertDoc(string $rawPath, array $data, bool $merge): array
    {
        $path = trim($rawPath, '/');
        [$parent, $collection, $id] = $this->splitDocPath($path);
        if ($parent === null && in_array($collection, self::NATIVE, true)) {
            if ($collection === 'users') return $this->upsertNativeUser($id, $data, $merge);
            if ($collection === 'gameStates') return $this->upsertNativeGameState($id, $data, $merge);
            if ($collection === 'studentReports') return $this->upsertNativeStudentReport($id, $data, $merge);
        }

        $doc = DocumentRecord::query()->where('path', $path)->first();
        $base = (array) ($doc?->payload ?? []);
        $payload = $merge ? $this->normalizePayload($data, $base) : $this->normalizePayload($data, []);
        if (! $doc) $doc = new DocumentRecord(['path' => $path, 'parent_path' => $parent, 'collection_name' => $collection, 'document_id' => $id]);
        $doc->payload = $payload;
        $doc->save();
        return $this->docPayload($doc);
    }

    private function findDoc(string $rawPath): ?array
    {
        $path = trim($rawPath, '/');
        [$parent, $collection, $id] = $this->splitDocPath($path);
        if ($parent === null && in_array($collection, self::NATIVE, true)) {
            if ($collection === 'users') return $this->findNativeUser($id);
            if ($collection === 'gameStates') return $this->findNativeGameState($id);
            if ($collection === 'studentReports') return $this->findNativeStudentReport($id);
        }
        $doc = DocumentRecord::query()->where('path', $path)->first();
        return $doc ? $this->docPayload($doc) : null;
    }

    private function upsertNativeUser(string $id, array $data, bool $merge): array
    {
        $user = User::query()->find($id) ?: new User(['id' => (int) $id, 'email' => (string) ($data['email'] ?? "user_{$id}@local.test"), 'password' => Hash::make((string) ($data['password'] ?? Str::random(16)))]);
        $profile = UserProfile::query()->firstOrNew(['user_id' => (int) $id]);
        $base = $this->nativeUserData($user, $profile);
        $payload = $merge ? $this->normalizePayload($data, $base) : $this->normalizePayload($data, []);
        $user->name = (string) ($payload['username'] ?? $payload['name'] ?? $user->name ?? "user_{$id}");
        if (isset($payload['email'])) $user->email = (string) $payload['email'];
        if (isset($payload['password']) && (string) $payload['password'] !== '') $user->password = Hash::make((string) $payload['password']);
        $user->save();
        $profile->username = (string) ($payload['username'] ?? $user->name);
        $profile->role = (string) ($payload['role'] ?? $profile->role ?? 'student');
        $profile->xp = (int) ($payload['xp'] ?? 0);
        $profile->total_time_seconds = (int) ($payload['totalTimeSeconds'] ?? 0);
        $profile->class_name = isset($payload['class']) ? (string) $payload['class'] : $profile->class_name;
        $profile->section = isset($payload['section']) ? (string) $payload['section'] : $profile->section;
        $profile->selected_avatar_id = isset($payload['selectedAvatarId']) ? (string) $payload['selectedAvatarId'] : $profile->selected_avatar_id;
        $profile->meta = $this->extractMeta($payload, ['username', 'name', 'email', 'password', 'role', 'xp', 'totalTimeSeconds', 'class', 'section', 'selectedAvatarId', 'createdAt', 'updatedAt']);
        $profile->save();
        return $this->findNativeUser((string) $user->id);
    }

    private function findNativeUser(string $id): ?array
    {
        $user = User::query()->find($id);
        if (! $user) return null;
        $profile = UserProfile::query()->firstOrCreate(['user_id' => $user->id], ['username' => $user->name, 'role' => 'student', 'xp' => 0, 'meta' => []]);
        $data = $this->nativeUserData($user, $profile);
        return ['id' => (string) $user->id, 'path' => "users/{$user->id}", 'parentPath' => null, 'collection' => 'users', 'data' => $data, 'createdAt' => optional($user->created_at)?->toIso8601String(), 'updatedAt' => optional($user->updated_at)?->toIso8601String()];
    }

    private function upsertNativeGameState(string $id, array $data, bool $merge): array
    {
        $row = GameState::query()->firstOrNew(['user_id' => (int) $id]);
        $base = (array) ($row->payload ?? []);
        $row->payload = $merge ? $this->normalizePayload($data, $base) : $this->normalizePayload($data, []);
        $row->save();
        return $this->findNativeGameState($id);
    }

    private function findNativeGameState(string $id): ?array
    {
        $row = GameState::query()->where('user_id', $id)->first();
        if (! $row) return null;
        return ['id' => (string) $id, 'path' => "gameStates/{$id}", 'parentPath' => null, 'collection' => 'gameStates', 'data' => (array) ($row->payload ?? []), 'createdAt' => optional($row->created_at)?->toIso8601String(), 'updatedAt' => optional($row->updated_at)?->toIso8601String()];
    }

    private function upsertNativeStudentReport(string $id, array $data, bool $merge): array
    {
        $row = StudentReport::query()->firstOrNew(['user_id' => (int) $id]);
        $base = $this->nativeStudentReportData($row);
        $payload = $merge ? $this->normalizePayload($data, $base) : $this->normalizePayload($data, []);
        $row->total_xp = (int) ($payload['totalXP'] ?? $payload['xp'] ?? 0);
        $row->total_duration_ms = (int) ($payload['totalDurationMs'] ?? 0);
        $row->completion_percent = (float) ($payload['completionPercent'] ?? 0);
        $row->meta = $this->extractMeta($payload, ['totalXP', 'xp', 'totalDurationMs', 'completionPercent', 'createdAt', 'updatedAt']);
        $row->save();
        return $this->findNativeStudentReport($id);
    }

    private function findNativeStudentReport(string $id): ?array
    {
        $row = StudentReport::query()->where('user_id', $id)->first();
        if (! $row) return null;
        return ['id' => (string) $id, 'path' => "studentReports/{$id}", 'parentPath' => null, 'collection' => 'studentReports', 'data' => $this->nativeStudentReportData($row), 'createdAt' => optional($row->created_at)?->toIso8601String(), 'updatedAt' => optional($row->updated_at)?->toIso8601String()];
    }

    private function nativeUserData(User $user, UserProfile $profile): array
    {
        return array_merge((array) ($profile->meta ?? []), [
            'username' => (string) ($profile->username ?: $user->name),
            'email' => (string) $user->email,
            'role' => (string) ($profile->role ?: 'student'),
            'xp' => (int) ($profile->xp ?? 0),
            'totalTimeSeconds' => (int) ($profile->total_time_seconds ?? 0),
            'class' => $profile->class_name,
            'section' => $profile->section,
            'selectedAvatarId' => $profile->selected_avatar_id,
            'createdAt' => optional($user->created_at)?->toIso8601String(),
            'updatedAt' => optional($user->updated_at)?->toIso8601String(),
        ]);
    }

    private function nativeStudentReportData(StudentReport $row): array
    {
        return array_merge((array) ($row->meta ?? []), [
            'totalXP' => (int) ($row->total_xp ?? 0),
            'totalDurationMs' => (int) ($row->total_duration_ms ?? 0),
            'completionPercent' => (float) ($row->completion_percent ?? 0),
            'createdAt' => optional($row->created_at)?->toIso8601String(),
            'updatedAt' => optional($row->updated_at)?->toIso8601String(),
        ]);
    }

    private function normalizePayload(array $incoming, array $base): array
    {
        foreach ($incoming as $k => $v) {
            if (is_array($v) && (($v['__op'] ?? null) === 'increment')) $base[$k] = (float) ($base[$k] ?? 0) + (float) ($v['by'] ?? 0);
            elseif (is_array($v) && (($v['__op'] ?? null) === 'serverTimestamp')) $base[$k] = now()->toIso8601String();
            else $base[$k] = $v;
        }
        return $base;
    }

    private function docPayload(DocumentRecord $doc): array
    {
        return ['id' => (string) $doc->document_id, 'path' => (string) $doc->path, 'parentPath' => $doc->parent_path, 'collection' => (string) $doc->collection_name, 'data' => (array) ($doc->payload ?? []), 'createdAt' => optional($doc->created_at)?->toIso8601String(), 'updatedAt' => optional($doc->updated_at)?->toIso8601String()];
    }

    private function splitCollectionPath(string $path): array
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/')), fn ($x) => $x !== ''));
        $collection = (string) end($parts);
        $parent = count($parts) > 1 ? implode('/', array_slice($parts, 0, -1)) : null;
        return [$parent, $collection];
    }

    private function splitDocPath(string $path): array
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/')), fn ($x) => $x !== ''));
        $id = (string) end($parts);
        $collection = count($parts) > 1 ? (string) $parts[count($parts) - 2] : '';
        $parent = count($parts) > 2 ? implode('/', array_slice($parts, 0, -2)) : null;
        return [$parent, $collection, $id];
    }

    private function applyDocumentRecordConstraints(Builder $query, array $constraints): void
    {
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') !== 'where') continue;
            $field = (string) ($c['field'] ?? '');
            $op = (string) ($c['op'] ?? '==');
            $value = $c['value'] ?? null;
            if ($field === '') continue;
            $column = $this->mapDocumentRecordField($field);
            if (! $column) continue;
            $this->applyWhereConstraint($query, $column, $op, $value);
        }
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') !== 'orderBy') continue;
            $field = (string) ($c['field'] ?? '');
            if ($field === '') continue;
            $column = $this->mapDocumentRecordField($field);
            if (! $column) continue;
            $dir = strtolower((string) ($c['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($column, $dir);
        }
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') !== 'limit') continue;
            $count = (int) ($c['count'] ?? 0);
            if ($count > 0) $query->limit($count);
        }
    }

    private function applyNativeUserConstraints(Builder $query, array $constraints): void
    {
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') !== 'where') continue;
            $field = (string) ($c['field'] ?? '');
            $op = (string) ($c['op'] ?? '==');
            $value = $c['value'] ?? null;
            if ($field === '') continue;
            $column = $this->mapNativeUserField($field);
            if (! $column) continue;
            $this->applyWhereConstraint($query, $column, $op, $value);
        }
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') !== 'orderBy') continue;
            $field = (string) ($c['field'] ?? '');
            if ($field === '') continue;
            $column = $this->mapNativeUserField($field);
            if (! $column) continue;
            $dir = strtolower((string) ($c['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($column, $dir);
        }
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') !== 'limit') continue;
            $count = (int) ($c['count'] ?? 0);
            if ($count > 0) $query->limit($count);
        }
    }

    private function mapDocumentRecordField(string $field): ?string
    {
        $plain = trim($field);
        if ($plain === 'id' || $plain === 'document_id') return 'document_id';
        if ($plain === 'path') return 'path';
        if ($plain === 'parentPath' || $plain === 'parent_path') return 'parent_path';
        if ($plain === 'collection' || $plain === 'collection_name') return 'collection_name';
        if ($plain === 'createdAt' || $plain === 'created_at') return 'created_at';
        if ($plain === 'updatedAt' || $plain === 'updated_at') return 'updated_at';
        $payloadField = str_replace('.', '->', $plain);
        return "payload->{$payloadField}";
    }

    private function mapNativeUserField(string $field): ?string
    {
        $plain = trim($field);
        return match ($plain) {
            'id', 'uid' => 'users.id',
            'username', 'name' => 'users.name',
            'email' => 'users.email',
            'role' => 'user_profiles.role',
            'xp' => 'user_profiles.xp',
            'totalTimeSeconds' => 'user_profiles.total_time_seconds',
            'class', 'className' => 'user_profiles.class_name',
            'section' => 'user_profiles.section',
            'selectedAvatarId' => 'user_profiles.selected_avatar_id',
            'createdAt', 'created_at' => 'users.created_at',
            'updatedAt', 'updated_at' => 'users.updated_at',
            default => null,
        };
    }

    private function applyWhereConstraint(Builder $query, string $column, string $op, mixed $value): void
    {
        if ($op === 'in' && is_array($value)) {
            $query->whereIn($column, $value);
            return;
        }
        if ($op === '==') {
            $query->where($column, '=', $value);
            return;
        }
        if ($op === '!=') {
            $query->where($column, '!=', $value);
            return;
        }
        if (in_array($op, ['>', '>=', '<', '<='], true)) {
            $query->where($column, $op, $value);
        }
    }

    private function applyConstraints(array $docs, array $constraints): array
    {
        $limit = null;
        foreach ($constraints as $c) {
            if (($c['type'] ?? '') === 'where') {
                $f = (string) ($c['field'] ?? ''); $op = (string) ($c['op'] ?? '=='); $val = $c['value'] ?? null;
                $docs = array_values(array_filter($docs, function ($d) use ($f, $op, $val) {
                    $v = data_get($d['data'] ?? [], $f);
                    if ($op === '==') return $v == $val;
                    if ($op === 'in' && is_array($val)) return in_array($v, $val, true);
                    if ($op === '!=') return $v != $val;
                    if ($op === '>') return $v > $val;
                    if ($op === '>=') return $v >= $val;
                    if ($op === '<') return $v < $val;
                    if ($op === '<=') return $v <= $val;
                    return false;
                }));
            }
        }
        foreach (array_reverse($constraints) as $c) {
            if (($c['type'] ?? '') === 'orderBy') {
                $f = (string) ($c['field'] ?? ''); $dir = strtolower((string) ($c['direction'] ?? 'asc'));
                usort($docs, function ($a, $b) use ($f, $dir) { $cmp = (data_get($a['data'] ?? [], $f) <=> data_get($b['data'] ?? [], $f)); return $dir === 'desc' ? -$cmp : $cmp; });
            }
            if (($c['type'] ?? '') === 'limit') $limit = (int) ($c['count'] ?? 0);
        }
        return ($limit && $limit > 0) ? array_slice($docs, 0, $limit) : $docs;
    }

    private function extractMeta(array $payload, array $reserved): array
    {
        $out = [];
        foreach ($payload as $k => $v) if (! in_array((string) $k, $reserved, true)) $out[$k] = $v;
        return $out;
    }

    private function issueClientToken(string $uid): string
    {
        $plain = Str::random(80);
        $ttlMinutes = max(30, (int) env('CLIENT_API_TOKEN_TTL_MINUTES', 1440));
        $hash = hash('sha256', $plain);
        Cache::put("client_api_token:{$uid}", $hash, now()->addMinutes($ttlMinutes));
        logger()->info('client_api_token_issued', [
            'uid' => $uid,
            'hash_prefix' => substr($hash, 0, 10),
        ]);
        try {
            $profile = UserProfile::query()->firstOrNew(['user_id' => (int) $uid]);
            $meta = $this->normalizeMeta($profile->meta);
            $meta['clientApiTokenHash'] = $hash;
            $profile->meta = $meta;
            $profile->save();
        } catch (\Throwable $e) {
            logger()->warning('client_api_token_meta_write_failed', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);
        }
        return $plain;
    }
}
