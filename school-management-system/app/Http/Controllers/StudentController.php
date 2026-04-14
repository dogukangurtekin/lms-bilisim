<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\Domain\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function __construct(private StudentService $service)
    {
    }

    public function index(Request $request)
    {
        $name = trim($request->string('name')->toString());
        $className = trim($request->string('class_name')->toString());
        $section = trim($request->string('section')->toString());
        $q = $request->string('q')->toString();
        $sort = in_array($request->string('sort')->toString(), ['id', 'student_no', 'created_at'], true) ? $request->string('sort')->toString() : 'id';
        $dir = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';

        $items = Student::with(['user', 'schoolClass', 'credential'])
            ->when($name !== '', fn ($query) => $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$name}%")))
            ->when($className !== '', fn ($query) => $query->whereHas('schoolClass', fn ($c) => $c->where('name', $className)))
            ->when($section !== '', fn ($query) => $query->whereHas('schoolClass', fn ($c) => $c->where('section', $section)))
            ->when($q !== '', fn ($query) => $query->where('student_no', 'like', "%{$q}%"))
            ->orderBy($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::query()
            ->select('name', 'section')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
        $classNames = $classes->pluck('name')->unique()->values();
        $sections = $classes->pluck('section')->unique()->values();

        return view('students.index', compact('items', 'q', 'sort', 'dir', 'name', 'className', 'section', 'classNames', 'sections'));
    }

    public function create() { return view('students.create'); }

    public function store(StoreStudentRequest $request)
    {
        $model = $this->service->create($request->validated());
        return $request->expectsJson() ? response()->json($model, 201) : redirect()->route('students.index')->with('ok', 'Ogrenci eklendi');
    }

    public function show(Student $student) { return view('students.show', compact('student')); }

    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        return view('students.edit', compact('student', 'classes'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $data = $request->validated();
        DB::transaction(function () use ($student, $data) {
            $student->update([
                'student_no' => $data['student_no'],
                'school_class_id' => $data['school_class_id'],
            ]);

            $user = $student->user;
            if ($user) {
                $user->name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
                if (! empty($data['password'])) {
                    $user->password = Hash::make($data['password'], ['rounds' => 10]);
                }
                $user->save();
            }
        });

        return $request->expectsJson() ? response()->json($student->refresh()) : redirect()->route('students.index')->with('ok', 'Ogrenci guncellendi');
    }

    public function destroy(Student $student)
    {
        $this->service->delete($student);
        return request()->expectsJson() ? response()->json([], 204) : redirect()->route('students.index')->with('ok', 'Ogrenci silindi');
    }

    public function destroyAll(): RedirectResponse
    {
        $studentUserIds = Student::query()->pluck('user_id')->all();

        DB::transaction(function () use ($studentUserIds) {
            Student::query()->delete();
            if ($studentUserIds !== []) {
                User::query()->whereIn('id', $studentUserIds)->delete();
            }
        });

        return redirect()->route('students.index')->with('ok', 'Tum ogrenciler silindi.');
    }

    public function downloadBulkTemplate(): StreamedResponse
    {
        $headers = ['Ad', 'Soyad', 'Kullanici Adi', 'Sifre', 'Sinif', 'Sube'];
        $sample = ['Ali', 'Yilmaz', 'ali.yilmaz', '123456', '6', 'A'];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'wb');
            fwrite($out, implode("\t", $headers) . PHP_EOL);
            fwrite($out, implode("\t", $sample) . PHP_EOL);
            fclose($out);
        }, 'ogrenci-toplu-kayit-sablonu.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ], [
            'file.required' => 'Lutfen bir dosya secin.',
        ]);

        $extension = strtolower((string) $request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, ['xls', 'xlsx', 'csv', 'txt'], true)) {
            return back()->withErrors(['file' => 'Yalnizca xls/xlsx/csv/txt formatlari destekleniyor.']);
        }

        $studentRole = Role::where('slug', 'student')->first();
        if (! $studentRole) {
            return back()->withErrors(['file' => 'Student rolu bulunamadi. Once rollerin seed edilmesi gerekiyor.']);
        }

        $filePath = $request->file('file')->getRealPath();
        $rows = $this->extractRowsFromUpload($filePath, $extension);
        if ($rows === null || count($rows) < 2) {
            return back()->withErrors(['file' => 'Dosya bos, bozuk veya okunamadi.']);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $cols = $row;
            if (count($cols) < 6) {
                $skipped++;
                $errors[] = 'Satir ' . ($index + 1) . ': Sutun sayisi eksik.';
                continue;
            }

            [$firstName, $lastName, $username, $password, $className, $section] = array_map(fn ($v) => trim((string) $v), array_slice($cols, 0, 6));
            $name = trim($firstName . ' ' . $lastName);
            $password = (string) $password;

            if ($firstName === '' || $lastName === '' || $username === '' || $password === '' || $className === '' || $section === '') {
                $skipped++;
                $errors[] = 'Satir ' . ($index + 1) . ': Zorunlu alanlar bos.';
                continue;
            }
            if (strlen($password) > 72) {
                $skipped++;
                $errors[] = 'Satir ' . ($index + 1) . ': Sifre en fazla 72 karakter olmali.';
                continue;
            }

            $email = Str::contains($username, '@') ? Str::lower($username) : Str::lower($username . '@school.local');
            if (User::where('email', $email)->exists()) {
                $skipped++;
                $errors[] = 'Satir ' . ($index + 1) . ": Kullanici adi ({$username}) zaten kullaniliyor.";
                continue;
            }

            $className = Str::upper($className);
            $section = Str::upper($section);
            $gradeLevel = $this->guessGradeLevel($className);
            $academicYear = now()->year . '-' . (now()->year + 1);

            try {
                DB::transaction(function () use ($name, $email, $password, $studentRole, $className, $section, $gradeLevel, $academicYear) {
                    $schoolClass = SchoolClass::firstOrCreate(
                        ['name' => $className, 'section' => $section, 'academic_year' => $academicYear],
                        ['grade_level' => $gradeLevel]
                    );

                    $user = User::create([
                        'role_id' => $studentRole->id,
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password, ['rounds' => 10]),
                        'is_active' => true,
                    ]);

                    Student::create([
                        'user_id' => $user->id,
                        'student_no' => $this->generateStudentNo(),
                        'school_class_id' => $schoolClass->id,
                    ]);
                });
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = 'Satir ' . ($index + 1) . ': Kayit basarisiz.';
            }
        }

        $message = "Toplu kayit tamamlandi. Basarili: {$created}, Atlanan: {$skipped}.";
        if ($errors !== []) {
            $message .= ' Hatalar: ' . implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect()->route('students.index')->with('ok', $message);
    }

    private function guessGradeLevel(string $className): int
    {
        if (preg_match('/\d+/', $className, $matches) === 1) {
            return max(1, min(12, (int) $matches[0]));
        }

        return 1;
    }

    private function generateStudentNo(): string
    {
        do {
            $value = 'ST' . now()->format('ymd') . random_int(1000, 9999);
        } while (Student::where('student_no', $value)->exists());

        return $value;
    }

    private function extractRowsFromUpload(string $filePath, string $extension): ?array
    {
        if (in_array($extension, ['xls', 'csv', 'txt'], true)) {
            $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (! $lines) {
                return null;
            }

            $rows = [];
            foreach ($lines as $line) {
                $cols = str_getcsv($line, "\t");
                if (count($cols) < 6) {
                    $cols = str_getcsv($line, ',');
                }
                $rows[] = $cols;
            }

            return $rows;
        }

        if ($extension === 'xlsx') {
            return $this->extractRowsFromXlsx($filePath);
        }

        return null;
    }

    private function extractRowsFromXlsx(string $filePath): ?array
    {
        if (! class_exists(\ZipArchive::class)) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedXml)) {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx !== false && isset($sx->si)) {
                foreach ($sx->si as $si) {
                    $sharedStrings[] = (string) ($si->t ?? '');
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! is_string($sheetXml)) {
            return null;
        }

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || ! isset($sheet->sheetData->row)) {
            return null;
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cols = [];
            foreach ($row->c as $cell) {
                $type = (string) ($cell['t'] ?? '');
                $raw = (string) ($cell->v ?? '');
                if ($type === 's') {
                    $idx = (int) $raw;
                    $cols[] = $sharedStrings[$idx] ?? '';
                } else {
                    $cols[] = $raw;
                }
            }
            if ($cols !== []) {
                $rows[] = $cols;
            }
        }

        return $rows;
    }
}
