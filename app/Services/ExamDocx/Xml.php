<?php

namespace App\Services\ExamDocx;

/**
 * WordprocessingML üretimi için küçük yardımcılar. Öğelerin sırası OOXML şemasına uygundur
 * (pPr: keepNext, keepLines, pBdr, shd, tabs, spacing, ind, jc, sectPr; rPr: rFonts, b, i, caps, color, sz).
 */
class Xml
{
    public const W_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public static function esc(string $s): string
    {
        // XML 1.0'da geçersiz denetim karakterlerini at
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s) ?? $s;

        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** Düz metin çalıştırması; satır sonları <w:br/> olur. */
    public static function run(string $text, array $o = []): string
    {
        $rpr = '';
        if (! empty($o['font'])) {
            $f = self::esc($o['font']);
            $rpr .= '<w:rFonts w:ascii="'.$f.'" w:hAnsi="'.$f.'" w:cs="'.$f.'" w:eastAsia="'.$f.'"/>';
        }
        if (! empty($o['b'])) {
            $rpr .= '<w:b/><w:bCs/>';
        }
        if (! empty($o['i'])) {
            $rpr .= '<w:i/><w:iCs/>';
        }
        if (! empty($o['caps'])) {
            $rpr .= '<w:caps/>';
        }
        if (! empty($o['color'])) {
            $rpr .= '<w:color w:val="'.self::hex($o['color']).'"/>';
        }
        if (! empty($o['sz'])) {
            $rpr .= '<w:sz w:val="'.(int) $o['sz'].'"/><w:szCs w:val="'.(int) $o['sz'].'"/>';
        }
        $rpr = $rpr !== '' ? '<w:rPr>'.$rpr.'</w:rPr>' : '';

        $parts = preg_split('/\R/u', $text) ?: [''];
        $out = '';
        foreach ($parts as $k => $part) {
            if ($k > 0) {
                $out .= '<w:br/>';
            }
            // Sekmeleri gerçek sekme öğesine çevir
            $segs = explode("\t", $part);
            foreach ($segs as $j => $seg) {
                if ($j > 0) {
                    $out .= '<w:tab/>';
                }
                if ($seg !== '') {
                    $out .= '<w:t xml:space="preserve">'.self::esc($seg).'</w:t>';
                }
            }
        }

        return '<w:r>'.$rpr.$out.'</w:r>';
    }

    public static function tab(): string
    {
        return '<w:r><w:tab/></w:r>';
    }

    /** Alan (sayfa numarası vb.). */
    public static function field(string $instr, string $placeholder, array $o = []): string
    {
        return '<w:fldSimple w:instr=" '.self::esc($instr).' ">'.self::run($placeholder, $o).'</w:fldSimple>';
    }

    /**
     * Paragraf.
     * $o: jc, before, after, line (tam yükseklik, twips), lineAuto (240=1 satır), keepNext, keepLines, shd (dolgu),
     *     borders [side => [val, sz, color, space]], ind [left, right, hanging], tabs [pos...], sect (sectPr xml)
     */
    public static function para(array $runs, array $o = []): string
    {
        $ppr = '';
        if (! empty($o['keepNext'])) {
            $ppr .= '<w:keepNext/>';
        }
        if (! empty($o['keepLines'])) {
            $ppr .= '<w:keepLines/>';
        }
        if (! empty($o['borders'])) {
            $b = '';
            foreach (['top', 'left', 'bottom', 'right', 'between'] as $side) {
                if (isset($o['borders'][$side])) {
                    [$val, $sz, $color, $space] = $o['borders'][$side] + [3 => 1];
                    $b .= '<w:'.$side.' w:val="'.$val.'" w:sz="'.(int) $sz.'" w:space="'.(int) $space.'" w:color="'.self::hex($color).'"/>';
                }
            }
            $ppr .= '<w:pBdr>'.$b.'</w:pBdr>';
        }
        if (! empty($o['shd'])) {
            $ppr .= '<w:shd w:val="clear" w:color="auto" w:fill="'.self::hex($o['shd']).'"/>';
        }
        if (! empty($o['tabs'])) {
            $ppr .= '<w:tabs>'.implode('', array_map(static fn ($p) => '<w:tab w:val="left" w:pos="'.(int) $p.'"/>', $o['tabs'])).'</w:tabs>';
        }
        if (isset($o['before']) || isset($o['after']) || isset($o['line']) || isset($o['lineAuto'])) {
            $sp = '';
            if (isset($o['before'])) {
                $sp .= ' w:before="'.(int) $o['before'].'"';
            }
            if (isset($o['after'])) {
                $sp .= ' w:after="'.(int) $o['after'].'"';
            }
            if (isset($o['line'])) {
                $sp .= ' w:line="'.(int) $o['line'].'" w:lineRule="exact"';
            } elseif (isset($o['lineAuto'])) {
                $sp .= ' w:line="'.(int) $o['lineAuto'].'" w:lineRule="auto"';
            }
            $ppr .= '<w:spacing'.$sp.'/>';
        }
        if (! empty($o['ind'])) {
            $i = $o['ind'];
            $ppr .= '<w:ind'
                .(isset($i['left']) ? ' w:left="'.(int) $i['left'].'"' : '')
                .(isset($i['right']) ? ' w:right="'.(int) $i['right'].'"' : '')
                .(isset($i['hanging']) ? ' w:hanging="'.(int) $i['hanging'].'"' : '')
                .'/>';
        }
        if (! empty($o['jc'])) {
            $ppr .= '<w:jc w:val="'.$o['jc'].'"/>';
        }
        if (! empty($o['sect'])) {
            $ppr .= $o['sect'];
        }

        return '<w:p>'.($ppr !== '' ? '<w:pPr>'.$ppr.'</w:pPr>' : '').implode('', $runs).'</w:p>';
    }

    /**
     * Tablo. $rows: [ [ hücre, ... ], ... ]; hücre = ['xml' => paragraflar, 'borders' => [...], 'shd' => 'hex', 'vAlign' => 'center']
     * $o: borders (tablo çerçevesi: top,left,bottom,right,insideH,insideV => [val,sz,color]), ind (twips), rowHeight (en az, twips)
     */
    public static function table(array $rows, array $widths, array $o = []): string
    {
        $total = array_sum($widths);
        $tblPr = '<w:tblW w:w="'.$total.'" w:type="dxa"/>';
        if (! empty($o['ind'])) {
            $tblPr .= '<w:tblInd w:w="'.(int) $o['ind'].'" w:type="dxa"/>';
        }
        $tb = '';
        foreach (['top', 'left', 'bottom', 'right', 'insideH', 'insideV'] as $side) {
            [$val, $sz, $color] = $o['borders'][$side] ?? ['nil', 0, '000000'];
            $tb .= $val === 'nil'
                ? '<w:'.$side.' w:val="nil"/>'
                : '<w:'.$side.' w:val="'.$val.'" w:sz="'.(int) $sz.'" w:space="0" w:color="'.self::hex($color).'"/>';
        }
        $tblPr .= '<w:tblBorders>'.$tb.'</w:tblBorders><w:tblLayout w:type="fixed"/>'
            .'<w:tblCellMar><w:left w:w="60" w:type="dxa"/><w:right w:w="60" w:type="dxa"/></w:tblCellMar>';

        $grid = '<w:tblGrid>'.implode('', array_map(static fn ($w) => '<w:gridCol w:w="'.(int) $w.'"/>', $widths)).'</w:tblGrid>';

        $trs = '';
        foreach ($rows as $row) {
            // Satır ['cells' => [...], 'h' => twips] biçiminde de verilebilir (satıra özel yükseklik)
            $rowH = $o['rowHeight'] ?? null;
            if (isset($row['cells'])) {
                $rowH = $row['h'] ?? $rowH;
                $row = $row['cells'];
            }
            $trPr = '<w:cantSplit/>'.(! empty($rowH) ? '<w:trHeight w:val="'.(int) $rowH.'" w:hRule="atLeast"/>' : '');
            $tcs = '';
            $gi = 0; // ızgara sütun göstergesi
            foreach ($row as $cell) {
                $span = max(1, (int) ($cell['span'] ?? 1));
                $cw = array_sum(array_slice($widths, $gi, $span));
                $gi += $span;
                $tcPr = '<w:tcW w:w="'.(int) $cw.'" w:type="dxa"/>'.($span > 1 ? '<w:gridSpan w:val="'.$span.'"/>' : '');
                if (! empty($cell['borders'])) {
                    $cb = '';
                    foreach (['top', 'left', 'bottom', 'right'] as $side) {
                        if (isset($cell['borders'][$side])) {
                            [$val, $sz, $color] = $cell['borders'][$side];
                            $cb .= '<w:'.$side.' w:val="'.$val.'" w:sz="'.(int) $sz.'" w:space="0" w:color="'.self::hex($color).'"/>';
                        }
                    }
                    $tcPr .= '<w:tcBorders>'.$cb.'</w:tcBorders>';
                }
                if (! empty($cell['shd'])) {
                    $tcPr .= '<w:shd w:val="clear" w:color="auto" w:fill="'.self::hex($cell['shd']).'"/>';
                }
                if (! empty($cell['vAlign'])) {
                    $tcPr .= '<w:vAlign w:val="'.$cell['vAlign'].'"/>';
                }
                // Hücre en az bir paragraf içermeli
                $content = $cell['xml'] !== '' ? $cell['xml'] : '<w:p/>';
                $tcs .= '<w:tc><w:tcPr>'.$tcPr.'</w:tcPr>'.$content.'</w:tc>';
            }
            $trs .= '<w:tr><w:trPr>'.$trPr.'</w:trPr>'.$tcs.'</w:tr>';
        }

        return '<w:tbl><w:tblPr>'.$tblPr.'</w:tblPr>'.$grid.$trs.'</w:tbl>';
    }

    public static function hex(string $c): string
    {
        $c = ltrim($c, '#');

        return preg_match('/^[0-9a-fA-F]{6}$/', $c) ? strtoupper($c) : '000000';
    }
}
