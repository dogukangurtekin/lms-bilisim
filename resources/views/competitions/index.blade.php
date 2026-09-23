@extends('layout.app')
@section('title','Canlı Yarışmalar')
@section('content')
<style>
.comp-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.comp-card{border:1px solid #dbe3ef;border-radius:14px;background:#fff;padding:16px}
.comp-row{display:grid;gap:6px;margin-bottom:10px}
.comp-row label{font-size:13px;font-weight:700;color:#475569}
@media (max-width:900px){.comp-grid{grid-template-columns:1fr}}
</style>
<div class="top"><h1>Canlı Yarışmalar</h1></div>
<p style="color:#64748b;margin-top:-6px">
    Oyun ve etkinlikler arasından bir oyun seçip bir yarışma odası oluştur. Öğrenciler odaya katılım koduyla girer,
    "Herkese Başlat" dediğinde herkes aynı anda başlar; süre dolunca ya da seviyeler bitince yarışma otomatik sona erer
    ve canlı sıralama en önde gidenin en üstte olduğu şekilde gösterilir.
</p>

<div class="comp-grid">
    <div class="comp-card">
        <h3>Yeni Yarışma Odası Oluştur</h3>
        <form method="POST" action="{{ route('competitions.store') }}">
            @csrf
            <div class="comp-row">
                <label>Oyun / Etkinlik</label>
                <select class="form-control" name="game_slug" required>
                    <option value="">Seçiniz...</option>
                    @foreach($games as $slug => $game)
                        <option value="{{ $slug }}" {{ old('game_slug') === $slug ? 'selected' : '' }}>{{ $game['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="comp-row">
                <label>Sınıf (boş bırakırsan tüm sınıflar katılabilir)</label>
                <select class="form-control" name="school_class_id">
                    <option value="">Tüm sınıflar</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}/{{ $class->section }}</option>
                    @endforeach
                </select>
            </div>
            <div class="comp-row">
                <label>Seviye Aralığı</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input class="form-control" type="number" name="level_from" min="1" value="{{ old('level_from', 1) }}" required style="margin:0">
                    <span>—</span>
                    <input class="form-control" type="number" name="level_to" min="1" value="{{ old('level_to', 5) }}" required style="margin:0">
                </div>
            </div>
            <div class="comp-row">
                <label>Süre (dakika)</label>
                <input class="form-control" type="number" name="duration_minutes" min="1" max="120" value="{{ old('duration_minutes', 10) }}" required>
            </div>
            @if($errors->any())
                <div style="color:#dc2626;font-size:13px;margin-bottom:8px">{{ $errors->first() }}</div>
            @endif
            <button class="btn btn-primary" type="submit">Yarışma Odası Oluştur</button>
        </form>
    </div>

    <div class="comp-card">
        <h3>Geçmiş / Aktif Odalar</h3>
        <div style="overflow:auto">
            <table>
                <thead><tr><th>Oyun</th><th>Kod</th><th>Sınıf</th><th>Durum</th><th>İşlem</th></tr></thead>
                <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>{{ $room->game_name }}</td>
                        <td>{{ $room->join_code }}</td>
                        <td>{{ $room->schoolClass ? $room->schoolClass->name.'/'.$room->schoolClass->section : 'Tümü' }}</td>
                        <td>
                            @if($room->status === 'lobby') Lobide
                            @elseif($room->status === 'live') Canlı
                            @else Bitti
                            @endif
                        </td>
                        <td style="display:flex;gap:6px;flex-wrap:wrap">
                            <a class="btn" href="{{ route('competitions.room.show', $room) }}">Odaya Git</a>
                            <a class="btn btn-danger" href="{{ route('competitions.room.destroy.confirm', $room) }}">Sil</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Henüz oda yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
