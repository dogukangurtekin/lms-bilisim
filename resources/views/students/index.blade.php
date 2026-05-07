@extends('layout.app')
@section('title','Ogrenciler')
@section('content')
<div id="bulk-upload-overlay" class="bulk-upload-overlay" aria-hidden="true">
    <div class="bulk-upload-overlay-card">
        <div class="bulk-spinner"></div>
        <strong>Öğrenciler ekleniyor</strong>
        <p>Lütfen bekleyin, işlem tamamlanana kadar sayfayı kapatmayın.</p>
    </div>
</div>

<div id="students-page-content">
<div class="top">
    <h1>Ogrenciler</h1>
    <div class="actions">
        <a class="btn" href="{{ route('students.bulk.template') }}">Toplu Kayit Sablonu (.xlsx)</a>
        <button class="btn" type="button" id="students-reset-passwords-btn">Tum Sifreleri 123456 Yap</button>
        <form id="delete-all-students-form" method="POST" action="{{ route('students.destroyAll') }}" style="display:none">
            @csrf
            @method('DELETE')
        </form>
        <button type="button" class="btn btn-danger" data-delete-form="delete-all-students-form">Tum Ogrencileri Sil</button>
    </div>
</div>
<div class="card">
    <form id="bulk-upload-form" method="POST" action="{{ route('students.bulk.store') }}" enctype="multipart/form-data" class="actions" style="margin-bottom:10px;align-items:end">
        @csrf
        <div style="min-width:280px">
            <label>Toplu Kayit Dosyasi (xls/xlsx/csv/txt)</label>
            <input type="file" name="file" required>
        </div>
        <button id="bulk-upload-submit" class="btn" type="submit">Toplu Kaydet</button>
    </form>
    @if($errors->any())
        <div style="color:#b91c1c;margin-bottom:10px">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="actions" style="margin-bottom:10px;align-items:end;flex-wrap:wrap">
        <div style="min-width:230px">
            <label>Ad Soyad</label>
            <input name="name" value="{{ $name ?? request('name') }}" placeholder="Ogrenci adi soyadi">
        </div>
        <div style="min-width:170px">
            <label>Sinif</label>
            <select name="class_name">
                <option value="">Tum siniflar</option>
                @foreach(($classNames ?? collect()) as $cn)
                    <option value="{{ $cn }}" @selected(($className ?? request('class_name')) === $cn)>{{ $cn }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:140px">
            <label>Sube</label>
            <select name="section">
                <option value="">Tum subeler</option>
                @foreach(($sections ?? collect()) as $sec)
                    <option value="{{ $sec }}" @selected(($section ?? request('section')) === $sec)>{{ $sec }}</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="sort" value="{{ $sort ?? 'id' }}">
        <input type="hidden" name="dir" value="{{ $dir ?? 'desc' }}">
        <button class="btn" type="submit">Filtrele</button>
        <a class="btn" href="{{ route('students.index') }}">Temizle</a>
    </form>

    <table>
        <thead><tr><th>ID</th><th>No</th><th>Ogrenci</th><th>Kullanici Adi</th><th>Sifre</th><th>Sinif</th><th>Islem</th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->student_no }}</td>
                <td>{{ $item->user?->name }}</td>
                <td>{{ $item->credential?->username ?? '-' }}</td>
                <td>{{ $item->credential?->plain_password ?? '-' }}</td>
                <td>{{ $item->schoolClass?->name }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('students.show', $item) }}">Goster</a>
                    <a class="btn" href="{{ route('students.edit', $item) }}">Duzenle</a>
                    <form id="delete-{{ '$' }}item->id" method="POST" action="{{ route('students.destroy', $item) }}">@csrf @method('DELETE')</form>
                    <button type="button" class="btn btn-danger" data-delete-form="delete-{{ '$' }}item->id">Sil</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links('partials.pagination') }}
</div>

</div>

<div id="students-password-progress" style="position:fixed;right:20px;bottom:20px;z-index:1500;display:none;min-width:320px;max-width:380px;background:#0f172a;color:#fff;border-radius:12px;padding:12px;box-shadow:0 20px 40px rgba(15,23,42,.35)">
    <strong id="students-password-progress-title">Sifreler guncelleniyor...</strong>
    <div style="margin-top:6px;font-size:13px;opacity:.9" id="students-password-progress-text">%0 tamamlandi</div>
    <div style="height:8px;background:rgba(255,255,255,.2);border-radius:999px;margin-top:8px;overflow:hidden">
        <div id="students-password-progress-bar" style="height:100%;width:0%;background:#22c55e"></div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const btn = document.getElementById('students-reset-passwords-btn');
    const box = document.getElementById('students-password-progress');
    const title = document.getElementById('students-password-progress-title');
    const text = document.getElementById('students-password-progress-text');
    const bar = document.getElementById('students-password-progress-bar');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function postJson(url, payload = {}) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token},
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Islem basarisiz');
        return data;
    }

    function setUi(percent, processed, total) {
        box.style.display = 'block';
        bar.style.width = percent + '%';
        text.textContent = `%${percent} tamamlandi (${processed}/${total})`;
    }

    async function runReset() {
        const ok = await (window.AppDialog?.confirm
            ? window.AppDialog.confirm('Tum ogrenci sifreleri 123456 yapilsin mi?')
            : Promise.resolve(false));
        if (!ok) return;
        try {
            btn.disabled = true;
            title.textContent = 'Sifreler guncelleniyor...';
            setUi(0, 0, 1);

            const start = await postJson('{{ route('student-data.passwords.reset-all.start') }}', {});
            let done = false;
            while (!done) {
                const step = await postJson('{{ url('/ogrenci-verileri/sifreleri-sifirla/adim') }}/' + start.task_id, {});
                setUi(step.percent || 0, step.processed || 0, step.total || 0);
                done = !!step.completed;
                if (!done) await new Promise(r => setTimeout(r, 180));
                if (done) {
                    title.textContent = 'Islem tamamlandi';
                    text.textContent = step.message || 'Tum ogrenci sifreleri 123456 yapildi.';
                    setTimeout(() => { box.style.display = 'none'; window.location.reload(); }, 1400);
                }
            }
        } catch (err) {
            title.textContent = 'Islem hatasi';
            text.textContent = err.message || 'Beklenmeyen hata';
        } finally {
            btn.disabled = false;
        }
    }

    btn?.addEventListener('click', runReset);
})();
</script>
@endpush
@endsection

