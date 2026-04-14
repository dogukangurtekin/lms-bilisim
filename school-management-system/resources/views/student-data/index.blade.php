@extends('layout.app')
@section('title', 'Ogrenci Verileri')
@section('content')
<div class="top">
    <h1>Ogrenci Verileri</h1>
    <div class="actions">
        <a class="btn" href="{{ route('student-data.login-cards') }}" target="_blank">Giris Kartlari (A4)</a>
        <button class="btn" type="button" id="bulk-report-preview-btn">Gelisim Raporlari Onizle</button>
        <button class="btn" type="button" id="bulk-report-download-btn">Gelisim Raporlari Indir</button>
    </div>
</div>

<div class="card">
    <form method="GET" class="actions" style="margin-bottom:12px;align-items:end;flex-wrap:wrap">
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
        <button class="btn" type="submit">Filtrele</button>
        <a class="btn" href="{{ route('student-data.index') }}">Temizle</a>
    </form>

    <table>
        <thead>
        <tr>
            <th>Ogrenci</th><th>Sinif</th><th>XP</th><th>Avatar</th><th>Rozet</th><th>Islem</th>
        </tr>
        </thead>
        <tbody>
        @foreach($students as $student)
            <tr>
                <td>{{ $student->user?->name }}</td>
                <td>{{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }}</td>
                <td>{{ $stats[$student->id]['xp'] ?? 0 }}</td>
                <td>
                    @if($student->currentAvatar)
                        <img src="{{ asset($student->currentAvatar->image_path) }}" alt="avatar" style="width:40px;height:40px;object-fit:cover;border-radius:6px;vertical-align:middle">
                        {{ $student->currentAvatar->name }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $student->badges->pluck('name')->implode(', ') ?: '-' }}</td>
                <td class="actions">
                    <a class="btn" target="_blank" href="{{ route('student-data.certificate', $student) }}">Sertifika</a>
                    <a class="btn" target="_blank" href="{{ route('student-data.progress-report', $student) }}">Gelisim Karnesi</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div id="bulk-report-progress" style="position:fixed;right:20px;bottom:20px;z-index:1500;display:none;min-width:320px;max-width:380px;background:#0f172a;color:#fff;border-radius:12px;padding:12px;box-shadow:0 20px 40px rgba(15,23,42,.35)">
    <strong id="bulk-report-title">Raporlar hazirlaniyor...</strong>
    <div style="margin-top:6px;font-size:13px;opacity:.9" id="bulk-report-text">%0 tamamlandi</div>
    <div style="height:8px;background:rgba(255,255,255,.2);border-radius:999px;margin-top:8px;overflow:hidden">
        <div id="bulk-report-bar" style="height:100%;width:0%;background:#22c55e"></div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const previewBtn = document.getElementById('bulk-report-preview-btn');
    const downloadBtn = document.getElementById('bulk-report-download-btn');
    const box = document.getElementById('bulk-report-progress');
    const title = document.getElementById('bulk-report-title');
    const text = document.getElementById('bulk-report-text');
    const bar = document.getElementById('bulk-report-bar');
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

    async function start(mode) {
        let previewWin = null;
        try {
            previewBtn.disabled = true;
            downloadBtn.disabled = true;
            title.textContent = 'Raporlar hazirlaniyor...';
            setUi(0, 0, 1);

            if (mode === 'preview') {
                previewWin = window.open('about:blank', '_blank');
                if (previewWin) {
                    previewWin.document.write('<title>Rapor Hazirlaniyor</title><p style="font-family:Arial;padding:16px">Raporlar hazirlaniyor, lutfen bekleyin...</p>');
                    previewWin.document.close();
                }
            }

            const startData = await postJson('{{ route('student-data.reports.bulk-start') }}', {mode});
            let done = false;
            while (!done) {
                const step = await postJson('{{ url('/ogrenci-verileri/gelisim-raporlari/toplu-adim') }}/' + startData.task_id, {});
                setUi(step.percent || 0, step.processed || 0, step.total || 0);
                done = !!step.completed;
                if (!done) await new Promise(r => setTimeout(r, 220));
                if (done) {
                    title.textContent = 'Raporlar hazirlandi';
                    if (mode === 'preview') {
                        if (previewWin && !previewWin.closed) {
                            previewWin.location.href = step.preview_url;
                        } else {
                            window.location.href = step.preview_url;
                        }
                    } else {
                        window.location.href = step.download_url;
                    }
                    setTimeout(() => { box.style.display = 'none'; }, 2200);
                }
            }
        } catch (err) {
            title.textContent = 'Islem hatasi';
            text.textContent = err.message || 'Beklenmeyen hata';
            if (previewWin && !previewWin.closed) {
                previewWin.close();
            }
        } finally {
            previewBtn.disabled = false;
            downloadBtn.disabled = false;
        }
    }

    previewBtn?.addEventListener('click', () => start('preview'));
    downloadBtn?.addEventListener('click', () => start('download'));
})();
</script>
@endpush
@endsection
