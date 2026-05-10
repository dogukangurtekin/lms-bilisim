<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\BulkTemplateWorkbook;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function destroySelectedStudents(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
        ]);

        $deleted = User::query()
            ->whereIn('id', $data['user_ids'])
            ->whereHas('role', fn ($q) => $q->where('slug', 'student'))
            ->delete();

        return redirect()->route('users.index')->with('ok', "Secili ogrenciler silindi. Silinen: {$deleted}");
    }

    public function destroyAllStudents(): RedirectResponse
    {
        $deleted = User::query()
            ->whereHas('role', fn ($q) => $q->where('slug', 'student'))
            ->delete();

        return redirect()->route('users.index')->with('ok', "Tum ogrenciler silindi. Silinen: {$deleted}");
    }

    public function downloadStudentBulkTemplate(): StreamedResponse
    {
        $headers = ['Ad', 'Soyad', 'Kullanici Adi', 'Sifre', 'Sinif', 'Sube'];
        $sample = ['Ali', 'Yilmaz', 'ali.yilmaz', '123456', '6', 'A'];
        return $this->downloadTemplate('ogrenci-toplu-kayit-sablonu.xlsx', $headers, $sample);
    }

    public function downloadTeacherBulkTemplate(): StreamedResponse
    {
        $headers = ['Ad', 'Soyad', 'Kullanici Adi', 'Sifre', 'Brans', 'Telefon'];
        $sample = ['Ayse', 'Demir', 'ayse.demir', '123456', 'Matematik', '05551234567'];
        return $this->downloadTemplate('ogretmen-toplu-kayit-sablonu.xlsx', $headers, $sample);
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
        @set_time_limit(300);

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
        $duplicateCount = 0;
        $invalidCount = 0;
        $failedCount = 0;
        $errorSamples = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }
            $cols = array_map(fn ($v) => trim((string) $v), $row);
            if (count($cols) < 4) {
                $skipped++;
                $invalidCount++;
                $errorSamples[] = 'Satir ' . ($index + 1) . ': sutun sayisi yetersiz.';
                continue;
            }

            $firstName = $cols[0] ?? '';
            $lastName = $cols[1] ?? '';
            $username = $cols[2] ?? '';
            $password = $cols[3] ?? '';
            if ($firstName === '' || $lastName === '' || $username === '' || $password === '') {
                $skipped++;
                $invalidCount++;
                $errorSamples[] = 'Satir ' . ($index + 1) . ': zorunlu alan bos.';
                continue;
            }

            $name = trim($firstName . ' ' . $lastName);
            $email = Str::contains($username, '@') ? Str::lower($username) : Str::lower($username . '@school.local');
            if (User::query()->where('email', $email)->exists()) {
                $skipped++;
                $duplicateCount++;
                $errorSamples[] = 'Satir ' . ($index + 1) . ': kullanici zaten var (' . $email . ').';
                continue;
            }

            try {
                DB::transaction(function () use ($roleSlug, $role, $cols, $name, $email, $password) {
                    $user = User::query()->create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password, ['rounds' => 10]),
                        'role_id' => $role->id,
                        'is_active' => true,
                    ]);

                    if ($roleSlug === 'student') {
                        $className = Str::upper((string) ($cols[4] ?? '1'));
                        $section = Str::upper((string) ($cols[5] ?? 'A'));
                        $gradeLevel = preg_match('/\d+/', $className, $m) ? (int) $m[0] : 1;
                        $academicYear = now()->year . '-' . (now()->year + 1);
                        $schoolClass = $this->resolveSchoolClass($className, $section, $academicYear, $gradeLevel);
                        Student::query()->updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'school_class_id' => $schoolClass->id,
                                'student_no' => Student::query()
                                    ->where('user_id', $user->id)
                                    ->value('student_no') ?: 'ST' . now()->format('ymd') . random_int(1000, 9999),
                            ]
                        );
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
            } catch (\Throwable $e) {
                $skipped++;
                $failedCount++;
                $errorSamples[] = 'Satir ' . ($index + 1) . ': ' . $e->getMessage();
                Log::error('Bulk user import row failed', [
                    'role' => $roleSlug,
                    'row_index' => $index + 1,
                    'email' => $email,
                    'exception' => $e,
                ]);
            }
        }

        if ($created === 0) {
            $message = "Toplu kayit yapilamadi. Atlanan: {$skipped}";
            if ($duplicateCount > 0) {
                $message .= ", mevcut kullanici: {$duplicateCount}";
            }
            if ($invalidCount > 0) {
                $message .= ", eksik/bozuk satir: {$invalidCount}";
            }
            if ($failedCount > 0) {
                $message .= ", islenemeyen satir: {$failedCount}";
            }
            if ($errorSamples !== []) {
                $message .= '. Ornek hata: ' . $errorSamples[0];
            }

            return back()->withErrors(['file' => $message . '. XLSX veya XLS kullanin.']);
        }

        return redirect()->route('users.index')->with('ok', "Toplu kayit tamamlandi ({$roleSlug}). Basarili: {$created}, Atlanan: {$skipped}.");
    }

    private function downloadTemplate(string $filename, array $headers, array $sample): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $sample) {
            echo BulkTemplateWorkbook::build($headers, $sample, 'Sablon');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function extractRowsFromUpload(string $filePath, string $extension): ?array
    {
        if (in_array($extension, ['csv', 'txt'], true)) {
            $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (! $lines) return null;
            $rows = [];
            foreach ($lines as $line) {
                $line = preg_replace('/^\xEF\xBB\xBF/', '', $line ?? '');
                $cols = str_getcsv($line, ',');
                if (count($cols) < 2) {
                    $cols = str_getcsv($line, "\t");
                }
                $rows[] = $cols;
            }
            return $rows;
        }
        if ($extension === 'xls') {
            $raw = @file_get_contents($filePath);
            if (! is_string($raw) || $raw === '') {
                return null;
            }

            // Old templates were tab-delimited text with an .xls extension.
            if (str_contains($raw, "\0")) {
                return null;
            }

            $lines = preg_split("/\r\n|\n|\r/", $raw);
            if (! is_array($lines) || $lines === []) {
                return null;
            }

            $rows = [];
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line === '') {
                    continue;
                }
                $rows[] = str_getcsv($line, "\t");
            }
            return $rows !== [] ? $rows : null;
        }
        if ($extension !== 'xlsx' || ! class_exists(\ZipArchive::class)) return null;
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) return null;
        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedXml)) {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx !== false && isset($sx->si)) {
                foreach ($sx->si as $si) {
                    if (isset($si->t)) {
                        $shared[] = (string) $si->t;
                        continue;
                    }

                    $parts = [];
                    foreach ($si->r ?? [] as $run) {
                        $parts[] = (string) ($run->t ?? '');
                    }
                    $shared[] = implode('', $parts);
                }
            }
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
                $ref = (string) ($cell['r'] ?? '');
                $raw = (string) ($cell->v ?? '');
                $value = match ($type) {
                    's' => $shared[(int) $raw] ?? '',
                    'inlineStr' => (string) ($cell->is->t ?? ''),
                    default => $raw,
                };

                $index = $this->columnIndexFromReference($ref);
                if ($index === null) {
                    $cols[] = $value;
                    continue;
                }

                $cols[$index] = $value;
            }
            if ($cols !== []) {
                ksort($cols);
                $rows[] = array_values($cols);
            }
        }
        return $rows;
    }

    private function columnIndexFromReference(string $reference): ?int
    {
        if (! preg_match('/^([A-Z]+)/i', $reference, $matches)) {
            return null;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function generateStudentNo(): string
    {
        do {
            $value = 'ST' . now()->format('ymd') . random_int(1000, 9999);
        } while (Student::query()->where('student_no', $value)->exists());

        return $value;
    }

    private function resolveSchoolClass(string $className, string $section, string $academicYear, int $gradeLevel): SchoolClass
    {
        $existing = SchoolClass::query()
            ->where('name', $className)
            ->where('section', $section)
            ->orderByDesc('academic_year')
            ->orderBy('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return SchoolClass::query()->create([
            'name' => $className,
            'section' => $section,
            'academic_year' => $academicYear,
            'grade_level' => max(1, min(12, $gradeLevel)),
        ]);
    }
}
