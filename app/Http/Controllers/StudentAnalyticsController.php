<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentSuccessAnalytics;
use App\Support\BulkTemplateWorkbook;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Öğrenci Verileri > Başarı İstatistikleri (sınıf bazlı) ve Öğrenci Analizi sayfaları.
 * Admin tüm sınıfları, öğretmen yalnızca kendi sınıflarını görür.
 */
class StudentAnalyticsController extends Controller
{
    private const CACHE_SECONDS = 300;

    public function __construct(private StudentSuccessAnalytics $analytics)
    {
    }

    // ------------------------------------------------------------------ sınıf bazlı sayfa

    public function index(Request $request)
    {
        $ctx = $this->context($request);
        $data = $this->cachedBuild($request, $ctx['class_ids'], $ctx['from'], $ctx['to'], true);
        $prev = $this->previousOverall($request, $ctx);

        $selectedClassId = (int) $request->query('class_id', 0);
        if ($selectedClassId > 0 && ! in_array($selectedClassId, $ctx['class_ids'], true)) {
            $selectedClassId = 0;
        }
        $selectedClass = null;
        if ($selectedClassId > 0) {
            $classStudents = collect($data['students'])->where('class_id', $selectedClassId)->sortByDesc('score')->values();
            $selectedClass = [
                'agg' => collect($data['classes'])->firstWhere('id', $selectedClassId),
                'students' => $classStudents->all(),
                'top' => $classStudents->take(5)->all(),
                'support' => $classStudents->sortBy([['score', 'asc'], ['quiz_answered', 'asc']])->take(5)->values()->all(),
            ];
        }

        return view('student-data.analytics.index', [
            'data' => $data,
            'prev' => $prev,
            'ctx' => $ctx,
            'filters' => $ctx['filters'],
            'selectedClassId' => $selectedClassId,
            'selectedClass' => $selectedClass,
            'buckets' => StudentSuccessAnalytics::BUCKETS,
        ]);
    }

    public function export(Request $request, string $format)
    {
        $ctx = $this->context($request);
        $data = $this->cachedBuild($request, $ctx['class_ids'], $ctx['from'], $ctx['to'], false);
        $students = collect($data['students'])->sortBy([['class_label', 'asc'], ['score', 'desc']])->values()->all();
        $stamp = now()->format('Ymd-His');

        if ($format === 'xlsx') {
            $headers = ['Sınıf', 'Öğrenci No', 'Ad Soyad', 'Başarı Puanı', 'Başarı Düzeyi', 'Ders Tamamlama %', 'Ödev Tamamlama %',
                'Quiz Doğruluk %', 'Cevaplanan Soru', 'Toplam XP', 'Aktif Süre (dk)', 'Son 30 Gün Aktif Gün', 'Son Görülme'];
            $rows = array_map(fn ($s) => [
                $s['class_label'], $s['no'], $s['name'], $s['score'], $s['bucket_label'],
                $s['lesson_pct'] ?? '', $s['homework_pct'] ?? '', $s['quiz_accuracy'] ?? '', $s['quiz_answered'],
                $s['total_xp'], (int) round($s['active_seconds'] / 60), $s['active_days_30'],
                $s['last_seen_at'] ? Carbon::parse($s['last_seen_at'])->format('d.m.Y H:i') : '',
            ], $students);
            $binary = BulkTemplateWorkbook::buildFromRows($headers, $rows, 'Basari Istatistikleri');

            return $this->download($binary, 'basari-istatistikleri-'.$stamp.'.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        // 600+ öğrencilik liste PDF'i birkaç saniye sürebilir; hosting varsayılan süre/bellek sınırına takılmasın.
        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $pdf = Pdf::loadView('student-data.analytics.pdf-classes', [
            'data' => $data,
            'students' => $students,
            'buckets' => StudentSuccessAnalytics::BUCKETS,
            'rangeLabel' => $ctx['range_label'],
            'scopeLabel' => $this->scopeLabel($ctx),
            'generatedAt' => now()->format('d.m.Y H:i'),
        ])->setPaper('a4', 'landscape')->output();

        return $this->download($pdf, 'basari-istatistikleri-'.$stamp.'.pdf', 'application/pdf');
    }

    // ------------------------------------------------------------------ öğrenci analizi

    public function student(Request $request, Student $student)
    {
        $allowed = $this->analytics->allowedClassIds($request->user());
        abort_unless(in_array((int) $student->school_class_id, $allowed, true), 403);

        $range = $this->parseRange($request);
        $type = $this->parseType($request);
        $cacheKey = 'sa:student:'.$student->id.':'.md5(json_encode([$range['key'], $type]));
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }
        $detail = Cache::remember(
            $cacheKey,
            self::CACHE_SECONDS,
            fn () => $this->analytics->studentDetail($student, $range['from'], $range['to'], $type)
        );

        // Öğrenci seçici: erişebildiği tüm öğrenciler, sınıf sınıf gruplu
        $picker = Student::query()
            ->with(['user:id,name', 'schoolClass:id,name,section'])
            ->whereIn('school_class_id', $allowed)
            ->get()
            ->sortBy(fn ($s) => Str::lower(($s->schoolClass?->name ?? '').'/'.($s->schoolClass?->section ?? '')).'|'.Str::lower($s->user?->name ?? ''), SORT_NATURAL)
            ->groupBy(fn ($s) => trim(($s->schoolClass?->name ?? '').'/'.($s->schoolClass?->section ?? ''), '/'));

        $mates = Student::query()->with('user:id,name')->where('school_class_id', $student->school_class_id)->get()
            ->sortBy(fn ($s) => Str::lower($s->user?->name ?? ''))->values();
        $pos = $mates->search(fn ($s) => (int) $s->id === (int) $student->id);
        $prevStudent = $pos !== false && $pos > 0 ? $mates[$pos - 1] : null;
        $nextStudent = $pos !== false && $pos < $mates->count() - 1 ? $mates[$pos + 1] : null;

        return view('student-data.analytics.student', [
            'student' => $student->loadMissing(['user', 'schoolClass']),
            'd' => $detail,
            'picker' => $picker,
            'prevStudent' => $prevStudent,
            'nextStudent' => $nextStudent,
            'range' => $range,
            'type' => $type,
            'buckets' => StudentSuccessAnalytics::BUCKETS,
        ]);
    }

    public function studentExport(Request $request, Student $student, string $format)
    {
        $allowed = $this->analytics->allowedClassIds($request->user());
        abort_unless(in_array((int) $student->school_class_id, $allowed, true), 403);

        $range = $this->parseRange($request);
        $type = $this->parseType($request);
        $d = $this->analytics->studentDetail($student, $range['from'], $range['to'], $type);
        $student->loadMissing(['user', 'schoolClass']);
        $slug = Str::slug($student->user?->name ?? 'ogrenci') ?: 'ogrenci';
        $stamp = now()->format('Ymd-His');

        if ($format === 'xlsx') {
            $r = $d['row'];
            $rows = [
                ['Özet', 'Öğrenci', $student->user?->name, ($r['class_label'] ?? '')],
                ['Özet', 'Başarı puanı', $r['score'] ?? '', $r['bucket_label'] ?? ''],
                ['Özet', 'Sınıf sırası', $d['rank'] ? $d['rank'].' / '.$d['class_size'] : '', $d['percentile'] !== null ? '%'.$d['percentile'].' dilimi' : ''],
                ['Özet', 'Ders tamamlama', ($r['lesson_done'] ?? 0).' / '.($r['lesson_total'] ?? 0), ($r['lesson_pct'] ?? '').' %'],
                ['Özet', 'Ödev tamamlama', ($r['homework_done'] ?? 0).' / '.($r['homework_total'] ?? 0), ($r['homework_pct'] ?? '').' %'],
                ['Özet', 'Quiz doğruluğu', ($r['quiz_correct'] ?? 0).' / '.($r['quiz_answered'] ?? 0), ($r['quiz_accuracy'] ?? '').' %'],
                ['Özet', 'Toplam XP', $r['total_xp'] ?? 0, ''],
                ['Özet', 'Aktif süre (dk)', (int) round(($r['active_seconds'] ?? 0) / 60), ''],
            ];
            foreach ($d['lessons'] as $l) {
                $rows[] = ['Ders', $l['name'], $l['done'] ? 'Tamamlandı' : 'Bekliyor', trim(($l['done_at'] ?? '').($l['q_total'] ? ' | Doğru '.$l['q_correct'].'/'.$l['q_total'] : '').($l['xp'] ? ' | '.$l['xp'].' XP' : ''))];
            }
            foreach ($d['homeworks'] as $h) {
                $rows[] = ['Ödev', $h['title'], $h['done'] ? 'Teslim edildi' : 'Bekliyor', trim(($h['done_at'] ?? '').($h['due'] ? ' | Son tarih '.$h['due'] : ''))];
            }
            foreach ($d['quizzes'] as $q) {
                $rows[] = ['Quiz', $q['title'], $q['correct'].' / '.$q['answered'].' doğru', trim(($q['date'] ?? '').($q['rank'] ? ' | '.$q['rank'].'. sıra ('.$q['participants'].' kişi)' : '').' | '.$q['xp'].' XP')];
            }
            foreach ($d['wrong_questions'] as $w) {
                $rows[] = ['Yanlış soru', $w['question'], $w['quiz'], $w['date']];
            }
            $binary = BulkTemplateWorkbook::buildFromRows(['Bölüm', 'Başlık', 'Değer', 'Detay'], $rows, 'Ogrenci Analizi');

            return $this->download($binary, $slug.'-analiz-'.$stamp.'.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        $pdf = Pdf::loadView('student-data.analytics.pdf-student', [
            'student' => $student,
            'd' => $d,
            'rangeLabel' => $range['label'],
            'generatedAt' => now()->format('d.m.Y H:i'),
        ])->setPaper('a4')->output();

        return $this->download($pdf, $slug.'-analiz-'.$stamp.'.pdf', 'application/pdf');
    }

    // ------------------------------------------------------------------ yardımcılar

    private function download(string $binary, string $filename, string $mime): StreamedResponse
    {
        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, $filename, ['Content-Type' => $mime, 'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0']);
    }

    /** Filtre + yetki bağlamı: erişilebilir sınıflar, seçilen filtrelere uyanlar, tarih aralığı. */
    private function context(Request $request): array
    {
        $allowed = $this->analytics->allowedClassIds($request->user());
        $catalog = $this->analytics->classCatalog($allowed);

        $year = trim((string) $request->query('academic_year', ''));
        $grade = (int) $request->query('grade_level', 0);
        $section = trim((string) $request->query('section', ''));
        $classId = (int) $request->query('class_id', 0);

        $matching = $catalog
            ->when($year !== '', fn ($c) => $c->where('academic_year', $year))
            ->when($grade > 0, fn ($c) => $c->where('grade_level', $grade))
            ->when($section !== '', fn ($c) => $c->where('section', $section));
        $range = $this->parseRange($request);

        return [
            'allowed' => $allowed,
            'catalog' => $catalog,
            'class_ids' => $matching->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'from' => $range['from'],
            'to' => $range['to'],
            'range_label' => $range['label'],
            'range_key' => $range['key'],
            'filters' => [
                'academic_year' => $year,
                'grade_level' => $grade,
                'section' => $section,
                'class_id' => $classId,
                'range' => $range['mode'],
                'from' => $range['from_input'],
                'to' => $range['to_input'],
            ],
            'options' => [
                'years' => $catalog->pluck('academic_year')->filter()->unique()->sort()->values()->all(),
                'grades' => $catalog->pluck('grade_level')->filter()->unique()->sort()->values()->all(),
                'sections' => $catalog->pluck('section')->filter()->unique()->sort()->values()->all(),
            ],
        ];
    }

    private function parseRange(Request $request): array
    {
        $mode = (string) $request->query('range', 'all');
        $fromInput = '';
        $toInput = '';
        $from = null;
        $to = null;
        $label = 'Tüm zamanlar';

        if (in_array($mode, ['7', '30', '90'], true)) {
            $from = now()->subDays((int) $mode - 1)->startOfDay();
            $to = now();
            $label = 'Son '.$mode.' gün';
        } elseif ($mode === 'custom') {
            try {
                $f = trim((string) $request->query('from', ''));
                $t = trim((string) $request->query('to', ''));
                $from = $f !== '' ? Carbon::parse($f)->startOfDay() : null;
                $to = $t !== '' ? Carbon::parse($t)->endOfDay() : null;
                $fromInput = $from?->format('Y-m-d') ?? '';
                $toInput = $to?->format('Y-m-d') ?? '';
                if ($from && $to && $from->gt($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                $label = trim(($from ? $from->format('d.m.Y') : '…').' – '.($to ? $to->format('d.m.Y') : '…'));
            } catch (\Throwable $e) {
                $from = $to = null;
                $mode = 'all';
            }
        } else {
            $mode = 'all';
        }

        return [
            'mode' => $mode, 'from' => $from, 'to' => $to, 'label' => $label,
            'from_input' => $fromInput, 'to_input' => $toInput,
            'key' => $mode.'|'.($from?->format('Y-m-d') ?? '').'|'.($to?->format('Y-m-d') ?? ''),
        ];
    }

    private function parseType(Request $request): string
    {
        $type = (string) $request->query('type', 'all');

        return in_array($type, ['all', 'lesson', 'homework', 'quiz'], true) ? $type : 'all';
    }

    private function cachedBuild(Request $request, array $classIds, $from, $to, bool $withSeries): array
    {
        $key = 'sa:build:'.md5(json_encode([$classIds, $from?->format('Y-m-d'), $to?->format('Y-m-d'), $withSeries]));
        if ($request->boolean('refresh')) {
            Cache::forget($key);
        }

        return Cache::remember($key, self::CACHE_SECONDS, fn () => $this->analytics->build($classIds, $from, $to, $withSeries));
    }

    /** Seçili tarih aralığının hemen öncesindeki eşit uzunluktaki dönemin özeti (▲/▼ karşılaştırma için). */
    private function previousOverall(Request $request, array $ctx): ?array
    {
        if (! $ctx['from'] || ! $ctx['to']) {
            return null;
        }
        $days = max(1, $ctx['from']->copy()->startOfDay()->diffInDays($ctx['to']->copy()->startOfDay()) + 1);
        $prevTo = $ctx['from']->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        return $this->cachedBuild($request, $ctx['class_ids'], $prevFrom, $prevTo, false)['overall'];
    }

    private function scopeLabel(array $ctx): string
    {
        $parts = [];
        if ($ctx['filters']['academic_year'] !== '') {
            $parts[] = $ctx['filters']['academic_year'];
        }
        if ($ctx['filters']['grade_level'] > 0) {
            $parts[] = $ctx['filters']['grade_level'].'. sınıf';
        }
        if ($ctx['filters']['section'] !== '') {
            $parts[] = $ctx['filters']['section'].' şubesi';
        }

        return $parts ? implode(' · ', $parts) : 'Tüm sınıflar';
    }
}
