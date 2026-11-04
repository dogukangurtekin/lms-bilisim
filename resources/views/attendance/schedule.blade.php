@extends('layout.app')
@section('title', 'Ders Programı')
@section('content')
@include('attendance._style')
@php $days = \App\Models\LessonSlot::WEEKDAYS; @endphp
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

    <div class="att-card">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:10px">
            <h3 style="margin:0">Haftalık Program</h3>
            <form method="GET" class="att-filter" style="gap:8px">
                <select name="class_id" onchange="this.form.submit()" aria-label="Sınıf">
                    @foreach($classes as $c)<option value="{{ $c->id }}" @selected($selected === (int) $c->id)>{{ $c->name }}/{{ $c->section }} ({{ (int) ($counts[$c->id] ?? 0) }} ders)</option>@endforeach
                </select>
            </form>
        </div>
        @if($classes->isEmpty())
            <div class="att-empty">Size atanmış sınıf bulunamadı.</div>
        @elseif($slots->isEmpty())
            <div class="att-empty">Bu sınıf için henüz ders tanımlanmamış. Şablonla yükleyebilir ya da yukarıdan tek ders ekleyebilirsiniz.</div>
        @else
            <div class="att-wrap"><table class="att-table">
                <thead><tr><th>Gün</th><th>Saat</th><th>Ders no</th><th>Ders</th><th>Öğretmen</th><th></th></tr></thead>
                <tbody>
                @foreach($slots as $weekday => $list)
                    @foreach($list as $s)
                        <tr>
                            <td>@if($loop->first)<b>{{ $days[$weekday] ?? $weekday }}</b>@endif</td>
                            <td>{{ $s->timeLabel() }}</td>
                            <td>{{ $s->lesson_no ?: '—' }}</td>
                            <td>{{ $s->subject }}</td>
                            <td>{{ $s->teacher?->name ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('attendance.schedule.destroy', $s) }}" data-confirm="Bu ders programdan silinsin mi? (Geçmiş yoklama kayıtları korunur.)" style="margin:0">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit" title="Sil">🗑</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
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
