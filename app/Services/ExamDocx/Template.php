<?php

namespace App\Services\ExamDocx;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use ZipArchive;

/**
 * Kullanıcının yüklediği Word (.docx) sınav kâğıdı.
 *  - parse(): kâğıttaki soruları (numaralı paragraflar + A) B) C) şıkları) editör biçiminde okur.
 *  - render(): kâğıdın başlık/alt bilgi/stilleri korunarak soru bölümünü verilen sorularla değiştirir.
 * Soru bölümü: "{{SORULAR}}" yer tutucusu varsa o paragraf; yoksa ilk numaralı sorudan son soru/şığa kadar olan bölge.
 */
class Template
{
    private const MAX_XML = 20 * 1024 * 1024;

    private ZipArchive $zip;
    private DOMDocument $dom;
    private DOMXPath $xp;
    private array $numFmt = [];

    public function __construct(private string $path)
    {
        $this->zip = new ZipArchive();
        if ($this->zip->open($path) !== true) {
            throw new \RuntimeException('Word dosyası açılamadı.');
        }
        if ($this->zip->numFiles > 3000) {
            throw new \RuntimeException('Word dosyası beklenenden fazla parça içeriyor.');
        }
        $stat = $this->zip->statName('word/document.xml');
        if ($stat === false || $stat['size'] > self::MAX_XML) {
            throw new \RuntimeException('Geçerli bir .docx dosyası değil.');
        }
        $this->loadDom();
        $this->loadNumbering();
    }

    public function __destruct()
    {
        @$this->zip->close();
    }

    private function loadDom(): void
    {
        $this->dom = new DOMDocument();
        $this->dom->preserveWhiteSpace = true;
        $xml = (string) $this->zip->getFromName('word/document.xml');
        if (! @$this->dom->loadXML($xml, LIBXML_NONET)) {
            throw new \RuntimeException('Word belgesi okunamadı.');
        }
        $this->xp = new DOMXPath($this->dom);
        $this->xp->registerNamespace('w', Xml::W_NS);
    }

    private function loadNumbering(): void
    {
        $xml = $this->zip->getFromName('word/numbering.xml');
        if (! is_string($xml) || strlen($xml) > self::MAX_XML) {
            return;
        }
        $d = new DOMDocument();
        if (! @$d->loadXML($xml, LIBXML_NONET)) {
            return;
        }
        $x = new DOMXPath($d);
        $x->registerNamespace('w', Xml::W_NS);
        $abs = [];
        foreach ($x->query('//w:abstractNum') as $a) {
            $id = $a->getAttributeNS(Xml::W_NS, 'abstractNumId');
            foreach ($x->query('w:lvl', $a) as $lvl) {
                $fmt = $x->query('w:numFmt', $lvl)->item(0);
                $abs[$id][$lvl->getAttributeNS(Xml::W_NS, 'ilvl')] = $fmt ? $fmt->getAttributeNS(Xml::W_NS, 'val') : '';
            }
        }
        foreach ($x->query('//w:num') as $n) {
            $numId = $n->getAttributeNS(Xml::W_NS, 'numId');
            $absId = $x->query('w:abstractNumId', $n)->item(0)?->getAttributeNS(Xml::W_NS, 'val');
            if ($absId !== null && isset($abs[$absId])) {
                $this->numFmt[$numId] = $abs[$absId];
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Çözümleme                                                          */
    /* ------------------------------------------------------------------ */

    private function body(): DOMElement
    {
        return $this->xp->query('/w:document/w:body')->item(0);
    }

    /** @return list<DOMElement> */
    private function children(): array
    {
        $out = [];
        foreach ($this->body()->childNodes as $c) {
            if ($c instanceof DOMElement) {
                $out[] = $c;
            }
        }

        return $out;
    }

    private function text(DOMElement $p): string
    {
        $t = '';
        foreach ($this->xp->query('.//w:t | .//w:tab | .//w:br | .//w:cr', $p) as $n) {
            $t .= match ($n->localName) {
                't' => $n->textContent,
                'tab' => "\t",
                default => "\n",
            };
        }

        return $t;
    }

    private const NUM_PREFIX = '/^\s*(?:Soru\s*)?(\d{1,3})\s*[\.\)\-]\s*(?=\S)/iu';
    private const OPT_PREFIX = '/^\s*\(?([A-Fa-f])[\)\.]\s+(?=\S)/u';

    /** "A. ÇOKTAN SEÇMELİ SORULAR" gibi bölüm başlığı mı? (tamamı büyük harf ya da büyük harfle başlayan kısa "A." satırı) */
    private function isSectionText(string $t): bool
    {
        $t = trim($t);
        if ($t === '' || mb_strlen($t) > 100) {
            return false;
        }
        $body = preg_replace('/^\s*\(?[A-Za-z]?[\)\.]?\s*/u', '', $t) ?? $t;
        $allCaps = $t === mb_strtoupper($t, 'UTF-8') && preg_match('/\p{L}{3}/u', $t);
        $letterCaps = preg_match('/^[A-Z]\s*[\.\)]\s+\p{Lu}/u', $t) && $body === mb_strtoupper($body, 'UTF-8') && mb_strlen($body) >= 8;

        return (bool) ($allCaps || $letterCaps);
    }

    /** @return array{kind: string, num?: int, text?: string, auto?: bool} */
    private function classify(DOMElement $el): array
    {
        if ($el->localName === 'tbl') {
            return ['kind' => 'tbl'];
        }
        if ($el->localName !== 'p') {
            return ['kind' => 'other'];
        }
        $text = $this->text($el);
        $trim = trim($text);

        $auto = null;
        $numPr = $this->xp->query('w:pPr/w:numPr', $el)->item(0);
        if ($numPr && $trim !== '') {
            $numId = $this->xp->query('w:numId', $numPr)->item(0)?->getAttributeNS(Xml::W_NS, 'val');
            $ilvl = $this->xp->query('w:ilvl', $numPr)->item(0)?->getAttributeNS(Xml::W_NS, 'val') ?? '0';
            $fmt = $this->numFmt[$numId ?? ''][$ilvl] ?? null;
            if ($fmt === 'decimal') {
                $auto = 'q';
            } elseif (in_array($fmt, ['upperLetter', 'lowerLetter'], true)) {
                $auto = 'opt';
            }
        }

        if ($trim === '') {
            return ['kind' => $this->xp->query('.//w:drawing | .//w:pict', $el)->length ? 'other' : 'blank'];
        }
        if (preg_match('/^[\s\.\_…\-–—]{5,}$/u', $trim)) {
            return ['kind' => 'line'];
        }
        if ($auto === 'q') {
            return ['kind' => 'q', 'auto' => true, 'text' => $trim];
        }
        if ($auto === 'opt') {
            return ['kind' => 'opt', 'auto' => true, 'text' => $trim];
        }
        // "1. Dönem 1. Yazılı" gibi başlık satırları soru sayılmaz
        if (preg_match(self::NUM_PREFIX, $text, $m) && ! preg_match('/^\s*\d{1,2}\s*[\.\)]\s*(dönem|yazılı|sınav|oturum|sınıf)\b/iu', $text)) {
            return ['kind' => 'q', 'num' => (int) $m[1], 'text' => $trim];
        }
        if (preg_match(self::OPT_PREFIX, $text) && ! $this->isSectionText($text)) {
            return ['kind' => 'opt', 'text' => $trim];
        }

        return ['kind' => 'other', 'text' => $trim];
    }

    /**
     * Soru bölgesini bulur.
     *
     * @return array{items: list<array>, start: int, end: int, placeholder: bool}
     */
    private function analyze(): array
    {
        $els = $this->children();
        $items = array_map(fn (DOMElement $e) => $this->classify($e), $els);

        // 1) yer tutucu
        foreach ($els as $i => $el) {
            if ($el->localName === 'p' && preg_match('/[\{\[]{1,2}\s*SORULAR\s*[\}\]]{1,2}/iu', $this->text($el))) {
                return ['items' => $items, 'start' => $i, 'end' => $i, 'placeholder' => true];
            }
        }

        // 2) ilk numaralı soru: numarası 1 olan (ya da otomatik numaralı) ilk aday; yoksa ilk aday
        $start = null;
        $firstAny = null;
        foreach ($items as $i => $it) {
            if ($it['kind'] !== 'q') {
                continue;
            }
            $firstAny ??= $i;
            if (! empty($it['auto']) || ($it['num'] ?? 0) === 1) {
                $start = $i;
                break;
            }
        }
        $start ??= $firstAny;
        if ($start === null) {
            return ['items' => $items, 'start' => -1, 'end' => -1, 'placeholder' => false];
        }

        // İlk sorudan hemen önceki bölüm başlığı (ör. "A. ÇOKTAN SEÇMELİ SORULAR") bölgeye dahil
        for ($k = $start - 1; $k >= 0; $k--) {
            if ($items[$k]['kind'] === 'blank') {
                continue;
            }
            if ($items[$k]['kind'] === 'other' && $this->isSectionText($items[$k]['text'] ?? '')) {
                $start = $k;
            }
            break;
        }

        $end = $start;
        foreach ($items as $i => $it) {
            if ($i > $start && in_array($it['kind'], ['q', 'opt'], true)) {
                $end = $i;
            }
        }
        // son sorunun hemen ardındaki boş / cevap çizgisi paragrafları da bölgeye dahil
        for ($j = $end + 1; $j < count($items) && in_array($items[$j]['kind'], ['blank', 'line'], true); $j++) {
            $end = $j;
        }

        return ['items' => $items, 'start' => $start, 'end' => $end, 'placeholder' => false];
    }

    /* ------------------------------------------------------------------ */
    /*  Soruları okuma                                                     */
    /* ------------------------------------------------------------------ */

    public function parse(): array
    {
        $a = $this->analyze();
        $els = $this->children();
        $questions = [];
        $notes = ['skipped_tables' => 0, 'mc_without_answer' => 0, 'region_found' => $a['start'] >= 0, 'placeholder' => $a['placeholder']];

        // Başlık tahmini (bölgeden önceki ilk satırlar)
        $header = ['school' => '', 'title' => ''];
        $limit = $a['start'] >= 0 ? $a['start'] : count($els);
        $seen = 0;
        foreach ($a['items'] as $i => $it) {
            if ($i >= $limit || $seen > 12) {
                break;
            }
            if (empty($it['text'])) {
                continue;
            }
            $seen++;
            if ($header['school'] === '' && preg_match('/(okul|lise|ortaokul|ilkokul|anadolu|kolej)/iu', $it['text'])) {
                $header['school'] = mb_substr($it['text'], 0, 200);
            } elseif ($header['title'] === '' && preg_match('/(sınav|yazılı|quiz|değerlendirme|test)/iu', $it['text'])) {
                $header['title'] = mb_substr($it['text'], 0, 200);
            }
        }

        if ($a['start'] < 0 || $a['placeholder']) {
            return ['questions' => [], 'header' => $header, 'notes' => $notes];
        }

        $cur = null;
        $pendingSection = '';
        $finish = function () use (&$cur, &$questions, &$notes): void {
            if ($cur === null) {
                return;
            }
            $q = $this->buildQuestion($cur);
            if ($q['type'] === 'multiple_choice') {
                $notes['mc_without_answer']++;
            }
            $questions[] = $q;
            $cur = null;
        };

        for ($i = $a['start']; $i <= $a['end']; $i++) {
            $it = $a['items'][$i];
            switch ($it['kind']) {
                case 'tbl':
                    $notes['skipped_tables']++;
                    break;
                case 'q':
                    $finish();
                    $cur = ['body' => preg_replace(self::NUM_PREFIX, '', $it['text']) ?? $it['text'], 'options' => [], 'lines' => 0, 'blanks' => 0, 'section' => $pendingSection];
                    $pendingSection = '';
                    break;
                case 'opt':
                    if ($cur !== null) {
                        foreach ($this->splitOptions($it['text'], ! empty($it['auto'])) as $o) {
                            $cur['options'][] = $o;
                        }
                    }
                    break;
                case 'line':
                    if ($cur !== null) {
                        $cur['lines']++;
                    }
                    break;
                case 'blank':
                    if ($cur !== null) {
                        $cur['blanks']++;
                    }
                    break;
                default:
                    $t = $it['text'] ?? '';
                    if ($t === '') {
                        break;
                    }
                    $isSection = $this->isSectionText($t);
                    if ($isSection) {
                        $pendingSection = $t;
                    } elseif ($cur !== null) {
                        $cur['body'] .= "\n".$t;
                    }
            }
        }
        $finish();

        return ['questions' => array_slice($questions, 0, 300), 'header' => $header, 'notes' => $notes];
    }

    private function splitOptions(string $text, bool $auto): array
    {
        if ($auto) {
            return [trim(preg_replace(self::OPT_PREFIX, '', $text) ?? $text)];
        }
        if (! preg_match_all('/(?:^|(?<=\s))\(?([A-Fa-f])[\)\.]\s+/u', $text, $m, PREG_OFFSET_CAPTURE)) {
            return [trim($text)];
        }
        // harfler A'dan başlayarak ardışık olmalı; aksi halde tek seçenek say
        $marks = [];
        $expect = 0;
        foreach ($m[1] as $k => $hit) {
            if (strtoupper($hit[0]) === chr(65 + $expect)) {
                $marks[] = [$m[0][$k][1], strlen($m[0][$k][0])];
                $expect++;
            }
        }
        if ($marks === []) {
            return [trim($text)];
        }
        $out = [];
        foreach ($marks as $k => [$pos, $len]) {
            $end = $marks[$k + 1][0] ?? strlen($text);
            $out[] = trim(substr($text, $pos + $len, $end - $pos - $len));
        }

        return array_values(array_filter($out, static fn ($o) => $o !== '')) ?: [trim($text)];
    }

    private function buildQuestion(array $c): array
    {
        $body = trim($c['body']);
        $points = 5.0;
        if (preg_match('/\(?\s*(\d+(?:[.,]\d+)?)\s*(?:puan|p)\s*\)?\s*$/iu', $body, $pm)) {
            $points = (float) str_replace(',', '.', $pm[1]);
            $body = trim(preg_replace('/\(?\s*\d+(?:[.,]\d+)?\s*(?:puan|p)\s*\)?\s*$/iu', '', $body) ?? $body);
        }
        $q = ['type' => 'open_ended', 'body' => $body, 'options' => [], 'answer' => '', 'points' => $points, 'section_title' => (string) $c['section'], 'answer_lines' => 0, 'bank_item_id' => null];

        if (count($c['options']) >= 2) {
            $q['type'] = 'multiple_choice';
            $q['options'] = array_map(static fn ($o) => mb_substr($o, 0, 500), array_slice($c['options'], 0, 6));
        } elseif (preg_match('/^\(\s*\)\s*/u', $body)) {
            $q['type'] = 'true_false';
            $q['body'] = trim(preg_replace('/^\(\s*\)\s*/u', '', $body) ?? $body);
            $q['answer'] = 'D';
        } elseif (preg_match('/\.{4,}|_{3,}|…{2,}/u', $body) && $c['lines'] === 0) {
            $q['type'] = 'fill_blank';
        } else {
            $lines = $c['lines'] > 0 ? $c['lines'] : ($c['blanks'] >= 2 ? $c['blanks'] : 3);
            $q['answer_lines'] = max(1, min(20, $lines));
            $q['points'] = $points === 5.0 ? 10.0 : $points;
        }
        $q['body'] = mb_substr($q['body'], 0, 6000);

        return $q;
    }

    /* ------------------------------------------------------------------ */
    /*  Şablona yazma                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * @param  string  $questionsXml  <w:p>/<w:tbl> dizisi (kök öğesiz)
     * @param  string  $tailXml  sorulardan sonra eklenecek bölüm (ör. cevap anahtarı), yoksa ''
     */
    public function render(string $questionsXml, int $columns, string $tailXml = '', array $images = []): string
    {
        $this->loadDom(); // temiz kopya
        $a = $this->analyze();
        $els = $this->children();
        $body = $this->body();

        // Son sectPr
        $finalSect = null;
        foreach (array_reverse($els) as $e) {
            if ($e->localName === 'sectPr') {
                $finalSect = $e;
                break;
            }
        }

        // Bölgeyi belirle
        if ($a['start'] >= 0) {
            $region = array_slice($els, $a['start'], $a['end'] - $a['start'] + 1);
            $anchor = $els[$a['end'] + 1] ?? null;
        } else {
            $region = [];
            $anchor = $finalSect; // içerik sonuna ekle
        }
        foreach ($region as $node) {
            $body->removeChild($node);
        }

        $frag = $this->importFragment($questionsXml);
        $insertBefore = static fn (DOMNode $n) => $anchor ? $body->insertBefore($n, $anchor) : $body->appendChild($n);

        if ($columns === 2 && $finalSect) {
            // Başlık bölümünü tek sütun olarak kapat, soruları iki sütunlu bölüme al
            $head = $finalSect->cloneNode(true);
            $this->setCols($head, 1, false);
            // Şablonda önceden bölüm sonu varsa boş sayfa oluşmasın diye sürekli bölüm
            $this->setType($head, true);
            $sectPara = $this->dom->createElementNS(Xml::W_NS, 'w:p');
            $ppr = $this->dom->createElementNS(Xml::W_NS, 'w:pPr');
            $ppr->appendChild($head);
            $sectPara->appendChild($ppr);
            $insertBefore($sectPara);
            $insertBefore($frag);

            if ($tailXml !== '') {
                $mid = $finalSect->cloneNode(true);
                $this->stripRefs($mid);
                $this->setType($mid, true);
                $this->setCols($mid, 2, true);
                $p2 = $this->dom->createElementNS(Xml::W_NS, 'w:p');
                $ppr2 = $this->dom->createElementNS(Xml::W_NS, 'w:pPr');
                $ppr2->appendChild($mid);
                $p2->appendChild($ppr2);
                $insertBefore($p2);
                $insertBefore($this->importFragment($tailXml));
                $this->stripRefs($finalSect);
                $this->removeType($finalSect);
                $this->setCols($finalSect, 1, false);
            } else {
                $this->stripRefs($finalSect);
                $this->setType($finalSect, true);
                $this->setCols($finalSect, 2, true);
            }
        } else {
            $insertBefore($frag);
            if ($tailXml !== '') {
                $insertBefore($this->importFragment('<w:p><w:r><w:br w:type="page"/></w:r></w:p>'));
                $insertBefore($this->importFragment($tailXml));
            }
        }

        return $this->repack($this->dom->saveXML(), $images);
    }

    private function importFragment(string $inner): \DOMDocumentFragment
    {
        $wrap = new DOMDocument();
        $ok = @$wrap->loadXML('<root xmlns:w="'.Xml::W_NS.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'.$inner.'</root>', LIBXML_NONET);
        if (! $ok) {
            throw new \RuntimeException('Soru içeriği Word biçimine çevrilemedi.');
        }
        $frag = $this->dom->createDocumentFragment();
        foreach ($wrap->documentElement->childNodes as $n) {
            $frag->appendChild($this->dom->importNode($n, true));
        }

        return $frag;
    }

    private function stripRefs(DOMElement $sect): void
    {
        foreach (iterator_to_array($this->xp->query('w:headerReference | w:footerReference', $sect)) as $n) {
            $sect->removeChild($n);
        }
    }

    private function removeType(DOMElement $sect): void
    {
        foreach (iterator_to_array($this->xp->query('w:type', $sect)) as $n) {
            $sect->removeChild($n);
        }
    }

    private function setType(DOMElement $sect, bool $continuous): void
    {
        $this->removeType($sect);
        if (! $continuous) {
            return;
        }
        $t = $this->dom->createElementNS(Xml::W_NS, 'w:type');
        $t->setAttributeNS(Xml::W_NS, 'w:val', 'continuous');
        // Şema sırası: ... type, pgSz ...
        $pgSz = $this->xp->query('w:pgSz', $sect)->item(0);
        if ($pgSz) {
            $sect->insertBefore($t, $pgSz);
        } else {
            $sect->appendChild($t);
        }
    }

    private function setCols(DOMElement $sect, int $num, bool $sep): void
    {
        foreach (iterator_to_array($this->xp->query('w:cols', $sect)) as $n) {
            $sect->removeChild($n);
        }
        $cols = $this->dom->createElementNS(Xml::W_NS, 'w:cols');
        if ($num > 1) {
            $cols->setAttributeNS(Xml::W_NS, 'w:num', (string) $num);
            $cols->setAttributeNS(Xml::W_NS, 'w:space', '360');
            if ($sep) {
                $cols->setAttributeNS(Xml::W_NS, 'w:sep', '1');
            }
        } else {
            $cols->setAttributeNS(Xml::W_NS, 'w:space', '720');
        }
        // Şema sırası: pgSz, pgMar, paperSrc, pgBorders, lnNumType, pgNumType, cols, ...
        $after = null;
        foreach (['w:pgNumType', 'w:lnNumType', 'w:pgBorders', 'w:paperSrc', 'w:pgMar', 'w:pgSz'] as $q) {
            $after = $this->xp->query($q, $sect)->item(0);
            if ($after) {
                break;
            }
        }
        if ($after && $after->nextSibling) {
            $sect->insertBefore($cols, $after->nextSibling);
        } else {
            $sect->appendChild($cols);
        }
    }

    private function repack(string $documentXml, array $images = []): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'exam-docx-');
        $out = new ZipArchive();
        if ($out->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Word dosyası oluşturulamadı.');
        }
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $name = $this->zip->getNameIndex($i);
            if ($name === false || str_ends_with($name, '/')) {
                continue;
            }
            $content = (string) $this->zip->getFromIndex($i);
            if ($name === 'word/document.xml') {
                $content = $documentXml;
            } elseif ($images !== [] && $name === 'word/_rels/document.xml.rels') {
                $add = '';
                foreach ($images as $k => $img) {
                    $add .= '<Relationship Id="'.$img['rid'].'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/examq'.($k + 1).'.'.$img['ext'].'"/>';
                }
                $content = str_replace('</Relationships>', $add.'</Relationships>', $content);
            } elseif ($images !== [] && $name === '[Content_Types].xml') {
                foreach (['png' => 'image/png', 'jpg' => 'image/jpeg'] as $ext => $mime) {
                    if (! preg_match('/Extension="'.$ext.'"/i', $content)) {
                        $content = preg_replace('/(<Types[^>]*>)/', '$1<Default Extension="'.$ext.'" ContentType="'.$mime.'"/>', $content, 1) ?? $content;
                    }
                }
            }
            $out->addFromString($name, $content);
        }
        foreach ($images as $k => $img) {
            $out->addFromString('word/media/examq'.($k + 1).'.'.$img['ext'], $img['bytes']);
        }
        $out->close();
        $bin = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $bin;
    }
}
