@extends('layout.app')
@section('title', 'Yoklama')
@section('content')
@include('attendance._style')
@php
    $stateLabel = ['upcoming' => 'Bekliyor', 'ongoing' => '● Devam ediyor', 'closed' => 'Tamamlandı', 'pending' => 'Kapatılıyor…'];
    $t = $board['totals'];
@endphp
<div class="att">
    <div class="top">
        <h1>📋 Yoklama</h1>
        <div class="att-tools">
            <a class="btn" href="{{ route('attendance.schedule') }}">🗓 Ders Programı</a>
            <a class="btn" href="{{ route('attendance.export', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'class_id' => $classId ?: null]) }}" title="Seçili tarih aralığının yoklamasını Excel'de açılacak CSV olarak indir">📄 Dökümü İndir</a>
        </div>
    </div>

    @if(session('ok'))<div class="att-flash ok">{{ session('ok') }}</div>@endif
    @if(session('err'))<div class="att-flash err">{{ session('err') }}</div>@endif

    <div class="att-tabs">
        <a class="att-tab {{ $tab === 'today' ? 'on' : '' }}" href="{{ route('attendance.index') }}">Bugün</a>
        <a class="att-tab {{ $tab === 'history' ? 'on' : '' }}" href="{{ route('attendance.index', ['tab' => 'history']) }}">Yoklama Geçmişi</a>
    </div>

    @if(! $board['has_schedule'])
        <div class="att-empty">
            <b>Henüz ders programı tanımlı değil.</b><br>
            Yoklamanın otomatik alınabilmesi için sınıfların haftalık ders programı gerekir. Şablonu doldurup yükleyebilir ya da dersleri tek tek ekleyebilirsiniz.<br>
            <a class="btn" style="margin-top:10px" href="{{ route('attendance.schedule') }}">Ders Programını Tanımla</a>
        </div>
    @elseif($tab === 'today')
        <div class="att-card">
            <h3>{{ $board['date']->translatedFormat('d F Y, l') }}</h3>
            @if($board['off'])
                <div class="att-flash err" style="margin-bottom:10px">🏖 Bugün tatil / ders yapılmayan gün olarak işaretli{{ $board['off_note'] ? ': ' . $board['off_note'] : '' }}. Otomatik yoklama alınmaz.</div>
            @endif
            <div class="att-chips">
                <div class="att-chip"><strong>{{ $t['lessons'] }}</strong><small>Bugünkü ders</small></div>
                <div class="att-chip live"><strong>{{ $t['ongoing'] }}</strong><small>Şu an devam eden</small></div>
                <div class="att-chip ok"><strong>{{ $t['closed'] }}</strong><small>Yoklaması alınan</small></div>
                <div class="att-chip bad"><strong>{{ $t['absent'] }}</strong><small>Toplam gelmeyen</small></div>
            </div>
        </div>

        <div class="att-grid2">
            <div class="att-card">
                <h3>Bugünün Dersleri</h3>
                @if($board['rows'] === [])
                    <div class="att-empty">Bugün için tanımlı ders yok.</div>
                @else
                    <div class="att-wrap"><table class="att-table">
                        <thead><tr><th>Saat</th><th>Sınıf</th><th>Ders</th><th>Durum</th><th>Gelen</th><th>Gelmeyen</th><th>Derste olmayanlar</th><th></th></tr></thead>
                        <tbody>
                        @foreach($board['rows'] as $r)
                            <tr>
                                <td><b>{{ $r['time'] }}</b>@if($r['lesson_no'])<br><small>{{ $r['lesson_no'] }}. ders</small>@endif</td>
                                <td>{{ $r['class'] }}</td>
                                <td>{{ $r['subject'] }}</td>
                                <td><span class="att-badge st-{{ $r['state'] }}">{{ $stateLabel[$r['state']] }}</span></td>
                                <td>
                                    @if($r['state'] === 'upcoming')<span class="muted">—</span>
                                    @else
                                        <span class="att-ok">{{ $r['present'] }}</span> / {{ $r['total'] }}
                                        <div class="att-bar"><i style="width:{{ $r['total'] > 0 ? round($r['present'] / $r['total'] * 100) : 0 }}%"></i></div>
                                    @endif
                                </td>
                                <td>@if($r['absent'] !== null)<span class="{{ $r['absent'] > 0 ? 'att-bad' : 'att-ok' }}">{{ $r['absent'] }}</span>@else<span class="muted">—</span>@endif</td>
                                <td style="min-width:170px">
                                    @if($r['state'] === 'upcoming')<span class="muted">—</span>
                                    @elseif(empty($r['missing']))<span class="att-ok">Herkes derste</span>
                                    @else
                                        <ul class="att-names {{ $r['state'] === 'ongoing' ? 'wait' : '' }}" title="{{ $r['state'] === 'ongoing' ? 'Henüz sisteme girmeyenler' : 'Derse girmeyenler' }}">
                                            @foreach(array_slice($r['missing'], 0, 12) as $n)<li>{{ $n }}</li>@endforeach
                                            @if(count($r['missing']) > 12)<li>+{{ count($r['missing']) - 12 }}</li>@endif
                                        </ul>
                                    @endif
                                </td>
                                <td>@if(! $r['lesson_id'] && $r['state'] !== 'upcoming')<form method="POST" action="{{ route('attendance.slot.open', $r['slot_id']) }}" style="display:inline;margin:0">@csrf<button class="btn btn-sm" type="submit" title="Derse giren olmasa da elle yoklama al">📝 Yoklama Al</button></form>@endif
                                    @if($r['lesson_id'])<a class="btn btn-sm" href="{{ route('attendance.lesson', $r['lesson_id']) }}">{{ $r['state'] === 'closed' ? 'Detay' : '📝 Yoklama Al' }}</a>
                                    <form method="POST" action="{{ route('attendance.lesson.all-present', $r['lesson_id']) }}" style="display:inline;margin:0" onsubmit="return confirm('Sınıftaki tüm öğrenciler Geldi olarak işaretlensin mi? (İzinliler değişmez)')">@csrf<button class="btn btn-sm" type="submit" title="Sınıf tam: herkesi Geldi yap">✅ Sınıf tam</button></form>@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                    <p class="muted" style="margin:10px 0 0;font-size:12.5px">Öğrenci, ders saatinde (başlamadan {{ \App\Services\LessonAttendanceService::EARLY_GRACE_MIN }} dk önce dahil) sisteme girip kullanırsa "Geldi" yazılır. Ders bitince derse hiç girmeyenler "Gelmedi" olur.</p>
                @endif
            </div>

            <div class="att-card">
                <h3>Son Biten Derslerde Gelmeyenler</h3>
                @forelse($board['absent_groups'] as $g)
                    <div style="margin-bottom:12px">
                        <a href="{{ route('attendance.lesson', $g['lesson_id']) }}" style="font-weight:800;text-decoration:none">{{ $g['class'] }} · {{ $g['subject'] }} <small class="muted">({{ $g['time'] }})</small></a>
                        <span class="att-bad"> {{ $g['count'] }} öğrenci</span>
                        <ul class="att-names">@foreach($g['names'] as $n)<li>{{ $n }}</li>@endforeach</ul>
                    </div>
                @empty
                    <div class="att-empty">Bugün biten derslerde gelmeyen öğrenci yok{{ $t['closed'] === 0 ? ' (henüz yoklaması alınmış ders yok)' : '' }}.</div>
                @endforelse
            </div>
        </div>
    @else
        <div class="att-card">
            <form method="GET" class="att-filter">
                <input type="hidden" name="tab" value="history">
                <div><label>Başlangıç</label><input type="date" name="from" value="{{ $from->toDateString() }}"></div>
                <div><label>Bitiş</label><input type="date" name="to" value="{{ $to->toDateString() }}"></div>
                <div><label>Sınıf</label>
                    <select name="class_id">
                        <option value="">Tüm sınıflar</option>
                        @foreach($classes as $c)<option value="{{ $c->id }}" @selected($classId === (int) $c->id)>{{ $c->name }}/{{ $c->section }}</option>@endforeach
                    </select>
                </div>
                <button class="btn" type="submit">Filtrele</button>
            </form>
        </div>

        <div class="att-grid2">
            <div class="att-card">
                <h3>Dersler ({{ $lessons->total() }})</h3>
                @if($lessons->isEmpty())
                    <div class="att-empty">Bu aralıkta yoklaması alınmış ders yok.</div>
                @else
                    <div class="att-wrap"><table class="att-table">
                        <thead><tr><th>Tarih</th><th>Saat</th><th>Sınıf</th><th>Ders</th><th>Gelen</th><th>Gelmeyen</th><th>Derste olmayanlar</th><th></th></tr></thead>
                        <tbody>
                        @foreach($lessons as $l)
                            <tr>
                                <td>{{ $l->date->format('d.m.Y') }}</td>
                                <td>{{ $l->start_at->format('H:i') }}-{{ $l->end_at->format('H:i') }}</td>
                                <td>{{ $l->classLabel() }}</td>
                                <td>{{ $l->subject }}</td>
                                <td><span class="att-ok">{{ $l->present_count }}</span> / {{ $l->total_students }}</td>
                                <td><span class="{{ $l->absent_count > 0 ? 'att-bad' : 'att-ok' }}">{{ $l->absent_count }}</span></td>
                                <td style="min-width:170px">
                                    @php $names = $absentNames[$l->id] ?? []; @endphp
                                    @if($names === [])<span class="muted">—</span>
                                    @else
                                        <ul class="att-names">@foreach(array_slice($names, 0, 8) as $n)<li>{{ $n }}</li>@endforeach @if(count($names) > 8)<li>+{{ count($names) - 8 }}</li>@endif</ul>
                                    @endif
                                </td>
                                <td><a class="btn btn-sm" href="{{ route('attendance.lesson', $l) }}">Detay</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                    <div style="margin-top:10px">{{ $lessons->links() }}</div>
                @endif
            </div>

            <div class="att-card">
                <h3>En Çok Devamsızlık Yapanlar</h3>
                @forelse($topAbsent as $a)
                    <div style="display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid #eef2f7">
                        <span><b>{{ $a->name }}</b> <small class="muted">{{ $a->class_name }}/{{ $a->class_section }}</small></span>
                        <span class="att-bad">{{ $a->absences }} ders</span>
                    </div>
                @empty
                    <div class="att-empty">Bu aralıkta devamsızlık kaydı yok.</div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
