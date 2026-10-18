<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Services\ExamDocx\Builder as DocxBuilder;
use App\Services\ExamDocx\Template as DocxTemplate;
use App\Support\ExamLayout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $exams = Exam::query()
            ->with('owner:id,name')
            ->withCount('questions')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('updated_at')
            ->get();

        return view('exams.index', [
            'exams' => $exams,
            'isAdmin' => $isAdmin,
            'templates' => Exam::TEMPLATES,
            'defaultYear' => ExamLayout::academicYear(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'subject' => ['nullable', 'string', 'max:120'],
            'grade_level' => ['nullable', 'integer', 'between:1,12'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'term_no' => ['nullable', 'in:1,2'],
            'exam_no' => ['nullable', 'in:1,2,3,4,5'],
            'exam_date' => ['nullable', 'date'],
            'duration_min' => ['nullable', 'integer', 'between:1,600'],
            'school_name' => ['nullable', 'string', 'max:200'],
            'template' => ['required', Rule::in(array_keys(Exam::TEMPLATES))],
            'columns' => ['required', 'integer', 'in:1,2'],
        ]);
        $settings = array_merge(self::DEFAULT_SETTINGS, [
            'academic_year' => trim((string) ($data['academic_year'] ?? '')) ?: ExamLayout::academicYear($data['exam_date'] ?? null),
            'term_no' => (string) ($data['term_no'] ?? ''),
            'exam_no' => (string) ($data['exam_no'] ?? ''),
        ]);
        unset($data['academic_year'], $data['term_no'], $data['exam_no']);

        $exam = Exam::query()->create($data + ['user_id' => $request->user()->id, 'status' => 'draft', 'settings' => $settings]);

        return redirect()->route('exams.edit', $exam)->with('ok', '"'.$exam->title.'" sınavı oluşturuldu. Soruları ekleyebilirsiniz.');
    }

    public const QUESTION_TYPES = ['multiple_choice', 'true_false', 'fill_blank', 'open_ended', 'matching'];

    public const DEFAULT_SETTINGS = [
        'font_size' => 11,
        'font_family' => 'serif',
        'margin_mm' => 15,
        'show_points' => true,
        'student_box' => true,
        'page_numbers' => true,
        'instructions' => '',
        // tasarım (Adım 4)
        'accent' => '#1e3a8a',
        'spacing' => 'normal',     // compact | normal | relaxed
        'num_style' => 'dot',      // dot: 1.  paren: 1)  soru: Soru 1
        'opt_style' => 'paren',    // paren: A)  bracket: (A)  dot: A.
        'page_border' => false,
        'watermark' => '',
        'show_logo' => false,
        'logo_path' => '',
        'logo2_path' => '', // Tablolu şablonda sağ üst logo
        // Başlık satırları: eğitim-öğretim yılı, dönem (1|2), yazılı numarası
        'academic_year' => '',
        // Üst kısım (başlık) yazı ayarları soru yazısından ayrıdır; boş = soru yazısıyla aynı
        'header_font_family' => '',
        'header_font_size' => '',
        // Kenarlık kalınlığı (px): 1, 1.5, 2, 3, 4
        'border_width' => 2,
        'term_no' => '',
        'exam_no' => '',
    ];

    public function edit(Request $request, Exam $exam)
    {
        $this->authorizeOwner($request, $exam);

        $questions = $exam->questions()->get()->map(fn (ExamQuestion $q) => [
            'type' => $q->type,
            'body' => $q->body,
            'options' => $q->options ?? [],
            'answer' => $q->answer ?? '',
            'points' => $q->points,
            'section_title' => $q->section_title ?? '',
            'answer_lines' => (int) $q->answer_lines,
            'bank_item_id' => $q->bank_item_id,
            'image_path' => $q->image_path ?? '',
            'image_scale' => (int) ($q->image_scale ?: 100),
            'image_align' => $q->image_align ?: 'left',
            'image_pos' => $q->image_pos ?: 'below',
        ])->values();

        return view('exams.edit', [
            'exam' => $exam,
            'templates' => Exam::TEMPLATES,
            'payload' => [
                'exam' => [
                    'title' => $exam->title,
                    'subject' => $exam->subject ?? '',
                    'grade_level' => $exam->grade_level,
                    'class_label' => $exam->class_label ?? '',
                    'term' => $exam->term ?? '',
                    'exam_date' => optional($exam->exam_date)->format('Y-m-d') ?? '',
                    'duration_min' => $exam->duration_min,
                    'school_name' => $exam->school_name ?? '',
                    'template' => $exam->template,
                    'columns' => (int) $exam->columns,
                    'status' => $exam->status,
                ],
                'settings' => array_diff_key(array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings), ['layout' => 1, 'layout_header_h' => 1]),
                'questions' => $questions,
            ],
        ]);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);

        $data = $request->validate([
            'exam.title' => ['required', 'string', 'max:200'],
            'exam.subject' => ['nullable', 'string', 'max:120'],
            'exam.grade_level' => ['nullable', 'integer', 'between:1,12'],
            'exam.class_label' => ['nullable', 'string', 'max:60'],
            'exam.term' => ['nullable', 'string', 'max:80'],
            'exam.exam_date' => ['nullable', 'date'],
            'exam.duration_min' => ['nullable', 'integer', 'between:1,600'],
            'exam.school_name' => ['nullable', 'string', 'max:200'],
            'exam.template' => ['required', Rule::in(array_keys(Exam::TEMPLATES))],
            'exam.columns' => ['required', 'integer', 'in:1,2'],
            'exam.status' => ['required', 'in:draft,final'],
            'settings' => ['required', 'array'],
            'settings.font_size' => ['required', 'integer', 'between:9,16'],
            'settings.font_family' => ['required', 'in:serif,sans,times,arial,calibri,cambria,georgia'],
            'settings.margin_mm' => ['required', 'integer', 'between:8,30'],
            'settings.show_points' => ['required', 'boolean'],
            'settings.student_box' => ['required', 'boolean'],
            'settings.page_numbers' => ['required', 'boolean'],
            'settings.instructions' => ['nullable', 'string', 'max:1000'],
            'settings.accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.spacing' => ['required', 'in:compact,normal,relaxed'],
            'settings.num_style' => ['required', 'in:dot,paren,soru'],
            'settings.opt_style' => ['required', 'in:paren,bracket,dot'],
            'settings.page_border' => ['required', 'boolean'],
            'settings.watermark' => ['nullable', 'string', 'max:40'],
            'settings.show_logo' => ['required', 'boolean'],
            'settings.academic_year' => ['nullable', 'string', 'max:20'],
            'settings.header_font_family' => ['nullable', 'in:serif,sans,times,arial,calibri,cambria,georgia'],
            'settings.header_font_size' => ['nullable', 'integer', 'between:8,24'],
            'settings.border_width' => ['required', 'in:1,1.5,2,3,4'],
            'settings.term_no' => ['nullable', 'in:1,2'],
            'settings.exam_no' => ['nullable', 'in:1,2,3,4,5'],
            'settings.logo_path' => ['nullable', 'string', 'max:120', 'regex:#^(exam-logos/[a-f0-9]{16,64}\.(png|jpe?g|webp))?$#'],
            'settings.logo2_path' => ['nullable', 'string', 'max:120', 'regex:#^(exam-logos/[a-f0-9]{16,64}\.(png|jpe?g|webp))?$#'],
            'layout' => ['nullable', 'array', 'max:80'],
            'layout_header_h' => ['nullable', 'integer', 'between:0,700'],
            'layout.*' => ['array', 'max:2'],
            'layout.*.*' => ['array', 'max:300'],
            'layout.*.*.*' => ['integer', 'min:0', 'max:299'],
            'questions' => ['present', 'array', 'max:300'],
            'questions.*.type' => ['required', Rule::in(self::QUESTION_TYPES)],
            'questions.*.body' => ['nullable', 'string', 'max:6000'],
            'questions.*.options' => ['nullable', 'array', 'max:10'],
            'questions.*.answer' => ['nullable', 'string', 'max:2000'],
            'questions.*.points' => ['nullable', 'numeric', 'between:0,1000'],
            'questions.*.section_title' => ['nullable', 'string', 'max:200'],
            'questions.*.answer_lines' => ['nullable', 'integer', 'between:0,20'],
            'questions.*.bank_item_id' => ['nullable', 'integer', 'exists:question_bank_items,id'],
            'questions.*.image_scale' => ['nullable', 'integer', 'between:10,200'],
            'questions.*.image_align' => ['nullable', Rule::in(['left', 'center', 'right'])],
            'questions.*.image_pos' => ['nullable', Rule::in(['above', 'below', 'end'])],
            'questions.*.image_path' => ['nullable', 'string', 'max:120', 'regex:#^exam-images/'.$exam->id.'-[a-f0-9]{20}-\d{2,4}x\d{2,4}\.(png|jpg)$#'],
        ]);

        // Doğrulama anahtar sırasını karıştırabildiği için sayfa/sütun sırasını açıkça düzeltiriz.
        $layout = isset($data['layout'])
            ? collect($data['layout'])->sortKeys()->map(
                fn ($page) => collect($page)->sortKeys()->map(fn ($col) => array_map('intval', array_values((array) $col)))->values()->all()
            )->values()->all()
            : null;

        DB::transaction(function () use ($exam, $data, $layout): void {
            $meta = $data['exam'];
            $exam->fill([
                'title' => $meta['title'],
                'subject' => $meta['subject'] ?? null,
                'grade_level' => $meta['grade_level'] ?? null,
                'class_label' => $meta['class_label'] ?? null,
                'term' => $meta['term'] ?? null,
                'exam_date' => $meta['exam_date'] ?? null,
                'duration_min' => $meta['duration_min'] ?? null,
                'school_name' => $meta['school_name'] ?? null,
                'template' => $meta['template'],
                'columns' => (int) $meta['columns'],
                'status' => $meta['status'],
                // Önizlemedeki sayfa/sütun dağılımı: PDF ve Word çıktısı aynı dağılımı kullanır.
                'settings' => $data['settings'] + ['layout' => $layout, 'layout_header_h' => $data['layout_header_h'] ?? null]
                    + array_intersect_key((array) $exam->settings, ['word_template' => 1, 'word_template_name' => 1]),
            ])->save();

            $exam->questions()->delete();
            foreach ($data['questions'] as $i => $q) {
                $options = self::cleanOptions((array) ($q['options'] ?? []));
                ExamQuestion::query()->create([
                    'exam_id' => $exam->id,
                    'sort_order' => $i + 1,
                    'type' => $q['type'],
                    'body' => (string) ($q['body'] ?? ''),
                    'options' => $options,
                    'answer' => $q['answer'] ?? null,
                    'points' => $q['points'] ?? 0,
                    'section_title' => $q['section_title'] ?? null,
                    'answer_lines' => (int) ($q['answer_lines'] ?? 0),
                    'bank_item_id' => $q['bank_item_id'] ?? null,
                    'image_path' => ($q['image_path'] ?? '') ?: null,
                    'image_scale' => (int) ($q['image_scale'] ?? 100) ?: 100,
                    'image_align' => $q['image_align'] ?? 'left',
                    'image_pos' => $q['image_pos'] ?? 'below',
                ]);
            }
        });

        $this->pruneImages($exam);

        return response()->json(['ok' => true, 'saved_at' => now()->format('H:i:s')]);
    }

    /** Sınavı PDF olarak indirir. mode: student | student_key | teacher | key */
    public function pdf(Request $request, Exam $exam)
    {
        $this->authorizeOwner($request, $exam);
        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $mode = (string) $request->query('mode', 'student');
        if (! in_array($mode, ['student', 'student_key', 'teacher', 'key'], true)) {
            $mode = 'student';
        }

        $qs = $this->exportQuestions($exam);

        if ($qs === []) {
            return redirect()->route('exams.edit', $exam)->with('error', 'PDF almak için sınava en az bir soru ekleyin.');
        }

        $s = array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings);
        $columns = (int) $exam->columns === 2 ? 2 : 1;
        $pages = $s['layout'] ?? null;
        if (! ExamLayout::isValid($pages, count($qs), $columns)) {
            $pages = ExamLayout::estimate($qs, $s, $columns);
        }

        $margin = (int) round(((int) $s['margin_mm']) * ExamLayout::MM);
        $contentW = ExamLayout::PAGE_W - 2 * $margin;
        $colW = $columns === 2 ? (int) floor(($contentW - 24) / 2) : $contentW;

        // dompdf, özel yazı tiplerinin ölçü dosyalarını (UFM) burada saklar; klasör yoksa Türkçe karakterler bozulur
        if (! is_dir(storage_path('fonts'))) {
            @mkdir(storage_path('fonts'), 0755, true);
        }

        $pdf = Pdf::loadView('exams.pdf', [
            'exam' => $exam, 's' => $s, 'qs' => $qs, 'pages' => $pages, 'mode' => $mode === 'student_key' ? 'student' : $mode,
            'withKey' => $mode === 'student_key', 'columns' => $columns, 'margin' => $margin, 'contentW' => $contentW, 'colW' => $colW,
            'contentH' => ExamLayout::contentHeight($s), 'headerH' => ExamLayout::headerHeight($s),
        ])->setPaper('a4', 'portrait')->setOption('enable_font_subsetting', true); // not: setOptions() (toplu) özel yazı tiplerinin (@font-face) kaydını bozuyor; varsayılanlar zaten dpi 96 / uzaktan erişim kapalı

        $suffix = ['student' => '', 'student_key' => '-cevap-anahtarli', 'teacher' => '-ogretmen', 'key' => '-cevap-anahtari'][$mode];

        return $pdf->download(Str::slug($exam->title ?: 'sinav').$suffix.'.pdf');
    }

    /** PDF ve Word çıktısı için sınav sorularını düz diziye çevirir. */
    private function exportQuestions(Exam $exam): array
    {
        return $exam->questions()->get()->map(fn (ExamQuestion $q) => [
            'type' => $q->type,
            'body' => (string) $q->body,
            'options' => $q->options ?? [],
            'answer' => (string) ($q->answer ?? ''),
            'points' => (float) $q->points,
            'section_title' => (string) ($q->section_title ?? ''),
            'answer_lines' => (int) $q->answer_lines,
            'image_path' => (string) ($q->image_path ?? ''),
            'image_scale' => (int) ($q->image_scale ?: 100),
            'image_align' => $q->image_align ?: 'left',
            'image_pos' => $q->image_pos ?: 'below',
        ])->values()->all();
    }

    private function templateDir(): string
    {
        return storage_path('app/exam-templates');
    }

    /** Sınava bağlı Word şablonunun tam yolu (yoksa/geçersizse null). */
    private function wordTemplatePath(Exam $exam): ?string
    {
        $name = (string) data_get($exam->settings, 'word_template', '');
        if (! preg_match('/^[a-f0-9]{24}\.docx$/', $name)) {
            return null;
        }
        $path = $this->templateDir().'/'.$name;

        return is_file($path) ? $path : null;
    }

    private function deleteWordTemplateFile(Exam $exam): void
    {
        if ($path = $this->wordTemplatePath($exam)) {
            @unlink($path);
        }
    }

    /** Yüklenen dosyayı doğrulayıp kalıcı depoya taşır; [dosya adı, çözümlenen içerik] döner. */
    private function storeWordUpload(Request $request): array
    {
        $request->validate(['word' => ['required', 'file', 'max:5120']]);
        $file = $request->file('word');
        if (strtolower((string) $file->getClientOriginalExtension()) !== 'docx') {
            abort(response()->json(['message' => 'Yalnızca .docx (Word) dosyaları yüklenebilir.'], 422));
        }
        try {
            $parsed = (new DocxTemplate($file->getRealPath()))->parse();
        } catch (\Throwable $e) {
            abort(response()->json(['message' => 'Bu dosya geçerli bir Word (.docx) belgesi değil ya da okunamadı.'], 422));
        }
        $dir = $this->templateDir();
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(12)).'.docx';
        $file->move($dir, $name);

        return [$name, $parsed, $this->cleanFileName((string) $file->getClientOriginalName())];
    }

    private function cleanFileName(string $n): string
    {
        $n = preg_replace('/[\x00-\x1F\x7F]/u', '', basename($n)) ?? '';
        $n = str_replace(['<', '>', '"', "'", '\\', '/'], '', $n);

        return mb_substr(trim($n), 0, 120);
    }

    /** Word şablonu yükle: şablon sınava bağlanır, kâğıttaki sorular okunup editöre döner. */
    public function uploadWordTemplate(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);
        [$name, $parsed, $orig] = $this->storeWordUpload($request);

        $this->deleteWordTemplateFile($exam);
        $exam->settings = array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings, ['word_template' => $name, 'word_template_name' => $orig]);
        $exam->save();

        return response()->json(['ok' => true, 'name' => $orig, 'questions' => $parsed['questions'], 'header' => $parsed['header'], 'notes' => $parsed['notes']]);
    }

    public function deleteWordTemplate(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);
        $this->deleteWordTemplateFile($exam);
        $settings = (array) $exam->settings;
        unset($settings['word_template'], $settings['word_template_name']);
        $exam->settings = $settings;
        $exam->save();

        return response()->json(['ok' => true]);
    }

    /** Word kâğıdından yeni sınav: şablon olarak saklanır, sorular okunup eklenir. */
    public function importWord(Request $request): JsonResponse|RedirectResponse
    {
        [$name, $parsed, $orig] = $this->storeWordUpload($request);

        $title = $parsed['header']['title'] ?: (pathinfo($orig, PATHINFO_FILENAME) ?: 'Word Sınavı');
        $exam = Exam::query()->create([
            'user_id' => $request->user()->id,
            'title' => mb_substr($title, 0, 200),
            'school_name' => $parsed['header']['school'] ?: null,
            'template' => 'classic',
            'columns' => 1,
            'status' => 'draft',
            'settings' => array_merge(self::DEFAULT_SETTINGS, ['word_template' => $name, 'word_template_name' => $orig]),
        ]);
        foreach ($parsed['questions'] as $i => $q) {
            ExamQuestion::query()->create([
                'exam_id' => $exam->id, 'sort_order' => $i + 1, 'type' => $q['type'], 'body' => $q['body'],
                'options' => self::cleanOptions($q['options']), 'answer' => $q['answer'] ?: null, 'points' => $q['points'],
                'section_title' => $q['section_title'] ?: null, 'answer_lines' => $q['answer_lines'],
            ]);
        }
        $n = count($parsed['questions']);
        $msg = $n > 0
            ? '"'.$orig.'" şablon olarak eklendi; '.$n.' soru okundu. Soruları kontrol edin'.($parsed['notes']['skipped_tables'] > 0 ? ' ('.$parsed['notes']['skipped_tables'].' tablo okunamadı)' : '').'.'
            : '"'.$orig.'" şablon olarak eklendi; kâğıtta numaralı soru bulunamadı. Soruları şimdi ekleyebilirsiniz.';

        if ($request->expectsJson()) {
            $request->session()->flash('ok', $msg);

            return response()->json(['ok' => true, 'url' => route('exams.edit', $exam)]);
        }

        return redirect()->route('exams.edit', $exam)->with('ok', $msg);
    }

    /** Sınavı Word (.docx) olarak indirir. mode: student | student_key | teacher | key */
    public function word(Request $request, Exam $exam)
    {
        $this->authorizeOwner($request, $exam);
        @set_time_limit(120);

        $mode = (string) $request->query('mode', 'student');
        if (! in_array($mode, ['student', 'student_key', 'teacher', 'key'], true)) {
            $mode = 'student';
        }
        $qs = $this->exportQuestions($exam);
        if ($qs === []) {
            return redirect()->route('exams.edit', $exam)->with('error', 'Word çıktısı için sınava en az bir soru ekleyin.');
        }

        $s = array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings);
        $builder = new DocxBuilder($exam, $qs, $s, $mode);
        $tpl = $this->wordTemplatePath($exam);
        try {
            $bin = $tpl ? $builder->buildFromTemplate($tpl) : $builder->build();
        } catch (\Throwable $e) {
            report($e);
            if ($tpl) {
                // Şablon bozuksa standart düzenle devam et
                $bin = $builder->build();
            } else {
                throw $e;
            }
        }
        $suffix = ['student' => '', 'student_key' => '-cevap-anahtarli', 'teacher' => '-ogretmen', 'key' => '-cevap-anahtari'][$mode];

        return response()->streamDownload(function () use ($bin): void {
            echo $bin;
        }, Str::slug($exam->title ?: 'sinav').$suffix.'.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function uploadLogo(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);
        $request->validate(['logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'mimetypes:image/png,image/jpeg,image/webp', 'max:1024']]);

        $file = $request->file('logo');
        $dir = public_path('exam-logos');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $ext = $file->guessExtension() === 'jpeg' ? 'jpg' : $file->guessExtension();
        $name = bin2hex(random_bytes(12)).'.'.$ext;
        $file->move($dir, $name);

        $key = (int) $request->input('slot', 1) === 2 ? 'logo2_path' : 'logo_path';
        $this->deleteLogoFile($exam, $key);
        $path = 'exam-logos/'.$name;
        $settings = array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings, [$key => $path] + ($key === 'logo_path' ? ['show_logo' => true] : []));
        $exam->settings = $settings;
        $exam->save();

        return response()->json(['ok' => true, 'logo_path' => $path, 'slot' => $key === 'logo2_path' ? 2 : 1, 'url' => asset($path)]);
    }

    public function deleteLogo(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);
        $key = (int) $request->query('slot', 1) === 2 ? 'logo2_path' : 'logo_path';
        $this->deleteLogoFile($exam, $key);
        $exam->settings = array_merge(self::DEFAULT_SETTINGS, (array) $exam->settings, [$key => ''] + ($key === 'logo_path' ? ['show_logo' => false] : []));
        $exam->save();

        return response()->json(['ok' => true]);
    }

    private function deleteLogoFile(Exam $exam, string $key = 'logo_path'): void
    {
        $old = (string) data_get($exam->settings, $key, '');
        if ($old !== '' && preg_match('#^exam-logos/[a-f0-9]{16,64}\.(png|jpe?g|webp)$#', $old)) {
            @unlink(public_path($old));
        }
    }

    /** Seçenek / eşleştirme çiftlerini temizler (kırpma + uzunluk sınırı). */
    public static function cleanOptions(array $options): array
    {
        $clean = static fn ($v): string => mb_substr(trim((string) $v), 0, 500);
        $out = [];
        foreach ($options as $opt) {
            $out[] = is_array($opt)
                ? ['l' => $clean($opt['l'] ?? ''), 'r' => $clean($opt['r'] ?? '')]
                : $clean($opt);
        }

        return $out;
    }

    /** Soruya görsel yükle: sunucuda küçültülür, boyutu dosya adına yazılır (önizleme/PDF/Word aynı ölçüyü kullanır). */
    public function uploadImage(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwner($request, $exam);
        $request->validate(['image' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'mimetypes:image/png,image/jpeg,image/webp', 'max:4096']]);
        if (! function_exists('imagecreatefromstring')) {
            return response()->json(['message' => 'Sunucuda görsel işleme desteği yok.'], 422);
        }

        $raw = (string) file_get_contents($request->file('image')->getRealPath());
        $mime = (string) ($request->file('image')->getMimeType() ?? '');
        $src = @imagecreatefromstring($raw);
        if (! $src) {
            return response()->json(['message' => 'Görsel okunamadı.'], 422);
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 1200;
        if ($w > $max || $h > $max) {
            $ratio = min($max / $w, $max / $h);
            $nw = max(1, (int) round($w * $ratio));
            $nh = max(1, (int) round($h * $ratio));
            $dst = imagecreatetruecolor($nw, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
            $w = $nw;
            $h = $nh;
        }
        $isJpeg = $mime === 'image/jpeg';
        ob_start();
        if ($isJpeg) {
            imagejpeg($src, null, 85);
        } else {
            imagealphablending($src, false);
            imagesavealpha($src, true);
            imagepng($src);
        }
        $bin = (string) ob_get_clean();
        imagedestroy($src);

        $dir = public_path('exam-images');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $name = $exam->id.'-'.bin2hex(random_bytes(10)).'-'.$w.'x'.$h.'.'.($isJpeg ? 'jpg' : 'png');
        file_put_contents($dir.'/'.$name, $bin);

        return response()->json(['ok' => true, 'path' => 'exam-images/'.$name, 'url' => asset('exam-images/'.$name)]);
    }

    /** Sınavın artık hiçbir soruda kullanılmayan görsellerini siler (yeni yüklenenlere 24 saat tolerans). */
    private function pruneImages(Exam $exam, bool $all = false): void
    {
        $used = $all ? [] : $exam->questions()->pluck('image_path')->filter()->all();
        foreach (glob(public_path('exam-images/'.$exam->id.'-*')) ?: [] as $file) {
            $rel = 'exam-images/'.basename($file);
            if (! in_array($rel, $used, true) && ($all || filemtime($file) < time() - 86400)) {
                @unlink($file);
            }
        }
    }

    /** Sınavı (sorular, ayarlar, logo, Word şablonu ve görselleriyle) kopyalar. */
    public function duplicate(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeOwner($request, $exam);

        $copy = DB::transaction(function () use ($request, $exam): Exam {
            $new = $exam->replicate(['created_at', 'updated_at']);
            $new->user_id = $request->user()->id;
            $new->title = mb_substr($exam->title.' (kopya)', 0, 200);
            $new->status = 'draft';
            $new->save();

            $settings = (array) $exam->settings;
            foreach (['logo_path', 'logo2_path'] as $lk) {
                $logo = (string) ($settings[$lk] ?? '');
                if ($logo !== '' && is_file(public_path($logo)) && preg_match('/\.(png|jpe?g|webp)$/', $logo, $m)) {
                    $nl = 'exam-logos/'.bin2hex(random_bytes(12)).'.'.$m[1];
                    @copy(public_path($logo), public_path($nl));
                    $settings[$lk] = $nl;
                }
            }
            if ($tpl = $this->wordTemplatePath($exam)) {
                $nt = bin2hex(random_bytes(12)).'.docx';
                @copy($tpl, $this->templateDir().'/'.$nt);
                $settings['word_template'] = $nt;
            }
            $new->settings = $settings;
            $new->save();

            foreach ($exam->questions()->get() as $q) {
                $nq = $q->replicate(['created_at', 'updated_at']);
                $nq->exam_id = $new->id;
                $img = (string) ($q->image_path ?? '');
                if ($img !== '' && is_file(public_path($img)) && preg_match('/-(\d{2,4}x\d{2,4})\.(png|jpg)$/', $img, $m)) {
                    $ni = 'exam-images/'.$new->id.'-'.bin2hex(random_bytes(10)).'-'.$m[1].'.'.$m[2];
                    @copy(public_path($img), public_path($ni));
                    $nq->image_path = $ni;
                }
                $nq->save();
            }

            return $new;
        });

        return redirect()->route('exams.edit', $copy)->with('ok', 'Sınav kopyalandı. Kopya üzerinde çalışıyorsunuz.');
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeOwner($request, $exam);
        $this->deleteLogoFile($exam);
        $this->deleteLogoFile($exam, 'logo2_path');
        $this->deleteWordTemplateFile($exam);
        $this->pruneImages($exam, true);
        $exam->delete();

        return redirect()->route('exams.index')->with('ok', 'Sınav silindi.');
    }

    private function authorizeOwner(Request $request, Exam $exam): void
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin') || $exam->user_id === $user->id, 403);
    }
}
