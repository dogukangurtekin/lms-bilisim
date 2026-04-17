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
        <input type="hidden" name="list" value="1">
        <div style="min-width:260px">
            <label>Arama</label>
            <input name="q" value="{{ $q ?? request('q') }}" placeholder="Ad soyad, no, kullanici adi, e-posta, sinif">
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
            <th>Sira</th><th>Ogrenci</th><th>Sinif</th><th>XP</th><th>Avatar</th><th>Rozet</th><th>Islem</th>
        </tr>
        </thead>
        <tbody>
        @forelse($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
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
                <td>{{ (int) ($stats[$student->id]['badges'] ?? 0) }}</td>
                <td class="actions">
                    <a class="btn" target="_blank" href="{{ route('student-data.certificate', $student) }}">Sertifika</a>
                    <button
                        class="btn student-login-info-btn"
                        type="button"
                        data-update-url="{{ route('students.update', $student) }}"
                        data-delete-url="{{ route('students.destroy', $student) }}"
                        data-name="{{ $student->user?->name }}"
                        data-first-name="{{ \Illuminate\Support\Str::before($student->user?->name ?? '', ' ') }}"
                        data-last-name="{{ \Illuminate\Support\Str::after($student->user?->name ?? '', ' ') }}"
                        data-student-no="{{ $student->student_no }}"
                        data-username="{{ $student->credential?->username ?: \Illuminate\Support\Str::before((string) ($student->user?->email ?? ''), '@') }}"
                        data-password="{{ $student->credential?->plain_password }}"
                        data-class-id="{{ $student->school_class_id }}"
                    >
                        Giris Bilgileri
                    </button>
                    <a class="btn" target="_blank" href="{{ route('student-data.progress-report', $student) }}">Gelisim Karnesi</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#64748b">
                    @if(request('list') !== '1' && empty(($q ?? request('q'))) && empty(($className ?? request('class_name'))) && empty(($section ?? request('section'))))
                        Listeleme icin arama veya filtre giriniz.
                    @else
                        Arama kriterine uygun ogrenci bulunamadi.
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($students instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div style="margin-top:12px">
        {{ $students->links() }}
    </div>
@endif

<div id="student-login-modal" style="position:fixed;inset:0;background:rgba(2,6,23,.65);display:none;align-items:center;justify-content:center;z-index:1600;padding:16px">
    <div style="width:min(560px,100%);background:#fff;border-radius:12px;padding:16px;box-shadow:0 24px 48px rgba(2,6,23,.35)">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px">
            <strong id="student-login-modal-title">Giris Bilgileri</strong>
            <button class="btn" type="button" id="student-login-modal-close">Kapat</button>
        </div>

        <form id="student-login-update-form" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="student_no" id="student-login-student-no">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                    <label>Ad</label>
                    <input id="student-login-first-name" name="first_name" required>
                </div>
                <div>
                    <label>Soyad</label>
                    <input id="student-login-last-name" name="last_name" required>
                </div>
            </div>

            <div style="margin-top:8px">
                <label>Kullanici Adi</label>
                <input id="student-login-username" readonly>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px">
                <div>
                    <label>Sifre (Yeni ya da mevcut)</label>
                    <input id="student-login-password" name="password" minlength="6" maxlength="72" placeholder="Sifre girin">
                </div>
                <div>
                    <label>Sifre Tekrar</label>
                    <input id="student-login-password-confirmation" name="password_confirmation" minlength="6" maxlength="72" placeholder="Sifreyi tekrar girin">
                </div>
            </div>

            <div style="margin-top:8px">
                <label>Sinif</label>
                <select id="student-login-class-id" name="school_class_id" required>
                    @foreach(($schoolClasses ?? collect()) as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}/{{ $class->section }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions" style="margin-top:14px;justify-content:flex-end">
                <button class="btn" type="submit">Guncelle</button>
            </div>
        </form>

        <form id="student-login-delete-form" method="POST" style="margin-top:8px">
            @csrf
            @method('DELETE')
            <div class="actions" style="justify-content:flex-end">
                <button class="btn btn-danger" type="submit">Sil</button>
            </div>
        </form>
    </div>
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

(() => {
    const modal = document.getElementById('student-login-modal');
    const closeBtn = document.getElementById('student-login-modal-close');
    const updateForm = document.getElementById('student-login-update-form');
    const deleteForm = document.getElementById('student-login-delete-form');
    const titleEl = document.getElementById('student-login-modal-title');
    const firstNameEl = document.getElementById('student-login-first-name');
    const lastNameEl = document.getElementById('student-login-last-name');
    const usernameEl = document.getElementById('student-login-username');
    const passwordEl = document.getElementById('student-login-password');
    const passwordConfirmEl = document.getElementById('student-login-password-confirmation');
    const studentNoEl = document.getElementById('student-login-student-no');
    const classIdEl = document.getElementById('student-login-class-id');

    function openModal(data) {
        updateForm.action = data.updateUrl;
        deleteForm.action = data.deleteUrl;
        titleEl.textContent = `Giris Bilgileri - ${data.name || ''}`.trim();
        firstNameEl.value = data.firstName || '';
        lastNameEl.value = data.lastName || '';
        usernameEl.value = data.username || '';
        passwordEl.value = data.password || '';
        passwordConfirmEl.value = data.password || '';
        studentNoEl.value = data.studentNo || '';
        classIdEl.value = data.classId || '';
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    document.querySelectorAll('.student-login-info-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            openModal({
                updateUrl: btn.dataset.updateUrl || '',
                deleteUrl: btn.dataset.deleteUrl || '',
                name: btn.dataset.name || '',
                firstName: btn.dataset.firstName || '',
                lastName: btn.dataset.lastName || '',
                username: btn.dataset.username || '',
                password: btn.dataset.password || '',
                studentNo: btn.dataset.studentNo || '',
                classId: btn.dataset.classId || '',
            });
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    deleteForm?.addEventListener('submit', (e) => {
        const ok = window.AppDialog?.confirm
            ? window.AppDialog.confirm('Bu ogrenciyi silmek istediginize emin misiniz?')
            : window.confirm('Bu ogrenciyi silmek istediginize emin misiniz?');
        if (!ok) e.preventDefault();
    });
})();
</script>
@endpush
@endsection
