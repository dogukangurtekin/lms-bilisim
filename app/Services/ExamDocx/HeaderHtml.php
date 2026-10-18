<?php

namespace App\Services\ExamDocx;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;

/**
 * Word şablonunun üst kısmını (soru bölgesinden önceki paragraflar, tablolar, logolar, sayfa üst bilgisi)
 * önizleme ve PDF için HTML'e çevirir. Biçim (hizalama, yazı boyutu, kalın/italik, renk, tablo hücreleri,
 * kenarlıklar, gölgeler, resimler) şablondan okunur; hiçbir metin ya da düzen uydurulmaz.
 * Üretilen HTML yalnızca bu sınıfın yazdığı etiketleri içerir; metinler kaçışlanır, CSS değerleri doğrulanır.
 */
class HeaderHtml
{
    private const W = Xml::W_NS;
    private const WP = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing';
    private const A = 'http://schemas.openxmlformats.org/drawingml/2006/main';
    private const R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const MC = 'http://schemas.openxmlformats.org/markup-compatibility/2006';

    private DOMXPath $sx;       // styles.xml
    private bool $hasStyles = false;
    private array $rels = [];   // geçerli parçanın ilişkileri
    private array $docDefaults = ['rpr' => [], 'ppr' => []];
    private array $styleCache = [];
    private int $imgBudget = 4 * 1024 * 1024; // gömülecek toplam resim baytı

    public function __construct(private ZipArchive $zip, private int $contentPx)
    {
        $styles = $this->loadXml('word/styles.xml');
        $dom = $styles ?? new DOMDocument();
        $this->sx = new DOMXPath($dom);
        $this->sx->registerNamespace('w', self::W);
        $this->hasStyles = $styles !== null;
        if ($this->hasStyles) {
            $dd = $this->sx->query('/w:styles/w:docDefaults')->item(0);
            if ($dd) {
                $this->docDefaults['rpr'] = $this->rprProps($this->sx->query('w:rPrDefault/w:rPr', $dd)->item(0));
                $this->docDefaults['ppr'] = $this->pprProps($this->sx->query('w:pPrDefault/w:pPr', $dd)->item(0));
            }
        }
    }

    /**
     * @param  list<DOMElement>  $bodyChildren  üst kısım (soru bölgesinden önceki gövde öğeleri)
     * @param  ?string  $headerPart  sayfa üst bilgi parçası (ör. word/header1.xml) ya da null
     */
    public function render(array $bodyChildren, ?string $headerPart = null): string
    {
        $out = '';
        if ($headerPart !== null && ($d = $this->loadXml($headerPart))) {
            $this->rels = $this->loadRels($headerPart);
            $xp = $this->xp($d);
            foreach ($xp->query('/w:hdr/*') as $el) {
                $out .= $this->block($el, $xp);
            }
        }
        $this->rels = $this->loadRels('word/document.xml');
        foreach ($bodyChildren as $el) {
            $out .= $this->block($el, $this->xp($el->ownerDocument));
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */

    private function loadXml(string $name): ?DOMDocument
    {
        $s = $this->zip->getFromName($name);
        if (! is_string($s) || strlen($s) > 20 * 1024 * 1024) {
            return null;
        }
        $d = new DOMDocument();

        return @$d->loadXML($s, LIBXML_NONET) ? $d : null;
    }

    private function loadRels(string $part): array
    {
        $relName = dirname($part).'/_rels/'.basename($part).'.rels';
        $d = $this->loadXml($relName);
        $map = [];
        if ($d) {
            foreach ($d->getElementsByTagName('Relationship') as $r) {
                $map[$r->getAttribute('Id')] = ['target' => $r->getAttribute('Target'), 'base' => dirname($part)];
            }
        }

        return $map;
    }

    private array $xpCache = [];

    private function xp(DOMDocument $d): DOMXPath
    {
        $k = spl_object_id($d);

        return $this->xpCache[$k] ??= tap(new DOMXPath($d), function (DOMXPath $x): void {
            $x->registerNamespace('w', self::W);
            $x->registerNamespace('wp', self::WP);
            $x->registerNamespace('a', self::A);
            $x->registerNamespace('r', self::R);
            $x->registerNamespace('mc', self::MC);
        });
    }

    private function attr(?DOMElement $e, string $name): ?string
    {
        return $e && $e->hasAttributeNS(self::W, $name) ? $e->getAttributeNS(self::W, $name) : null;
    }

    private function on(?DOMElement $e): bool
    {
        if (! $e) {
            return false;
        }
        $v = $this->attr($e, 'val');

        return ! in_array($v, ['0', 'false', 'off', 'none'], true);
    }

    /* ---------------- Stil çözümleme ---------------- */

    private function rprProps(?DOMElement $rpr): array
    {
        if (! $rpr) {
            return [];
        }
        $x = $this->xp($rpr->ownerDocument);
        $p = [];
        $q = fn (string $n) => $x->query('w:'.$n, $rpr)->item(0);
        if ($q('b')) { $p['b'] = $this->on($q('b')); }
        if ($q('i')) { $p['i'] = $this->on($q('i')); }
        if ($q('strike')) { $p['strike'] = $this->on($q('strike')); }
        if ($q('caps')) { $p['caps'] = $this->on($q('caps')); }
        if ($q('smallCaps')) { $p['smallCaps'] = $this->on($q('smallCaps')); }
        if (($u = $q('u')) && $this->attr($u, 'val') !== 'none') { $p['u'] = true; }
        if (($c = $q('color')) && preg_match('/^[0-9A-Fa-f]{6}$/', (string) $this->attr($c, 'val'))) { $p['color'] = strtoupper($this->attr($c, 'val')); }
        if (($s = $q('sz')) && ctype_digit((string) $this->attr($s, 'val'))) { $p['sz'] = (int) $this->attr($s, 'val') / 2; }
        if ($f = $q('rFonts')) { $p['font'] = $this->attr($f, 'ascii') ?? $this->attr($f, 'hAnsi'); }
        if (($v = $q('vertAlign')) && in_array($this->attr($v, 'val'), ['superscript', 'subscript'], true)) { $p['va'] = $this->attr($v, 'val'); }
        if (($h = $q('shd')) && preg_match('/^[0-9A-Fa-f]{6}$/', (string) $this->attr($h, 'fill'))) { $p['bg'] = strtoupper($this->attr($h, 'fill')); }

        return $p;
    }

    private function pprProps(?DOMElement $ppr): array
    {
        if (! $ppr) {
            return [];
        }
        $x = $this->xp($ppr->ownerDocument);
        $p = [];
        if (($j = $x->query('w:jc', $ppr)->item(0)) && $this->attr($j, 'val')) {
            $p['jc'] = ['left' => 'left', 'start' => 'left', 'center' => 'center', 'right' => 'right', 'end' => 'right', 'both' => 'justify', 'distribute' => 'justify'][$this->attr($j, 'val')] ?? 'left';
        }
        if ($s = $x->query('w:spacing', $ppr)->item(0)) {
            foreach (['before', 'after'] as $k) {
                if (($v = $this->attr($s, $k)) !== null && is_numeric($v)) {
                    $p[$k] = (int) $v / 15;
                }
            }
            if (($l = $this->attr($s, 'line')) !== null && is_numeric($l)) {
                $p['line'] = (int) $l;
                $p['lineRule'] = $this->attr($s, 'lineRule') ?? 'auto';
            }
        }
        if ($i = $x->query('w:ind', $ppr)->item(0)) {
            foreach (['left' => 'indL', 'start' => 'indL', 'right' => 'indR', 'end' => 'indR', 'firstLine' => 'first', 'hanging' => 'hang'] as $k => $key) {
                if (($v = $this->attr($i, $k)) !== null && is_numeric($v)) {
                    $p[$key] = (int) $v / 15;
                }
            }
        }
        if (($h = $x->query('w:shd', $ppr)->item(0)) && preg_match('/^[0-9A-Fa-f]{6}$/', (string) $this->attr($h, 'fill'))) {
            $p['bg'] = strtoupper($this->attr($h, 'fill'));
        }
        if ($b = $x->query('w:pBdr', $ppr)->item(0)) {
            foreach (['top', 'left', 'bottom', 'right'] as $side) {
                if ($e = $x->query('w:'.$side, $b)->item(0)) {
                    $css = $this->border($e);
                    if ($css) {
                        $p['bd'][$side] = $css;
                    }
                }
            }
        }

        return $p;
    }

    /** Stil zincirini (basedOn) çözüp özellikleri birleştirir. @return array{rpr: array, ppr: array} */
    private function style(?string $id): array
    {
        if ($id === null || ! $this->hasStyles) {
            return ['rpr' => [], 'ppr' => []];
        }
        if (isset($this->styleCache[$id])) {
            return $this->styleCache[$id];
        }
        $this->styleCache[$id] = ['rpr' => [], 'ppr' => []]; // döngüye karşı
        $node = $this->sx->query('/w:styles/w:style[@w:styleId="'.preg_replace('/[^A-Za-z0-9_\-]/', '', $id).'"]')->item(0);
        if (! $node) {
            return $this->styleCache[$id];
        }
        $base = $this->attr($this->sx->query('w:basedOn', $node)->item(0), 'val');
        $parent = $this->style($base);
        $own = [
            'rpr' => $this->rprProps($this->sx->query('w:rPr', $node)->item(0)),
            'ppr' => $this->pprProps($this->sx->query('w:pPr', $node)->item(0)),
        ];

        return $this->styleCache[$id] = ['rpr' => $own['rpr'] + $parent['rpr'], 'ppr' => $own['ppr'] + $parent['ppr']];
    }

    private function border(DOMElement $e): ?string
    {
        $val = $this->attr($e, 'val');
        if ($val === null || in_array($val, ['nil', 'none'], true)) {
            return null;
        }
        $sz = is_numeric($this->attr($e, 'sz')) ? max(1, round(((int) $this->attr($e, 'sz')) / 6, 1)) : 1; // 1/8 pt -> ~px
        $color = preg_match('/^[0-9A-Fa-f]{6}$/', (string) $this->attr($e, 'color')) ? '#'.$this->attr($e, 'color') : '#000000';
        $style = in_array($val, ['dotted', 'dashed', 'double'], true) ? $val : 'solid';

        return min($sz, 6).'px '.$style.' '.$color;
    }

    /* ---------------- Blok çevirme ---------------- */

    private function block(DOMElement $el, DOMXPath $x): string
    {
        switch ($el->localName) {
            case 'p':
                return $this->paragraph($el, $x);
            case 'tbl':
                return $this->table($el, $x);
            case 'sdt':
                $out = '';
                foreach ($x->query('w:sdtContent/*', $el) as $c) {
                    $out .= $this->block($c, $x);
                }

                return $out;
            default:
                return '';
        }
    }

    private function paragraph(DOMElement $p, DOMXPath $x): string
    {
        $style = $this->style($this->attr($x->query('w:pPr/w:pStyle', $p)->item(0), 'val'));
        $pp = $this->pprProps($x->query('w:pPr', $p)->item(0)) + $style['ppr'] + $this->docDefaults['ppr'];
        $pr = $style['rpr'] + $this->docDefaults['rpr'];

        $runs = '';
        foreach ($p->childNodes as $c) {
            if (! $c instanceof DOMElement) {
                continue;
            }
            $runs .= match ($c->localName) {
                'r' => $this->run($c, $x, $pr),
                'hyperlink', 'smartTag', 'fldSimple' => $this->runsIn($c, $x, $pr),
                'sdt' => $this->runsIn($x->query('w:sdtContent', $c)->item(0) ?? $c, $x, $pr),
                default => '',
            };
        }

        $css = [];
        if (isset($pp['jc'])) { $css[] = 'text-align:'.$pp['jc']; }
        $css[] = 'margin:'.round($pp['before'] ?? 0).'px 0 '.round($pp['after'] ?? 0).'px';
        if (($pp['indL'] ?? 0) > 0) { $css[] = 'padding-left:'.round($pp['indL']).'px'; }
        if (($pp['indR'] ?? 0) > 0) { $css[] = 'padding-right:'.round($pp['indR']).'px'; }
        $firstLine = ($pp['first'] ?? 0) - ($pp['hang'] ?? 0);
        if (abs($firstLine) >= 1) { $css[] = 'text-indent:'.round($firstLine).'px'; }
        if (isset($pp['line'])) {
            $css[] = $pp['lineRule'] === 'exact' || $pp['lineRule'] === 'atLeast' ? 'line-height:'.round($pp['line'] / 15).'px' : 'line-height:'.round($pp['line'] / 240 * 1.17, 2);
        }
        if (isset($pp['bg'])) { $css[] = 'background-color:#'.$pp['bg']; }
        foreach ($pp['bd'] ?? [] as $side => $b) { $css[] = 'border-'.$side.':'.$b; }
        $size = $pr['sz'] ?? 11;
        $css[] = 'font-size:'.$size.'pt';
        $cls = $this->fontClass($pr['font'] ?? null);

        if (trim(strip_tags($runs)) === '' && ! str_contains($runs, '<img')) {
            return '<div class="'.$cls.'" style="'.implode(';', $css).';height:'.round($size * 1.333 * 1.2).'px">&nbsp;</div>';
        }

        return '<div class="'.$cls.'" style="'.implode(';', $css).'">'.$runs.'</div>';
    }

    private function runsIn(DOMElement $parent, DOMXPath $x, array $pr): string
    {
        $out = '';
        foreach ($parent->childNodes as $c) {
            if ($c instanceof DOMElement && $c->localName === 'r') {
                $out .= $this->run($c, $x, $pr);
            }
        }

        return $out;
    }

    private function run(DOMElement $r, DOMXPath $x, array $paraRpr): string
    {
        $cs = $this->style($this->attr($x->query('w:rPr/w:rStyle', $r)->item(0), 'val'));
        $rp = $this->rprProps($x->query('w:rPr', $r)->item(0)) + $cs['rpr'] + $paraRpr;

        $inner = '';
        foreach ($r->childNodes as $c) {
            if (! $c instanceof DOMElement) {
                continue;
            }
            switch ($c->localName) {
                case 't':
                    $inner .= htmlspecialchars($c->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    break;
                case 'tab':
                    $inner .= '&emsp;&emsp;';
                    break;
                case 'br':
                case 'cr':
                    $inner .= '<br>';
                    break;
                case 'drawing':
                    $inner .= $this->drawing($c, $x);
                    break;
                case 'AlternateContent':
                    $inner .= $this->alternate($c, $x);
                    break;
                case 'pict':
                    $inner .= $this->textbox($c, $x);
                    break;
            }
        }
        if ($inner === '') {
            return '';
        }
        if (trim(html_entity_decode(strip_tags($inner))) === '') {
            return $inner; // yalnızca resim / satır sonu: metin biçimi gerekmez
        }

        $css = [];
        $paraSz = $paraRpr['sz'] ?? 11;
        if (isset($rp['sz']) && $rp['sz'] != $paraSz) { $css[] = 'font-size:'.$rp['sz'].'pt'; }
        if (! empty($rp['b'])) { $css[] = 'font-weight:bold'; }
        if (! empty($rp['i'])) { $css[] = 'font-style:italic'; }
        $deco = array_filter([! empty($rp['u']) ? 'underline' : null, ! empty($rp['strike']) ? 'line-through' : null]);
        if ($deco) { $css[] = 'text-decoration:'.implode(' ', $deco); }
        if (isset($rp['color']) && $rp['color'] !== '000000') { $css[] = 'color:#'.$rp['color']; }
        if (! empty($rp['caps'])) { $css[] = 'text-transform:uppercase'; }
        if (! empty($rp['smallCaps'])) { $css[] = 'font-variant:small-caps'; }
        if (isset($rp['bg'])) { $css[] = 'background-color:#'.$rp['bg']; }
        if (($rp['va'] ?? '') === 'superscript') { $css[] = 'vertical-align:super;font-size:0.75em'; }
        if (($rp['va'] ?? '') === 'subscript') { $css[] = 'vertical-align:sub;font-size:0.75em'; }

        return $css ? '<span style="'.implode(';', $css).'">'.$inner.'</span>' : $inner;
    }

    private function fontClass(?string $font): string
    {
        $f = preg_replace('/[^A-Za-z0-9 \-]/', '', (string) $font) ?? '';

        return preg_match('/times|georgia|garamond|cambria|book antiqua|palatino|serif|minion/i', $f) && ! preg_match('/sans/i', $f) ? 'wf-serif' : 'wf-sans';
    }

    /* ---------------- Resim / metin kutusu ---------------- */

    private function drawing(DOMElement $d, DOMXPath $x): string
    {
        $ext = $x->query('.//wp:extent', $d)->item(0);
        $blip = $x->query('.//a:blip', $d)->item(0);
        if (! $blip) {
            return '';
        }
        $rid = $blip->getAttributeNS(self::R, 'embed');
        $uri = $this->imageUri($rid);
        if ($uri === null) {
            return '';
        }
        $w = $ext ? (int) $ext->getAttribute('cx') / 9525 : 0;
        $h = $ext ? (int) $ext->getAttribute('cy') / 9525 : 0;
        if ($w <= 0 || $h <= 0) {
            return '<img src="'.$uri.'" alt="" style="max-width:100%">';
        }
        if ($w > $this->contentPx) {
            $h = $h * $this->contentPx / $w;
            $w = $this->contentPx;
        }

        return '<img src="'.$uri.'" alt="" width="'.round($w).'" height="'.round($h).'" style="width:'.round($w).'px;height:'.round($h).'px">';
    }

    private function imageUri(string $rid): ?string
    {
        $rel = $this->rels[$rid] ?? null;
        if (! $rel) {
            return null;
        }
        $target = $rel['target'];
        $path = str_starts_with($target, '/') ? ltrim($target, '/') : $rel['base'].'/'.$target;
        $path = preg_replace('#/[^/]+/\.\./#', '/', $path) ?? $path;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif'][$ext] ?? null;
        if ($mime === null) {
            return null; // emf/wmf/svg gibi biçimler tarayıcıda/PDF'te çizilemez
        }
        $bin = $this->zip->getFromName($path);
        if (! is_string($bin) || $bin === '' || strlen($bin) > $this->imgBudget) {
            return null;
        }
        $this->imgBudget -= strlen($bin);

        return 'data:'.$mime.';base64,'.base64_encode($bin);
    }

    /** mc:AlternateContent: yalnızca ilk geçerli dal (Choice, yoksa Fallback) işlenir. */
    private function alternate(DOMElement $ac, DOMXPath $x): string
    {
        $branch = $x->query('mc:Choice', $ac)->item(0) ?? $x->query('mc:Fallback', $ac)->item(0);
        if (! $branch) {
            return '';
        }
        $out = '';
        foreach ($x->query('.//w:drawing', $branch) as $d) {
            $out .= $this->drawing($d, $x);
        }
        if ($out === '') {
            $out = $this->textbox($branch, $x);
        }

        return $out;
    }

    /** Metin kutusu içeriğini (w:txbxContent) bloğa çevirir. */
    private function textbox(DOMElement $scope, DOMXPath $x): string
    {
        $tx = $x->query('.//w:txbxContent', $scope)->item(0);
        if (! $tx) {
            return '';
        }
        $inner = '';
        foreach ($x->query('*', $tx) as $c) {
            $inner .= $this->block($c, $x);
        }

        return $inner !== '' ? '<div style="display:block;border:1px solid #94a3b8;padding:2px 6px;margin:2px 0">'.$inner.'</div>' : '';
    }

    /* ---------------- Tablo ---------------- */

    private function table(DOMElement $t, DOMXPath $x): string
    {
        $grid = [];
        foreach ($x->query('w:tblGrid/w:gridCol', $t) as $g) {
            $grid[] = max(1, (int) $this->attr($g, 'w') / 15);
        }
        $total = array_sum($grid) ?: 1;
        $scale = $total > $this->contentPx ? $this->contentPx / $total : 1.0;

        // tablo çerçeveleri (doğrudan tblBorders; "TableGrid" stili için tüm kenarlıklar)
        $tb = [];
        $tblPr = $x->query('w:tblPr', $t)->item(0);
        if ($tblPr) {
            foreach (['top', 'left', 'bottom', 'right', 'insideH', 'insideV'] as $side) {
                if ($e = $x->query('w:tblBorders/w:'.$side, $tblPr)->item(0)) {
                    $tb[$side] = $this->border($e);
                }
            }
            $styleId = $this->attr($x->query('w:tblStyle', $tblPr)->item(0), 'val');
            if ($tb === [] && $styleId && preg_match('/grid/i', $styleId)) {
                $tb = array_fill_keys(['top', 'left', 'bottom', 'right', 'insideH', 'insideV'], '1px solid #000000');
            }
        }

        $rows = $x->query('w:tr', $t);
        $rowCount = $rows->length;
        $html = '<table style="border-collapse:collapse;width:'.round(min($total, $this->contentPx)).'px"><colgroup>';
        foreach ($grid as $w) {
            $html .= '<col style="width:'.round($w * $scale).'px">';
        }
        $html .= '</colgroup>';

        // dikey birleştirme (rowspan) için satır/sütun izleme
        $vspan = [];
        foreach ($rows as $ri => $tr) {
            $html .= '<tr>';
            $col = 0;
            foreach ($x->query('w:tc', $tr) as $tc) {
                $tcPr = $x->query('w:tcPr', $tc)->item(0);
                $span = $tcPr && ($gs = $x->query('w:gridSpan', $tcPr)->item(0)) ? max(1, (int) $this->attr($gs, 'val')) : 1;
                $vm = $tcPr ? $x->query('w:vMerge', $tcPr)->item(0) : null;
                $isCont = $vm && $this->attr($vm, 'val') !== 'restart';
                $startCol = $col;
                $col += $span;
                if ($isCont) {
                    continue;
                }
                $rowspan = 1;
                if ($vm) {
                    for ($k = $ri + 1; $k < $rowCount; $k++) {
                        $found = false;
                        $c2 = 0;
                        foreach ($x->query('w:tc', $rows->item($k)) as $tc2) {
                            $p2 = $x->query('w:tcPr', $tc2)->item(0);
                            $sp2 = $p2 && ($g2 = $x->query('w:gridSpan', $p2)->item(0)) ? max(1, (int) $this->attr($g2, 'val')) : 1;
                            if ($c2 === $startCol) {
                                $v2 = $p2 ? $x->query('w:vMerge', $p2)->item(0) : null;
                                $found = $v2 && $this->attr($v2, 'val') !== 'restart';
                                break;
                            }
                            $c2 += $sp2;
                        }
                        if (! $found) {
                            break;
                        }
                        $rowspan++;
                    }
                }
                $cellW = array_sum(array_slice($grid, $startCol, $span)) * $scale;
                $css = ['width:'.round($cellW).'px', 'vertical-align:'.$this->vAlign($tcPr, $x), 'padding:2px 5px', 'overflow:hidden', 'word-wrap:break-word'];
                // kenarlıklar: hücre kendi kenarlığı > tablo dış/iç çerçevesi
                foreach (['top', 'left', 'bottom', 'right'] as $side) {
                    $own = $tcPr ? $x->query('w:tcBorders/w:'.$side, $tcPr)->item(0) : null;
                    if ($own) {
                        $b = $this->border($own);
                    } else {
                        $outer = ($side === 'top' && $ri === 0) || ($side === 'bottom' && $ri + $rowspan >= $rowCount) || ($side === 'left' && $startCol === 0) || ($side === 'right' && $col >= count($grid));
                        $b = $tb[$outer ? $side : (in_array($side, ['top', 'bottom'], true) ? 'insideH' : 'insideV')] ?? null;
                    }
                    if ($b) {
                        $css[] = 'border-'.$side.':'.$b;
                    }
                }
                if ($tcPr && ($sh = $x->query('w:shd', $tcPr)->item(0)) && preg_match('/^[0-9A-Fa-f]{6}$/', (string) $this->attr($sh, 'fill'))) {
                    $css[] = 'background-color:#'.$this->attr($sh, 'fill');
                }
                $cell = '';
                foreach ($x->query('*', $tc) as $c) {
                    $cell .= $this->block($c, $x);
                }
                $html .= '<td'.($span > 1 ? ' colspan="'.$span.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').' style="'.implode(';', $css).'">'.($cell !== '' ? $cell : '&nbsp;').'</td>';
            }
            $html .= '</tr>';
        }

        return $html.'</table>';
    }

    private function vAlign(?DOMElement $tcPr, DOMXPath $x): string
    {
        $v = $tcPr ? $this->attr($x->query('w:vAlign', $tcPr)->item(0), 'val') : null;

        return ['center' => 'middle', 'bottom' => 'bottom'][$v] ?? 'top';
    }
}
