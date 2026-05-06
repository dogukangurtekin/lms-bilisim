<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $roleFilter = trim((string) $request->query('role', ''));
        $users = User::query()
            ->with(['role', 'teacher'])
            ->when($roleFilter !== '', fn ($q) => $q->whereHas('role', fn ($r) => $r->where('slug', $roleFilter)))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();
        return view('users.index', compact('users', 'classes', 'roleFilter'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'in:admin,teacher,student'],
            'school_class_id' => ['required_if:role,student', 'nullable', 'integer', 'exists:school_classes,id'],
        ]);

        $role = Role::query()->where('slug', $data['role'])->firstOrFail();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        if ($data['role'] === 'teacher') {
            Teacher::query()->firstOrCreate(['user_id' => $user->id], ['branch' => null, 'phone' => null, 'hire_date' => null]);
        }
        if ($data['role'] === 'student') {
            Student::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['school_class_id' => $data['school_class_id'] ?? null, 'student_no' => $this->generateStudentNo()]
            );
        }

        return redirect()->route('users.index')->with('ok', 'Kullanici eklendi.');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')->with('err', 'Admin hesabi silinemez.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('ok', 'Kullanici silindi.');
    }

    public function downloadStudentBulkTemplate(): StreamedResponse
    {
        $headers = ['Ad', 'Soyad', 'Kullanici Adi', 'Sifre', 'Sinif', 'Sube'];
        $sample = ['Ali', 'Yilmaz', 'ali.yilmaz', '123456', '6', 'A'];
        return $this->downloadTemplate('ogrenci-toplu-kayit-sablonu.xls', $headers, $sample);
    }

    public function downloadTeacherBulkTemplate(): StreamedResponse
    {
        $headers = ['Ad', 'Soyad', 'Kullanici Adi', 'Sifre', 'Brans', 'Telefon'];
        $sample = ['Ayse', 'Demir', 'ayse.demir', '123456', 'Matematik', '05551234567'];
        return $this->downloadTemplate('ogretmen-toplu-kayit-sablonu.xls', $headers, $sample);
    }

    public function bulkStoreStudents(Request $request): RedirectResponse
    {
        return $this->bulkStoreByRole($request, 'student');
    }

    public function bulkStoreTeachers(Request $request): RedirectResponse
    {
        return $this->bulkStoreByRole($request, 'teacher');
    }

    private function bulkStoreByRole(Request $request, string $roleSlug): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:5120']]);
        $extension = strtolower((string) $request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, ['xls', 'xlsx', 'csv', 'txt'], true)) {
            return back()->withErrors(['file' => 'Yalnizca xls/xlsx/csv/txt destekleniyor.']);
        }

        $role = Role::query()->where('slug', $roleSlug)->first();
        if (! $role) {
            return back()->withErrors(['file' => "Rol bulunamadi: {$roleSlug}"]);
        }

        $rows = $this->extractRowsFromUpload((string) $request->file('file')->getRealPath(), $extension);
        if ($rows === null || count($rows) < 2) {
            return back()->withErrors(['file' => 'Dosya bos veya okunamadi.']);
        }

        $created = 0;
        $skipped = 0;
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }
            $cols = array_map(fn ($v) => trim((string) $v), $row);
            if (count($cols) < 4) {
                $skipped++;
                continue;
            }

            $firstName = $cols[0] ?? '';
            $lastName = $cols[1] ?? '';
            $username = $cols[2] ?? '';
            $password = $cols[3] ?? '';
            if ($firstName === '' || $lastName === '' || $username === '' || $password === '') {
                $skipped++;
                continue;
            }

            $name = trim($firstName . ' ' . $lastName);
            $email = Str::contains($username, '@') ? Str::lower($username) : Str::lower($username . '@school.local');
            if (User::query()->where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($roleSlug, $role, $cols, $name, $email, $password) {
                    $user = User::query()->create([
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'role_id' => $role->id,
                        'is_active' => true,
                    ]);

                    if ($roleSlug === 'student') {
                        $className = Str::upper((string) ($cols[4] ?? '1'));
                        $section = Str::upper((string) ($cols[5] ?? 'A'));
                        $gradeLevel = preg_match('/\d+/', $className, $m) ? (int) $m[0] : 1;
                        $academicYear = now()->year . '-' . (now()->year + 1);
                        $schoolClass = SchoolClass::firstOrCreate(
                            ['name' => $className, 'section' => $section, 'academic_year' => $academicYear],
                            ['grade_level' => max(1, min(12, $gradeLevel))]
                        );
                        Student::query()->create([
                            'user_id' => $user->id,
                            'school_class_id' => $schoolClass->id,
                            'student_no' => 'ST' . now()->format('ymd') . random_int(1000, 9999),
                        ]);
                    } else {
                        Teacher::query()->create([
                            'user_id' => $user->id,
                            'branch' => ($cols[4] ?? '') !== '' ? $cols[4] : null,
                            'phone' => ($cols[5] ?? '') !== '' ? $cols[5] : null,
                            'hire_date' => null,
                        ]);
                    }
                });
                $created++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        return redirect()->route('users.index')->with('ok', "Toplu kayit tamamlandi ({$roleSlug}). Basarili: {$created}, Atlanan: {$skipped}.");
    }

    private function downloadTemplate(string $filename, array $headers, array $sample): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'wb');
            fwrite($out, implode("\t", $headers) . PHP_EOL);
            fwrite($out, implode("\t", $sample) . PHP_EOL);
            fclose($out);
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    private function extractRowsFromUpload(string $filePath, string $extension): ?array
    {
        if (in_array($extension, ['xls', 'csv', 'txt'], true)) {
            $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (! $lines) return null;
            $rows = [];
            foreach ($lines as $line) {
                $cols = str_getcsv($line, "\t");
                if (count($cols) < 2) $cols = str_getcsv($line, ',');
                $rows[] = $cols;
            }
            return $rows;
        }
        if ($extension !== 'xlsx' || ! class_exists(\ZipArchive::class)) return null;
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) return null;
        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedXml)) {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx !== false && isset($sx->si)) foreach ($sx->si as $si) $shared[] = (string) ($si->t ?? '');
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! is_string($sheetXml)) return null;
        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || ! isset($sheet->sheetData->row)) return null;
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cols = [];
            foreach ($row->c as $cell) {
                $type = (string) ($cell['t'] ?? '');
                $raw = (string) ($cell->v ?? '');
                $cols[] = $type === 's' ? ($shared[(int) $raw] ?? '') : $raw;
            }
            if ($cols !== []) $rows[] = $cols;
        }
        return $rows;
    }

    private function generateStudentNo(): string
    {
        do {
            $value = 'ST' . now()->format('ymd') . random_int(1000, 9999);
        } while (Student::query()->where('student_no', $value)->exists());

        return $value;
    }
}
