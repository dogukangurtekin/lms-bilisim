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