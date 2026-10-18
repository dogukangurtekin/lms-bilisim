<?php

namespace App\Support;

/**
 * Sınav kâğıdının sayfa/sütun dağılımı.
 *
 * Gerçek dağılım editördeki önizlemeden gelir (tarayıcı sorunun gerçek yüksekliğini ölçer) ve kayıt sırasında saklanır.
 * Saklı dağılım yoksa ya da soru sayısıyla uyuşmuyorsa, yaklaşık yükseklik tahminiyle bu sınıf dağılım üretir.
 */
class ExamLayout
{
    public const PAGE_W = 794;
    public const PAGE_H = 1123;
    public const MM = 3.7795;
    public const FOOTER_H = 26;

    /** Dağılım: sayfalar -> sütunlar -> soru dizinleri. Her soru tam bir kez geçmeli. */
    public static function isValid(mixed $layout, int $questionCount, int $columns): bool
    {
        if (! is_array($layout) || $layout === []) {
            return $questionCount === 0 && is_array($layout);
        }
        $seen = [];
        foreach ($layout as $page) {
            if (! is_array($page) || count($page) !== $columns) {
                return false;
            }
            foreach ($page as $col) {
                if (! is_array($col)) {
                    return false;
                }
                foreach ($col as $idx) {
                    if (! is_int($idx) || $idx < 0 || $idx >= $questionCount || isset($seen[$idx])) {
                        return false;
                    }
                    $seen[$idx] = true;
                }
            }
        }

        return count($seen) === $questionCount;
    }

    /** Soru görselinin ekrandaki/PDF'teki boyutu; ölçü dosya adındaki "-{G}x{Y}" bölümünden okunur. [genişlik, yükseklik] ya da null. */
    public static function imageSize(?string $path, int $maxW, int $maxH = 200, int $scale = 100): ?array
    {
        if (! $path || ! preg_match('/-(\d{2,4})x(\d{2,4})\.(png|jpg)$/', $path, $m)) {
            return null;
        }
        $w = (int) $m[1];
        $h = (int) $m[2];
        if ($w < 1 || $h < 1) {
            return null;
        }
        $dw = min($w, $maxW);
        $dh = $dw * $h / $w;
        if ($dh > $maxH) {
            $dh = $maxH;
            $dw = $dh * $w / $h;
        }
        // Kullanıcı ölçeği (%10–200); sütun genişliğini aşamaz.
        $f = max(10, min(200, $scale ?: 100)) / 100;
        $dw *= $f;
        $dh *= $f;
        if ($dw > $maxW) {
            $dh *= $maxW / $dw;
            $dw = $maxW;
        }

        return [max(1, (int) round($dw)), max(1, (int) round($dh))];
    }

    /** İçinde bulunulan eğitim-öğretim yılı (Eylül'den itibaren yeni yıl): 2026-2027. */
    public static function academicYear(?string $date = null): string
    {
        $t = $date ? strtotime($date) : time();
        $y = (int) date('Y', $t ?: time());
        $m = (int) date('n', $t ?: time());

        return $m >= 9 ? $y.'-'.($y + 1) : ($y - 1).'-'.$y;
    }

    /**
     * Sınav kâğıdı başlık satırları: 1) yıl, 2) okul adı, 3) ders adı + dönem ve yazılı numarası (aynı satırda).
     *
     * @return array{year: string, school: string, course: string, last: string}
     */
    public static function titleLines(object $exam, array $s): array
    {
        $up = fn (?string $t) => self::upper(trim((string) $t));
        $year = trim((string) ($s['academic_year'] ?? ''));
        $subject = $up($exam->subject);
        $course = $subject !== '' ? (preg_match('/DERS[İI]?$/u', $subject) ? $subject : $subject.' DERSİ') : '';
        $cls = trim((string) $exam->class_label) !== '' ? $up($exam->class_label).' SINIFI' : ($exam->grade_level ? $exam->grade_level.'. SINIF' : '');
        $term = (string) ($s['term_no'] ?? '');
        $no = (string) ($s['exam_no'] ?? '');
        $last = ($term !== '' || $no !== '')
            ? trim(($term !== '' ? $term.'. DÖNEM' : '').' '.($no !== '' ? $no.'. YAZILISI' : ''))
            : $up($exam->title);

        // Üç satır: yıl, okul adı, "sınıf + ders adı + dönem ve yazılı numarası" (dönem/yazılı dersin devamında gelir)
        return [
            'year' => $year !== '' ? $year.' EĞİTİM - ÖĞRETİM YILI' : $up($exam->term),
            'school' => $up($exam->school_name),
            'course' => trim($cls.' '.$course.' '.$last),
            'last' => '',
        ];
    }

    /**
     * Başlık satırlarının hücreye tek satırda sığması için yazı boyutu çarpanı (em): en uzun satıra göre küçültülür.
     * $cellPx: satırların yazıldığı hücre genişliği (px), $basePx: temel yazı boyutu (px).
     */
    public static function fitTitleScale(array $lines, float $cellPx, float $basePx, string $fontKey = 'calibri'): float
    {
        $max = max(array_map(fn ($l) => mb_strlen((string) $l), $lines ?: ['']));
        $factor = in_array($fontKey, ['serif', 'sans'], true) ? 0.72 : 0.62; // büyük harfli kalın yazının ortalama genişliği (em)
        if ($max < 1 || $basePx <= 0) {
            return 1.08;
        }

        return max(0.7, min(1.08, ($cellPx - 12) / ($max * $factor * $basePx)));
    }

    /** Türkçe büyük harf (i→İ, ı→I). */
    public static function upper(?string $s): string
    {
        return mb_strtoupper(strtr((string) $s, ['i' => 'İ', 'ı' => 'I']), 'UTF-8');
    }

    /** Sayfada soruların kullanabileceği yükseklik (kenar boşlukları, sayfa numarası ve güvenlik payı düşülmüş). */
    public static function contentHeight(array $settings): int
    {
        $margin = (int) round(((int) ($settings['margin_mm'] ?? 15)) * self::MM);

        return (int) (self::PAGE_H - 2 * $margin - (! empty($settings['page_numbers']) ? self::FOOTER_H : 0) - 14);
    }

    /** İlk sayfadaki başlık bloğunun yüksekliği: editörün ölçtüğü değer yoksa tahmin. */
    public static function headerHeight(array $settings): int
    {
        $measured = (int) ($settings['layout_header_h'] ?? 0);
        if ($measured > 0) {
            return $measured;
        }

        return 120 + (! empty($settings['student_box']) ? 36 : 0) + (trim((string) ($settings['instructions'] ?? '')) !== '' ? 36 : 0);
    }

    /** Yaklaşık yükseklikle sayfalara dağıtır (kayıtlı dağılım yoksa). */
    public static function estimate(array $questions, array $settings, int $columns): array
    {
        $margin = (int) round(((int) ($settings['margin_mm'] ?? 15)) * self::MM);
        $contentW = self::PAGE_W - 2 * $margin;
        $colW = $columns === 2 ? (int) floor(($contentW - 24) / 2) : $contentW;
        $fontPx = ((int) ($settings['font_size'] ?? 11)) * 1.333;
        $lineH = $fontPx * 1.35;
        $cpl = max(10, (int) floor($colW / ($fontPx * 0.58)));
        $pad = ['compact' => 6, 'relaxed' => 20][$settings['spacing'] ?? 'normal'] ?? 12;
        $contentH = self::contentHeight($settings);

        $lines = static function (string $text) use ($cpl): int {
            $n = 0;
            foreach (preg_split('/\R/u', $text) ?: [''] as $para) {
                $n += max(1, (int) ceil(mb_strlen($para) / $cpl));
            }

            return $n;
        };

        $heights = [];
        foreach ($questions as $q) {
            $h = $lines((string) ($q['body'] ?? '')) * $lineH + $pad;
            if (! empty($q['section_title'])) {
                $h += $lineH + 12;
            }
            $opts = (array) ($q['options'] ?? []);
            if (($q['type'] ?? '') === 'multiple_choice') {
                $short = $columns === 1 && count($opts) <= 4 && collect($opts)->every(fn ($o) => mb_strlen((string) $o) <= 20);
                $h += $short ? $lineH + 4 : array_sum(array_map(fn ($o) => $lines((string) $o) * $lineH, $opts));
            } elseif (($q['type'] ?? '') === 'matching') {
                $h += count($opts) * $lineH + 6;
            } elseif (($q['type'] ?? '') === 'open_ended') {
                $h += ((int) ($q['answer_lines'] ?? 0)) * 25;
            }
            if ($sz = self::imageSize($q['image_path'] ?? null, $colW - 40, 200, (int) ($q['image_scale'] ?? 100))) {
                $h += $sz[1] + 8;
            }
            $heights[] = $h;
        }

        // Tahmin her zaman gerçekten biraz fazla yer ayırır: taşan içerik sayfa sonunda kesilmesin.
        $heights = array_map(static fn ($h) => $h * 1.15, $heights);

        return self::paginate($heights, self::headerHeight($settings), $contentH, $columns);
    }

    /** Tarayıcıdaki sayfalama algoritmasının birebir karşılığı (bkz. edit.blade.php paginate). */
    public static function paginate(array $heights, float $headerH, float $pageContentH, int $nCols): array
    {
        $newPage = static fn () => array_fill(0, $nCols, []);
        $pages = [$newPage()];
        $pi = 0;
        $ci = 0;
        $used = 0.0;
        foreach ($heights as $idx => $h) {
            $cap = $pageContentH - ($pi === 0 ? $headerH : 0);
            if ($used > 0 && $used + $h > $cap) {
                $ci++;
                $used = 0.0;
                if ($ci >= $nCols) {
                    $pages[] = $newPage();
                    $pi++;
                    $ci = 0;
                }
            }
            $pages[$pi][$ci][] = $idx;
            $used += $h;
        }

        return $pages;
    }
}
