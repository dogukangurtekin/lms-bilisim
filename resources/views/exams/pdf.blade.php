@php
    // Word'deki fontlarla aynı ölçülü, Türkçe destekli yazı tipleri (public/fonts/exam)
    $examFonts = ['times' => ['ExamTimes', 'Tinos', 'serif'], 'arial' => ['ExamArial', 'Arimo', 'sans-serif'], 'calibri' => ['ExamCalibri', 'Carlito', 'sans-serif'], 'cambria' => ['ExamCambria', 'Caladea', 'serif'], 'georgia' => ['ExamGeorgia', 'Gelasio', 'serif']];
    $fk = $s['font_family'] ?? 'serif';
    $custom = $examFonts[$fk] ?? null;
    $fam = $custom ? "'".$custom[0]."', ".$custom[2] : ($fk === 'sans' ? "'DejaVu Sans', sans-serif" : "'DejaVu Serif', serif");
    // Üst kısım (başlık) yazı tipi/boyutu soru yazısından ayrıdır; boşsa soru yazısıyla aynıdır
    $hk = ($s['header_font_family'] ?? '') ?: $fk;
    $hcustom = $examFonts[$hk] ?? null;
    $hfam = $hcustom ? "'".$hcustom[0]."', ".$hcustom[2] : ($hk === 'sans' ? "'DejaVu Sans', sans-serif" : "'DejaVu Serif', serif");
    $hsize = (int) (($s['header_font_size'] ?? '') ?: $s['font_size']);
    $bw = (float) ($s['border_width'] ?? 2); // kenarlık kalınlığı (px)
    $bwf = rtrim(rtrim(number_format($bw, 2, '.', ''), '0'), '.');
    $tblFrame = $exam->template === 'table' && ! empty($s['page_border']);
    $fpad = 8; // çerçeve ile içerik arasındaki boşluk
    $ac = $s['accent'];
    $padB = ['compact' => 6, 'relaxed' => 20][$s['spacing']] ?? 12;
    $bi = (int) round($margin / 2);
    $total = count($pages);
    $totalPoints = collect($qs)->sum(fn ($q) => (float) $q['points']);
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $info = collect([$exam->subject, $exam->grade_level ? $exam->grade_level.'. Sınıf' : null, $exam->class_label, $exam->term])->filter()->implode(' · ');
    $date = $exam->exam_date ? $exam->exam_date->format('d.m.Y') : '…/…/……';
    $logo = null;
    if (! empty($s['show_logo']) && ! empty($s['logo_path']) && is_file(public_path($s['logo_path']))) {
        $ext = pathinfo($s['logo_path'], PATHINFO_EXTENSION) === 'jpg' ? 'jpeg' : pathinfo($s['logo_path'], PATHINFO_EXTENSION);
        $logo = 'data:image/'.$ext.';base64,'.base64_encode((string) file_get_contents(public_path($s['logo_path'])));
    }
    $tpl = $exam->template;
    $logoUri = function (?string $path) {
        if (! $path || ! is_file(public_path($path))) {
            return null;
        }
        $e = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return 'data:image/'.($e === 'jpg' ? 'jpeg' : $e).';base64,'.base64_encode((string) file_get_contents(public_path($path)));
    };
    $up = fn ($t) => \App\Support\ExamLayout::upper($t);
    $keyOf = function (array $q) {
        $a = (string) ($q['answer'] ?? '');
        return match ($q['type']) {
            'true_false' => $a === 'Y' ? 'Yanlış' : 'Doğru',
            default => $a,
        };
    };
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>{{ $exam->title }}</title>
<style>
    @page { margin: 0; }
@foreach(collect([$custom, $hcustom])->filter()->unique(fn ($c) => $c[0]) as $cf)
    @font-face { font-family: '{{ $cf[0] }}'; font-style: normal; font-weight: normal; src: url('{{ public_path('fonts/exam/'.$cf[1].'-Regular.ttf') }}') format('truetype'); }
    @font-face { font-family: '{{ $cf[0] }}'; font-style: normal; font-weight: bold; src: url('{{ public_path('fonts/exam/'.$cf[1].'-Bold.ttf') }}') format('truetype'); }
    @font-face { font-family: '{{ $cf[0] }}'; font-style: italic; font-weight: normal; src: url('{{ public_path('fonts/exam/'.$cf[1].'-Italic.ttf') }}') format('truetype'); }
    @font-face { font-family: '{{ $cf[0] }}'; font-style: italic; font-weight: bold; src: url('{{ public_path('fonts/exam/'.$cf[1].'-BoldItalic.ttf') }}') format('truetype'); }
@endforeach
    body { margin: 0; padding: 0; font-family: {!! $fam !!}; font-size: {{ (int) $s['font_size'] }}pt; line-height: 1.35; color: #111; }
    .pg { position: relative; width: 794px; height: 1118px; overflow: hidden; page-break-after: always; }
    .pg.last { page-break-after: auto; }
    .inner { position: absolute; left: {{ $margin }}px; top: {{ $margin }}px; width: {{ $contentW }}px; }
    .frame { position: absolute; left: {{ $bi }}px; top: {{ $bi }}px; width: {{ 794 - 2 * $bi }}px; height: {{ 1118 - 2 * $bi }}px; border: {{ $bwf }}px solid {{ $ac }}; }
    .wm { position: absolute; left: 0; top: 520px; width: 794px; text-align: center; font-size: 84px; font-weight: bold; color: #e5e7eb; transform: rotate(-30deg); text-transform: uppercase; }
    .pgno { position: absolute; left: 0; bottom: 0; width: 794px; height: 26px; line-height: 26px; text-align: center; font-size: 8.5pt; color: #64748b; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }
    .qblk { padding-bottom: {{ $padB }}px; }
    .qr { width: 100%; }
    .qn { width: 1%; white-space: nowrap; font-weight: bold; padding-right: 6px; {{ in_array($tpl, ['modern', 'school', 'quiz'], true) ? 'color:'.$ac.';' : '' }} }
    .qb { word-wrap: break-word; }
    .qp { font-size: 0.82em; color: #475569; font-weight: bold; white-space: nowrap; }
    .qsec { font-weight: bold; margin: 2px 0 8px; padding: 3px 8px; background: #eef2f7; border-left: 4px solid {{ $ac }}; }
    .qo { margin-top: 2px; }
    .qo b { margin-right: 3px; }
    .ok { color: #15803d; font-weight: bold; }
    .qo4 { width: 100%; margin-top: 4px; table-layout: fixed; }
    .qm { width: 100%; margin-top: 5px; table-layout: fixed; }
    .qm td { padding-right: 10px; }
    .qm td div { margin-bottom: 3px; }
    .ln { height: 25px; border-bottom: 1px dotted #64748b; }
    .ans { margin-top: 4px; padding: 4px 8px; background: #f0fdf4; border-left: 3px solid #15803d; color: #14532d; font-size: 0.92em; }
    .ph { margin-bottom: 12px; }
    .ph-school { text-align: {{ $tpl === 'plain' || $tpl === 'quiz' ? 'left' : 'center' }}; font-weight: bold; text-transform: uppercase; }
    .ph-sub { text-align: {{ $tpl === 'plain' || $tpl === 'quiz' ? 'left' : 'center' }}; font-weight: bold; font-size: 1.08em; }
    .ph-title { text-align: {{ $tpl === 'plain' || $tpl === 'quiz' ? 'left' : 'center' }}; font-weight: bold; font-size: {{ $tpl === 'quiz' ? '1.15' : '1.35' }}em; margin: 2px 0; {{ $tpl === 'school' ? 'color:'.$ac.';' : '' }} }
    .ph-info { text-align: {{ $tpl === 'plain' || $tpl === 'quiz' ? 'left' : 'center' }}; color: #334155; font-size: 0.95em; }
    .ph-meta { width: 100%; margin-top: 8px; border-top: {{ $tpl === 'plain' ? '0' : '1px solid '.($tpl === 'classic' ? $ac : '#111') }}; border-bottom: {{ $tpl === 'plain' ? '0' : '1px solid '.($tpl === 'classic' ? $ac : '#111') }}; font-size: 0.92em; font-weight: bold; }
    .ph-meta td { padding: 4px 0; }
    .ph-stu { width: 100%; margin-top: 8px; font-size: 0.92em; font-weight: bold; table-layout: fixed; }
    .ph-stu td { border-bottom: 1px solid #111; height: 26px; padding-right: 8px; }
    .ph-ins { margin-top: 8px; font-size: 0.88em; font-style: italic; color: #334155; }
    .ph-box { {{ $tpl === 'classic' ? 'border:'.$bwf.'px solid '.$ac.'; padding:8px 10px;' : '' }} {{ $tpl === 'school' ? 'border-bottom:3px double '.$ac.'; padding-bottom:8px;' : '' }} {{ $tpl === 'quiz' ? 'border-bottom:2px solid '.$ac.'; padding-bottom:6px;' : '' }} }
    .ph-band { background: {{ $ac }}; color: #fff; padding: 10px 14px; width: {{ $contentW - 28 }}px; }
    .ph-band .ph-info { color: #e0e7ff; }
    .ph-rest { padding: 6px 14px 10px; border: 1px solid {{ $ac }}; border-top: 0; width: {{ $contentW - 28 }}px; }
    .tag { text-align: right; font-size: 8pt; font-weight: bold; color: #b91c1c; margin-bottom: 4px; }
    .key-h { font-size: 1.4em; font-weight: bold; margin-bottom: 4px; }
    .key-t { width: 100%; margin-top: 10px; table-layout: fixed; }
    .key-t th { text-align: left; background: #f1f5f9; border-bottom: 1.5px solid #94a3b8; padding: 4px 6px; font-size: 0.9em; }
    .key-t td { border-bottom: 1px solid #e2e8f0; padding: 4px 6px; font-size: 0.95em; word-wrap: break-word; }
</style>
</head>
<body>
@if($mode !== 'key')
@foreach($pages as $pIdx => $pg)
    <div class="pg {{ ($pIdx === $total - 1 && ! $withKey) ? 'last' : '' }}">
        @if(! empty($s['page_border']) && ! $tblFrame)<div class="frame"></div>@endif
        @if($tblFrame)<div style="position:absolute;left:{{ $margin - $fpad }}px;top:{{ $margin }}px;width:{{ $contentW + 2 * $fpad - 2 * $bw }}px;height:{{ 1118 - 2 * $margin - (! empty($s['page_numbers']) ? 26 : 0) + $fpad - 2 * $bw }}px;border:{{ $bwf }}px solid #000"></div>@endif
        @if(trim((string) ($s['watermark'] ?? '')) !== '')<div class="wm">{{ $s['watermark'] }}</div>@endif
        <div class="inner">
            <div style="font-family:{!! $hfam !!};font-size:{{ $hsize }}pt">
            @if($pIdx === 0 && $tpl === 'table')
                @php
                    $pct = [12.3, 13.5, 10.5, 7.5, 21.2, 21.4, 13.6];
                    $px = fn (int ...$cols) => (int) round(array_sum(array_map(fn ($i) => $pct[$i], $cols)) / 100 * $contentW);
                    $TL = \App\Support\ExamLayout::titleLines($exam, $s);
                    $lines = collect([$TL['year'], $TL['school'], $TL['course'], $TL['last']])->filter(fn ($t) => $t !== '');
                    $fitEm = \App\Support\ExamLayout::fitTitleScale($lines->all(), ($contentW + ($tblFrame ? 2 * $fpad : 0)) * 0.741, $hsize * 1.3333, $hk);
                    $lg1 = $logoUri($s['logo_path'] ?? null);
                    $lg2 = $logoUri($s['logo2_path'] ?? null);
                    $cell = 'border:'.$bwf.'px solid #000;padding:4px 6px;font-weight:bold;vertical-align:middle;';
                    $note = trim((string) ($s['instructions'] ?? ''));
                    $tw = $contentW + ($tblFrame ? 2 * $fpad : 0);
                    $px = fn (int ...$cols) => (int) round(array_sum(array_map(fn ($i) => $pct[$i], $cols)) / 100 * $tw);
                @endphp
                <div class="ph" style="{{ $tblFrame ? 'margin-left:-'.$fpad.'px;' : '' }}">
                    @if($mode === 'teacher')<div class="tag" style="{{ $tblFrame ? 'padding-right:'.$fpad.'px' : '' }}">ÖĞRETMEN NÜSHASI — CEVAPLI</div>@endif
                    <table style="width:{{ $tw }}px;border-collapse:collapse">
                        <tr style="height:86px">
                            <td style="{{ $cell }}width:{{ $px(0) }}px;text-align:center">@if($lg1)<img src="{{ $lg1 }}" style="max-width:{{ $px(0) - 12 }}px;max-height:78px">@endif</td>
                            <td colspan="5" style="{{ $cell }}width:{{ $px(1, 2, 3, 4, 5) }}px;text-align:center;font-size:{{ number_format($fitEm, 3, '.', '') }}em;line-height:1.3">@foreach($lines as $ln)<div>{{ $ln }}</div>@endforeach</td>
                            <td style="{{ $cell }}width:{{ $px(6) }}px;text-align:center">@if($lg2)<img src="{{ $lg2 }}" style="max-width:{{ $px(6) - 12 }}px;max-height:78px">@endif</td>
                        </tr>
                        <tr style="height:46px">
                            <td style="{{ $cell }}font-size:0.9em;white-space:nowrap">AD SOYAD:</td>
                            <td colspan="3" style="{{ $cell }}"></td>
                            <td style="{{ $cell }}font-size:0.78em;text-align:center">{{ $exam->duration_min ? 'Sınav süresi '.$exam->duration_min.' dakikadır.' : '' }}</td>
                            <td style="{{ $cell }}font-size:0.78em;text-align:center">TARİH: {{ $date }}</td>
                            <td style="{{ $cell }}font-size:1.1em;text-align:center">PUAN</td>
                        </tr>
                        <tr style="height:40px">
                            <td style="{{ $cell }}font-size:0.9em;white-space:nowrap">SINIF:</td>
                            <td style="{{ $cell }}"></td>
                            <td style="{{ $cell }}font-size:0.9em;white-space:nowrap">NO:</td>
                            <td style="{{ $cell }}"></td>
                            <td colspan="2" style="{{ $cell }}font-size:0.78em;font-weight:normal;line-height:1.25;text-align:left">{!! $note !== '' ? nl2br(e($note)) : '' !!}</td>
                            <td style="{{ $cell }}"></td>
                        </tr>
                    </table>
                </div>
            @elseif($pIdx === 0)
                @php
                    $TL = \App\Support\ExamLayout::titleLines($exam, $s);
                    $boxW = $contentW - 30;
                    $basePx = $hsize * 1.3333;
                    $fitFor = fn (string $t, float $base) => number_format(min($base, \App\Support\ExamLayout::fitTitleScale([$t], $boxW, $basePx, $hk)), 3, '.', '');
                    $top = ($TL['year'] !== '' ? '<div class="ph-info">'.e($TL['year']).'</div>' : '').($TL['school'] !== '' ? '<div class="ph-school" style="font-size:'.$fitFor($TL['school'], 1.05).'em">'.e($TL['school']).'</div>' : '').($TL['course'] !== '' ? '<div class="ph-sub" style="font-size:'.$fitFor($TL['course'], 1.08).'em">'.e($TL['course']).'</div>' : '').($TL['last'] !== '' ? '<div class="ph-title">'.e($TL['last']).'</div>' : '');
                    $topWithLogo = $logo
                        ? '<table style="width:100%"><tr><td style="width:70px;vertical-align:middle"><img src="'.$logo.'" style="height:56px;width:auto"></td><td style="vertical-align:middle">'.$top.'</td></tr></table>'
                        : $top;
                @endphp
                <div class="ph">
                    @if($mode === 'teacher')<div class="tag">ÖĞRETMEN NÜSHASI — CEVAPLI</div>@endif
                    <div class="{{ $tpl === 'modern' ? '' : 'ph-box' }}">
                        @if($tpl === 'modern')<div class="ph-band">{!! $topWithLogo !!}</div><div class="ph-rest">@else {!! $topWithLogo !!} @endif
                        <table class="ph-meta"><tr><td style="text-align:left">Tarih: {{ $date }}</td><td style="text-align:center">Süre: {{ $exam->duration_min ? $exam->duration_min.' dk' : '……' }}</td><td style="text-align:right">Toplam Puan: {{ $fmt($totalPoints) }}</td></tr></table>
                        @if(! empty($s['student_box']))
                            <table class="ph-stu"><tr><td style="width:40%">Adı Soyadı:</td><td style="width:20%">Numara:</td><td style="width:20%">Sınıf:</td><td style="width:20%">Puan:</td></tr></table>
                        @endif
                        @if(trim((string) ($s['instructions'] ?? '')) !== '')<div class="ph-ins">{!! nl2br(e($s['instructions'])) !!}</div>@endif
                        @if($tpl === 'modern')</div>@endif
                    </div>
                </div>
            @endif
            </div>
            <div style="position:relative;width:{{ $contentW }}px;height:{{ $pIdx === 0 ? $contentH - $headerH : $contentH }}px;{{ $tblFrame && $pIdx > 0 ? 'margin-top:'.$fpad.'px;' : '' }}">
                @foreach($pg as $cIdx => $col)
                    @if($cIdx > 0)<div style="position:absolute;left:{{ $colW + 11 }}px;top:0;width:1px;height:{{ $pIdx === 0 ? $contentH - $headerH : $contentH }}px;background:#cbd5e1"></div>@endif
                    <div style="position:absolute;left:{{ $cIdx * ($colW + 24) }}px;top:0;width:{{ $colW }}px">
                        @foreach($col as $i)
                            @include('exams.pdf-q', ['q' => $qs[$i], 'i' => $i])
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        @if(! empty($s['page_numbers']))<div class="pgno">Sayfa {{ $pIdx + 1 }} / {{ $total }}</div>@endif
    </div>
@endforeach
@endif

@if($mode === 'key' || $withKey)
    @php
        $rows = [];
        foreach ($qs as $i => $q) {
            $rows[] = ['n' => $i + 1, 'type' => $q['type'], 'a' => $keyOf($q), 'p' => $q['points']];
        }
        $chunks = array_chunk($rows, 34);
    @endphp
    @foreach($chunks as $cIdx => $chunk)
        <div class="pg {{ $cIdx === count($chunks) - 1 ? 'last' : '' }}">
            <div class="inner">
                <div class="key-h">CEVAP ANAHTARI</div>
                <div class="ph-info" style="text-align:left">{{ $exam->title }} · {{ $info }} · Toplam {{ $fmt($totalPoints) }} puan</div>
                <table class="key-t">
                    <tr><th style="width:9%">No</th><th style="width:22%">Tür</th><th>Cevap</th><th style="width:12%">Puan</th></tr>
                    @foreach($chunk as $r)
                        <tr>
                            <td><b>{{ $r['n'] }}</b></td>
                            <td>{{ ['multiple_choice' => 'Çoktan seçmeli', 'true_false' => 'Doğru/Yanlış', 'fill_blank' => 'Boşluk doldurma', 'open_ended' => 'Açık uçlu', 'matching' => 'Eşleştirme'][$r['type']] ?? '' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($r['a'] !== '' ? $r['a'] : '—', 220) }}</td>
                            <td>{{ $fmt($r['p']) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            @if(! empty($s['page_numbers']))<div class="pgno">Cevap anahtarı — {{ $cIdx + 1 }} / {{ count($chunks) }}</div>@endif
        </div>
    @endforeach
@endif
</body>
</html>
