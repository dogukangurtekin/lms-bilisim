@extends('layout.app')
@section('title','Bildirimler')
@section('content')
<div class="teacher-v2 teacher-v2-compact">
    <div class="teacher-v2-layout">
        <div class="teacher-v2-main">
            <section class="v2-hero card soft-surface soft-surface-blue">
                <div>
                    <h1>Bildirimler</h1>
                    <p>Web Push, tercih ve log yonetimi.</p>
                </div>
            </section>

            <section class="card soft-surface soft-surface-mint">
                <h2>Bildirim Gonder</h2>
                <form id="adminSendForm" class="parent-wa-form">
                    @csrf
                    <div class="parent-wa-row">
                        <label>Tip</label>
                        <select id="notifType" class="form-control" required>
                            @foreach($types as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row">
                        <label>Hedef</label>
                        <select id="notifTarget" class="form-control" required>
                            @if($isAdmin)
                                <option value="all">Tum Kullanicilar</option>
                                <option value="self">Sadece Kendim</option>
                                <option value="admins">Sadece Adminler</option>
                                <option value="students">Sadece Ogrenciler</option>
                                <option value="teachers">Sadece Ogretmenler</option>
                                <option value="class">Sinif Bazli (Sinifin Tamami)</option>
                                <option value="class_student">Sinif Ici Ogrenci Bazli</option>
                                <option value="teacher">Ogretmen Bazli (Tek Ogretmen)</option>
                            @else
                                <option value="self">Sadece Kendim</option>
                                <option value="students">Sadece Ogrenciler</option>
                                <option value="class">Sinif Bazli (Sinifin Tamami)</option>
                                <option value="class_student">Sinif Ici Ogrenci Bazli</option>
                            @endif
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifClassRow" style="display:none;">
                        <label>Sinif</label>
                        <select id="notifClassId" class="form-control">
                            <option value="">Sinif secin</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section ? ('-'.$class->section) : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifStudentRow" style="display:none;">
                        <label>Ogrenci</label>
                        <select id="notifStudentId" class="form-control">
                            <option value="">Ogrenci secin</option>
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifTeacherRow" style="display:none;">
                        <label>Ogretmen</label>
                        <select id="notifTeacherId" class="form-control">
                            <option value="">Ogretmen secin</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Ogretmen #'.$teacher->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row">
                        <label>Baslik</label>
                        <input id="notifTitle" class="form-control" maxlength="190" required>
                    </div>
                    <div class="parent-wa-row">
                        <label>Mesaj</label>
                        <textarea id="notifBody" class="form-control" rows="4" maxlength="4000" required></textarea>
                    </div>
                    <div class="parent-wa-row">
                        <label>Yonlendirme URL (opsiyonel)</label>
                        <input id="notifUrl" class="form-control" placeholder="{{ url('/dashboard') }}">
                    </div>
                    <div class="parent-wa-actions">
                        <button id="notifSendBtn" class="btn" type="submit">Gonder</button>
                    </div>
                </form>
                <div id="notifSendStatus" class="pdf-status">Hazir</div>
            </section>

            <section class="card soft-surface soft-surface-lilac">
                <h2>Kullanici Tercihleri (Benim)</h2>
                <div class="parent-wa-actions" style="margin-bottom:10px;gap:8px;align-items:center;">
                    <button type="button" id="notifPermissionToggleBtn" class="btn btn-secondary">Bildirim Iznini Ac</button>
                    <span id="notifPermissionState" class="pdf-status" style="margin:0;">Durum: kontrol ediliyor...</span>
                </div>
                <form id="notifPrefForm" class="parent-wa-form">
                    @csrf
                    @foreach($preferences as $pref)
                        <label class="parent-wa-checkbox">
                            <input type="checkbox" data-type="{{ $pref['type'] }}" {{ $pref['enabled'] ? 'checked' : '' }}>
                            {{ $pref['label'] }}
                        </label>
                    @endforeach
                    <div class="parent-wa-actions">
                        <button class="btn" type="submit">Tercihleri Kaydet</button>
                    </div>
                </form>
                <div id="notifPrefStatus" class="pdf-status">Hazir</div>
            </section>

            <section class="card soft-surface soft-surface-peach">
                <h2>Son Gonderim Loglari</h2>
                <div class="parent-wa-actions" style="margin-bottom:10px;">
                    <form method="POST" action="{{ route('notifications.logs.destroy-all.post') }}" data-confirm="Tum bildirim loglari silinsin mi?">
                        @csrf
                        <button type="submit" class="btn btn-danger">Tumunu Sil</button>
                    </form>
                </div>
                <div id="notifLogStatus" class="pdf-status">Hazir</div>
                <div class="notification-recent-list">
                    @forelse($recentLogs as $log)
                        <article class="notification-recent-item" data-log-id="{{ $log->id }}">
                            <header>
                                <strong>#{{ $log->id }} - {{ $log->title }}</strong>
                                <span>{{ strtoupper($log->status) }}</span>
                            </header>
                            <p>{{ $log->body }}</p>
                            <p><small>Tip: {{ $log->type }} | Hedef: {{ $log->user?->name ?? 'N/A' }} | Teslim: {{ $log->delivered_count }} | Hata: {{ $log->failed_count }}</small></p>
                            <div class="actions" style="margin-top:6px;">
                                <button type="button" class="btn btn-secondary js-resend" data-id="{{ $log->id }}">Tekrar Gonder</button>
                                <form method="POST" action="{{ route('notifications.logs.destroy.post', ['log' => $log->id]) }}" style="display:inline;" data-confirm="Log silinsin mi?">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Sil</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p>Henuz log yok.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const sendForm = document.getElementById('adminSendForm');
    const sendStatus = document.getElementById('notifSendStatus');
    const prefForm = document.getElementById('notifPrefForm');
    const prefStatus = document.getElementById('notifPrefStatus');
    const targetEl = document.getElementById('notifTarget');
    const classRowEl = document.getElementById('notifClassRow');
    const studentRowEl = document.getElementById('notifStudentRow');
    const teacherRowEl = document.getElementById('notifTeacherRow');
    const classEl = document.getElementById('notifClassId');
    const studentEl = document.getElementById('notifStudentId');
    const teacherEl = document.getElementById('notifTeacherId');
    const logStatus = document.getElementById('notifLogStatus');
    const classStudentMap = @json($classStudentMap);
    const permissionBtn = document.getElementById('notifPermissionToggleBtn');
    const permissionState = document.getElementById('notifPermissionState');

    const setStatus = (el, text, ok = true) => {
        if (!el) return;
        el.classList.add('show');
        el.textContent = text;
        el.style.background = ok ? '#ecfdf5' : '#fef2f2';
        el.style.color = ok ? '#065f46' : '#991b1b';
        el.style.borderColor = ok ? '#10b981' : '#ef4444';
    };

    const refreshPermissionUi = () => {
        const permission = (typeof window.getWebPushPermission === 'function')
            ? window.getWebPushPermission()
            : ((window.Notification && Notification.permission) ? Notification.permission : 'default');
        if (permissionState) permissionState.textContent = `Durum: ${permission}`;
        if (!permissionBtn) return;
        if (permission === 'granted') {
            permissionBtn.textContent = 'Bildirim Iznini Kapat';
            permissionBtn.disabled = false;
        } else if (permission === 'denied') {
            permissionBtn.textContent = 'Izin Engellendi (Tarayici Ayari Gerekli)';
            permissionBtn.disabled = true;
        } else {
            permissionBtn.textContent = 'Bildirim Iznini Ac';
            permissionBtn.disabled = false;
        }
    };

    const updateTargetFields = () => {
        const target = targetEl?.value || 'all';
        if (classRowEl) classRowEl.style.display = (target === 'class' || target === 'class_student') ? '' : 'none';
        if (studentRowEl) studentRowEl.style.display = (target === 'class_student') ? '' : 'none';
        if (teacherRowEl) teacherRowEl.style.display = (target === 'teacher') ? '' : 'none';
    };

    const rebuildStudentOptions = () => {
        if (!studentEl || !classEl) return;
        const classId = classEl.value || '';
        const students = classStudentMap[classId] || [];
        studentEl.innerHTML = '<option value="">Ogrenci secin</option>';
        students.forEach((s) => {
            const opt = document.createElement('option');
            opt.value = String(s.id);
            opt.textContent = s.name || ('Ogrenci #' + s.id);
            studentEl.appendChild(opt);
        });
    };

    targetEl?.addEventListener('change', () => {
        updateTargetFields();
        if ((targetEl?.value || '') !== 'class_student' && studentEl) {
            studentEl.value = '';
        }
    });
    classEl?.addEventListener('change', rebuildStudentOptions);

    updateTargetFields();
    rebuildStudentOptions();
    refreshPermissionUi();

    permissionBtn?.addEventListener('click', async () => {
        const permission = (typeof window.getWebPushPermission === 'function')
            ? window.getWebPushPermission()
            : ((window.Notification && Notification.permission) ? Notification.permission : 'default');
        permissionBtn.disabled = true;
        try {
            if (permission === 'granted') {
                if (typeof window.disableWebPushSubscription === 'function') {
                    await window.disableWebPushSubscription();
                }
            } else {
                if (typeof window.requestWebPushPermission === 'function') {
                    await window.requestWebPushPermission();
                }
            }
        } finally {
            permissionBtn.disabled = false;
            refreshPermissionUi();
        }
    });

    sendForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            type: document.getElementById('notifType')?.value || 'system_message',
            target: document.getElementById('notifTarget')?.value || 'all',
            title: document.getElementById('notifTitle')?.value?.trim() || '',
            body: document.getElementById('notifBody')?.value?.trim() || '',
            url: document.getElementById('notifUrl')?.value?.trim() || '',
            class_id: classEl?.value || '',
            student_id: studentEl?.value || '',
            teacher_id: teacherEl?.value || '',
        };
        if (!payload.title || !payload.body) {
            setStatus(sendStatus, 'Baslik ve mesaj zorunlu.', false);
            return;
        }
        if ((payload.target === 'class' || payload.target === 'class_student') && !payload.class_id) {
            setStatus(sendStatus, 'Sinif secimi zorunlu.', false);
            return;
        }
        if (payload.target === 'class_student' && !payload.student_id) {
            setStatus(sendStatus, 'Ogrenci secimi zorunlu.', false);
            return;
        }
        if (payload.target === 'teacher' && !payload.teacher_id) {
            setStatus(sendStatus, 'Ogretmen secimi zorunlu.', false);
            return;
        }
        try {
            const res = await fetch('{{ route('notifications.send') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.ok) throw new Error(data.message || 'Gonderim hatasi');
            setStatus(sendStatus, `Gonderildi. Sent:${data.result?.sent ?? 0} Failed:${data.result?.failed ?? 0} NoTarget:${data.result?.no_target ?? 0}`, true);
            window.setTimeout(() => window.location.reload(), 700);
        } catch (err) {
            setStatus(sendStatus, err?.message || 'Gonderim hatasi.', false);
        }
    });

    prefForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const preferences = {};
        prefForm.querySelectorAll('input[type="checkbox"][data-type]').forEach((el) => {
            preferences[el.getAttribute('data-type')] = el.checked;
        });
        try {
            const res = await fetch('{{ route('notifications.preferences.update') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ preferences }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.ok) throw new Error();
            setStatus(prefStatus, 'Tercihler kaydedildi.', true);
        } catch (_) {
            setStatus(prefStatus, 'Tercih kaydi basarisiz.', false);
        }
    });

    document.querySelectorAll('.js-resend').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            if (!id) return;
            try {
                const res = await fetch(`{{ url('/app-notifications') }}/${id}/resend`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) throw new Error(data.message || 'Tekrar gonderim basarisiz.');
                setStatus(logStatus, 'Bildirim tekrar gonderildi.', true);
                window.location.reload();
            } catch (err) {
                setStatus(logStatus, err?.message || 'Tekrar gonderim basarisiz.', false);
            }
        });
    });

})();
</script>
@endpush
