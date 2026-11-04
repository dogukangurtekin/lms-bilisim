@extends('layout.app')
@section('title', 'Ders Programı')
@section('content')
@include('attendance._style')
@php $days = $weekdays = \App\Models\LessonSlot::WEEKDAYS; @endphp
<style>
    .att-grid{width:100%;border-collapse:collapse;min-width:640px;table-layout:fixed}
    .att-grid th,.att-grid td{border:1px solid #cbd5e1;text-align:center;vertical-align:middle;padding:6px 4px}
    .att-grid thead th{background:#f1f5f9;font-weight:600;font-size:12.5px;color:#334155}
    .att-grid thead th b{display:block;font-size:17px;color:#0f172a}
    .att-grid thead th small{display:block;font-size:11px;color:#64748b;white-space:nowrap}
    .att-grid-day{width:88px;background:#f8fafc!important;font-size:24px!important;font-weight:700!important;color:#0f172a!important}
    .att-grid-day small{display:block;font-size:10.5px;font-weight:600;color:#64748b}
    .att-grid td{height:66px;background:#fff}
    .att-cls{position:relative;display:inline-flex;flex-direction:column;align-items:center;min-width:54px;margin:2px;padding:4px 18px 4px 10px;border-radius:10px;background:#eef2ff;border:1px solid #c7d2fe}
    .att-cls b{font-size:15px;color:#3730a3;line-height:1.2}
    .att-cls small{font-size:9.5px;font-weight:700;color:#475569;text-transform:uppercase;max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .att-cls form{margin:0;position:absolute;top:1px;right:1px}
    .att-cls button{border:0;background:transparent;color:#94a3b8;font-size:11px;line-height:1;cursor:pointer;padding:3px}
    .att-cls button:hover{color:#b91c1c}
</style>
<div class="att">
    <div class="top">
        <h1>🗓 Ders Programı</h1>
        <div class="att-tools"><a class="btn" href="{{ route('attendance.index') }}">← Yoklama</a></div>
    </div>

    @if(session('ok'))<div class="att-flash ok">{{ session('ok') }}</div>@endif
    @if(session('err'))
        <div class="att-flash err">{{ session('err') }}
            @if(session('import_errors'))<ul class="att-errors">@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
        </div>
    @endif
    @if($errors->any())<div class="att-flash err">{{ $errors->first() }}</div>@endif

    <div class="att-grid2">
        <div class="att-card">
            <h3>📥 Şablonla Toplu Yükle</h3>
            <p class="muted" style="margin:0 0 10px;font-size:13.5px;line-height:1.55">
                Şablonu indirip okulunuzun ders programını doldurun ve yükleyin. Sütunlar: <b>Sınıf, Şube, Gün, Ders No, Başlangıç, Bitiş, Ders Adı, Öğretmen</b>.
                Gün adı (Pazartesi…), saat <code>09:00</code> biçiminde yazılır; sınıf ve şube sistemdeki adlarla aynı olmalı. Dosyada hata varsa hiçbir ders kaydedilmez, hatalar satır satır gösterilir.
            </p>
            <form method="POST" action="{{ route('attendance.schedule.import') }}" enctype="multipart/form-data" class="att-form">
                @csrf
                <div style="grid-column:1/-1"><label>Doldurulmuş şablon (xlsx, csv)</label><input type="file" name="file" accept=".xlsx,.csv,.txt" required></div>
                <label style="grid-column:1/-1;display:flex;gap:8px;align-items:flex-start;font-weight:600;cursor:pointer">
                    <input type="checkbox" name="replace" value="1" style="width:auto;margin-top:3px" checked>
                    <span>Dosyadaki sınıfların mevcut programını bu dosyadakiyle <b>değiştir</b> (işaretli değilse yalnızca yeni dersler eklenir).</span>
                </label>
                <div style="grid-column:1/-1;display:flex;gap:8px;flex-wrap:wrap">
                    <button class="btn" type="submit">Yükle ve Programı Tanımla</button>
                    <a class="btn btn-ghost" href="{{ route('attendance.schedule.template') }}">⬇ Şablonu İndir (.xlsx)</a>
                </div>
            </form>
        </div>

        <div class="att-card">
            <h3>➕ Tek Ders Ekle</h3>
            <form method="POST" action="{{ route('attendance.schedule.store') }}" class="att-form">
                @csrf
                <div><label>Sınıf</label>
                    <select name="school_class_id" required>
                        @foreach($classes as $c)<option value="{{ $c->id }}" @selected((int) old('school_class_id', $selected) === (int) $c->id)>{{ $c->name }}/{{ $c->section }}</option>@endforeach
                    </select>
                </div>
                <div><label>Gün</label>
                    <select name="weekday" required>@foreach($days as $n => $d)<option value="{{ $n }}" @selected((int) old('weekday', 1) === $n)>{{ $d }}</option>@endforeach</select>
                </div>
                <div><label>Başlangıç</label><input type="time" name="start_time" value="{{ old('start_time') }}" required></div>
                <div><label>Bitiş</label><input type="time" name="end_time" value="{{ old('end_time') }}" required></div>
                <div><label>Ders adı</label><input type="text" name="subject" maxlength="120" value="{{ old('subject') }}" placeholder="Bilişim Teknolojileri"></div>
                <div><label>Ders no</label><input type="number" name="lesson_no" min="1" max="20" value="{{ old('lesson_no') }}" placeholder="isteğe bağlı"></div>
                <div style="grid-column:1/-1"><button class="btn" type="submit">Programa Ekle</button></div>
            </form>
        </div>
    </div>

    @php $short = [1 => 'Pa', 2 => 'Sa', 3 => 'Ça', 4 => 'Pe', 5 => 'Cu', 6 => 'Ct', 7 => 'Pz']; @endphp
    <div class="att-card">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:baseline;justify-content:space-between;margin-bottom:10px">
            <h3 style="margin:0">Haftalık Program</h3>
            <small class="muted">{{ $totalSlots }} ders · tüm sınıflar tek tabloda</small>
        </div>
        @if($classes->isEmpty())
            <div class="att-empty">Size atanmış sınıf bulunamadı.</div>
        @elseif($totalSlots === 0)
            <div class="att-empty">Henüz ders tanımlanmamış. Şablonla yükleyebilir ya da yukarıdan tek ders ekleyebilirsiniz.</div>
        @else
            <div class="att-wrap"><table class="att-grid">
                <thead>
                    <tr>
                        <th class="att-grid-day"></th>
                        @foreach($columns as $c)
                            <th><b>{{ $c['no'] ?: '' }}</b><small>{{ $c['start'] }} - {{ $c['end'] }}</small></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($dayNums as $d)
                    <tr>
                        <th class="att-grid-day" title="{{ $weekdays[$d] ?? $d }}">{{ $short[$d] ?? $d }}<small>{{ $weekdays[$d] ?? '' }}</small></th>
                        @foreach($columns as $c)
                            <td>
                                @foreach(($grid[$d][$c['key']] ?? []) as $s)
                                    <div class="att-cls" title="{{ $s->schoolClass?->name }}/{{ $s->schoolClass?->section }} · {{ $s->subject }}">
                                        <b>{{ $s->schoolClass?->name }}{{ $s->schoolClass?->section }}</b>
                                        <small>{{ $s->subject }}</small>
                                        <form method="POST" action="{{ route('attendance.schedule.destroy', $s) }}" data-confirm="{{ $s->schoolClass?->name }}{{ $s->schoolClass?->section }} - {{ $weekdays[$d] ?? '' }} {{ $c['start'] }} dersi programdan silinsin mi? (Geçmiş yoklama kayıtları korunur.)">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Dersi sil" aria-label="Dersi sil">✕</button>
                                        </form>
                                    </div>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    @if($isAdmin)
        <div class="att-card">
            <h3>🏖 Tatil / Ders Yapılmayan Günler</h3>
            <p class="muted" style="margin:0 0 10px;font-size:13.5px">Bu günlerde (bayram, tatil, sınav günü vb.) otomatik yoklama alınmaz; aksi halde öğrenciler "gelmedi" yazılırdı.</p>
            <form method="POST" action="{{ route('attendance.offday.store') }}" class="att-form" style="margin-bottom:12px">
                @csrf
                <div><label>Tarih</label><input type="date" name="date" required></div>
                <div><label>Açıklama</label><input type="text" name="note" maxlength="190" placeholder="Örn. Cumhuriyet Bayramı"></div>
                <div><button class="btn" type="submit">Tatil Günü Ekle</button></div>
            </form>
            @forelse($offDays as $o)
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #eef2f7">
                    <span><b>{{ $o->date->format('d.m.Y') }}</b> {{ $o->date->translatedFormat('l') }} <small class="muted">{{ $o->note }}</small></span>
                    <form method="POST" action="{{ route('attendance.offday.destroy', $o) }}" style="margin:0">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit" title="Kaldır">✕</button></form>
                </div>
            @empty
                <div class="att-empty">Tanımlı tatil günü yok.</div>
            @endforelse
        </div>
    @endif
</div>
@endsection
