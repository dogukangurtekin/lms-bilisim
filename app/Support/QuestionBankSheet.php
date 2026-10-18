<?php

namespace App\Support;

use App\Models\QuestionBankItem;

/**
 * Soru havuzu dosya biçimi (Excel/CSV). Dışa aktarma, boş şablon ve içe aktarma aynı sütunları kullanır.
 */
class QuestionBankSheet
{
    public const HEADERS = [
        'Soru Tipi', 'Ders', 'Sınıf', 'Ünite', 'Zorluk', 'Soru Metni',
        'Seçenek 1', 'Seçenek 2', 'Seçenek 3', 'Seçenek 4', 'Seçenek 5', 'Seçenek 6', 'Seçenek 7', 'Seçenek 8',
        'Doğru Cevap', 'Etiketler', 'Paylaş',
    ];

    private const OPT_START = 6;   // "Seçenek 1" sütun indeksi
    private const OPT_COUNT = 8;
    private const LETTERS = 'ABCDEFGH';

    private const TYPE_LABELS = [
        'multiple_choice' => 'Çoktan Seçmeli',
        'true_false' => 'Doğru/Yanlış',
        'fill_blank' => 'Boşluk Doldurma',
        'open_ended' => 'Açık Uçlu',
        'matching' => 'Eşleştirme',
    ];

    private const DIFF_LABELS = ['kolay' => 'Kolay', 'orta' => 'Orta', 'zor' => 'Zor'];

    /** Başlıksız örnek satırlar: her soru tipinden bir tane (şablon dosyası). */
    public static function sampleRows(): array
    {
        $blank = static fn (array $opts) => array_pad($opts, self::OPT_COUNT, '');

        return [
            ['Çoktan Seçmeli', 'Bilişim Teknolojileri', '5', 'Algoritma', 'Kolay', 'Aşağıdakilerden hangisi bir giriş birimidir?', ...$blank(['Monitör', 'Klavye', 'Hoparlör', 'Yazıcı']), 'B', 'donanım, giriş', 'Hayır'],
            ['Doğru/Yanlış', 'Bilişim Teknolojileri', '5', 'Algoritma', 'Kolay', 'Algoritma, bir problemin çözümü için izlenen adımlardır.', ...$blank([]), 'Doğru', '', 'Hayır'],
            ['Boşluk Doldurma', 'Bilişim Teknolojileri', '6', 'Kodlama', 'Orta', 'Bir işlemi tekrar eden yapıya ........ denir.', ...$blank([]), 'döngü', '', 'Hayır'],
            ['Açık Uçlu', 'Bilişim Teknolojileri', '7', 'Ağlar', 'Zor', 'İnternet ile yerel ağ arasındaki farkları açıklayınız.', ...$blank([]), 'İnternet küresel, yerel ağ sınırlı alandır...', '', 'Hayır'],
            ['Eşleştirme', 'Bilişim Teknolojileri', '6', 'Donanım', 'Orta', 'Birimleri görevleriyle eşleştirin.', ...$blank(['Klavye|Yazı girer', 'Fare|İmleci yönetir', 'Monitör|Görüntü verir']), '', '', 'Hayır'],
        ];
    }

    public static function toRow(QuestionBankItem $i): array
    {
        $opts = array_fill(0, self::OPT_COUNT, '');
        foreach (array_slice((array) $i->options, 0, self::OPT_COUNT) as $k => $o) {
            $opts[$k] = is_array($o) ? ($o['l'] ?? '').'|'.($o['r'] ?? '') : (string) $o;
        }
        $answer = (string) $i->answer;
        if ($i->type === 'true_false') {
            $answer = $answer === 'Y' ? 'Yanlış' : 'Doğru';
        } elseif ($i->type === 'matching') {
            $answer = '';
        }

        return [
            self::TYPE_LABELS[$i->type] ?? $i->type,
            (string) $i->subject,
            $i->grade_level ? (string) $i->grade_level : '',
            (string) $i->unit,
            self::DIFF_LABELS[$i->difficulty] ?? 'Orta',
            (string) $i->body,
            ...$opts,
            $answer,
            (string) $i->tags,
            $i->is_shared ? 'Evet' : 'Hayır',
        ];
    }

    /**
     * Bir dosya satırını havuz kaydına çevirir.
     *
     * @return array{0: array|null, 1: string|null} [veri, hata]
     */
    public static function parseRow(array $c): array
    {
        $c = array_pad(array_map(static fn ($v) => trim((string) $v), $c), count(self::HEADERS), '');
        $norm = static fn (string $s): string => mb_strtolower(strtr($s, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');

        $typeKey = null;
        $t = $norm($c[0]);
        foreach ([
            'multiple_choice' => ['çoktan seçmeli', 'coktan secmeli', 'test', 'mc'],
            'true_false' => ['doğru/yanlış', 'doğru yanlış', 'dogru/yanlis', 'dogru yanlis', 'd/y', 'tf'],
            'fill_blank' => ['boşluk doldurma', 'bosluk doldurma', 'boşluk', 'bosluk'],
            'open_ended' => ['açık uçlu', 'acik uclu', 'klasik', 'açık'],
            'matching' => ['eşleştirme', 'eslestirme', 'eşleştir'],
        ] as $key => $aliases) {
            if (in_array($t, $aliases, true)) {
                $typeKey = $key;
                break;
            }
        }
        if ($typeKey === null) {
            return [null, 'Soru tipi tanınmadı: "'.$c[0].'"'];
        }
        if ($c[5] === '') {
            return [null, 'Soru metni boş'];
        }
        if (mb_strlen($c[5]) > 6000) {
            return [null, 'Soru metni 6000 karakteri aşıyor'];
        }

        $grade = null;
        if ($c[2] !== '') {
            $g = (int) preg_replace('/\D+/', '', $c[2]);
            if ($g < 1 || $g > 12) {
                return [null, 'Sınıf 1-12 arasında olmalı'];
            }
            $grade = $g;
        }
        $diffMap = ['kolay' => 'kolay', 'orta' => 'orta', 'zor' => 'zor', '' => 'orta'];
        $diff = $diffMap[$norm($c[4])] ?? null;
        if ($diff === null) {
            return [null, 'Zorluk Kolay/Orta/Zor olmalı'];
        }

        $rawOpts = array_values(array_filter(array_slice($c, self::OPT_START, self::OPT_COUNT), static fn ($o) => $o !== ''));
        $options = [];
        $answer = $c[14];

        if ($typeKey === 'multiple_choice') {
            if (count($rawOpts) < 2) {
                return [null, 'Çoktan seçmeli için en az 2 seçenek gerekli'];
            }
            if (count($rawOpts) > 6) {
                return [null, 'Çoktan seçmelide en fazla 6 seçenek olabilir'];
            }
            $options = array_map(static fn ($o) => mb_substr($o, 0, 500), $rawOpts);
            $letter = strtoupper(mb_substr($answer, 0, 1));
            $pos = strpos(self::LETTERS, $letter);
            if ($answer === '' || $pos === false || $pos >= count($options)) {
                return [null, 'Doğru cevap A-'.self::LETTERS[count($options) - 1].' harflerinden biri olmalı'];
            }
            $answer = $letter;
        } elseif ($typeKey === 'true_false') {
            $a = $norm($answer);
            if (in_array($a, ['doğru', 'dogru', 'd', 'true', 'evet'], true)) {
                $answer = 'D';
            } elseif (in_array($a, ['yanlış', 'yanlis', 'y', 'false', 'hayır', 'hayir'], true)) {
                $answer = 'Y';
            } else {
                return [null, 'Doğru cevap "Doğru" ya da "Yanlış" olmalı'];
            }
        } elseif ($typeKey === 'matching') {
            foreach ($rawOpts as $o) {
                $parts = explode('|', $o, 2);
                if (count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '') {
                    return [null, 'Eşleştirme çiftleri "sol|sağ" biçiminde olmalı: "'.$o.'"'];
                }
                $options[] = ['l' => mb_substr(trim($parts[0]), 0, 500), 'r' => mb_substr(trim($parts[1]), 0, 500)];
            }
            if (count($options) < 2) {
                return [null, 'Eşleştirme için en az 2 çift gerekli'];
            }
            if (count($options) > 8) {
                return [null, 'Eşleştirmede en fazla 8 çift olabilir'];
            }
            // Editördeki karıştırma kuralıyla aynı anahtar (bkz. public/js/exam-q.js)
            $n = count($options);
            $shift = max(1, intdiv($n, 2));
            $order = [];
            for ($j = 0; $j < $n; $j++) {
                $order[] = ($j + $shift) % $n;
            }
            $answer = implode(' ', array_map(fn ($i) => ($i + 1).'-'.self::LETTERS[array_search($i, $order, true)], range(0, $n - 1)));
        } else {
            $answer = mb_substr($answer, 0, 2000);
        }

        $share = in_array($norm($c[16]), ['evet', 'e', '1', 'true', 'var'], true);

        return [[
            'type' => $typeKey,
            'subject' => $c[1] !== '' ? mb_substr($c[1], 0, 120) : null,
            'grade_level' => $grade,
            'unit' => $c[3] !== '' ? mb_substr($c[3], 0, 160) : null,
            'difficulty' => $diff,
            'body' => $c[5],
            'options' => $options,
            'answer' => $answer !== '' ? $answer : null,
            'tags' => $c[15] !== '' ? mb_substr($c[15], 0, 255) : null,
            'is_shared' => $share,
        ], null];
    }

    /** Dosyanın ilk satırı başlık satırı mı? */
    public static function looksLikeHeader(array $row): bool
    {
        return mb_strtolower(trim((string) ($row[0] ?? '')), 'UTF-8') === 'soru tipi';
    }
}
