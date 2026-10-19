@extends('layout.app')
@section('title','Canlı Yarışmalar')
@section('content')
@include('competitions._theme')
<div class="modal" id="compDeleteModal">
    <div class="modal-card">
        <div class="modal-head"><strong>Odayı Sil</strong></div>
        <p style="margin:0 0 14px;color:#475569;">Bu yarışma odasını kalıcı olarak silmek üzeresiniz. Tüm katılımcı kayıtları da birlikte silinecek. Bu işlem geri alınamaz.</p>
        <form method="POST" id="compDeleteForm" style="margin:0;display:flex;gap:8px;justify-content:flex-end;">
            @csrf
            @method('DELETE')
            <button type="button" class="btn" id="compDeleteCancel">Vazgeç</button>
            <button type="submit" class="btn btn-danger">Evet, Sil</button>
        </form>
    </div>
</div>
<div class="cx">
<div class="cx-hero">
    <div class="cx-hero-icon">🏁</div>
    <div>
        <h1>Canlı Yarışmalar</h1>
        <p>Bir oyun seç, oda oluştur. Öğrenciler kodla katılır, "Herkese Başlat" ile herkes aynı anda başlar; canlı sıralama en önde gideni üstte gösterir.</p>
    </div>
    <div class="cx-chips"><span class="cx-chip">🎮 {{ count($games) }} oyun</span><span class="cx-chip">🏠 {{ $rooms->count() }} oda</span></div>
</div>

<div class="cx-grid">
    <div class="cx-panel">
        <h3>Yeni Yarışma Odası Oluştur</h3>
        <form method="POST" action="{{ route('competitions.store') }}" id="compCreateForm">
            @csrf
            <div class="cx-row">
                <label>Oyun / Etkinlik</label>
                <select class="form-control" name="game_slug" required>
                    <option value="">Seçiniz...</option>
                    @foreach($games as $slug => $game)
                        <option value="{{ $slug }}" {{ old('game_slug') === $slug ? 'selected' : '' }}>{{ $game['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="cx-row">
                <label>Sınıf (boş bırakırsan tüm sınıflar katılabilir)</label>
                <select class="form-control" name="school_class_id">
                    <option value="">Tüm sınıflar</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}/{{ $class->section }}</option>
                    @endforeach
                </select>
            </div>
            <div class="cx-row">
                <label>Seviye Aralığı</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input class="form-control" type="number" name="level_from" min="1" value="{{ old('level_from', 1) }}" required style="margin:0">
                    <span>—</span>
                    <input class="form-control" type="number" name="level_to" min="1" value="{{ old('level_to', 5) }}" required style="margin:0">
                </div>
            </div>
            <div class="cx-row">
                <label>Süre (dakika)</label>
                <input class="form-control" type="number" name="duration_minutes" min="1" max="120" value="{{ old('duration_minutes', 10) }}" required>
            </div>
            @if($errors->any())
                <div class="cx-err">{{ $errors->first() }}</div>
            @endif
            <button class="btn btn-primary" type="submit" style="width:100%;font-size:16px">🚀 Yarışma Odası Oluştur</button>
        </form>
    </div>

    <div class="cx-panel light">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <h3 style="margin:0">Geçmiş / Aktif Odalar</h3>
            @if(auth()->user()?->hasRole('admin') && $rooms->isNotEmpty())
                <form method="POST" action="{{ route('competitions.rooms.destroy-all') }}" data-confirm="Tüm canlı yarışma oturumları ve katılımcı kayıtları kalıcı olarak silinecek. Bu işlem geri alınamaz. Devam edilsin mi?" style="margin:0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Tüm Oturumları Sil</button>
                </form>
            @endif
        </div>
        <div style="overflow:auto">
            <table>
                <thead><tr><th>Oyun</th><th>Kod</th><th>Sınıf</th><th>Durum</th><th>İşlem</th></tr></thead>
                <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>{{ $room->game_name }}</td>
                        <td><span class="code">{{ $room->join_code }}</span></td>
                        <td>{{ $room->schoolClass ? $room->schoolClass->name.'/'.$room->schoolClass->section : 'Tümü' }}</td>
                        <td>
                            @if($room->status === 'lobby') <span class="tag lobby">Lobide</span>
                            @elseif($room->status === 'live') <span class="tag live">● Canlı</span>
                            @else <span class="tag done">Bitti</span>
                            @endif
                        </td>
                        <td style="display:flex;gap:6px;flex-wrap:wrap">
                            <a class="btn" href="{{ route('competitions.room.show', $room) }}">Odaya Git</a>
                            <button type="button" class="btn btn-danger comp-delete-trigger" data-delete-url="{{ route('competitions.room.destroy', $room) }}">Sil</button>
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
</div>

<script>
(() => {
    // Klavye Yarışması seçilince seviye/süre alanları gerekmez: oda kendi ekranında (metin seçimiyle) açılır.
    const form = document.getElementById('compCreateForm');
    const sel = form?.querySelector('select[name="game_slug"]');
    const btn = form?.querySelector('button[type="submit"]');
    const sync = () => {
        const kb = sel?.value === 'keyboard-race';
        form.querySelectorAll('input[name="level_from"], input[name="level_to"], input[name="duration_minutes"]').forEach((i) => {
            i.required = !kb;
            const row = i.closest('.cx-row');
            if (row) row.style.display = kb ? 'none' : '';
        });
        if (btn) btn.textContent = kb ? '⌨️ Klavye Yarışması Odası Aç' : '🚀 Yarışma Odası Oluştur';
    };
    sel?.addEventListener('change', sync);
    sync();
})();
(() => {
    const modal = document.getElementById('compDeleteModal');
    const form = document.getElementById('compDeleteForm');
    const cancelBtn = document.getElementById('compDeleteCancel');
    if (!modal || !form) return;

    document.querySelectorAll('.comp-delete-trigger').forEach((btn) => {
        btn.addEventListener('click', () => {
            form.action = btn.getAttribute('data-delete-url');
            modal.classList.add('open');
        });
    });
    const closeModal = () => modal.classList.remove('open');
    cancelBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
})();
</script>
@endsection
