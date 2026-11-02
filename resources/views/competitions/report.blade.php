@extends('layout.app')
@section('title','Yarışma Raporu')
@section('content')
@include('competitions._theme')
@php
    $fmt = function ($s) { return $s === null ? '-' : ($s >= 60 ? intdiv($s, 60).' dk '.($s % 60).' sn' : $s.' sn'); };
    $rows = $r['rows'];
    $maxXp = max(1, (int) $r['max_xp']);
    $maxTime = max(1, (int) collect($rows)->max('completed_seconds'));
    $maxBucket = max(1, max($r['buckets']));
    $maxProg = $r['is_scratch'] ? max(1, (int) collect($rows)->max('progress_percent')) : 100;
    $donePct = $r['participants'] ? round($r['finished_count'] / $r['participants'] * 100) : 0;
    $isLive = $room->status !== 'finished';
@endphp
<style>
.rp-actions{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
.rp-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-top:14px}
.rp-kpi{padding:12px 14px;border:1px solid var(--cx-line);border-radius:14px;background:rgba(255,255,255,.06)}
.rp-kpi small{display:block;color:var(--cx-mute);font-weight:700;font-size:12px}
.rp-kpi strong{display:block;font-size:24px;color:#fff;margin-top:2px}
.rp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));gap:14px}
.rp-bar{display:grid;grid-template-columns:minmax(80px,150px) minmax(0,1fr) auto;gap:8px;align-items:center;margin:6px 0;font-size:13px}
.rp-bar .nm{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#e2e8f0}
.rp-bar .tr{height:14px;border-radius:99px;background:rgba(148,163,184,.2);overflow:hidden}
.rp-bar .fl{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#48dbfb,#54a0ff)}
.rp-bar .fl.g{background:linear-gradient(90deg,#1dd1a1,#10b981)}
.rp-bar .fl.o{background:linear-gradient(90deg,#feca57,#ff9f43)}
.rp-bar .v{color:#fff;font-weight:800;min-width:44px;text-align:right}
.rp-hist{display:flex;align-items:flex-end;gap:10px;height:170px;padding-top:8px}
.rp-hist .col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;font-size:12px;color:var(--cx-mute)}
.rp-hist .b{width:100%;max-width:56px;border-radius:8px 8px 0 0;background:linear-gradient(180deg,#a29bfe,#54a0ff);min-height:2px}
.rp-hist .n{color:#fff;font-weight:800;margin-bottom:3px}
.rp-donut{width:150px;height:150px;border-radius:50%;display:grid;place-items:center;margin:0 auto;background:conic-gradient(#1dd1a1 {{ $donePct }}%, rgba(148,163,184,.3) 0)}
.rp-donut span{display:grid;place-items:center;width:104px;height:104px;border-radius:50%;background:#0f172a;color:#fff;font-size:26px;font-weight:800}
.rp-leg{text-align:center;color:var(--cx-mute);font-size:13px;margin-top:8px}
.rp-table{width:100%;border-collapse:collapse;font-size:14px}
.rp-table th,.rp-table td{padding:8px 10px;border-bottom:1px solid var(--cx-line);text-align:left;color:#e2e8f0}
.rp-table th{color:var(--cx-mute);font-size:12px}
.rp-ok{color:#1dd1a1;font-weight:800}.rp-no{color:#feca57;font-weight:800}
@media print{
  body{background:#fff!important}.sidebar,.navbar,.rp-actions,header{display:none!important}
  .cx{background:#fff!important;color:#0f172a;border:0;padding:0}.cx::before{display:none}
  .cx h1,.cx h2,.cx h3,.rp-kpi strong,.rp-bar .v,.rp-hist .n{color:#0f172a!important}
  .cx p,.rp-kpi small,.rp-table th,.rp-table td,.rp-bar .nm{color:#334155!important}
  .rp-donut span{background:#fff;color:#0f172a}
  .rp-kpi,.cx-panel{box-shadow:none;background:#fff;border-color:#cbd5e1;break-inside:avoid}
}
</style>
<div class="cx">
<div class="cx-hero">
    <div class="cx-hero-icon">📊</div>
    <div>
        <h1>{{ $room->game_name }} — Yarışma Raporu</h1>
        <p>
            {{ $room->schoolClass ? $room->schoolClass->name.'/'.$room->schoolClass->section : 'Tüm sınıflar' }}
            @unless($r['is_scratch']) · Seviye {{ $room->level_from }}–{{ $room->level_to }}@endunless
            · Planlanan süre {{ (int) ($room->duration_seconds / 60) }} dk
            @if($r['started_at']) · {{ $r['started_at']->format('d.m.Y H:i') }}@endif
            @if($r['finished_at']) – {{ $r['finished_at']->format('H:i') }}@endif
        </p>
    </div>
    <div class="cx-chips"><span class="cx-chip {{ $isLive ? 'live' : 'done' }}">{{ $isLive ? '● Devam ediyor / başlamadı' : '🏁 Bitti' }}</span></div>
</div>
<div class="rp-actions">
    <a class="btn" href="{{ route('competitions.room.show', $room) }}">← Odaya Dön</a>
    <button class="btn" type="button" onclick="window.print()">🖨 Yazdır / PDF</button>
    <a class="btn" href="{{ route('competitions.room.report.csv', $room) }}">⬇ Excel (CSV)</a>
</div>

<div class="rp-kpis">
    <div class="rp-kpi"><small>Katılımcı</small><strong>{{ $r['participants'] }}</strong></div>
    <div class="rp-kpi"><small>Tamamlayan</small><strong>{{ $r['finished_count'] }}</strong></div>
    <div class="rp-kpi"><small>Ortalama ilerleme</small><strong>{{ $r['is_scratch'] ? $r['avg_progress'].' blok' : '%'.$r['avg_progress'] }}</strong></div>
    <div class="rp-kpi"><small>Toplam XP</small><strong>{{ $r['total_xp'] }}</strong></div>
    <div class="rp-kpi"><small>Ortalama XP</small><strong>{{ $r['avg_xp'] }}</strong></div>
    <div class="rp-kpi"><small>En yüksek XP</small><strong>{{ $r['max_xp'] }}</strong></div>
    <div class="rp-kpi"><small>En hızlı bitiş</small><strong>{{ $fmt($r['best_time']) }}</strong></div>
    <div class="rp-kpi"><small>Ortalama bitiş</small><strong>{{ $fmt($r['avg_time']) }}</strong></div>
</div>

@if($r['participants'] === 0)
    <div class="cx-panel"><p>Bu yarışmaya katılan öğrenci yok.</p></div>
@else
<div class="rp-grid" style="margin-top:14px">
    <div class="cx-panel" style="margin-top:0">
        <h3>XP Sıralaması</h3>
        @foreach($rows as $row)
            <div class="rp-bar"><span class="nm" title="{{ $row['name'] }}">{{ $loop->iteration }}. {{ $row['name'] }}</span><span class="tr"><i class="fl" style="width:{{ round($row['xp_earned'] / $maxXp * 100) }}%"></i></span><span class="v">{{ $row['xp_earned'] }}</span></div>
        @endforeach
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>{{ $r['is_scratch'] ? 'Blok Sayısı' : 'İlerleme (%)' }}</h3>
        @foreach($rows as $row)
            <div class="rp-bar"><span class="nm" title="{{ $row['name'] }}">{{ $row['name'] }}</span><span class="tr"><i class="fl {{ $row['finished'] ? 'g' : 'o' }}" style="width:{{ round(min(100, $row['progress_percent'] / $maxProg * 100)) }}%"></i></span><span class="v">{{ $r['is_scratch'] ? (int) $row['progress_percent'] : round($row['progress_percent']).'%' }}</span></div>
        @endforeach
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>Tamamlama Durumu</h3>
        <div class="rp-donut"><span>%{{ $donePct }}</span></div>
        <div class="rp-leg">{{ $r['finished_count'] }} / {{ $r['participants'] }} öğrenci tamamladı</div>
    </div>
    <div class="cx-panel" style="margin-top:0">
        <h3>İlerleme Dağılımı</h3>
        <div class="rp-hist">
            @foreach($r['buckets'] as $label => $cnt)
                <div class="col"><span class="n">{{ $cnt }}</span><span class="b" style="height:{{ round($cnt / $maxBucket * 100) }}%"></span><span>{{ $label === '100' ? '%100' : '%'.$label }}</span></div>
            @endforeach
        </div>
    </div>
    @if($r['finished_count'] > 0)
    <div class="cx-panel" style="margin-top:0">
        <h3>Bitirme Süreleri</h3>
        @foreach(collect($rows)->where('finished', true)->sortBy('completed_seconds') as $row)
            <div class="rp-bar"><span class="nm" title="{{ $row['name'] }}">{{ $row['name'] }}</span><span class="tr"><i class="fl g" style="width:{{ round(($row['completed_seconds'] ?? 0) / $maxTime * 100) }}%"></i></span><span class="v" style="min-width:70px">{{ $fmt($row['completed_seconds']) }}</span></div>
        @endforeach
    </div>
    @endif
</div>

<div class="cx-panel" style="overflow:auto">
    <h3>Ayrıntılı Sonuçlar</h3>
    <table class="rp-table">
        <thead><tr><th>#</th><th>Öğrenci</th><th>{{ $r['progress_label'] }}</th><th>{{ $r['level_label'] }}</th><th>XP</th><th>Süre</th><th>Durum</th></tr></thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td><td>{{ $row['name'] }}</td>
                <td>{{ $r['is_scratch'] ? (int) $row['progress_percent'] : '%'.round($row['progress_percent']) }}</td>
                <td>{{ $row['current_level_index'] }}</td><td>{{ $row['xp_earned'] }}</td>
                <td>{{ $fmt($row['completed_seconds']) }}</td>
                <td>@if($row['finished'])<span class="rp-ok">✔ Tamamladı</span>@else<span class="rp-no">Süre doldu</span>@endif</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if($r['is_scratch'])<p style="margin:10px 0 0">Not: Blok Kodlama Stüdyosu'nda ilerleme = proje bloğu sayısı, XP = blok + 3×betik + 5×nesne (en çok 100). Projeler "Öğrenci Blok Projeleri" sayfasından incelenebilir.</p>@endif
</div>
@endif
</div>
@endsection
