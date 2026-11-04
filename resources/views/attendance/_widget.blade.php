@php
    $t = $board['totals'];
    $rows = collect($board['rows']);
    // Öncelik: devam eden, sıradaki, son biten dersler.
    $ongoing = $rows->where('state', 'ongoing')->values();
    $upcoming = $rows->where('state', 'upcoming')->take(2)->values();
    $recent = $rows->whereIn('state', ['closed', 'pending'])->sortByDesc(fn ($r) => $r['end']->timestamp)->take(3)->values();
    $shown = $ongoing->concat($upcoming)->concat($recent)->take(7);
    $stateLabel = ['upcoming' => 'Bekliyor', 'ongoing' => '● Devam', 'closed' => 'Bitti', 'pending' => 'Kapanıyor'];
@endphp
<div class="aw-head">
    <div><strong>📋 Bugünkü Yoklama</strong><span>{{ $board['date']->translatedFormat('d F, l') }}</span></div>
    <a href="{{ route('attendance.index') }}" class="aw-link" title="Yoklama sayfası">Aç →</a>
</div>

@if(! $board['has_schedule'])
    <p class="aw-empty">Ders programı tanımlı değil. <a href="{{ route('attendance.schedule') }}">Programı tanımla</a> — yoklama otomatik alınsın.</p>
@elseif($board['off'])
    <p class="aw-empty">🏖 Bugün tatil / ders yapılmayan gün{{ $board['off_note'] ? ': ' . $board['off_note'] : '' }}.</p>
@elseif($rows->isEmpty())
    <p class="aw-empty">Bugün için tanımlı ders yok.</p>
@else
    <div class="aw-chips">
        <span><b>{{ $t['lessons'] }}</b>ders</span>
        <span class="live"><b>{{ $t['ongoing'] }}</b>devam eden</span>
        <span class="ok"><b>{{ $t['closed'] }}</b>alınan</span>
        <span class="bad"><b>{{ $t['absent'] }}</b>gelmeyen</span>
    </div>
    <ul class="aw-list">
        @foreach($shown as $r)
            <li>
                <a href="{{ $r['lesson_id'] ? route('attendance.lesson', $r['lesson_id']) : route('attendance.index') }}">
                    <div class="aw-row"><b>{{ $r['class'] }}</b><small>{{ $r['time'] }}</small><em class="aw-st {{ $r['state'] }}">{{ $stateLabel[$r['state']] }}</em></div>
                    @if($r['state'] !== 'upcoming')
                        <div class="aw-bar"><i style="width:{{ $r['total'] > 0 ? round($r['present'] / $r['total'] * 100) : 0 }}%"></i></div>
                        <div class="aw-meta"><span class="ok">{{ $r['present'] }} geldi</span>@if($r['absent'] !== null)<span class="bad">{{ $r['absent'] }} gelmedi</span>@endif<span>/ {{ $r['total'] }}</span></div>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
    @foreach(array_slice($board['absent_groups'], 0, 1) as $g)
        <div class="aw-absent">
            <b>{{ $g['class'] }} · gelmeyenler ({{ $g['count'] }})</b>
            <div>{{ implode(', ', array_slice($g['names'], 0, 8)) }}@if(count($g['names']) > 8) <em>+{{ $g['count'] - 8 }}</em>@endif</div>
        </div>
    @endforeach
@endif
