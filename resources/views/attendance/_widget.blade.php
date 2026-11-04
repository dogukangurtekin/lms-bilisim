@php
    $t = $board['totals'];
    $rows = collect($board['rows']);
    // Öncelik: devam eden, sıradaki, son biten dersler (en çok 5 satır).
    $ongoing = $rows->where('state', 'ongoing')->values();
    $upcoming = $rows->where('state', 'upcoming')->take(1)->values();
    $recent = $rows->whereIn('state', ['closed', 'pending'])->sortByDesc(fn ($r) => $r['end']->timestamp)->take(3)->values();
    $shown = $ongoing->concat($upcoming)->concat($recent)->take(5);
    $short = fn ($label) => str_replace('/', '', (string) $label);
    $stateTitle = ['upcoming' => 'Bekliyor', 'ongoing' => 'Devam ediyor', 'closed' => 'Yoklama alındı', 'pending' => 'Kapanıyor'];
@endphp
<div class="aw-head">
    <strong>📋 Bugünkü Yoklama</strong>
    <span>{{ $board['date']->translatedFormat('d M, D') }}</span>
    <a href="{{ route('attendance.index') }}" class="aw-link" title="Yoklama sayfası">Aç →</a>
</div>

@if(! $board['has_schedule'])
    <p class="aw-empty">Ders programı tanımlı değil. <a href="{{ route('attendance.schedule') }}">Programı tanımla</a></p>
@elseif($board['off'])
    <p class="aw-empty">🏖 Bugün tatil / ders yok{{ $board['off_note'] ? ': ' . $board['off_note'] : '' }}.</p>
@elseif($rows->isEmpty())
    <p class="aw-empty">Bugün için ders yok.</p>
@else
    <div class="aw-chips">
        <span title="Bugünkü ders"><b>{{ $t['lessons'] }}</b> ders</span>
        <span class="live" title="Devam eden"><b>{{ $t['ongoing'] }}</b> devam</span>
        <span class="ok" title="Yoklaması alınan"><b>{{ $t['closed'] }}</b> alınan</span>
        <span class="bad" title="Toplam gelmeyen"><b>{{ $t['absent'] }}</b> yok</span>
    </div>
    <ul class="aw-list">
        @foreach($shown as $r)
            <li>
                <a class="aw-item {{ $r['state'] }}" href="{{ $r['lesson_id'] ? route('attendance.lesson', $r['lesson_id']) : route('attendance.index') }}" title="{{ $r['class'] }} · {{ $r['subject'] }} · {{ $r['time'] }} · {{ $stateTitle[$r['state']] }}">
                    <b>{{ $short($r['class']) }}</b>
                    <small>{{ substr($r['time'], 0, 5) }}</small>
                    @if($r['state'] === 'upcoming')
                        <span class="aw-bar"></span><em class="aw-n">bekliyor</em>
                    @else
                        <span class="aw-bar"><i style="width:{{ $r['total'] > 0 ? round($r['present'] / $r['total'] * 100) : 0 }}%"></i></span>
                        @if($r['absent'] !== null)
                            <em class="aw-n {{ $r['absent'] > 0 ? 'bad' : 'ok' }}">{{ $r['absent'] > 0 ? $r['absent'] . ' yok' : 'tam' }}</em>
                        @else
                            <em class="aw-n live">{{ $r['present'] }}/{{ $r['total'] }}</em>
                        @endif
                    @endif
                </a>
                {{-- O sınıfta o saatte derste olmayan (hesabı açık olmayan) öğrencilerin adları --}}
                @if($r['state'] !== 'upcoming' && ! empty($r['missing']))
                    <div class="aw-miss {{ $r['state'] === 'ongoing' ? 'wait' : '' }}">
                        <b>{{ $r['state'] === 'ongoing' ? 'Henüz girmedi:' : 'Gelmedi:' }}</b>
                        {{ implode(', ', array_slice($r['missing'], 0, 6)) }}@if(count($r['missing']) > 6) <em>+{{ count($r['missing']) - 6 }}</em>@endif
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
