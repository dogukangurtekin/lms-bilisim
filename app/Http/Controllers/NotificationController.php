<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationLogRead;
use App\Models\NotificationPreference;
use App\Models\PushDeviceStatus;
use App\Models\PushSubscription;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class NotificationController extends Controller
{
    public function __construct(private PushNotificationService $pushService)
    {
    }

    public function index()
    {
        $user = auth()->user();
        abort_unless($user?->hasRole('admin', 'teacher'), 403);
        $isAdmin = $user?->hasRole('admin') === true;
        $teacherClassIds = [];
        if (! $isAdmin && $user) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();
            $teacherClassIds = $teacher
                ? $teacher->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all()
                : [];
        }

        $types = (array) config('notification-preferences.types', []);
        $prefs = NotificationPreference::query()
            ->where('user_id', auth()->id())
            ->whereIn('type', array_keys($types))
            ->pluck('enabled', 'type')
            ->all();

        $preferences = [];
        foreach ($types as $key => $label) {
            $preferences[] = [
                'type' => $key,
                'label' => $label,
                'enabled' => array_key_exists($key, $prefs) ? (bool) $prefs[$key] : true,
            ];
        }

        $recentLogs = NotificationLog::query()
            ->with('user:id,name')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', (int) $user->id))
            ->latest('id')
            ->limit(30)
            ->get();

        $schoolClasses = SchoolClass::query()
            ->with([
                'teacher.user:id,name',
                'students.user:id,name',
            ])
            ->when(! $isAdmin, fn ($q) => $q->whereIn('id', $teacherClassIds))
            ->orderBy('name')
            ->orderBy('section')
            ->get(['id', 'name', 'section', 'teacher_id']);

        $classStudentMap = $schoolClasses
            ->mapWithKeys(function ($class) {
                return [
                    (string) $class->id => $class->students
                        ->filter(fn ($student) => $student->user_id)
                        ->map(fn ($student) => [
                            'id' => $student->id,
                            'name' => $student->user?->name ?? ('Ogrenci #' . $student->id),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        return view('notifications.index', [
            'preferences' => $preferences,
            'recentLogs' => $recentLogs,
            'types' => $types,
            'schoolClasses' => $schoolClasses,
            'classStudentMap' => $classStudentMap,
            'teachers' => Teacher::query()
                ->with('user:id,name')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->get(['id', 'user_id']),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function publicKey(): JsonResponse
    {
        $publicKey = trim((string) config('webpush.vapid.public_key', ''));

        if ($publicKey === '') {
            return response()->json([
                'ok' => false,
                'message' => 'WEBPUSH_VAPID_PUBLIC_KEY missing',
                'public_key' => '',
            ], 503);
        }

        return response()->json([
            'ok' => true,
            'public_key' => $publicKey,
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:1500'],
            'keys.auth' => ['required', 'string', 'max:500'],
            'encoding' => ['nullable', 'string', 'max:32'],
        ]);

        PushSubscription::query()->updateOrCreate(
            ['endpoint' => (string) $data['endpoint']],
            [
                'user_id' => auth()->id(),
                'content_encoding' => (string) ($data['encoding'] ?? 'aes128gcm'),
                'public_key' => (string) $data['keys']['p256dh'],
                'auth_token' => (string) $data['keys']['auth'],
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
        ]);

        PushSubscription::query()
            ->where('endpoint', (string) $data['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function syncDeviceStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['nullable', 'url', 'max:2000'],
            'permission' => ['required', 'in:default,granted,denied'],
            'platform' => ['nullable', 'string', 'max:80'],
            'is_pwa' => ['nullable', 'boolean'],
        ]);

        $endpoint = (string) ($data['endpoint'] ?? '');
        if ($endpoint !== '') {
            PushDeviceStatus::query()->updateOrCreate(
                ['endpoint' => $endpoint],
                [
                    'user_id' => auth()->id(),
                    'permission' => (string) $data['permission'],
                    'platform' => (string) ($data['platform'] ?? ''),
                    'is_pwa' => (bool) ($data['is_pwa'] ?? false),
                    'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                    'last_seen_at' => now(),
                ]
            );
        } else {
            PushDeviceStatus::query()->updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'platform' => (string) ($data['platform'] ?? ''),
                    'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                ],
                [
                    'permission' => (string) $data['permission'],
                    'is_pwa' => (bool) ($data['is_pwa'] ?? false),
                    'last_seen_at' => now(),
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $types = array_keys((array) config('notification-preferences.types', []));
        $data = $request->validate([
            'preferences' => ['required', 'array'],
        ]);

        foreach ($data['preferences'] as $type => $enabled) {
            if (!in_array($type, $types, true)) {
                continue;
            }
            NotificationPreference::query()->updateOrCreate(
                ['user_id' => auth()->id(), 'type' => (string) $type],
                ['enabled' => (bool) $enabled]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function markRead(NotificationLog $log): JsonResponse
    {
        if ((int) $log->user_id !== (int) auth()->id()) {
            return response()->json(['ok' => false, 'message' => 'Yetkisiz islem.'], 403);
        }

        NotificationLogRead::query()->updateOrCreate(
            ['notification_log_id' => $log->id, 'user_id' => auth()->id()],
            ['read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function myLogs(Request $request): JsonResponse
    {
        $userId = (int) auth()->id();
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'message' => 'Yetkisiz islem.'], 401);
        }

        $limit = max(1, min(50, (int) $request->query('limit', 20)));
        $logs = NotificationLog::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'type', 'title', 'body', 'url', 'status', 'created_at']);

        $readIds = NotificationLogRead::query()
            ->where('user_id', $userId)
            ->whereIn('notification_log_id', $logs->pluck('id')->all())
            ->pluck('notification_log_id')
            ->map(fn ($x) => (int) $x)
            ->all();
        $readSet = array_flip($readIds);

        return response()->json([
            'ok' => true,
            'items' => $logs->map(fn (NotificationLog $log) => [
                'id' => (int) $log->id,
                'type' => (string) $log->type,
                'title' => (string) $log->title,
                'body' => (string) $log->body,
                'url' => (string) ($log->url ?? ''),
                'status' => (string) ($log->status ?? ''),
                'created_at' => optional($log->created_at)?->toIso8601String(),
                'read' => isset($readSet[(int) $log->id]),
            ])->values()->all(),
        ]);
    }

    public function destroyMyLog(NotificationLog $log): JsonResponse
    {
        $userId = (int) auth()->id();
        if ($userId <= 0 || (int) $log->user_id !== $userId) {
            return response()->json(['ok' => false, 'message' => 'Yetkisiz islem.'], 403);
        }

        try {
            DB::transaction(function () use ($log, $userId): void {
                NotificationLogRead::query()
                    ->where('user_id', $userId)
                    ->where('notification_log_id', $log->id)
                    ->delete();
                $log->delete();
            });

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Bildirim silinemedi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAllMyLogs(): JsonResponse
    {
        $userId = (int) auth()->id();
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'message' => 'Yetkisiz islem.'], 401);
        }

        try {
            DB::transaction(function () use ($userId): void {
                $myLogIds = NotificationLog::query()
                    ->where('user_id', $userId)
                    ->pluck('id')
                    ->all();

                if ($myLogIds !== []) {
                    NotificationLogRead::query()
                        ->where('user_id', $userId)
                        ->whereIn('notification_log_id', $myLogIds)
                        ->delete();
                }

                NotificationLog::query()
                    ->where('user_id', $userId)
                    ->delete();
            });

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Bildirimler silinemedi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user?->hasRole('admin', 'teacher'), 403);
        $isAdmin = $user?->hasRole('admin') === true;

        $data = $request->validate([
            'type' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:4000'],
            'url' => ['nullable', 'string', 'max:500'],
            'target' => ['required', $isAdmin ? 'in:all,students,teachers,class,class_student,teacher' : 'in:students,class,class_student'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
        ]);

        try {
            $target = (string) $data['target'];
            if ($target === 'all') {
                $result = $this->pushService->sendToAll((string) $data['type'], (string) $data['title'], (string) $data['body'], $data['url'] ?? null, [
                    'trigger' => 'admin_send',
                    'by' => auth()->id(),
                ]);
            } else {
                if (! $isAdmin && in_array($target, ['class', 'class_student'], true) && ! $this->teacherHasClassAccess((int) ($data['class_id'] ?? 0), (int) $user->id)) {
                    return response()->json(['ok' => false, 'message' => 'Bu sinif icin yetkiniz yok.'], 403);
                }

                $userIds = match ($target) {
                    'students' => Student::query()
                        ->whereNotNull('user_id')
                        ->pluck('user_id')
                        ->map(fn ($x) => (int) $x)
                        ->all(),
                    'teachers' => User::query()
                        ->whereHas('role', fn ($q) => $q->whereIn('slug', ['teacher', 'admin']))
                        ->pluck('id')
                        ->map(fn ($x) => (int) $x)
                        ->all(),
                    'class' => $this->collectClassUserIds((int) ($data['class_id'] ?? 0)),
                    'class_student' => $this->collectSingleStudentUserId((int) ($data['class_id'] ?? 0), (int) ($data['student_id'] ?? 0)),
                    'teacher' => $this->collectSingleTeacherUserId((int) ($data['teacher_id'] ?? 0)),
                    default => [],
                };

                if ($userIds === []) {
                    return response()->json(['ok' => false, 'message' => 'Secilen hedef icin aktif kullanici bulunamadi.'], 422);
                }

                $result = $this->pushService->sendToUsers($userIds, (string) $data['type'], (string) $data['title'], (string) $data['body'], $data['url'] ?? null, [
                    'trigger' => 'admin_send',
                    'by' => auth()->id(),
                    'target' => $target,
                    'class_id' => (int) ($data['class_id'] ?? 0),
                    'student_id' => (int) ($data['student_id'] ?? 0),
                    'teacher_id' => (int) ($data['teacher_id'] ?? 0),
                ]);
            }

            return response()->json(['ok' => true, 'result' => $result]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Bildirim gonderimi basarisiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resend(NotificationLog $log): JsonResponse
    {
        abort_unless(auth()->user()?->hasRole('admin', 'teacher'), 403);
        if (auth()->user()?->hasRole('admin') !== true && (int) $log->user_id !== (int) auth()->id()) {
            return response()->json(['ok' => false, 'message' => 'Bu log icin yetkiniz yok.'], 403);
        }
        if (!$log->user_id) {
            return response()->json(['ok' => false, 'message' => 'Kullanici hedefi yok.'], 422);
        }
        try {
            $result = $this->pushService->sendToUsers([(int) $log->user_id], (string) $log->type, (string) $log->title, (string) $log->body, $log->url, [
                'trigger' => 'resend',
                'source_log_id' => $log->id,
                'by' => auth()->id(),
            ]);
            return response()->json(['ok' => true, 'result' => $result]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Tekrar gonderim basarisiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyLog(Request $request, NotificationLog $log)
    {
        abort_unless(auth()->user()?->hasRole('admin', 'teacher'), 403);
        if (auth()->user()?->hasRole('admin') !== true && (int) $log->user_id !== (int) auth()->id()) {
            abort(403);
        }
        try {
            DB::transaction(function () use ($log): void {
                NotificationLogRead::query()
                    ->where('notification_log_id', $log->id)
                    ->delete();
                $log->delete();
            });
            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }
            return redirect()->route('notifications.index')->with('ok', 'Log silindi.');
        } catch (Throwable $e) {
            report($e);
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Log silinemedi: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('notifications.index')->withErrors(['log' => 'Log silinemedi: ' . $e->getMessage()]);
        }
    }

    public function destroyAllLogs(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('admin', 'teacher'), 403);
        try {
            $isAdmin = auth()->user()?->hasRole('admin') === true;
            $userId = (int) auth()->id();
            DB::transaction(function () use ($isAdmin, $userId): void {
                if ($isAdmin) {
                    NotificationLogRead::query()->delete();
                    NotificationLog::query()->delete();
                    return;
                }

                $myLogIds = NotificationLog::query()->where('user_id', $userId)->pluck('id')->all();
                if ($myLogIds !== []) {
                    NotificationLogRead::query()->whereIn('notification_log_id', $myLogIds)->delete();
                    NotificationLog::query()->whereIn('id', $myLogIds)->delete();
                }
            });
            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }
            return redirect()->route('notifications.index')->with('ok', 'Tum loglar silindi.');
        } catch (Throwable $e) {
            report($e);
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tum loglar silinemedi: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('notifications.index')->withErrors(['log' => 'Tum loglar silinemedi: ' . $e->getMessage()]);
        }
    }

    private function collectClassUserIds(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $class = SchoolClass::query()->find($classId);
        if (!$class) {
            return [];
        }

        $ids = Student::query()
            ->where('school_class_id', $classId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($x) => (int) $x)
            ->all();

        if ($class->teacher_id) {
            $teacherUserId = Teacher::query()->whereKey($class->teacher_id)->value('user_id');
            if ($teacherUserId) {
                $ids[] = (int) $teacherUserId;
            }
        }

        return array_values(array_unique($ids));
    }

    private function collectSingleStudentUserId(int $classId, int $studentId): array
    {
        if ($classId <= 0 || $studentId <= 0) {
            return [];
        }

        $userId = Student::query()
            ->where('id', $studentId)
            ->where('school_class_id', $classId)
            ->value('user_id');

        return $userId ? [(int) $userId] : [];
    }

    private function collectSingleTeacherUserId(int $teacherId): array
    {
        if ($teacherId <= 0) {
            return [];
        }
        $userId = Teacher::query()->whereKey($teacherId)->value('user_id');
        return $userId ? [(int) $userId] : [];
    }

    private function teacherHasClassAccess(int $classId, int $userId): bool
    {
        if ($classId <= 0 || $userId <= 0) {
            return false;
        }

        return Teacher::query()
            ->where('user_id', $userId)
            ->whereHas('classes', fn ($q) => $q->whereKey($classId))
            ->exists();
    }
}
