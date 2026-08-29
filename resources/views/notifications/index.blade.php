@extends('layout.app')
@section('title','Bildirimler')
@section('content')
<div class="teacher-v2 teacher-v2-compact">
    <div class="teacher-v2-layout">
        <div class="teacher-v2-main">
            <section class="v2-hero card soft-surface soft-surface-blue">
                <div>
                    <h1>Bildirimler</h1>
                    <p>Web Push, tercih ve log yönetimi.</p>
                </div>
            </section>

            <section class="card soft-surface" id="notifSettingsCard">
                <h2 style="margin:0 0 6px;">Bu Cihazda Bildirim Ayarları</h2>
                <p style="margin:0 0 14px;color:#64748b;font-size:13.5px;">Bu cihazda (bu tarayıcı/telefon) gerçek zamanlı bildirim alabilmek için önce izin vermen gerekir. Her cihazda ayrı ayrı açılmalıdır.</p>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--paper);">
                    <span id="notifPermDot" style="width:12px;height:12px;border-radius:999px;background:#94a3b8;flex:0 0 auto;"></span>
                    <span id="notifPermLabel" style="font-weight:700;">Durum kontrol ediliyor…</span>
                    <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;">
                        <button type="button" id="notifEnableBtn" class="btn" style="padding:9px 14px;font-size:13.5px;">Bildirim İznini Aç</button>
                        <button type="button" id="notifDisableBtn" class="btn btn-danger" style="padding:9px 14px;font-size:13.5px;display:none;">Bu Cihazda Kapat</button>
                        <button type="button" id="notifTestBtn" class="btn" style="padding:9px 14px;font-size:13.5px;background:var(--mint);border-color:var(--mint);">Test Bildirimi Gönder</button>
                    </div>
                </div>
                <p id="notifPermHelp" style="margin:10px 0 0;font-size:12.5px;color:#94a3b8;"></p>
            </section>

            <section class="card soft-surface soft-surface-mint">
                <h2>Bildirim Gönder</h2>
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
                                <option value="all">Tüm Kullanıcılar</option>
                                <option value="self">Sadece Kendim</option>
                                <option value="admins">Sadece Adminler</option>
                                <option value="students">Sadece Öğrenciler</option>
                                <option value="teachers">Sadece Öğretmenler</option>
                                <option value="class">Sınıf Bazlı (Sınıfın Tamamı)</option>
                                <option value="class_student">Sınıf İçi Öğrenci Bazlı</option>
                                <option value="teacher">Öğretmen Bazlı (Tek Öğretmen)</option>
                            @else
                                <option value="self">Sadece Kendim</option>
                                <option value="students">Sadece Öğrenciler</option>
                                <option value="class">Sınıf Bazlı (Sınıfın Tamamı)</option>
                                <option value="class_student">Sınıf İçi Öğrenci Bazlı</option>
                            @endif
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifClassRow" style="display:none;">
                        <label>Sınıf</label>
                        <select id="notifClassId" class="form-control">
                            <option value="">Sınıf seçin</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section ? ('-'.$class->section) : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifStudentRow" style="display:none;">
                        <label>Öğrenci</label>
                        <select id="notifStudentId" class="form-control">
                            <option value="">Öğrenci seçin</option>
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifTeacherRow" style="display:none;">
                        <label>Öğretmen</label>
                        <select id="notifTeacherId" class="form-control">
                            <option value="">Öğretmen seçin</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #'.$teacher->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row"><label>Başlık</label><input id="notifTitle" class="form-control" maxlength="190" required></div>
                    <div class="parent-wa-row"><label>Mesaj</label><textarea id="notifBody" class="form-control" rows="4" maxlength="4000" required></textarea></div>
                    <div class="parent-wa-row"><label>Yönlendirme URL (opsiyonel)</label><input id="notifUrl" class="form-control" placeholder="{{ url('/dashboard') }}"></div>
                    <div class="parent-wa-actions"><button id="notifSendBtn" class="btn" type="submit">Gönder</button></div>
                </form>
                <div id="notifSendStatus" class="pdf-status">Hazır</div>
            </section>

            <section class="card soft-surface" style="margin-top:16px;">
                <h2>Bildirim Tercihlerim</h2>
                <p style="margin:0 0 12px;color:#64748b;font-size:13.5px;">Hangi bildirim türlerini almak istediğini buradan yönetebilirsin.</p>
                <form id="notifPrefsForm" style="display:grid;gap:8px;">
                    @csrf
                    @foreach($preferences as $pref)
                        <label style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 14px;border:1px solid var(--line);border-radius:10px;background:#fff;">
                            <span style="font-weight:600;">{{ $pref['label'] }}</span>
                            <input type="checkbox" class="notif-pref-check" data-type="{{ $pref['type'] }}" style="width:20px;height:20px;" @checked($pref['enabled'])>
                        </label>
                    @endforeach
                </form>
                <div id="notifPrefsStatus" class="pdf-status" style="margin-top:8px;">Değişiklikler otomatik kaydedilir.</div>
            </section>

            <section class="card soft-surface" style="margin-top:16px;">
                <h2 style="margin:0 0 6px;">Cihaz Bildirim İzinleri</h2>
                <p style="margin:0 0 12px;color:#64748b;font-size:13.5px;">Bir kullanıcıda bildirim "partial" (kısmi) gidiyorsa, o kişinin cihazında izin verilmemiş ya da abonelik süresi dolmuş olabilir — aşağıdan kontrol edebilirsin.</p>
                @if($deviceStatuses->isEmpty())
                    <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;color:#475569;">
                        Henüz hiçbir cihaz bildirim durumu bildirmedi.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kullanıcı</th>
                                    <th>İzin</th>
                                    <th>Platform</th>
                                    <th>PWA</th>
                                    <th>Abonelik</th>
                                    <th>Son Görülme</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deviceStatuses as $d)
                                    @php
                                        $permLabel = match($d['permission']) {
                                            'granted' => 'İzin Verildi',
                                            'denied' => 'Reddedildi',
                                            default => 'Sorulmadı',
                                        };
                                        $permColor = match($d['permission']) {
                                            'granted' => '#0EA57A',
                                            'denied' => '#E14B4B',
                                            default => '#94a3b8',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $d['user_name'] }}</td>
                                        <td><span style="display:inline-block;padding:2px 8px;border-radius:999px;background:{{ $permColor }}22;color:{{ $permColor }};font-size:12px;font-weight:700;">{{ $permLabel }}</span></td>
                                        <td>{{ $d['platform'] }}</td>
                                        <td>{{ $d['is_pwa'] ? 'Evet' : 'Hayır' }}</td>
                                        <td>{{ $d['has_subscription'] ? 'Aktif' : 'Yok' }}</td>
                                        <td>{{ $d['last_seen_at']?->format('d.m.Y H:i') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="card soft-surface" style="margin-top:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <h2 style="margin:0;">Son Bildirim Kayıtları</h2>
                    @if($recentLogs->isNotEmpty())
                        <button type="button" id="notifDeleteAllBtn" class="btn btn-danger" style="padding:8px 14px;font-size:13px;">Tümünü Sil</button>
                    @endif
                </div>
                @if($recentLogs->isEmpty())
                    <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;color:#475569;margin-top:12px;">
                        Henüz gönderilmiş bir bildirim yok.
                    </div>
                @else
                    <div style="overflow-x:auto;margin-top:12px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kime</th>
                                    <th>Başlık</th>
                                    <th>Mesaj</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLogs as $log)
                                    <tr id="notif-log-row-{{ $log->id }}">
                                        <td>{{ $log->user?->name ?? '-' }}</td>
                                        <td>{{ $log->title }}</td>
                                        <td style="max-width:280px;overflow-wrap:anywhere;">{{ \Illuminate\Support\Str::limit($log->body, 120) }}</td>
                                        <td>{{ $log->status ?? '-' }}</td>
                                        <td>{{ optional($log->created_at)->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <div style="display:flex;gap:6px;">
                                                <button type="button" class="btn notif-resend-btn" data-id="{{ $log->id }}" style="padding:6px 10px;font-size:12px;">Tekrar Gönder</button>
                                                <button type="button" class="btn btn-danger notif-delete-btn" data-id="{{ $log->id }}" style="padding:6px 10px;font-size:12px;">Sil</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>

<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ---- Bu cihazda bildirim ayarları ----
    var permDot = document.getElementById('notifPermDot');
    var permLabel = document.getElementById('notifPermLabel');
    var permHelp = document.getElementById('notifPermHelp');
    var enableBtn = document.getElementById('notifEnableBtn');
    var disableBtn = document.getElementById('notifDisableBtn');
    var testBtn = document.getElementById('notifTestBtn');

    function isIos() {
        return /iPhone|iPad|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function isStandalonePwa() {
        return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
    }

    function refreshPermUi() {
        if (isIos() && !isStandalonePwa()) {
            permDot.style.background = '#FF7A45';
            permLabel.textContent = 'iPhone: önce ana ekrana eklenmeli';
            enableBtn.style.display = 'none';
            disableBtn.style.display = 'none';
            testBtn.style.display = 'none';
            permHelp.innerHTML = 'iPhone/iPad\'de (Safari) web bildirimleri yalnızca site <b>ana ekrana eklendiğinde</b> çalışır — Apple\'ın kısıtlaması, normal sekmede hiçbir izin düğmesi bunu açamaz.<br>' +
                '1) Safari\'de alttaki <b>Paylaş</b> (kare + yukarı ok) simgesine bas.<br>' +
                '2) <b>"Ana Ekrana Ekle"</b> seçeneğini seç.<br>' +
                '3) Ana ekrandaki uygulama simgesinden aç, sonra bu sayfaya tekrar gel — "Bildirim İznini Aç" düğmesi o zaman görünecek.';
            return;
        }
        if (!('Notification' in window)) {
            permDot.style.background = '#94a3b8';
            permLabel.textContent = 'Bu tarayıcı bildirim desteklemiyor.';
            enableBtn.style.display = 'none';
            disableBtn.style.display = 'none';
            testBtn.style.display = 'none';
            return;
        }
        testBtn.style.display = 'inline-flex';
        var perm = window.getWebPushPermission ? window.getWebPushPermission() : Notification.permission;
        if (perm === 'granted') {
            permDot.style.background = '#0EA57A';
            permLabel.textContent = 'Bildirimler açık';
            enableBtn.style.display = 'none';
            disableBtn.style.display = 'inline-flex';
            permHelp.textContent = 'Bu cihazda gerçek zamanlı bildirim alabilirsin.';
        } else if (perm === 'denied') {
            permDot.style.background = '#E14B4B';
            permLabel.textContent = 'Bildirimler engellenmiş';
            enableBtn.style.display = 'inline-flex';
            enableBtn.textContent = 'İzin Engellenmiş';
            enableBtn.disabled = true;
            disableBtn.style.display = 'none';
            permHelp.textContent = 'Tarayıcı ayarlarından (adres çubuğundaki kilit simgesi) bu site için bildirim iznini elle açman gerekiyor.';
        } else {
            permDot.style.background = '#FF7A45';
            permLabel.textContent = 'Bildirimler kapalı';
            enableBtn.style.display = 'inline-flex';
            enableBtn.disabled = false;
            enableBtn.textContent = 'Bildirim İznini Aç';
            disableBtn.style.display = 'none';
            permHelp.textContent = 'Mobilde bildirim almıyorsan büyük ihtimalle bu cihazda henüz izin verilmedi — "Bildirim İznini Aç" düğmesine bas.';
        }
    }

    if (enableBtn) {
        enableBtn.addEventListener('click', async function () {
            enableBtn.disabled = true;
            permLabel.textContent = 'İzin isteniyor…';
            try {
                if (typeof window.requestWebPushPermission === 'function') {
                    await window.requestWebPushPermission();
                } else if ('Notification' in window) {
                    await Notification.requestPermission();
                }
            } catch (_) {}
            refreshPermUi();
        });
    }

    if (disableBtn) {
        disableBtn.addEventListener('click', async function () {
            disableBtn.disabled = true;
            try {
                if (typeof window.disableWebPushSubscription === 'function') {
                    await window.disableWebPushSubscription();
                }
            } catch (_) {}
            disableBtn.disabled = false;
            refreshPermUi();
        });
    }

    if (testBtn) {
        testBtn.addEventListener('click', function () {
            testBtn.disabled = true;
            fetch('{{ route('notifications.send') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    type: 'system',
                    title: 'Test Bildirimi',
                    body: 'Bu bir test bildirimidir. Bu cihazda görüyorsan bildirimler çalışıyor demektir.',
                    target: 'self',
                }),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (window.appToast) window.appToast(data && data.ok ? 'success' : 'error', data && data.ok ? 'Test bildirimi gönderildi.' : (data.message || 'Gönderilemedi.'));
                })
                .catch(function () { if (window.appToast) window.appToast('error', 'Gönderilemedi.'); })
                .finally(function () { testBtn.disabled = false; });
        });
    }

    refreshPermUi();
    window.addEventListener('focus', refreshPermUi);

    // ---- Bildirim Gönder formu ----
    var sendForm = document.getElementById('adminSendForm');
    var sendBtn = document.getElementById('notifSendBtn');
    var sendStatus = document.getElementById('notifSendStatus');
    var targetSelect = document.getElementById('notifTarget');
    var classRow = document.getElementById('notifClassRow');
    var studentRow = document.getElementById('notifStudentRow');
    var teacherRow = document.getElementById('notifTeacherRow');
    var classSelect = document.getElementById('notifClassId');
    var studentSelect = document.getElementById('notifStudentId');
    var classStudentMap = @json($classStudentMap ?? []);

    function updateTargetRows() {
        var target = targetSelect ? targetSelect.value : '';
        if (classRow) classRow.style.display = (target === 'class' || target === 'class_student') ? '' : 'none';
        if (studentRow) studentRow.style.display = (target === 'class_student') ? '' : 'none';
        if (teacherRow) teacherRow.style.display = (target === 'teacher') ? '' : 'none';
    }

    function populateStudents() {
        if (!studentSelect || !classSelect) return;
        var students = classStudentMap[classSelect.value] || [];
        studentSelect.innerHTML = '<option value="">Öğrenci seçin</option>' + students.map(function (s) {
            return '<option value="' + s.id + '">' + s.name.replace(/</g, '&lt;') + '</option>';
        }).join('');
    }

    if (targetSelect) {
        targetSelect.addEventListener('change', updateTargetRows);
        updateTargetRows();
    }
    if (classSelect) {
        classSelect.addEventListener('change', populateStudents);
    }

    if (sendForm) {
        sendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var target = targetSelect ? targetSelect.value : 'self';
            var payload = {
                type: document.getElementById('notifType')?.value || 'system',
                title: document.getElementById('notifTitle')?.value || '',
                body: document.getElementById('notifBody')?.value || '',
                url: document.getElementById('notifUrl')?.value || '',
                target: target,
            };
            if (target === 'class' || target === 'class_student') {
                payload.class_id = classSelect ? classSelect.value : '';
            }
            if (target === 'class_student') {
                payload.student_id = studentSelect ? studentSelect.value : '';
            }
            if (target === 'teacher') {
                payload.teacher_id = document.getElementById('notifTeacherId')?.value || '';
            }

            if (sendBtn) sendBtn.disabled = true;
            if (sendStatus) sendStatus.textContent = 'Gönderiliyor…';

            fetch('{{ route('notifications.send') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (res) {
                    if (res.ok && res.data && res.data.ok) {
                        var result = res.data.result || {};
                        sendStatus.textContent = 'Gönderildi. Toplam: ' + (result.total ?? '-') + ', Gönderilen: ' + (result.sent ?? '-') + ', Başarısız: ' + (result.failed ?? '-');
                        if (window.appToast) window.appToast('success', 'Bildirim gönderildi.');
                        sendForm.reset();
                        updateTargetRows();
                    } else {
                        sendStatus.textContent = (res.data && res.data.message) || 'Gönderilemedi.';
                        if (window.appToast) window.appToast('error', sendStatus.textContent);
                    }
                })
                .catch(function () {
                    sendStatus.textContent = 'Gönderilemedi (bağlantı hatası).';
                    if (window.appToast) window.appToast('error', sendStatus.textContent);
                })
                .finally(function () {
                    if (sendBtn) sendBtn.disabled = false;
                });
        });
    }

    // Preferences
    var prefsForm = document.getElementById('notifPrefsForm');
    var prefsStatus = document.getElementById('notifPrefsStatus');
    if (prefsForm) {
        prefsForm.addEventListener('change', function (e) {
            var check = e.target.closest('.notif-pref-check');
            if (!check) return;
            var payload = { preferences: {} };
            payload.preferences[check.getAttribute('data-type')] = check.checked;
            prefsStatus.textContent = 'Kaydediliyor…';
            fetch('{{ route('notifications.preferences.update') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    prefsStatus.textContent = data && data.ok ? 'Kaydedildi.' : 'Kaydedilemedi.';
                })
                .catch(function () { prefsStatus.textContent = 'Kaydedilemedi.'; });
        });
    }

    // Resend / delete
    document.addEventListener('click', function (e) {
        var resendBtn = e.target.closest('.notif-resend-btn');
        var deleteBtn = e.target.closest('.notif-delete-btn');
        var deleteAllBtn = e.target.closest('#notifDeleteAllBtn');

        if (resendBtn) {
            var id = resendBtn.getAttribute('data-id');
            resendBtn.disabled = true;
            fetch('/app-notifications/' + id + '/resend', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (window.appToast) window.appToast(data && data.ok ? 'success' : 'error', data && data.ok ? 'Bildirim tekrar gönderildi.' : (data.message || 'Gönderilemedi.'));
                })
                .finally(function () { resendBtn.disabled = false; });
        }

        if (deleteBtn) {
            var did = deleteBtn.getAttribute('data-id');
            if (!confirm('Bu bildirim kaydı silinsin mi?')) return;
            deleteBtn.disabled = true;
            fetch('/app-notifications/' + did + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.ok) {
                        var row = document.getElementById('notif-log-row-' + did);
                        if (row) row.remove();
                    } else {
                        deleteBtn.disabled = false;
                        if (window.appToast) window.appToast('error', (data && data.message) || 'Silinemedi.');
                    }
                })
                .catch(function () {
                    deleteBtn.disabled = false;
                    if (window.appToast) window.appToast('error', 'Silinemedi.');
                });
        }

        if (deleteAllBtn) {
            if (!confirm('Tüm bildirim kayıtları silinsin mi?')) return;
            deleteAllBtn.disabled = true;
            fetch('{{ route('notifications.logs.destroy-all.post') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.ok) {
                        window.location.reload();
                    } else {
                        deleteAllBtn.disabled = false;
                        if (window.appToast) window.appToast('error', (data && data.message) || 'Silinemedi.');
                    }
                })
                .catch(function () {
                    deleteAllBtn.disabled = false;
                    if (window.appToast) window.appToast('error', 'Silinemedi.');
                });
        }
    });
})();
</script>
@endsection