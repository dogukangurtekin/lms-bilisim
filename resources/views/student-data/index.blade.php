@extends('layout.app')
@section('title', 'Öğrenci Verileri')
@section('content')
@php
    $listRows = $students instanceof \Illuminate\Contracts\Pagination\Paginator ? $students->getCollection() : collect($students);
    $pageXp = (int) $listRows->sum(fn ($st) => (int) ($stats[$st->id]['xp'] ?? 0));
    $pageBadges = (int) $listRows->sum(fn ($st) => (int) ($stats[$st->id]['badges'] ?? 0));
    $maxXp = max(1, (int) $listRows->max(fn ($st) => (int) ($stats[$st->id]['xp'] ?? 0)));
    $totalCount = method_exists($students, 'total') ? $students->total() : $listRows->count();
    $firstNo = method_exists($students, 'firstItem') ? (int) ($students->firstItem() ?? 1) : 1;
@endphp
<style>
    /* Başarı İstatistikleri ile aynı görünüm dili (an-*) */
    .an-wrap{display:grid;gap:14px}
    .an-filter{display:flex;flex-wrap:wrap;gap:10px;align-items:end}
    .an-filter label{display:grid;gap:4px;font-size:12px;font-weight:700;color:#475569}
    .an-filter select,.an-filter input{min-width:140px;margin:0}
    .an-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .an-kpi{border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#fff}
    .an-kpi span{display:block;font-size:12px;font-weight:700;color:#64748b}
    .an-kpi strong{display:block;font-size:26px;font-weight:900;margin-top:4px;color:#0f172a}
    .an-kpi small{display:block;margin-top:2px;font-size:12px;font-weight:700;color:#64748b}
    .an-card{border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#fff}
    .an-card h3{margin:0 0 4px;font-size:16px}
    .an-table-wrap{overflow:auto;-webkit-overflow-scrolling:touch}
    .an-table{width:100%;border-collapse:collapse;font-size:13px}
    .an-table th,.an-table td{padding:9px 10px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:middle}
    .an-table th{font-size:12px;color:#475569;background:#f8fafc;white-space:nowrap}
    .an-table tr:hover td{background:#faf9ff}
    .an-table td.num,.an-table th.num{text-align:right}
    .an-pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:800;background:#eff6ff;color:#1d4ed8;white-space:nowrap}
    .an-empty{text-align:center;padding:30px;color:#64748b;font-weight:700}
    .an-note{font-size:12px;color:#64748b;line-height:1.5}
    /* Bu sayfaya özel */
    .sd-actions{display:flex;flex-wrap:wrap;gap:8px}
    .sd-actions .btn{margin:0}
    .sd-person{display:flex;align-items:center;gap:10px;min-width:190px}
    .sd-ava{width:38px;height:38px;border-radius:50%;object-fit:cover;flex:0 0 auto;border:2px solid #e2e8f0;background:#f1f5f9}
    .sd-ava.ph{display:grid;place-items:center;font-weight:900;font-size:14px;color:#1d4ed8;background:#eff6ff}
    .sd-person strong{display:block;font-size:13px;color:#0f172a}
    .sd-person small{display:block;font-size:11px;color:#64748b}
    .sd-rank{display:inline-grid;place-items:center;min-width:26px;height:26px;border-radius:50%;background:#e2e8f0;font-size:12px;font-weight:800;color:#334155}
    .sd-rank.top{background:#fde68a;color:#92400e}
    .sd-xp{min-width:120px}
    .sd-xp b{font-size:13px;color:#0f172a}
    .sd-bar{height:6px;border-radius:99px;background:#e2e8f0;overflow:hidden;margin-top:4px}
    .sd-bar i{display:block;height:100%;background:linear-gradient(90deg,#2563eb,#7c3aed)}
    .sd-row-actions{display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end}
    .sd-row-actions .btn{padding:5px 10px;font-size:12px;margin:0}
    .sd-push{display:inline-block;width:11px;height:11px;border-radius:999px;border:1px solid rgba(15,23,42,.15)}
    #student-login-modal > div,#student-login-modal .sd-modal-card{border-radius:16px !important}
    @media (max-width:1100px){.an-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:768px){
        .sd-actions{display:grid;grid-template-columns:1fr;gap:10px}
        .sd-actions .btn{width:100%}
        .an-filter select,.an-filter input{min-width:0;width:100%}
        .an-filter label{flex:1 1 140px}
        .an-table{min-width:820px}
    }
</style>
<div class="top">
    <h1>Öğrenci Verileri</h1>
    <div class="actions sd-actions">
        <a class="btn btn-primary" href="{{ route('student-data.analytics') }}">📊 Başarı İstatistikleri</a>
        <a class="btn" href="{{ route('student-data.login-cards') }}" target="_blank">🪪 Giriş Kartları (A4)</a>
        <button class="btn" type="button" id="bulk-report-preview-btn">📄 Gelişim Raporları Önizle</button>
        <button class="btn" type="button" id="bulk-report-download-btn">⬇ Gelişim Raporları İndir</button>
        <a class="btn" href="{{ route('parent-whatsapp.report-list', ['format' => 'xlsx']) }}" target="_blank" id="parent-sms-list-btn">📋 Veli SMS Excel Listesi</a>
        <button class="btn btn-success" type="button" id="parent-sms-send-btn">✉ Toplu SMS Gönder</button>
    </div>
</div>

<div class="an-wrap">
    <form id="student-data-filter-form" method="GET" class="card an-filter student-filter">
        <input type="hidden" name="list" value="1">
        <label style="min-width:260px;flex:2 1 260px">Arama
            <input id="student-data-q" name="q" value="{{ $q ?? request('q') }}" placeholder="Ad soyad, no, kullanıcı adı, e-posta, sınıf">
        </label>
        <label>Sınıf
            <select id="student-data-class-name" name="class_name">
                <option value="">Tüm sınıflar</option>
                @foreach(($classNames ?? collect()) as $cn)
                    <option value="{{ $cn }}" @selected(($className ?? request('class_name')) === $cn)>{{ $cn }}</option>
                @endforeach
            </select>
        </label>
        <label>Şube
            <select id="student-data-section" name="section">
                <option value="">Tüm şubeler</option>
                @foreach(($sections ?? collect()) as $sec)
                    <option value="{{ $sec }}" @selected(($section ?? request('section')) === $sec)>{{ $sec }}</option>
                @endforeach
            </select>
        </label>
        <label>Sayfa başına
            <select id="student-data-per-page" name="per_page">
                @foreach([20, 50, 100, 200] as $size)
                    <option value="{{ $size }}" @selected((int) (($perPage ?? request('per_page', 50))) === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </label>
        <button class="btn" type="button" id="class-report-preview-btn" disabled>📘 Sınıf Gelişim Karnesini Önizle</button>
    </form>

    @if($listRows->isNotEmpty())
    <div class="an-kpis">
        <div class="an-kpi"><span>Listelenen öğrenci</span><strong>{{ number_format($totalCount, 0, ',', '.') }}</strong><small>bu sayfada {{ $listRows->count() }}</small></div>
        <div class="an-kpi"><span>Toplam XP (bu sayfa)</span><strong>{{ number_format($pageXp, 0, ',', '.') }}</strong><small>en yüksek: {{ number_format($maxXp, 0, ',', '.') }} XP</small></div>
        <div class="an-kpi"><span>Ortalama XP</span><strong>{{ number_format($listRows->count() ? $pageXp / $listRows->count() : 0, 0, ',', '.') }}</strong><small>öğrenci başına</small></div>
        <div class="an-kpi"><span>Toplam rozet (bu sayfa)</span><strong>{{ number_format($pageBadges, 0, ',', '.') }}</strong><small>kazanılan rozetler</small></div>
    </div>
    @endif

    <div class="an-card">
        <div class="an-table-wrap"><table class="an-table">
            <thead>
            <tr>
                <th>#</th><th>Öğrenci</th><th>Sınıf</th><th>XP</th><th>Avatar</th><th class="num">Rozet</th><th>Bildirim</th><th class="num">İşlem</th>
            </tr>
            </thead>
            <tbody>
            @forelse($students as $student)
                @php
                    $xpv = (int) ($stats[$student->id]['xp'] ?? 0);
                    $uname = $student->credential?->username ?: \Illuminate\Support\Str::before((string) ($student->user?->email ?? ''), '@');
                    $pushPerm = (string) ($pushPermissionByUser[$student->user_id] ?? '');
                    $pushColor = match($pushPerm) { 'granted' => '#0EA57A', 'denied' => '#E14B4B', default => '#94a3b8' };
                    $pushTitle = match($pushPerm) {
                        'granted' => 'Bildirim izni açık',
                        'denied' => 'Bildirim izni reddedilmiş',
                        'default' => 'Bildirim izni henüz sorulmamış',
                        default => 'Bildirim durumu bilinmiyor (henüz cihaz kaydı yok)',
                    };
                    $no = $firstNo + $loop->index;
                @endphp
                <tr>
                    <td><span class="sd-rank{{ $no <= 3 ? ' top' : '' }}">{{ $no }}</span></td>
                    <td>
                        <div class="sd-person">
                            @if($student->currentAvatar)
                                <img class="sd-ava" src="{{ asset($student->currentAvatar->image_path) }}" alt="" loading="lazy">
                            @else
                                <span class="sd-ava ph">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $student->user?->name, 0, 1)) }}</span>
                            @endif
                            <div><strong>{{ $student->user?->name }}</strong><small>{{ $uname ?: '—' }}@if($student->student_no) · No {{ $student->student_no }}@endif</small></div>
                        </div>
                    </td>
                    <td><span class="an-pill">{{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }}</span></td>
                    <td class="sd-xp"><b>{{ number_format($xpv, 0, ',', '.') }}</b><div class="sd-bar"><i style="width:{{ round($xpv / $maxXp * 100) }}%"></i></div></td>
                    <td>{{ $student->currentAvatar?->name ?? '—' }}</td>
                    <td class="num">{{ (int) ($stats[$student->id]['badges'] ?? 0) }}</td>
                    <td><span class="sd-push" style="background:{{ $pushColor }}" title="{{ $pushTitle }}"></span></td>
                    <td>
                        <div class="sd-row-actions">
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
                                data-username="{{ $uname }}"
                                data-password="{{ $student->credential?->plain_password }}"
                                data-class-id="{{ $student->school_class_id }}"
                            >Giriş Bilgileri</button>
                            <a class="btn" target="_blank" href="{{ route('student-data.progress-report', $student) }}">Gelişim Karnesi</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="an-empty">
                        @if(request('list') !== '1' && empty(($q ?? request('q'))) && empty(($className ?? request('class_name'))) && empty(($section ?? request('section'))))
                            Listeleme için arama veya filtre giriniz.
                        @else
                            Arama kriterine uygun öğrenci bulunamadı.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table></div>
    </div>

    @if($students instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div>{{ $students->links() }}</div>
    @endif
</div>

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
    const form = document.getElementById('student-data-filter-form');
    if (form) {
        const q = document.getElementById('student-data-q');
        const className = document.getElementById('student-data-class-name');
        const section = document.getElementById('student-data-section');
        const perPage = document.getElementById('student-data-per-page');
        let timer = null;

        const submitLater = () => {
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 300);
        };

        q?.addEventListener('input', submitLater);
        className?.addEventListener('change', () => form.submit());
        section?.addEventListener('change', () => form.submit());
        perPage?.addEventListener('change', () => form.submit());
    }
})();

(() => {
    const previewBtn = document.getElementById('bulk-report-preview-btn');
    const downloadBtn = document.getElementById('bulk-report-download-btn');
    const classPreviewBtn = document.getElementById('class-report-preview-btn');
    const smsListBtn = document.getElementById('parent-sms-list-btn');
    const smsSendBtn = document.getElementById('parent-sms-send-btn');
    const filterForm = document.getElementById('student-data-filter-form');
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

    function selectedClassPayload() {
        return {
            class_name: document.getElementById('student-data-class-name')?.value || '',
            section: document.getElementById('student-data-section')?.value || ''
        };
    }

    function syncClassPreviewButton() {
        if (!classPreviewBtn) return;
        const selected = selectedClassPayload();
        classPreviewBtn.disabled = selected.class_name === '' || selected.section === '';
    }

    async function start(mode, filters = {}) {
        let previewWin = null;
        try {
            previewBtn.disabled = true;
            downloadBtn.disabled = true;
            if (classPreviewBtn) classPreviewBtn.disabled = true;
            title.textContent = 'Raporlar hazirlaniyor...';
            setUi(0, 0, 1);

            if (mode === 'preview') {
                previewWin = window.open('about:blank', '_blank');
                if (previewWin) {
                    previewWin.document.write('<title>Rapor Hazirlaniyor</title><p style="font-family:Arial;padding:16px">Raporlar hazirlaniyor, lutfen bekleyin...</p>');
                    previewWin.document.close();
                }
            }

            const startData = await postJson('{{ route('student-data.reports.bulk-start') }}', {mode, ...filters});
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
            syncClassPreviewButton();
        }
    }

    async function startParentSms() {
        let stepTimer = null;
        try {
            if (smsSendBtn) smsSendBtn.disabled = true;
            title.textContent = 'Veli SMS gönderimi hazırlanıyor...';
            setUi(0, 0, 1);

            const className = document.getElementById('student-data-class-name')?.value || '';
            const section = document.getElementById('student-data-section')?.value || '';
            const payload = {};
            if (className !== '') payload.class_name = className;
            if (section !== '') payload.section = section;

            const startData = await postJson('{{ route('parent-whatsapp.report-send') }}', payload);
            let done = false;
            while (!done) {
                const step = await postJson('{{ url('/veli-bildirim/whatsapp/adim') }}/' + startData.task_id, {});
                setUi(step.percent || 0, step.processed || 0, step.total || 0);
                done = !!step.completed;
                if (!done) await new Promise(r => setTimeout(r, 220));
                if (done) {
                    title.textContent = 'Veli SMS gönderimi tamamlandı';
                    text.textContent = `Başarılı: ${step.success || 0}, Hatalı: ${step.failed || 0}`;
                    setTimeout(() => { box.style.display = 'none'; }, 2500);
                }
            }
        } catch (err) {
            title.textContent = 'SMS gönderim hatası';
            text.textContent = err.message || 'Beklenmeyen hata';
        } finally {
            if (smsSendBtn) smsSendBtn.disabled = false;
        }
    }

    previewBtn?.addEventListener('click', () => start('preview'));
    downloadBtn?.addEventListener('click', () => start('download'));
    classPreviewBtn?.addEventListener('click', () => start('preview', selectedClassPayload()));
    syncClassPreviewButton();
    smsSendBtn?.addEventListener('click', () => startParentSms());
    if (smsListBtn && filterForm) {
        smsListBtn.addEventListener('click', (event) => {
            const url = new URL(smsListBtn.href);
            const className = document.getElementById('student-data-class-name')?.value || '';
            const section = document.getElementById('student-data-section')?.value || '';
            if (className !== '') url.searchParams.set('class_name', className);
            if (section !== '') url.searchParams.set('section', section);
            event.preventDefault();
            window.location.href = url.toString();
        });
    }
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
