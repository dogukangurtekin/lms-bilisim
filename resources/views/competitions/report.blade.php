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
@include('competitions._report-style')
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
        <div class="rp-donut" style="--p:{{ $donePct }}"><span>%{{ $donePct }}</span></div>
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
