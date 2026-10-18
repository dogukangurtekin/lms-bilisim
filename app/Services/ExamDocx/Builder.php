<?php

namespace App\Services\ExamDocx;

use App\Models\Exam;
use ZipArchive;

/**
 * Sınavı Word (.docx) olarak üretir.
 * - Şablonsuz: sıfırdan paket (A4, 1/2 sütun, başlık, soru blokları, sayfa numarası, filigran, logo).
 * - Word şablonlu: yüklenen kâğıdın başlık/alt bilgi/stilleri korunur, sorular şablonun soru bölümüne yazılır.
 */
class Builder
{
    private const NS_DECL = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
        .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
        .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
        .'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
        .'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture" '
        .'xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"';

    private const LETTERS = 'ABCDEFGHIJ';
    private const TYPE_NAME = ['multiple_choice' => 'Çoktan seçmeli', 'true_false' => 'Doğru/Yanlış', 'fill_blank' => 'Boşluk doldurma', 'open_ended' => 'Açık uçlu', 'matching' => 'Eşleştirme'];

    private array $s;
    private int $contentTw;
    private int $marginTw;
    private bool $templated = false;
    /** @var list<array{rid: string, ext: string, bytes: string}> */
    private array $images = [];

    public function __construct(private Exam $exam, private array $qs, array $settings, private string $mode)
    {
        $this->s = $settings;
        $this->marginTw = (int) round(((int) $settings['margin_mm']) * 56.7);
        $this->contentTw = 11906 - 2 * $this->marginTw;
    }

    /** Şablonsuz paket. */
    public function build(): string
    {
        $isTable = $this->exam->template === 'table';
        $withLogo = $isTable ? $this->logoData('logo_path', false, 78) : $this->logoData();
        $withLogo2 = $isTable ? $this->logoData('logo2_path', false, 78) : null;
        $files = [];

        $body = '';
        $columns = (int) $this->exam->columns === 2 ? 2 : 1;
        $withKey = $this->mode === 'student_key';
        $hasWatermark = trim((string) ($this->s['watermark'] ?? '')) !== '';

        $refs = ['footer' => ! empty($this->s['page_numbers']), 'header' => $hasWatermark];

        if ($this->mode === 'key') {
            $body .= $this->keyXml(true);
            $sect = $this->sectPr(1, false, $refs);
        } else {
            $body .= $this->headerBlock($withLogo, $withLogo2);
            // Başlık bölümü (tek sütun) burada biter
            $body .= Xml::para([], ['after' => 0, 'before' => 0, 'line' => 20, 'sect' => $this->sectPr(1, false, $refs)]);
            $questions = $this->questionsXml($columns);
            if ($withKey) {
                $body .= $questions;
                $body .= Xml::para([], ['after' => 0, 'before' => 0, 'line' => 20, 'sect' => $this->sectPr($columns, true, [])]);
                $body .= $this->keyXml(false);
                $sect = $this->sectPr(1, false, []);
            } else {
                $body .= $questions;
                $sect = $this->sectPr($columns, true, []);
            }
        }

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document '.self::NS_DECL.'><w:body>'.$body.$sect.'</w:body></w:document>';

        $rels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>';
        $overrides = '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>';

        if ($refs['footer']) {
            $rels .= '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>';
            $overrides .= '<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>';
            $files['word/footer1.xml'] = $this->footerXml();
        }
        if ($refs['header']) {
            $rels .= '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>';
            $overrides .= '<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>';
            $files['word/header1.xml'] = $this->watermarkHeaderXml((string) $this->s['watermark']);
        }
        foreach ($this->images as $k => $img) {
            $rels .= '<Relationship Id="'.$img['rid'].'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/q'.($k + 1).'.'.$img['ext'].'"/>';
            $files['word/media/q'.($k + 1).'.'.$img['ext']] = $img['bytes'];
        }
        if ($withLogo) {
            $rels .= '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo.png"/>';
            $files['word/media/logo.png'] = $withLogo['png'];
        }
        if ($withLogo2) {
            $rels .= '<Relationship Id="rId6" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo2.png"/>';
            $files['word/media/logo2.png'] = $withLogo2['png'];
        }

        $files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Default Extension="jpg" ContentType="image/jpeg"/>'
            .$overrides.'</Types>';
        $files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>';
        $files['word/_rels/document.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$rels.'</Relationships>';
        $files['word/document.xml'] = $document;
        $files['word/styles.xml'] = $this->stylesXml();
        $files['word/settings.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:settings xmlns:w="'.Xml::W_NS.'"><w:defaultTabStop w:val="720"/></w:settings>';

        return $this->zip($files);
    }

    /** Word şablonlu çıktı: şablonun soru bölümünü bizim sorularımızla değiştirir. */
    public function buildFromTemplate(string $templatePath): string
    {
        $this->templated = true;
        $columns = (int) $this->exam->columns === 2 ? 2 : 1;
        $tpl = new Template($templatePath);
        $questions = $this->mode === 'key' ? $this->keyXml(true) : $this->questionsXml($columns);
        $tail = ($this->mode === 'student_key') ? $this->keyXml(false) : '';

        return $tpl->render($questions, $this->mode === 'key' ? 1 : $columns, $tail, $this->images);
    }

    /* ------------------------------------------------------------------ */
    /*  Bölüm / sayfa yapısı                                               */
    /* ------------------------------------------------------------------ */

    private function sectPr(int $cols, bool $continuous, array $refs): string
    {
        $x = '<w:sectPr>';
        if (! empty($refs['header'])) {
            $x .= '<w:headerReference w:type="default" r:id="rId4"/>';
        }
        if (! empty($refs['footer'])) {
            $x .= '<w:footerReference w:type="default" r:id="rId3"/>';
        }
        if ($continuous) {
            $x .= '<w:type w:val="continuous"/>';
        }
        $m = $this->marginTw;
        $x .= '<w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="'.$m.'" w:right="'.$m.'" w:bottom="'.$m.'" w:left="'.$m.'" w:header="360" w:footer="360" w:gutter="0"/>';
        $x .= $this->pgBorders();
        $x .= $cols === 2 ? '<w:cols w:num="2" w:space="360" w:sep="1"/>' : '<w:cols w:space="720"/>';

        return $x.'</w:sectPr>';
    }

    private function stylesXml(): string
    {
        $font = $this->font();
        $sz = ((int) $this->s['font_size']) * 2;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="'.Xml::W_NS.'">'
            .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="'.$font.'" w:hAnsi="'.$font.'" w:cs="'.$font.'" w:eastAsia="'.$font.'"/>'
            .'<w:sz w:val="'.$sz.'"/><w:szCs w:val="'.$sz.'"/><w:lang w:val="tr-TR" w:eastAsia="tr-TR" w:bidi="ar-SA"/></w:rPr></w:rPrDefault>'
            .'<w:pPrDefault><w:pPr><w:spacing w:after="0" w:line="324" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>'
            .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/></w:style>'
            .'<w:style w:type="character" w:default="1" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/><w:uiPriority w:val="1"/><w:semiHidden/></w:style>'
            .'<w:style w:type="table" w:default="1" w:styleId="TableNormal"><w:name w:val="Normal Table"/><w:uiPriority w:val="99"/><w:semiHidden/>'
            .'<w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style>'
            .'</w:styles>';
    }

    /** Seçilen yazı tipinin Word'deki karşılığı (önizleme/PDF aynı ölçülü uyumlu fontu kullanır). */
    private function font(): string
    {
        return ['serif' => 'Times New Roman', 'sans' => 'Arial', 'times' => 'Times New Roman', 'arial' => 'Arial', 'calibri' => 'Calibri', 'cambria' => 'Cambria', 'georgia' => 'Georgia'][$this->s['font_family'] ?? 'serif'] ?? 'Times New Roman';
    }

    /** Sayfa çerçevesi (Tablolu şablonda başlık tablosuyla hizalı; diğerlerinde kenardan içeride). */
    private function pgBorders(): string
    {
        if (empty($this->s['page_border'])) {
            return '';
        }
        if ($this->exam->template === 'table') {
            $side = fn (string $n) => '<w:'.$n.' w:val="single" w:sz="16" w:space="6" w:color="000000"/>';

            return '<w:pgBorders w:offsetFrom="text">'.$side('top').$side('left').$side('bottom').$side('right').'</w:pgBorders>';
        }
        $space = max(4, min(31, (int) round($this->marginTw / 20 / 2)));
        $c = Xml::hex($this->s['accent']);
        $side = fn (string $n) => '<w:'.$n.' w:val="single" w:sz="12" w:space="'.$space.'" w:color="'.$c.'"/>';

        return '<w:pgBorders w:offsetFrom="page">'.$side('top').$side('left').$side('bottom').$side('right').'</w:pgBorders>';
    }

    private function footerXml(): string
    {
        $o = ['sz' => 17, 'color' => '64748B'];

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:ftr '.self::NS_DECL.'>'
            .Xml::para([Xml::run('Sayfa ', $o), Xml::field('PAGE', '1', $o), Xml::run(' / ', $o), Xml::field('NUMPAGES', '1', $o)], ['jc' => 'center'])
            .'</w:ftr>';
    }

    private function watermarkHeaderXml(string $text): string
    {
        $t = Xml::esc(mb_strtoupper($text, 'UTF-8'));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:hdr '.self::NS_DECL.'><w:p><w:pPr><w:spacing w:after="0"/></w:pPr><w:r><w:rPr><w:noProof/></w:rPr><w:pict>'
            .'<v:shapetype id="_x0000_t136" coordsize="21600,21600" o:spt="136" adj="10800" path="m@7,l@8,m@5,21600l@6,21600e"><v:formulas>'
            .'<v:f eqn="sum #0 0 10800"/><v:f eqn="prod #0 2 1"/><v:f eqn="sum 21600 0 @1"/><v:f eqn="sum 0 0 @2"/><v:f eqn="sum 21600 0 @3"/><v:f eqn="if @0 @3 0"/>'
            .'<v:f eqn="if @0 21600 @1"/><v:f eqn="if @0 0 @2"/><v:f eqn="if @0 @4 21600"/><v:f eqn="mid @5 @6"/><v:f eqn="mid @8 @5"/><v:f eqn="mid @7 @8"/><v:f eqn="mid @6 @7"/><v:f eqn="sum @6 0 @5"/></v:formulas>'
            .'<v:path textpathok="t" o:connecttype="custom" o:connectlocs="@9,0;@10,10800;@11,21600;@12,10800" o:connectangles="270,180,90,0"/><v:textpath on="t" fitshape="t"/>'
            .'<v:handles><v:h position="#0,bottomRight" xrange="6629,14971"/></v:handles><o:lock v:ext="edit" text="t" shapetype="t"/></v:shapetype>'
            .'<v:shape id="PowerPlusWaterMarkObject1" o:spid="_x0000_s2049" type="#_x0000_t136" style="position:absolute;margin-left:0;margin-top:0;width:430pt;height:107pt;rotation:315;z-index:-251658752;mso-position-horizontal:center;mso-position-horizontal-relative:margin;mso-position-vertical:center;mso-position-vertical-relative:margin" o:allowincell="f" fillcolor="silver" stroked="f">'
            .'<v:fill opacity=".35"/><v:textpath style="font-family:&quot;Arial&quot;;font-size:1pt" string="'.$t.'"/></v:shape></w:pict></w:r></w:p></w:hdr>';
    }

    /* ------------------------------------------------------------------ */
    /*  Başlık bloğu                                                       */
    /* ------------------------------------------------------------------ */

    private function logoData(string $key = 'logo_path', bool $needShow = true, int $maxH = 56): ?array
    {
        if (($needShow && empty($this->s['show_logo'])) || empty($this->s[$key]) || ! is_file(public_path($this->s[$key]))) {
            return null;
        }
        $raw = (string) file_get_contents(public_path($this->s[$key]));
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }
        $img = @imagecreatefromstring($raw);
        if (! $img) {
            return null;
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);
        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();
        $w = imagesx($img);
        $h = imagesy($img);
        imagedestroy($img);
        if ($w < 1 || $h < 1) {
            return null;
        }
        $cy = $maxH * 9525;
        $cx = min((int) round($cy * $w / $h), 130 * 9525);
        $cy = (int) round($cx * $h / $w);

        return ['png' => $png, 'cx' => $cx, 'cy' => $cy];
    }

    private function logoRun(array $logo, string $rid = 'rId5', int $id = 1): string
    {
        return $this->drawingRun($rid, $logo['cx'], $logo['cy'], $id, $rid === 'rId6' ? 'logo2.png' : 'logo.png');
    }

    /** Satır içi resim (logo ve soru görselleri için ortak). */
    private function drawingRun(string $rid, int $cx, int $cy, int $id, string $name): string
    {
        return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="'.$cx.'" cy="'.$cy.'"/><wp:docPr id="'.$id.'" name="'.Xml::esc($name).'"/>'
            .'<wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            .'<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="'.Xml::esc($name).'"/><pic:cNvPicPr/></pic:nvPicPr>'
            .'<pic:blipFill><a:blip r:embed="'.$rid.'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            .'<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
    }

    private function info(): string
    {
        return collect([$this->exam->subject, $this->exam->grade_level ? $this->exam->grade_level.'. Sınıf' : null, $this->exam->class_label, $this->exam->term])->filter()->implode(' · ');
    }

    private function totalPoints(): float
    {
        return (float) collect($this->qs)->sum(fn ($q) => (float) $q['points']);
    }

    private function fmt(float|int|string $v): string
    {
        return rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    }

    private function headerBlock(?array $logo, ?array $logo2 = null): string
    {
        if ($this->exam->template === 'table') {
            return $this->tableHeaderXml($logo, $logo2);
        }
        $e = $this->exam;
        $s = $this->s;
        $ac = Xml::hex($s['accent']);
        $tpl = $e->template;
        $left = in_array($tpl, ['plain', 'quiz'], true);
        $modern = $tpl === 'modern';
        $fs = ((int) $s['font_size']) * 2;
        $txt = $modern ? 'FFFFFF' : '000000';

        $box = [];
        if ($tpl === 'classic') {
            $b = ['single', 12, $ac, 4];
            $box = ['top' => $b, 'left' => $b, 'bottom' => $b, 'right' => $b];
        }
        $base = ['jc' => $left ? 'left' : 'center', 'borders' => $box, 'keepNext' => true];
        if ($modern) {
            $base['shd'] = $ac;
            $base['ind'] = ['left' => 120, 'right' => 120];
        }

        $paras = '';
        if ($this->mode === 'teacher') {
            $paras .= Xml::para([Xml::run('ÖĞRETMEN NÜSHASI — CEVAPLI', ['b' => 1, 'color' => 'B91C1C', 'sz' => 16])], ['jc' => 'right', 'after' => 60]);
        }
        // Satırlar sırayla: yıl, okul adı, ders adı, (en sonda) dönem ve yazılı numarası
        $TL = \App\Support\ExamLayout::titleLines($e, $s);
        $textParas = '';
        if ($TL['year'] !== '') {
            $textParas .= Xml::para([Xml::run($TL['year'], ['color' => $modern ? 'E0E7FF' : '334155', 'sz' => $fs - 1])], $base + ['before' => 60, 'after' => 0]);
        }
        if ($TL['school'] !== '') {
            $textParas .= Xml::para([Xml::run($TL['school'], ['b' => 1, 'color' => $txt, 'sz' => $fs + 2])], $base + ['before' => 0, 'after' => 0]);
        }
        if ($TL['course'] !== '') {
            $textParas .= Xml::para([Xml::run($TL['course'], ['b' => 1, 'color' => $txt, 'sz' => $fs + 1])], $base + ['before' => 0, 'after' => 0]);
        }
        $lastOpts = $base;
        if ($tpl === 'quiz') {
            $lastOpts['borders'] = ['bottom' => ['single', 12, $ac, 4]];
        } elseif ($tpl === 'school') {
            $lastOpts['borders'] = ['bottom' => ['double', 12, $ac, 6]];
        }
        $textParas .= Xml::para([Xml::run($TL['last'], ['b' => 1, 'color' => $tpl === 'school' ? $ac : $txt, 'sz' => $fs + 6])], $lastOpts + ['before' => 0, 'after' => 120]);

        if ($logo) {
            $logoCell = ['xml' => Xml::para([$this->logoRun($logo)], ['jc' => 'left']), 'vAlign' => 'center'];
            $textCell = ['xml' => $textParas, 'vAlign' => 'center'];
            $paras .= Xml::table([[$logoCell, $textCell]], [1500, $this->contentTw - 1500]);
            $paras .= Xml::para([], ['before' => 0, 'after' => 0, 'line' => 20]); // iki tablo birleşmesin
        } else {
            $paras .= $textParas;
        }

        // Tarih / süre / puan
        $line = ['single', 8, $tpl === 'classic' ? $ac : '000000'];
        $mb = $tpl === 'plain' ? [] : ['top' => $line, 'bottom' => $line];
        $third = (int) floor($this->contentTw / 3);
        $metaOpt = ['sz' => $fs - 1, 'b' => 1];
        $paras .= Xml::table([[
            ['xml' => Xml::para([Xml::run('Tarih: '.($e->exam_date ? $e->exam_date->format('d.m.Y') : '…/…/……'), $metaOpt)], ['jc' => 'left', 'before' => 40, 'after' => 40]), 'borders' => $mb],
            ['xml' => Xml::para([Xml::run('Süre: '.($e->duration_min ? $e->duration_min.' dk' : '……'), $metaOpt)], ['jc' => 'center', 'before' => 40, 'after' => 40]), 'borders' => $mb],
            ['xml' => Xml::para([Xml::run('Toplam Puan: '.$this->fmt($this->totalPoints()), $metaOpt)], ['jc' => 'right', 'before' => 40, 'after' => 40]), 'borders' => $mb],
        ]], [$third, $third, $this->contentTw - 2 * $third]);

        if (! empty($s['student_box'])) {
            $bb = ['bottom' => ['single', 6, '000000']];
            $w1 = (int) round($this->contentTw * 0.4);
            $w = (int) round($this->contentTw * 0.2);
            $so = ['sz' => $fs - 1, 'b' => 1];
            $paras .= Xml::para([], ['line' => 80, 'before' => 0, 'after' => 0]);
            $paras .= Xml::table([[
                ['xml' => Xml::para([Xml::run('Adı Soyadı:', $so)], []), 'borders' => $bb, 'vAlign' => 'bottom'],
                ['xml' => Xml::para([Xml::run('Numara:', $so)], []), 'borders' => $bb, 'vAlign' => 'bottom'],
                ['xml' => Xml::para([Xml::run('Sınıf:', $so)], []), 'borders' => $bb, 'vAlign' => 'bottom'],
                ['xml' => Xml::para([Xml::run('Puan:', $so)], []), 'borders' => $bb, 'vAlign' => 'bottom'],
            ]], [$w1, $w, $w, $this->contentTw - $w1 - 2 * $w], ['rowHeight' => 460]);
        }
        $ins = trim((string) ($s['instructions'] ?? ''));
        if ($ins !== '') {
            $paras .= Xml::para([Xml::run($ins, ['i' => 1, 'color' => '334155', 'sz' => $fs - 2])], ['before' => 100, 'after' => 0]);
        }

        return $paras.Xml::para([], ['before' => 0, 'after' => 120, 'line' => 120]);
    }

    /** Tablolu (okul) başlık: logo | başlık satırları | logo ; AD SOYAD | süre | tarih | PUAN ; SINIF | NO hücreleri. */
    private function tableHeaderXml(?array $logo, ?array $logo2): string
    {
        $e = $this->exam;
        $fs = ((int) $this->s['font_size']) * 2;
        $up = fn ($t) => \App\Support\ExamLayout::upper($t);
        $pct = [12.3, 13.5, 10.5, 7.5, 21.2, 21.4, 13.6];
        // Sayfa çerçevesi açıksa tablo çerçeveye hizalanır (her yandan 6 pt = 120 twips dışarı taşar)
        $framed = ! empty($this->s['page_border']);
        $tw = $this->contentTw + ($framed ? 240 : 0);
        $w = array_map(fn ($p) => (int) round($tw * $p / 100), $pct);
        $w[6] = $tw - array_sum(array_slice($w, 0, 6));

        $TL = \App\Support\ExamLayout::titleLines($e, $this->s);
        $lines = collect([$TL['year'], $TL['school'], $TL['course'], $TL['last']])->filter(fn ($t) => $t !== '');
        $titleXml = $lines->map(fn ($t) => Xml::para([Xml::run($t, ['b' => 1, 'sz' => $fs + 2])], ['jc' => 'center', 'before' => 20, 'after' => 20]))->implode('');
        $c = fn (string $t, array $o = [], string $jc = 'left') => Xml::para([Xml::run($t, ['b' => 1] + $o)], ['jc' => $jc, 'before' => 20, 'after' => 20]);
        $logoCell = fn (?array $l, string $rid, int $id) => ['xml' => $l ? Xml::para([$this->logoRun($l, $rid, $id)], ['jc' => 'center']) : '', 'vAlign' => 'center'];
        $date = $e->exam_date ? $e->exam_date->format('d.m.Y') : '…/…/……';

        $rows = [
            ['h' => 1300, 'cells' => [
                $logoCell($logo, 'rId5', 1),
                ['xml' => $titleXml, 'span' => 5, 'vAlign' => 'center'],
                $logoCell($logo2, 'rId6', 2),
            ]],
            ['h' => 700, 'cells' => [
                ['xml' => $c('AD SOYAD:', ['sz' => $fs]), 'vAlign' => 'center'],
                ['xml' => '', 'span' => 3],
                ['xml' => $c($e->duration_min ? 'Sınav süresi '.$e->duration_min.' dakikadır.' : '', ['sz' => $fs - 3], 'center'), 'vAlign' => 'center'],
                ['xml' => $c('TARİH: '.$date, ['sz' => $fs - 3], 'center'), 'vAlign' => 'center'],
                ['xml' => $c('PUAN', ['sz' => $fs + 2], 'center'), 'vAlign' => 'center'],
            ]],
            ['h' => 600, 'cells' => [
                ['xml' => $c('SINIF:', ['sz' => $fs]), 'vAlign' => 'center'],
                ['xml' => ''],
                ['xml' => $c('NO:', ['sz' => $fs]), 'vAlign' => 'center'],
                ['xml' => ''], ['xml' => ''], ['xml' => ''], ['xml' => ''],
            ]],
        ];
        $b = ['single', 16, '000000'];
        $x = '';
        if ($this->mode === 'teacher') {
            $x .= Xml::para([Xml::run('ÖĞRETMEN NÜSHASI — CEVAPLI', ['b' => 1, 'color' => 'B91C1C', 'sz' => 16])], ['jc' => 'right', 'after' => 60]);
        }
        $x .= Xml::table($rows, $w, ['borders' => ['top' => $b, 'left' => $b, 'bottom' => $b, 'right' => $b, 'insideH' => $b, 'insideV' => $b]] + ($framed ? ['ind' => -120] : []));
        $ins = trim((string) ($this->s['instructions'] ?? ''));
        if ($ins !== '') {
            $x .= Xml::para([Xml::run($ins, ['i' => 1, 'color' => '334155', 'sz' => $fs - 2])], ['before' => 100, 'after' => 0]);
        }

        return $x.Xml::para([], ['before' => 0, 'after' => 120, 'line' => 120]);
    }

    /* ------------------------------------------------------------------ */
    /*  Sorular                                                            */
    /* ------------------------------------------------------------------ */

    public function questionsXml(int $columns): string
    {
        $out = '';
        foreach ($this->qs as $i => $q) {
            $out .= $this->questionXml($q, $i, $columns);
        }

        return $out;
    }

    private function optLabel(int $k): string
    {
        $L = self::LETTERS[$k];

        return match ($this->s['opt_style']) { 'bracket' => '('.$L.')', 'dot' => $L.'.', default => $L.')' };
    }

    private function questionXml(array $q, int $i, int $columns): string
    {
        $s = $this->s;
        $pad = (['compact' => 6, 'relaxed' => 20][$s['spacing']] ?? 12) * 15;
        $teacher = $this->mode === 'teacher';
        $ac = Xml::hex($s['accent']);
        $numColor = in_array($this->exam->template, ['modern', 'school', 'quiz'], true) ? $ac : null;
        $fs = ((int) $s['font_size']) * 2;
        $ans = (string) ($q['answer'] ?? '');
        $opts = (array) ($q['options'] ?? []);
        $num = match ($s['num_style']) { 'paren' => ($i + 1).')', 'soru' => 'Soru '.($i + 1).'.', default => ($i + 1).'.' };
        $hang = $s['num_style'] === 'soru' ? 900 : 480;
        $x = '';

        if (trim((string) ($q['section_title'] ?? '')) !== '') {
            $x .= Xml::para([Xml::run((string) $q['section_title'], ['b' => 1])], [
                'keepNext' => true, 'keepLines' => true, 'shd' => 'EEF2F7', 'before' => 60, 'after' => 120,
                'borders' => ['left' => ['single', 24, $ac, 4]], 'ind' => ['left' => 80],
            ]);
        }

        $ptsRun = (! empty($s['show_points']) && (float) $q['points'] > 0)
            ? [Xml::run('  ('.$this->fmt($q['points']).' p)', ['b' => 1, 'color' => '475569', 'sz' => max(14, (int) round($fs * 0.82))])] : [];
        $body = trim((string) $q['body']) !== '' ? (string) $q['body'] : '';
        $numRun = Xml::run($num, ['b' => 1, 'color' => $numColor]).Xml::tab();
        $qInd = ['left' => $hang, 'hanging' => $hang];
        $detInd = ['left' => $hang];

        $lastKeep = false; // son paragrafta keepNext yok; bloğun sonu aralığı taşır
        $paraList = [];
        $add = function (string $xml) use (&$paraList): void { $paraList[] = $xml; };

        $first = [$numRun];
        $imgP = $this->imagePara($q, $columns, $hang);
        switch ($q['type']) {
            case 'multiple_choice':
                $first[] = Xml::run($body);
                $short = $columns === 1 && count($opts) <= 4 && collect($opts)->every(fn ($o) => mb_strlen((string) $o) <= 20);
                $add(Xml::para(array_merge($first, $ptsRun), ['keepNext' => true, 'keepLines' => true, 'ind' => $qInd, 'tabs' => [$hang], 'after' => 40]));
                if ($imgP !== '') {
                    $add($imgP);
                }
                if ($short) {
                    $span = max(1, (int) floor(($this->contentTw - $hang) / max(1, count($opts))));
                    $runs = [];
                    foreach ($opts as $k => $o) {
                        if ($k > 0) {
                            $runs[] = Xml::tab();
                        }
                        $ok = $teacher && $ans === self::LETTERS[$k];
                        $runs[] = Xml::run($this->optLabel($k).' ', ['b' => 1, 'color' => $ok ? '15803D' : null]).Xml::run((string) $o, ['b' => $ok, 'color' => $ok ? '15803D' : null]);
                    }
                    $tabs = [];
                    for ($k = 1; $k < count($opts); $k++) {
                        $tabs[] = $hang + $span * $k;
                    }
                    $add(Xml::para($runs, ['keepLines' => true, 'ind' => $detInd, 'tabs' => $tabs]));
                } else {
                    foreach ($opts as $k => $o) {
                        $ok = $teacher && $ans === self::LETTERS[$k];
                        $add(Xml::para([
                            Xml::run($this->optLabel($k).' ', ['b' => 1, 'color' => $ok ? '15803D' : null]),
                            Xml::run((string) $o.($ok ? '  ✓' : ''), ['b' => $ok, 'color' => $ok ? '15803D' : null]),
                        ], ['keepLines' => true, 'keepNext' => $k < count($opts) - 1, 'ind' => $detInd, 'before' => 20]));
                    }
                }
                break;

            case 'true_false':
                $mark = $teacher ? '( '.($ans === 'Y' ? 'Y' : 'D').' ) ' : '(      ) ';
                $first[] = Xml::run($mark, ['b' => 1]);
                $first[] = Xml::run($body);
                $add(Xml::para(array_merge($first, $ptsRun), ['keepNext' => $imgP !== '', 'keepLines' => true, 'ind' => $qInd, 'tabs' => [$hang]]));
                if ($imgP !== '') {
                    $add($imgP);
                }
                break;

            case 'matching':
                $first[] = Xml::run($body);
                $add(Xml::para(array_merge($first, $ptsRun), ['keepNext' => true, 'keepLines' => true, 'ind' => $qInd, 'tabs' => [$hang], 'after' => 60]));
                if ($imgP !== '') {
                    $add($imgP);
                }
                $n = count($opts);
                $shift = max(1, intdiv($n, 2));
                $order = [];
                for ($j = 0; $j < $n; $j++) {
                    $order[] = ($j + $shift) % $n;
                }
                $leftXml = '';
                foreach ($opts as $k => $o) {
                    $letter = $teacher ? self::LETTERS[array_search($k, $order, true)] : '   ';
                    $leftXml .= Xml::para([Xml::run('( '.$letter.' ) ', ['b' => 1]), Xml::run(($k + 1).'. '.(is_array($o) ? ($o['l'] ?? '') : $o))], ['keepNext' => true, 'keepLines' => true, 'before' => 20]);
                }
                $rightXml = '';
                foreach ($order as $k => $src) {
                    $rightXml .= Xml::para([Xml::run($this->optLabel($k).' ', ['b' => 1]), Xml::run(is_array($opts[$src] ?? null) ? (string) ($opts[$src]['r'] ?? '') : '')], ['keepNext' => $k < $n - 1, 'keepLines' => true, 'before' => 20]);
                }
                $tw = $columns === 2 ? (int) floor(($this->contentTw - 360) / 2) : $this->contentTw;
                $half = (int) floor(($tw - $hang) / 2);
                $add(Xml::table([[['xml' => $leftXml], ['xml' => $rightXml]]], [$half, $tw - $hang - $half], ['ind' => $hang - 60]));
                break;

            case 'open_ended':
                $first[] = Xml::run($body);
                $lines = max(0, min(20, (int) ($q['answer_lines'] ?? 0)));
                $keep = $lines > 0 && ! $teacher;
                $add(Xml::para(array_merge($first, $ptsRun), ['keepNext' => $keep || ($teacher && trim($ans) !== '') || $imgP !== '', 'keepLines' => true, 'ind' => $qInd, 'tabs' => [$hang]]));
                if ($imgP !== '') {
                    $add($imgP);
                }
                if ($teacher) {
                    if (trim($ans) !== '') {
                        $add($this->answerPara('Cevap: '.$ans, $hang));
                    }
                } else {
                    for ($l = 0; $l < $lines; $l++) {
                        $d = ['dotted', 4, '64748B', 1];
                        $add(Xml::para([], ['keepNext' => $l < $lines - 1, 'line' => 375, 'before' => 0, 'after' => 0, 'ind' => $detInd, 'borders' => ['bottom' => $d, 'between' => $d]]));
                    }
                }
                break;

            default: // fill_blank
                $first[] = Xml::run($body);
                $add(Xml::para(array_merge($first, $ptsRun), ['keepNext' => ($teacher && trim($ans) !== '') || $imgP !== '', 'keepLines' => true, 'ind' => $qInd, 'tabs' => [$hang]]));
                if ($imgP !== '') {
                    $add($imgP);
                }
                if ($teacher && trim($ans) !== '') {
                    $add($this->answerPara('Cevap: '.$ans, $hang));
                }
        }

        // Blok sonu aralığı: son paragrafın sonuna boşluk paragrafı
        $x .= implode('', $paraList);
        $x .= Xml::para([], ['before' => 0, 'after' => 0, 'line' => max(20, $pad)]);

        return $x;
    }

    /** Sorunun görselini satır içi resim paragrafı olarak üretir (yoksa ''). */
    private function imagePara(array $q, int $columns, int $left): string
    {
        $path = (string) ($q['image_path'] ?? '');
        if ($path === '' || ! is_file(public_path($path))) {
            return '';
        }
        $contentPx = (int) round($this->contentTw / 15);
        $colPx = $columns === 2 ? (int) floor(($contentPx - 24) / 2) : $contentPx;
        $sz = \App\Support\ExamLayout::imageSize($path, $colPx - 40);
        if (! $sz) {
            return '';
        }
        $n = count($this->images) + 1;
        $rid = 'rIdQ'.$n;
        $this->images[] = ['rid' => $rid, 'ext' => str_ends_with($path, '.jpg') ? 'jpg' : 'png', 'bytes' => (string) file_get_contents(public_path($path))];

        return Xml::para([$this->drawingRun($rid, $sz[0] * 9525, $sz[1] * 9525, 100 + $n, 'Soru görseli '.$n)], ['keepNext' => true, 'keepLines' => true, 'ind' => ['left' => $left], 'before' => 60, 'after' => 60]);
    }

    private function answerPara(string $text, int $left): string
    {
        return Xml::para([Xml::run($text, ['color' => '14532D', 'sz' => max(16, ((int) $this->s['font_size']) * 2 - 2)])], [
            'keepLines' => true, 'shd' => 'F0FDF4', 'before' => 40, 'after' => 0,
            'borders' => ['left' => ['single', 18, '15803D', 4]], 'ind' => ['left' => $left],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Cevap anahtarı                                                     */
    /* ------------------------------------------------------------------ */

    public function keyXml(bool $withTitle): string
    {
        $fs = ((int) $this->s['font_size']) * 2;
        $x = Xml::para([Xml::run('CEVAP ANAHTARI', ['b' => 1, 'sz' => $fs + 8])], ['after' => 40]);
        $x .= Xml::para([Xml::run($this->exam->title.($this->info() !== '' ? ' · '.$this->info() : '').' · Toplam '.$this->fmt($this->totalPoints()).' puan', ['color' => '334155', 'sz' => $fs - 2])], ['after' => 160]);

        $bl = ['single', 4, 'CBD5E1'];
        $hd = ['single', 8, '94A3B8'];
        $w = $this->contentTw;
        $cols = [(int) round($w * 0.08), (int) round($w * 0.2), (int) round($w * 0.62)];
        $cols[] = $w - array_sum($cols);
        $cell = fn (string $t, array $o, array $b, ?string $shd = null) => ['xml' => Xml::para([Xml::run($t, $o)], ['before' => 40, 'after' => 40]), 'borders' => ['bottom' => $b], 'shd' => $shd];
        $rows = [[
            $cell('No', ['b' => 1, 'sz' => $fs - 2], $hd, 'F1F5F9'), $cell('Tür', ['b' => 1, 'sz' => $fs - 2], $hd, 'F1F5F9'),
            $cell('Cevap', ['b' => 1, 'sz' => $fs - 2], $hd, 'F1F5F9'), $cell('Puan', ['b' => 1, 'sz' => $fs - 2], $hd, 'F1F5F9'),
        ]];
        foreach ($this->qs as $i => $q) {
            $a = (string) ($q['answer'] ?? '');
            if ($q['type'] === 'true_false') {
                $a = $a === 'Y' ? 'Yanlış' : 'Doğru';
            }
            $a = mb_strimwidth($a !== '' ? $a : '—', 0, 220, '…', 'UTF-8');
            $rows[] = [
                $cell((string) ($i + 1), ['b' => 1, 'sz' => $fs - 2], $bl), $cell(self::TYPE_NAME[$q['type']] ?? '', ['sz' => $fs - 2], $bl),
                $cell($a, ['sz' => $fs - 2], $bl), $cell($this->fmt($q['points']), ['sz' => $fs - 2], $bl),
            ];
        }
        $x .= Xml::table($rows, $cols);

        return $x.Xml::para([], ['before' => 0, 'after' => 0, 'line' => 20]);
    }

    /* ------------------------------------------------------------------ */

    private function zip(array $files): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'exam-docx-');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Word dosyası oluşturulamadı.');
        }
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        $bin = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $bin;
    }
}
