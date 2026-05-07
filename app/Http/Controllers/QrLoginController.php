<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class QrLoginController extends Controller
{
    private function qrCache(): CacheRepository
    {
        // Keep QR login tokens in file cache to survive between HTTP requests.
        return Cache::store('file');
    }

    public function menuPage(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user?->role?->slug === 'admin';
        $teacherClassIds = [];
        if (! $isAdmin && $user) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();
            $teacherClassIds = $teacher
                ? $teacher->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all()
                : [];
        }

        $classes = SchoolClass::query()
            ->withCount('students')
            ->when(! $isAdmin, fn ($q) => $q->whereIn('id', $teacherClassIds))
            ->orderByDesc('academic_year')
            ->orderBy('name')
            ->orderBy('section')
            ->get()
            ->unique(fn ($class) => $class->name . '|' . $class->section)
            ->values();
        $selectedClassId = (int) $request->query('class_id', 0);
        $selectedClass = $classes->firstWhere('id', $selectedClassId);
        $students = $selectedClass
            ? Student::query()
                ->with('user')
                ->where('school_class_id', $selectedClassId)
                ->when(! $isAdmin, fn ($q) => $q->whereIn('school_class_id', $teacherClassIds))
                ->orderBy('student_no')
                ->get()
            : collect();

        return view('qr-login.menu', compact('classes', 'selectedClassId', 'selectedClass', 'students'));
    }

    public function scannerPage(Student $student)
    {
        $this->authorizeStudentForTeacher(auth()->user(), $student);

        $host = strtolower((string) request()->getHost());
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

        if (!$isLocalHost && !request()->isSecure()) {
            $requestUri = (string) request()->server('REQUEST_URI', '/');
            return redirect()->away('https://' . request()->getHttpHost() . $requestUri);
        }

        return view('qr-login.scanner', compact('student'));
    }

    public function generateGuest(Request $request)
    {
        $token = Str::uuid()->toString();
        $this->qrCache()->put("qr-login:{$token}", ['approved' => false, 'user_id' => null], now()->addMinutes(2));
        return response()->json(['ok' => true, 'token' => $token]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);
        $student = Student::query()->with('user')->findOrFail((int) $data['student_id']);
        $this->authorizeStudentForTeacher($request->user(), $student);
        $userId = (int) ($student->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'message' => 'Ogrenci kullanicisi bulunamadi.'], 422);
        }
        $this->qrCache()->put("qr-login:{$data['token']}", ['approved' => true, 'user_id' => $userId], now()->addMinutes(2));
        return response()->json(['ok' => true]);
    }

    private function authorizeStudentForTeacher(?User $user, Student $student): void
    {
        if (! $user || $user->role?->slug === 'admin') {
            return;
        }

        $classId = (int) ($student->school_class_id ?? 0);
        if ($classId <= 0) {
            abort(403);
        }

        $hasAccess = Teacher::query()
            ->where('user_id', $user->id)
            ->whereHas('classes', fn ($q) => $q->whereKey($classId))
            ->exists();

        if (! $hasAccess) {
            abort(403);
        }
    }

    public function status(string $token)
    {
        $payload = $this->qrCache()->get("qr-login:{$token}");
        if (!is_array($payload)) {
            return response()->json(['approved' => false, 'expired' => true, 'message' => 'QR token suresi dolmus.'], 200);
        }
        if (!($payload['approved'] ?? false)) {
            return response()->json(['approved' => false]);
        }
        return response()->json(['approved' => true, 'redirect' => route('qr.login.consume', ['token' => $token])]);
    }

    public function consume(Request $request, string $token)
    {
        $payload = $this->qrCache()->pull("qr-login:{$token}");
        if (!is_array($payload) || !($payload['approved'] ?? false)) {
            return redirect()->route('login')->withErrors(['email' => 'QR token gecersiz ya da suresi dolmus.']);
        }
        $user = User::query()->with('role')->find((int) ($payload['user_id'] ?? 0));
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Kullanici bulunamadi.']);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard')->with('success', 'QR ile giris basarili.');
    }
}
