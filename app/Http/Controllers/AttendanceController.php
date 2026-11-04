<?php

namespace App\Http\Controllers;

use App\Models\AttendanceOffDay;
use App\Models\LessonAttendance;
use App\Models\LessonAttendanceRecord;
use App\Models\LessonSlot;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\LessonAttendanceService as Att;
use App\Support\BulkTemplateWorkbook;
use App\Support\SpreadsheetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Ders programına göre otomatik yoklama: bugünün durumu, geçmiş, ders programı ve şablonla yükleme. */
class AttendanceController extends Controller
{
    private function scope(): ?array
    {
        return Att::classIdsFor(auth()->user());
    }

    private function authorizeClass(int $classId): void
    {
        $scope = $this->scope();
        abort_unless($scope === null || in_array($classId, $scope, true), 403);
    }

    private function classes(): \Illuminate\Support\Collection
    {
        $scope = $this->scope();

        return SchoolClass::query()->when($scope !== null, fn ($q) => $q->whereIn('id', $scope))->orderBy('grade_level')->orderBy('name')->orderBy('section')->get();
    }

    // ───────────────────────── bugün + geçmiş ─────────────────────────

    public function index(Request $request)
    {
        Att::finalizeDue();
        $user = $request->user();
        $scope = $this->scope();
        $tab = $request->query('tab') === 'history' ? 'history' : 'today';

        $from = $this->date($request->query('from'), now()->subDays(13));
        $to = $this->date($request->query('to'), now());
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        $classId = (int) $request->query('class_id', 0);
        if ($classId > 0) {
            $this->authorizeClass($classId);
        }

        $lessons = LessonAttendance::query()->with('schoolClass')->whereNotNull('finalized_at')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($scope !== null, fn ($q) => $q->whereIn('school_class_id', $scope))
            ->when($classId > 0, fn ($q) => $q->where('school_class_id', $classId))
            ->orderByDesc('start_at')->paginate(30, ['*'], 'page')->withQueryString();

        // Geçmiş listesinde her dersin gelmeyenlerinin adları (yalnızca sayfadaki dersler için tek sorgu).
        $absentNames = $lessons->isEmpty() ? collect() : DB::table('lesson_attendance_records as r')
            ->join('students as s', 's.id', '=', 'r.student_id')->join('users as u', 'u.id', '=', 's.user_id')
            ->whereIn('r.lesson_attendance_id', $lessons->pluck('id'))->where('r.status', 'absent')->orderBy('u.name')
            ->get(['r.lesson_attendance_id as lid', 'u.name'])->groupBy('lid')->map(fn ($g) => $g->pluck('name')->all());

        $topAbsent = DB::table('lesson_attendance_records as r')
            ->join('lesson_attendances as l', 'l.id', '=', 'r.lesson_attendance_id')
            ->join('students as s', 's.id', '=', 'r.student_id')->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('school_classes as c', 'c.id', '=', 's.school_class_id')
            ->where('r.status', 'absent')->whereNotNull('l.finalized_at')
            ->whereBetween('l.date', [$from->toDateString(), $to->toDateString()])
            ->when($scope !== null, fn ($q) => $q->whereIn('l.school_class_id', $scope))
            ->when($classId > 0, fn ($q) => $q->where('l.school_class_id', $classId))
            ->selectRaw('s.id as student_id, u.name, c.name as class_name, c.section as class_section, COUNT(*) as absences')
            ->groupBy('s.id', 'u.name', 'c.name', 'c.section')->orderByDesc('absences')->orderBy('u.name')->limit(10)->get();

        return view('attendance.index', [
            'tab' => $tab, 'board' => Att::todayBoard($user), 'lessons' => $lessons, 'topAbsent' => $topAbsent, 'absentNames' => $absentNames,
            'classes' => $this->classes(), 'from' => $from, 'to' => $to, 'classId' => $classId, 'isAdmin' => $user->hasRole('admin'),
        ]);
    }

    private function date(?string $value, Carbon $default): Carbon
    {
        try {
            return $value ? Carbon::parse($value)->startOfDay() : $default->copy()->startOfDay();
        } catch (\Throwable) {
            return $default->copy()->startOfDay();
        }
    }

    /** Dashboard sağ paneli için küçük özet (HTML parçası). */
    public function widget(Request $request)
    {
        Att::finalizeDue();

        return response()->view('attendance._widget', ['board' => Att::todayBoard($request->user())])->header('Cache-Control', 'no-store');
    }

    // ───────────────────────── tek ders ─────────────────────────

    public function lesson(LessonAttendance $lesson)
    {
        $this->authorizeClass((int) $lesson->school_class_id);
        $lesson->load('schoolClass');

        $records = LessonAttendanceRecord::query()->with('student.user')->where('lesson_attendance_id', $lesson->id)->get()
            ->sortBy(fn ($r) => [$r->status === 'absent' ? 0 : ($r->status === 'excused' ? 1 : 2), mb_strtolower((string) $r->student?->user?->name)])->values();

        return view('attendance.lesson', ['lesson' => $lesson, 'records' => $records, 'lateAfter' => Att::LATE_AFTER_MIN]);
    }

    /** Elle düzeltme: öğrenci derste olduğu halde sisteme girememişse "Geldi" ya da "İzinli" yapılabilir. */
    public function setStatus(Request $request, LessonAttendance $lesson, Student $student): RedirectResponse
    {
        $this->authorizeClass((int) $lesson->school_class_id);
        $data = $request->validate(['status' => ['required', 'in:present,absent,excused'], 'note' => ['nullable', 'string', 'max:190']]);
        abort_unless((int) $student->school_class_id === (int) $lesson->school_class_id
            || LessonAttendanceRecord::query()->where('lesson_attendance_id', $lesson->id)->where('student_id', $student->id)->exists(), 404);

        $record = LessonAttendanceRecord::query()->firstOrNew(['lesson_attendance_id' => $lesson->id, 'student_id' => $student->id]);
        $record->fill(['status' => $data['status'], 'source' => 'manual', 'note' => $data['note'] ?? $record->note])->save();
        Att::recount($lesson->id);

        return back()->with('ok', ($student->user?->name ?? 'Öğrenci') . ': ' . LessonAttendanceRecord::STATUSES[$data['status']] . ' olarak güncellendi.');
    }

    // ───────────────────────── ders programı ─────────────────────────

    public function schedule(Request $request)
    {
        $classes = $this->classes();
        $selected = (int) $request->query('class_id', 0);
        if ($selected === 0 && $classes->isNotEmpty()) {
            $selected = (int) $classes->first()->id;
        }
        if ($selected > 0) {
            $this->authorizeClass($selected);
        }

        // Tüm sınıfların haftalık programı tek tabloda: satırlar gün, sütunlar ders saatleri (öğretmen programı gibi).
        $scope = $this->scope();
        $all = LessonSlot::query()->with('schoolClass')->when($scope !== null, fn ($q) => $q->whereIn('school_class_id', $scope))->orderBy('start_time')->get();
        $key = fn (LessonSlot $s) => substr((string) $s->start_time, 0, 5) . '|' . substr((string) $s->end_time, 0, 5);
        $columns = $all->groupBy($key)->map(function ($list, $k) {
            [$start, $end] = explode('|', $k);
            $no = $list->pluck('lesson_no')->filter()->countBy()->sortDesc()->keys()->first();

            return ['key' => $k, 'start' => $start, 'end' => $end, 'no' => $no];
        })->sortBy('start')->values();
        $grid = [];
        foreach ($all->sortBy(fn ($s) => sprintf('%02d-%s-%s', (int) ($s->schoolClass?->grade_level ?? 0), $s->schoolClass?->name, $s->schoolClass?->section)) as $s) {
            $grid[(int) $s->weekday][$key($s)][] = $s;
        }
        $days = collect([1, 2, 3, 4, 5])->merge($all->pluck('weekday')->map(fn ($d) => (int) $d))->unique()->sort()->values();
        $teachers = User::query()->whereHas('role', fn ($q) => $q->whereIn('slug', ['teacher', 'admin']))->orderBy('name')->get(['id', 'name']);

        return view('attendance.schedule', [
            'classes' => $classes, 'selected' => $selected, 'columns' => $columns, 'grid' => $grid, 'dayNums' => $days, 'teachers' => $teachers, 'totalSlots' => $all->count(),
            'isAdmin' => auth()->user()->hasRole('admin'),
            'offDays' => AttendanceOffDay::query()->where('date', '>=', now()->subDays(14)->toDateString())->orderBy('date')->limit(40)->get(),
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'weekday' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'subject' => ['nullable', 'string', 'max:120'],
            'lesson_no' => ['nullable', 'integer', 'between:1,20'],
            'teacher_user_id' => ['nullable', 'exists:users,id'],
        ], ['end_time.after' => 'Bitiş saati başlangıçtan sonra olmalı.', 'date_format' => 'Saat SS:DD biçiminde olmalı (örnek 09:00).']);
        $this->authorizeClass((int) $data['school_class_id']);

        $start = $data['start_time'] . ':00';
        $end = $data['end_time'] . ':00';
        $clash = LessonSlot::query()->where('school_class_id', $data['school_class_id'])->where('weekday', $data['weekday'])
            ->where('start_time', '<', $end)->where('end_time', '>', $start)->exists();
        if ($clash) {
            return back()->withInput()->with('err', 'Bu sınıfta aynı gün bu saatlerle çakışan başka bir ders var.');
        }

        LessonSlot::query()->create([
            'school_class_id' => (int) $data['school_class_id'], 'weekday' => (int) $data['weekday'], 'lesson_no' => $data['lesson_no'] ?? null,
            'start_time' => $start, 'end_time' => $end, 'subject' => trim((string) ($data['subject'] ?? '')) ?: Att::DEFAULT_SUBJECT,
            'teacher_user_id' => $data['teacher_user_id'] ?? null,
        ]);
        Att::bust();

        return redirect()->route('attendance.schedule', ['class_id' => $data['school_class_id']])->with('ok', 'Ders programa eklendi.');
    }

    public function destroySlot(LessonSlot $slot): RedirectResponse
    {
        $this->authorizeClass((int) $slot->school_class_id);
        $classId = $slot->school_class_id;
        $slot->delete();
        Att::bust();

        return redirect()->route('attendance.schedule', ['class_id' => $classId])->with('ok', 'Ders programdan silindi (geçmiş yoklama kayıtları korunur).');
    }

    public function template(): StreamedResponse
    {
        $binary = BulkTemplateWorkbook::buildFromRows(Att::HEADERS, Att::sampleRows(), 'Ders Programi');

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, 'ders-programi-sablonu.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Şablon dosyasından ders programını yükler. Tek hata bile varsa hiçbir şey kaydedilmez ve hatalar satır satır gösterilir. */
    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:5120']], ['file.required' => 'Bir dosya seçin.']);
        $ext = strtolower((string) $request->file('file')->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'csv', 'txt'], true)) {
            return back()->with('err', 'Yalnızca xlsx, csv veya txt dosyaları yüklenebilir.');
        }
        $rows = SpreadsheetReader::read((string) $request->file('file')->getRealPath(), $ext);
        if ($rows === null) {
            return back()->with('err', 'Dosya okunamadı. Lütfen şablon dosyasını kullanın.');
        }
        $rows = array_values(array_filter($rows, static fn ($r) => trim(implode('', $r)) !== ''));
        if ($rows === []) {
            return back()->with('err', 'Dosyada ders satırı bulunamadı.');
        }
        if (count($rows) > 600) {
            return back()->with('err', 'Tek seferde en fazla 600 satır yüklenebilir.');
        }

        $replace = $request->boolean('replace');
        $parsed = Att::parseImport($rows, $this->scope(), $replace);
        if ($parsed['errors'] !== []) {
            return back()->with('import_errors', array_slice($parsed['errors'], 0, 40))->with('err', 'Dosyada ' . count($parsed['errors']) . ' hata var; hiçbir ders kaydedilmedi. Hataları düzeltip yeniden yükleyin.');
        }
        if ($parsed['valid'] === []) {
            return back()->with('err', 'Dosyada geçerli ders satırı bulunamadı.');
        }

        $r = Att::applyImport($parsed['valid'], $replace);

        return redirect()->route('attendance.schedule', ['class_id' => $parsed['class_ids'][0] ?? null])
            ->with('ok', "Ders programı yüklendi: {$r['created']} yeni, {$r['updated']} güncellenen" . ($replace ? ", {$r['removed']} kaldırılan" : '') . ' ders.');
    }

    // ───────────────────────── tatil günleri (yalnızca yönetici) ─────────────────────────

    public function storeOffDay(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => ['required', 'date'], 'note' => ['nullable', 'string', 'max:190']]);
        AttendanceOffDay::query()->updateOrCreate(['date' => Carbon::parse($data['date'])->toDateString()], ['note' => $data['note'] ?? null]);
        Att::bust();

        return back()->with('ok', 'Tatil / ders yapılmayan gün eklendi: bu günde otomatik yoklama alınmaz.');
    }

    public function destroyOffDay(AttendanceOffDay $offDay): RedirectResponse
    {
        $offDay->delete();
        Att::bust();

        return back()->with('ok', 'Tatil günü kaldırıldı.');
    }

    // ───────────────────────── dışa aktarma ─────────────────────────

    public function export(Request $request): StreamedResponse
    {
        $scope = $this->scope();
        $from = $this->date($request->query('from'), now()->subDays(13));
        $to = $this->date($request->query('to'), now());
        $classId = (int) $request->query('class_id', 0);
        if ($classId > 0) {
            $this->authorizeClass($classId);
        }
        $q = DB::table('lesson_attendance_records as r')
            ->join('lesson_attendances as l', 'l.id', '=', 'r.lesson_attendance_id')
            ->join('students as s', 's.id', '=', 'r.student_id')->join('users as u', 'u.id', '=', 's.user_id')
            ->join('school_classes as c', 'c.id', '=', 'l.school_class_id')
            ->whereNotNull('l.finalized_at')->whereBetween('l.date', [$from->toDateString(), $to->toDateString()])
            ->when($scope !== null, fn ($x) => $x->whereIn('l.school_class_id', $scope))
            ->when($classId > 0, fn ($x) => $x->where('l.school_class_id', $classId))
            ->orderBy('l.start_at')->orderBy('c.name')->orderBy('c.section')->orderBy('u.name')
            ->select('l.date', 'l.start_at', 'l.end_at', 'l.subject', 'c.name as cn', 'c.section as cs', 'u.name as student', 'r.status', 'r.first_seen_at', 'r.source');

        return response()->streamDownload(function () use ($q): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tarih', 'Saat', 'Ders', 'Sınıf', 'Öğrenci', 'Durum', 'İlk Giriş', 'Kaynak'], ';');
            $q->chunk(500, function ($rows) use ($out): void {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        Carbon::parse($r->date)->format('d.m.Y'), Carbon::parse($r->start_at)->format('H:i') . '-' . Carbon::parse($r->end_at)->format('H:i'),
                        $r->subject, trim($r->cn . '/' . $r->cs), $r->student, LessonAttendanceRecord::STATUSES[$r->status] ?? $r->status,
                        $r->first_seen_at ? Carbon::parse($r->first_seen_at)->format('H:i') : '', $r->source === 'manual' ? 'Elle' : 'Otomatik',
                    ], ';');
                }
            });
            fclose($out);
        }, 'yoklama-dokumu-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
