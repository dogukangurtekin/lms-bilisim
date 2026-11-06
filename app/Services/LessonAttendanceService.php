<?php

namespace App\Services;

use App\Models\AttendanceOffDay;
use App\Models\LessonAttendance;
use App\Models\LessonSlot;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ders programına göre otomatik yoklama.
 *
 * - Öğrenci, ders saatinde (başlangıçtan 10 dk önce, bitişe kadar) sisteme girip kullanıyorsa o ders için "Geldi" yazılır.
 * - Ders bitince derse hiç girmeyen öğrenciler "Gelmedi" yazılır (zamanlayıcı, öğrenci istekleri ya da yönetici sayfası tetikler).
 * - Program eklenmeden önce başlamış dersler, tatil günleri ve yapılandırılmamış sınıflar için yoklama alınmaz.
 */
class LessonAttendanceService
{
    /** Ders başlamadan kaç dakika önceki giriş de "derste" sayılır. */
    public const EARLY_GRACE_MIN = 10;
    /** Başlangıçtan bu kadar dakika sonra ilk giriş yapanlar "geç" etiketi alır (yalnızca gösterim). */
    public const LATE_AFTER_MIN = 10;
    /** Zamanlayıcı çalışmadığı günler için en fazla kaç gün geriye dönük kapanış yapılır. */
    public const CATCHUP_DAYS = 3;

    public const DEFAULT_SUBJECT = 'Bilişim Teknolojileri';

    public const HEADERS = ['Sınıf', 'Şube', 'Gün', 'Ders No', 'Başlangıç', 'Bitiş', 'Ders Adı', 'Öğretmen (e-posta, isteğe bağlı)'];

    // ───────────────────────── önbellek ─────────────────────────

    private static function version(): int
    {
        return (int) Cache::get('att_ver', 1);
    }

    /** Ders programı / tatil günü değişince önbelleği geçersiz kılar. */
    public static function bust(): void
    {
        Cache::forever('att_ver', self::version() + 1);
    }

    public static function isOffDay(Carbon $date): bool
    {
        return (bool) Cache::remember('att_off:' . self::version() . ':' . $date->toDateString(), 300,
            fn () => AttendanceOffDay::query()->whereDate('date', $date->toDateString())->exists());
    }

    private static function slotsFor(int $classId, Carbon $date): array
    {
        return Cache::remember('att_slots:' . self::version() . ':' . $classId . ':' . $date->toDateString(), 300, function () use ($classId, $date) {
            return LessonSlot::query()
                ->where('school_class_id', $classId)->where('weekday', $date->isoWeekday())->where('is_active', true)
                ->orderBy('start_time')->get()
                ->map(fn (LessonSlot $s) => ['id' => $s->id, 'start' => (string) $s->start_time, 'end' => (string) $s->end_time, 'subject' => $s->subject])
                ->all();
        });
    }

    // ───────────────────────── canlı yoklama ─────────────────────────

    /**
     * Öğrenci bir istek yaptığında çağrılır (TrackStudentActiveTime). Öğrenci başına dakikada en çok bir kez çalışır.
     * Dersin saati içindeyse öğrenciyi "geldi" olarak işaretler; ayrıca (dakikada bir) biten dersleri kapatır.
     */
    public static function noteStudentSeen(int $studentId, ?Carbon $now = null): void
    {
        if (! Cache::add('att_tick:' . $studentId, 1, 60)) {
            return;
        }
        $now ??= now();
        $classId = (int) Cache::remember('student_class:' . $studentId, 600, fn () => (int) Student::query()->whereKey($studentId)->value('school_class_id'));

        if ($classId > 0 && ! self::isOffDay($now)) {
            foreach (self::slotsFor($classId, $now) as $s) {
                $start = $now->copy()->setTimeFromTimeString($s['start']);
                $end = $now->copy()->setTimeFromTimeString($s['end']);
                if ($now->lt($start->copy()->subMinutes(self::EARLY_GRACE_MIN)) || $now->gt($end)) {
                    continue;
                }
                $lessonId = self::ensureLesson($classId, (int) $s['id'], (string) $s['subject'], $start, $end);
                DB::table('lesson_attendance_records')->insertOrIgnore([[
                    'lesson_attendance_id' => $lessonId, 'student_id' => $studentId, 'status' => 'present',
                    'first_seen_at' => $now->toDateTimeString(), 'source' => 'auto', 'created_at' => $now, 'updated_at' => $now,
                ]]);
            }
        }

        if (Cache::add('att_finalize_tick', 1, 60)) {
            self::finalizeDue($now);
        }
    }

    private static function ensureLesson(int $classId, ?int $slotId, string $subject, Carbon $start, Carbon $end): int
    {
        $now = now();
        DB::table('lesson_attendances')->insertOrIgnore([[
            'lesson_slot_id' => $slotId, 'school_class_id' => $classId, 'date' => $start->toDateString(),
            'start_at' => $start->toDateTimeString(), 'end_at' => $end->toDateTimeString(), 'subject' => $subject,
            'created_at' => $now, 'updated_at' => $now,
        ]]);

        return (int) DB::table('lesson_attendances')->where('school_class_id', $classId)->where('start_at', $start->toDateTimeString())->value('id');
    }

    /** Programdaki bugünkü dersin yoklama kaydını açar (henüz kimse girmemiş olsa da elle yoklama alınabilsin). Ders başlamadıysa null. */
    public static function openLesson(LessonSlot $slot, ?Carbon $now = null): ?int
    {
        $now ??= now();
        $date = $now->copy()->startOfDay();
        if (self::isOffDay($date) || (int) $slot->weekday !== (int) $date->isoWeekday()) {
            return null;
        }
        $start = $slot->startOn($date);
        $end = $slot->endOn($date);
        if ($now->lt($start->copy()->subMinutes(self::EARLY_GRACE_MIN))) {
            return null;
        }

        return self::ensureLesson((int) $slot->school_class_id, (int) $slot->id, (string) $slot->subject, $start, $end);
    }

    /** Bitmiş ve henüz kapatılmamış dersleri kapatır: derse girmeyenler "Gelmedi" olur. Kapatılan ders sayısını döndürür. */
    public static function finalizeDue(?Carbon $now = null): int
    {
        $now ??= now();
        $done = 0;
        for ($i = 0; $i < self::CATCHUP_DAYS; $i++) {
            $date = $now->copy()->startOfDay()->subDays($i);
            if (self::isOffDay($date)) {
                continue;
            }
            $slots = LessonSlot::query()->where('is_active', true)->where('weekday', $date->isoWeekday())->get();
            if ($slots->isEmpty()) {
                continue;
            }
            $closed = LessonAttendance::query()->whereDate('date', $date->toDateString())->whereNotNull('finalized_at')->get(['school_class_id', 'start_at'])
                ->mapWithKeys(fn ($l) => [$l->school_class_id . '|' . $l->start_at->format('H:i:s') => true]);

            foreach ($slots as $slot) {
                $start = $slot->startOn($date);
                $end = $slot->endOn($date);
                if ($end->gt($now)) {
                    continue;      // ders henüz bitmedi
                }
                if ($slot->created_at && $slot->created_at->gt($start)) {
                    continue;      // program, ders başladıktan sonra eklendi: geriye dönük devamsızlık yazılmaz
                }
                if ($closed->has($slot->school_class_id . '|' . $start->format('H:i:s'))) {
                    continue;
                }
                $lessonId = self::ensureLesson((int) $slot->school_class_id, (int) $slot->id, (string) $slot->subject, $start, $end);
                if (self::finalizeLesson($lessonId)) {
                    $done++;
                }
            }
        }

        return $done;
    }

    private static function finalizeLesson(int $lessonId): bool
    {
        $claimed = DB::table('lesson_attendances')->where('id', $lessonId)->whereNull('finalized_at')->update(['finalized_at' => now(), 'updated_at' => now()]);
        if (! $claimed) {
            return false;
        }
        $classId = (int) DB::table('lesson_attendances')->where('id', $lessonId)->value('school_class_id');
        $studentIds = Student::query()->where('school_class_id', $classId)->pluck('id')->map(fn ($v) => (int) $v)->all();
        $seen = DB::table('lesson_attendance_records')->where('lesson_attendance_id', $lessonId)->pluck('student_id')->map(fn ($v) => (int) $v)->all();

        $now = now();
        foreach (array_chunk(array_values(array_diff($studentIds, $seen)), 200) as $chunk) {
            DB::table('lesson_attendance_records')->insertOrIgnore(array_map(fn ($sid) => [
                'lesson_attendance_id' => $lessonId, 'student_id' => $sid, 'status' => 'absent', 'first_seen_at' => null,
                'source' => 'auto', 'created_at' => $now, 'updated_at' => $now,
            ], $chunk));
        }
        self::recount($lessonId, count($studentIds));

        return true;
    }

    /** Ders satırındaki geldi / gelmedi sayılarını kayıtlardan yeniden hesaplar (elle düzeltmelerden sonra da çağrılır). */
    public static function recount(int $lessonId, ?int $total = null): void
    {
        $counts = DB::table('lesson_attendance_records')->where('lesson_attendance_id', $lessonId)
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present, SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent")->first();
        DB::table('lesson_attendances')->where('id', $lessonId)->update([
            'total_students' => $total ?? (int) ($counts->total ?? 0),
            'present_count' => (int) ($counts->present ?? 0),
            'absent_count' => (int) ($counts->absent ?? 0),
            'updated_at' => now(),
        ]);
    }

    // ───────────────────────── kapsam ─────────────────────────

    /** Yöneticinin tüm sınıfları (null), öğretmenin yalnızca kendisine atanmış sınıfları. */
    public static function classIdsFor(User $user): ?array
    {
        if ($user->hasRole('admin')) {
            return null;
        }
        $teacherId = Teacher::query()->where('user_id', $user->id)->value('id');

        return $teacherId ? SchoolClass::query()->where('teacher_id', $teacherId)->pluck('id')->map(fn ($v) => (int) $v)->all() : [];
    }

    // ───────────────────────── bugünün panosu (sayfa + panel widget'ı) ─────────────────────────

    public static function todayBoard(User $user, ?Carbon $now = null): array
    {
        $now ??= now();
        $date = $now->copy()->startOfDay();
        $ids = self::classIdsFor($user);
        $off = self::isOffDay($date);
        $offNote = $off ? (string) AttendanceOffDay::query()->whereDate('date', $date->toDateString())->value('note') : '';

        $slots = LessonSlot::query()->with('schoolClass')->where('is_active', true)->where('weekday', $date->isoWeekday())
            ->when($ids !== null, fn ($q) => $q->whereIn('school_class_id', $ids))->orderBy('start_time')->get();
        $classIds = $slots->pluck('school_class_id')->unique()->values()->all();

        $lessons = $classIds === [] ? collect() : LessonAttendance::query()->whereDate('date', $date->toDateString())->whereIn('school_class_id', $classIds)->get()
            ->keyBy(fn ($l) => $l->school_class_id . '|' . $l->start_at->format('H:i:s'));
        $studentCounts = $classIds === [] ? collect() : Student::query()->whereIn('school_class_id', $classIds)->selectRaw('school_class_id, COUNT(*) as c')->groupBy('school_class_id')->pluck('c', 'school_class_id');
        $presentCounts = $lessons->isEmpty() ? collect() : DB::table('lesson_attendance_records')->whereIn('lesson_attendance_id', $lessons->pluck('id'))->where('status', 'present')
            ->selectRaw('lesson_attendance_id, COUNT(*) as c')->groupBy('lesson_attendance_id')->pluck('c', 'lesson_attendance_id');

        // O sınıfta o saatte derste olmayan (henüz girmeyen / gelmeyen) öğrencilerin adları için sınıf mevcutları ve derse "geldi/izinli" yazılanlar.
        $studentsByClass = $classIds === [] ? collect() : Student::query()->with('user:id,name')->whereIn('school_class_id', $classIds)->get(['id', 'user_id', 'school_class_id'])->groupBy('school_class_id');
        $okByLesson = $lessons->isEmpty() ? collect() : DB::table('lesson_attendance_records')->whereIn('lesson_attendance_id', $lessons->pluck('id'))->whereIn('status', ['present', 'excused'])
            ->get(['lesson_attendance_id', 'student_id'])->groupBy('lesson_attendance_id')->map(fn ($g) => $g->pluck('student_id')->map(fn ($v) => (int) $v)->all());

        $rows = [];
        foreach ($slots as $slot) {
            $start = $slot->startOn($date);
            $end = $slot->endOn($date);
            $lesson = $lessons->get($slot->school_class_id . '|' . $start->format('H:i:s'));
            $closed = $lesson && $lesson->finalized_at;
            $state = $now->lt($start) ? 'upcoming' : ($now->lt($end) ? 'ongoing' : ($closed ? 'closed' : 'pending'));
            $total = $closed ? (int) $lesson->total_students : (int) ($studentCounts[$slot->school_class_id] ?? 0);
            $present = $lesson ? (int) ($presentCounts[$lesson->id] ?? 0) : 0;
            $missing = [];
            if ($state !== 'upcoming') {
                $ok = $lesson ? ($okByLesson[$lesson->id] ?? []) : [];
                $missing = ($studentsByClass[$slot->school_class_id] ?? collect())
                    ->reject(fn ($st) => in_array((int) $st->id, $ok, true))
                    ->map(fn ($st) => (string) ($st->user?->name ?? 'Öğrenci'))->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
            }
            $rows[] = [
                'slot_id' => $slot->id, 'lesson_id' => $lesson?->id, 'class_id' => (int) $slot->school_class_id,
                'class' => $slot->schoolClass ? trim($slot->schoolClass->name . '/' . $slot->schoolClass->section) : '-',
                'subject' => $slot->subject, 'time' => $slot->timeLabel(), 'lesson_no' => $slot->lesson_no,
                'state' => $state, 'total' => $total, 'present' => $closed ? (int) $lesson->present_count : $present,
                'absent' => $closed ? (int) $lesson->absent_count : null, 'missing' => $missing, 'start' => $start, 'end' => $end,
            ];
        }

        // Son biten (kapatılmış) derslerin devamsız öğrencileri.
        $absentGroups = [];
        $closedRows = array_values(array_filter($rows, fn ($r) => $r['state'] === 'closed' && $r['lesson_id'] && ($r['absent'] ?? 0) > 0));
        usort($closedRows, fn ($a, $b) => $b['end']->timestamp <=> $a['end']->timestamp);
        foreach (array_slice($closedRows, 0, 3) as $r) {
            $names = DB::table('lesson_attendance_records as r')->join('students as s', 's.id', '=', 'r.student_id')->join('users as u', 'u.id', '=', 's.user_id')
                ->where('r.lesson_attendance_id', $r['lesson_id'])->where('r.status', 'absent')->orderBy('u.name')->limit(40)->pluck('u.name')->all();
            $absentGroups[] = ['lesson_id' => $r['lesson_id'], 'class' => $r['class'], 'subject' => $r['subject'], 'time' => $r['time'], 'names' => $names, 'count' => (int) $r['absent']];
        }

        return [
            'date' => $date, 'now' => $now, 'off' => $off, 'off_note' => $offNote, 'rows' => $rows, 'absent_groups' => $absentGroups,
            'has_schedule' => LessonSlot::query()->when($ids !== null, fn ($q) => $q->whereIn('school_class_id', $ids))->exists(),
            'totals' => [
                'lessons' => count($rows),
                'closed' => count(array_filter($rows, fn ($r) => $r['state'] === 'closed')),
                'ongoing' => count(array_filter($rows, fn ($r) => $r['state'] === 'ongoing')),
                'absent' => array_sum(array_map(fn ($r) => (int) ($r['absent'] ?? 0), $rows)),
            ],
        ];
    }

    // ───────────────────────── ders programı: şablon ve içe aktarma ─────────────────────────

    public static function sampleRows(): array
    {
        return [
            ['5', 'A', 'Pazartesi', '1', '08:30', '09:10', self::DEFAULT_SUBJECT, ''],
            ['5', 'A', 'Çarşamba', '3', '10:20', '11:00', self::DEFAULT_SUBJECT, ''],
            ['6', 'B', 'Salı', '2', '09:20', '10:00', self::DEFAULT_SUBJECT, ''],
            ['6', 'B', 'Cuma', '5', '12:30', '13:10', self::DEFAULT_SUBJECT, ''],
        ];
    }

    public static function fold(string $s): string
    {
        return strtr(mb_strtolower(trim($s), 'UTF-8'), ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'i̇' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u']);
    }

    public static function parseWeekday(string $v): ?int
    {
        $f = self::fold($v);
        if (preg_match('/^[1-7]$/', $f)) {
            return (int) $f;
        }
        $map = ['pazartesi' => 1, 'pzt' => 1, 'sali' => 2, 'sal' => 2, 'carsamba' => 3, 'car' => 3, 'persembe' => 4, 'per' => 4, 'cuma' => 5, 'cum' => 5, 'cumartesi' => 6, 'cmt' => 6, 'pazar' => 7, 'paz' => 7];

        return $map[$f] ?? null;
    }

    /** "09:00", "9.30", "09:00:00" ya da Excel'in zaman kesri (0.375) → "HH:MM:00"; geçersizse null. */
    public static function parseTime(string $v): ?string
    {
        $v = trim($v);
        if ($v === '') {
            return null;
        }
        $h = $m = null;
        if (preg_match('/^(\d{1,2})[:.](\d{2})(?::\d{2})?$/', $v, $mm)) {
            [$h, $m] = [(int) $mm[1], (int) $mm[2]];
        } elseif (is_numeric(str_replace(',', '.', $v))) {
            $f = (float) str_replace(',', '.', $v);
            if ($f >= 0 && $f < 1) {
                $minutes = (int) round($f * 1440);
                [$h, $m] = [intdiv($minutes, 60), $minutes % 60];
            }
        }

        return ($h !== null && $h < 24 && $m < 60) ? sprintf('%02d:%02d:00', $h, $m) : null;
    }

    /**
     * Şablon satırlarını doğrular.
     * @return array{valid: array<int,array>, errors: array<int,string>, class_ids: array<int,int>}
     */
    public static function parseImport(array $rows, ?array $scopeClassIds, bool $replace): array
    {
        $classMap = [];
        foreach (SchoolClass::query()->get(['id', 'name', 'section']) as $c) {
            $classMap[self::fold((string) $c->name) . '|' . self::fold((string) $c->section)] = (int) $c->id;
        }
        $valid = [];
        $errors = [];
        $first = true;

        foreach ($rows as $i => $cols) {
            $line = $i + 1;
            $cols = array_pad(array_map(fn ($c) => trim((string) $c), array_values($cols)), 8, '');
            if (implode('', $cols) === '') {
                continue;
            }
            if ($first) {
                $first = false;
                if (in_array(self::fold($cols[0]), ['sinif', 'sinifi'], true)) {
                    continue;      // başlık satırı
                }
            }
            [$cls, $sec, $day, $no, $start, $end, $subject, $teacherEmail] = $cols;
            $row = [];
            $classId = $classMap[self::fold($cls) . '|' . self::fold($sec)] ?? null;
            if (! $classId) {
                $errors[] = "Satır {$line}: \"{$cls}/{$sec}\" adlı bir sınıf bulunamadı.";
            } elseif ($scopeClassIds !== null && ! in_array($classId, $scopeClassIds, true)) {
                $errors[] = "Satır {$line}: {$cls}/{$sec} sınıfı size atanmış değil.";
            }
            $weekday = self::parseWeekday($day);
            if (! $weekday) {
                $errors[] = "Satır {$line}: \"{$day}\" geçerli bir gün değil (Pazartesi, Salı, Çarşamba, Perşembe, Cuma…).";
            }
            $startT = self::parseTime($start);
            $endT = self::parseTime($end);
            if (! $startT) {
                $errors[] = "Satır {$line}: başlangıç saati \"{$start}\" geçersiz (örnek: 09:00).";
            }
            if (! $endT) {
                $errors[] = "Satır {$line}: bitiş saati \"{$end}\" geçersiz (örnek: 09:40).";
            }
            if ($startT && $endT && $endT <= $startT) {
                $errors[] = "Satır {$line}: bitiş saati başlangıçtan sonra olmalı.";
            }
            $lessonNo = null;
            if ($no !== '') {
                if (! ctype_digit($no) || (int) $no < 1 || (int) $no > 20) {
                    $errors[] = "Satır {$line}: ders no 1-20 arasında bir sayı olmalı.";
                } else {
                    $lessonNo = (int) $no;
                }
            }
            $teacherId = null;
            if ($teacherEmail !== '') {
                $teacherId = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($teacherEmail)])->whereHas('role', fn ($q) => $q->whereIn('slug', ['teacher', 'admin']))->value('id');
                if (! $teacherId) {
                    $errors[] = "Satır {$line}: \"{$teacherEmail}\" e-postalı öğretmen bulunamadı.";
                }
            }
            if ($classId && $weekday && $startT && $endT && $endT > $startT) {
                $valid[] = ['line' => $line, 'school_class_id' => $classId, 'weekday' => $weekday, 'lesson_no' => $lessonNo, 'start_time' => $startT, 'end_time' => $endT,
                    'subject' => mb_substr($subject !== '' ? $subject : self::DEFAULT_SUBJECT, 0, 120), 'teacher_user_id' => $teacherId];
            }
        }

        // Dosya içi (ve eklemede mevcut programla) çakışmalar
        $byDay = [];
        foreach ($valid as $v) {
            $byDay[$v['school_class_id'] . '|' . $v['weekday']][] = $v;
        }
        $classIds = array_values(array_unique(array_column($valid, 'school_class_id')));
        if (! $replace && $classIds !== []) {
            foreach (LessonSlot::query()->whereIn('school_class_id', $classIds)->get() as $e) {
                $byDay[$e->school_class_id . '|' . $e->weekday][] = ['line' => 0, 'start_time' => (string) $e->start_time, 'end_time' => (string) $e->end_time, 'existing' => true];
            }
        }
        foreach ($byDay as $list) {
            usort($list, fn ($a, $b) => [$a['start_time'], $a['end_time']] <=> [$b['start_time'], $b['end_time']]);
            for ($k = 1; $k < count($list); $k++) {
                $prev = $list[$k - 1];
                $cur = $list[$k];
                $bothExisting = ($prev['existing'] ?? false) && ($cur['existing'] ?? false);
                // Aynı gün/saatli ders zaten varsa (ekle modunda) çakışma değil, güncelleme sayılır.
                $sameSlot = $prev['start_time'] === $cur['start_time'] && $prev['end_time'] === $cur['end_time'] && (($prev['existing'] ?? false) || ($cur['existing'] ?? false));
                if ($cur['start_time'] < $prev['end_time'] && ! $sameSlot && ! $bothExisting) {
                    $lineNo = $cur['line'] ?: $prev['line'];
                    if ($lineNo) {
                        $errors[] = "Satır {$lineNo}: aynı sınıfta aynı gün çakışan bir ders saati var (" . substr($prev['start_time'], 0, 5) . '-' . substr($prev['end_time'], 0, 5) . ' ile ' . substr($cur['start_time'], 0, 5) . '-' . substr($cur['end_time'], 0, 5) . ').';
                    }
                }
            }
        }

        return ['valid' => $valid, 'errors' => array_values(array_unique($errors)), 'class_ids' => $classIds];
    }

    /**
     * Doğrulanmış satırları programa yazar. "Değiştir": dosyadaki sınıfların programı dosyadaki gibi olur (aynı gün/saatli dersler korunur,
     * böylece geçmiş yoklama ve "program eklenmeden önceki dersler" kuralı bozulmaz). "Ekle": yalnızca yeni dersler eklenir.
     * @return array{created:int, updated:int, removed:int}
     */
    public static function applyImport(array $valid, bool $replace): array
    {
        $created = $updated = $removed = 0;
        DB::transaction(function () use ($valid, $replace, &$created, &$updated, &$removed) {
            $byClass = [];
            foreach ($valid as $v) {
                $byClass[$v['school_class_id']][] = $v;
            }
            foreach ($byClass as $classId => $list) {
                $existing = LessonSlot::query()->where('school_class_id', $classId)->get()->keyBy(fn ($e) => $e->weekday . '|' . substr((string) $e->start_time, 0, 5) . '|' . substr((string) $e->end_time, 0, 5));
                $kept = [];
                foreach ($list as $v) {
                    $key = $v['weekday'] . '|' . substr($v['start_time'], 0, 5) . '|' . substr($v['end_time'], 0, 5);
                    $data = ['lesson_no' => $v['lesson_no'], 'subject' => $v['subject'], 'teacher_user_id' => $v['teacher_user_id'], 'is_active' => true];
                    if ($existing->has($key)) {
                        $existing[$key]->update($data);
                        $kept[] = $key;
                        $updated++;
                    } else {
                        LessonSlot::query()->create($data + ['school_class_id' => $classId, 'weekday' => $v['weekday'], 'start_time' => $v['start_time'], 'end_time' => $v['end_time']]);
                        $created++;
                    }
                }
                if ($replace) {
                    foreach ($existing as $key => $e) {
                        if (! in_array($key, $kept, true)) {
                            $e->delete();
                            $removed++;
                        }
                    }
                }
            }
        });
        self::bust();

        return ['created' => $created, 'updated' => $updated, 'removed' => $removed];
    }
}
