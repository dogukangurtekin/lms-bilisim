@extends('layout.app')
@section('title','Canlı Quiz Raporu')
@section('content')
@include('competitions._theme')
@include('competitions._report-style')
@php
    $rows = $r['rows'];
    $qs = $r['questions'];
    $maxXp = max(1, (int) $r['max_xp']);
    $maxBucket = max(1, max($r['buckets']));
    $ans = max(1, $r['total_answers'] + $r['total_blank']);
    $pc = round($r['total_correct'] / $ans * 100);
    $pw = round($r['total_wrong'] / $ans * 100);
    $isLive = $session->status !== 'finished';
    $fmtSec = function ($s) { return $s === null ? '-' : ($s >= 60 ? intdiv($s, 60).' dk '.($s % 60).' sn' : $s.' sn'); };
@endphp
<style>
.rp-donut3{width:150px;height:150px;border-radius:50%;display:grid;place-items:center;margin:0 auto}
.rp-donut3 span{display:grid;place-items:center;width:104px;height:104px;border-radius:50%;background:#0f172a;color:#fff;font-size:26px;font-weight:800}
.rp-legend{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-top:10px;font-size:13px;color:var(--cx-mute)}
.rp-legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:5px}
.rp-opt{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
.rp-opt span{padding:3px 9px;border-radius:99px;font-size:12px;background:rgba(148,163,184,.2);color:#e2e8f0}
.rp-opt span.ok{background:rgba(29,209,161,.25);color:#1dd1a1;font-weight:800}
.rp-callout{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:10px;margin-top:14px}
.rp-callout div{padding:12px 14px;border-radius:14px;border:1px solid var(--cx-line);background:rgba(255,255,255,.06);font-size:14px;color:#e2e8f0}
.rp-callout b{display:block;font-size:12px;color:var(--cx-mute);margin-bottom:3px}
@media print{.rp-donut3 span{background:#fff;color:#0f172a}.rp-callout div{background:#fff;color:#334155;border-color:#cbd5e1}.rp-opt span{background:#e2e8f0;color:#334155}}
</style>
<div class="cx">
<div class="cx-hero">
    <div class="cx-hero-icon">📊</div>
    <div>
        <h1>{{ $session->quiz?->title }} — Canlı Quiz Raporu</h1>
        <p>
            Kod {{ $session->join_code }} · {{ $r['question_total'] }} soru
            @if($r['started_at']) · {{ $r['started_at']->format('d.m.Y H:i') }}@endif
            @if($r['finished_at']) – {{ $r['finished_at']->format('H:i') }}@endif
            @if($r['used_seconds'] !== null) · Süre {{ $fmtSec($r['used_seconds']) }}@endif
        </p>
    </div>
    <div class="cx-chips"><span class="cx-chip {{ $isLive ? 'live' : 'done' }}">{{ $isLive ? '● Devam ediyor / başlamadı' : '🏁 Tamamlandı' }}</span></div>
</div>
<div class="rp-actions">
    <a class="btn" href="{{ route('live-quiz.index') }}">← Canlı Quiz Merkezi</a>
    <button class="btn" type="button" onclick="window.print()">🖨 Yazdır / PDF</button>
    <a class="btn" href="{{ route('live-quiz.session.report.csv', $session) }}">⬇ Excel (CSV)</a>
</div>

<div class="rp-kpis">
    <div class="rp-kpi"><small>Katılımcı</small><strong>{{ $r['participants'] }}</strong></div>
    <div class="rp-kpi"><small>Cevap veren</small><strong>{{ $r['active'] }}</strong></div>
    <div class="rp-kpi"><small>Soru sayısı</small><strong>{{ $r['question_total'] }}</strong></div>
    <div class="rp-kpi"><small>Genel doğruluk</small><strong>%{{ $r['accuracy'] }}</strong></div>
    <div class="rp-kpi"><small>Toplam XP</small><strong>{{ $r['total_xp'] }}</strong></div>
    <div class="rp-kpi"><small>Ortalama XP</small><strong>{{ $r['avg_xp'] }}</strong></div>
    <div class="rp-kpi"><small>En yüksek XP</small><strong>{{ $r['max_xp'] }}</strong></div>
    <div class="rp-kpi"><small>Toplam cevap</small><strong>{{ $r['total_answers'] }}</strong></div>
</div>

@if($r['hardest'] || $r['easiest'])
<div class="rp-callout">
    @if($r['hardest'])<div><b>⚠ En zor soru (%{{ $r['hardest']['correct_pct'] }} doğru)</b>{{ $r['hardest']['index'] + 1 }}. {{ \Illuminate\Support\Str::limit($r['hardest']['question_text'], 140) }}</div>@endif
    @if($r['easiest'])<div><b>✔ En kolay soru (%{{ $r['easiest']['correct_pct'] }} doğru)</b>{{ $r['easiest']['index'] + 1 }}. {{ \Illuminate\Support\Str::limit($r['easiest']['question_text'], 140) }}</div>@endif
</div>
@endif

@if($r['participants'] === 0)
    <div class="cx-panel"><p>Bu oturuma katılan öğrenci yok.</p></div>
@else
<div class="rp-grid" style="margin-top:14px">
    <div class="cx-panel" style="margin-top:0">
        <h3>XP Sıralaması</h3>
        @foreach($rows as $row)
            <div class="rp-bar"><span class="nm" title="{{ $row['student_name'] }}">{{ $loop->iteration }}. {{ $row['student_name'] }}</span><span class="tr"><i class="fl" style="width:{{ round($row['xp'] / $maxXp * 100) }}%"></i></span><span class="v">{{ $row['xp'] }}</span></div>
        @endforeach
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>Öğrenci Doğruluk Oranı</h3>
        @foreach($rows as $row)
            <div class="rp-bar"><span class="nm" title="{{ $row['student_name'] }}">{{ $row['student_name'] }}</span><span class="tr"><i class="fl {{ $row['accuracy'] >= 70 ? 'g' : ($row['accuracy'] >= 40 ? 'o' : '') }}" style="width:{{ $row['accuracy'] }}%"></i></span><span class="v">%{{ $row['accuracy'] }}</span></div>
        @endforeach
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>Soru Bazında Doğru Oranı</h3>
        @foreach($qs as $q)
            <div class="rp-bar"><span class="nm" title="{{ $q['question_text'] }}">{{ $q['index'] + 1 }}. {{ $q['question_text'] }}</span><span class="tr"><i class="fl {{ $q['correct_pct'] >= 70 ? 'g' : ($q['correct_pct'] >= 40 ? 'o' : '') }}" style="width:{{ $q['correct_pct'] }}%"></i></span><span class="v">%{{ $q['correct_pct'] }}</span></div>
        @endforeach
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>Cevap Dağılımı</h3>
        <div class="rp-donut3" style="background:conic-gradient(#1dd1a1 0 {{ $pc }}%, #ff6b6b {{ $pc }}% {{ $pc + $pw }}%, rgba(148,163,184,.3) {{ $pc + $pw }}% 100%)"><span>%{{ $r['accuracy'] }}</span></div>
        <div class="rp-legend"><span><i style="background:#1dd1a1"></i>Doğru {{ $r['total_correct'] }}</span><span><i style="background:#ff6b6b"></i>Yanlış {{ $r['total_wrong'] }}</span><span><i style="background:rgba(148,163,184,.5)"></i>Boş {{ $r['total_blank'] }}</span></div>
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>Doğruluk Dağılımı (öğrenci sayısı)</h3>
        <div class="rp-hist">
            @foreach($r['buckets'] as $label => $cnt)
                <div class="col"><span class="n">{{ $cnt }}</span><span class="b" style="height:{{ round($cnt / $maxBucket * 100) }}%"></span><span>{{ $label === '100' ? '%100' : '%'.$label }}</span></div>
            @endforeach
        </div>
    </div>
</div>

<div class="cx-panel" style="overflow:auto">
    <h3>Soru Bazında Ayrıntı</h3>
    <table class="rp-table">
        <thead><tr><th>#</th><th>Soru</th><th>Cevaplayan</th><th>Doğru</th><th>Yanlış</th><th>Boş</th><th>Doğru %</th><th>Ort. XP</th></tr></thead>
        <tbody>
        @forelse($qs as $q)
            <tr>
                <td>{{ $q['index'] + 1 }}</td>
                <td>{{ $q['question_text'] }}
                    @if(count($q['dist']))
                        <div class="rp-opt">@foreach($q['dist'] as $d)<span class="{{ $d['is_correct'] ? 'ok' : '' }}">{{ $d['letter'] }}: {{ \Illuminate\Support\Str::limit($d['label'], 24) }} · {{ $d['count'] }}{{ $d['is_correct'] ? ' ✔' : '' }}</span>@endforeach</div>
                    @endif
                </td>
                <td>{{ $q['answered'] }}</td><td>{{ $q['correct'] }}</td><td>{{ $q['wrong'] }}</td><td>{{ $q['blank'] }}</td><td>%{{ $q['correct_pct'] }}</td><td>{{ $q['avg_xp'] }}</td>
            </tr>
        @empty
            <tr><td colspan="8">Soru bulunamadı.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="cx-panel" style="overflow:auto">
    <h3>Öğrenci Sonuçları</h3>
    <table class="rp-table">
        <thead><tr><th>#</th><th>Öğrenci</th><th>Cevaplanan</th><th>Doğru</th><th>Yanlış</th><th>Boş</th><th>Doğruluk</th><th>XP</th></tr></thead>
        <tbody>
        @foreach($rows as $row)
            <tr><td>{{ $loop->iteration }}</td><td>{{ $row['student_name'] }}</td><td>{{ $row['answered'] }}</td><td class="rp-ok">{{ $row['correct'] }}</td><td>{{ $row['wrong'] }}</td><td>{{ $row['blank'] }}</td><td>%{{ $row['accuracy'] }}</td><td><b>{{ $row['xp'] }}</b></td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
</div>
@endsection
