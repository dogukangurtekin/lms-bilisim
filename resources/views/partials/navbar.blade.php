@php
    $uid = (int) (auth()->id() ?? 0);
    $navUnreadCount = \App\Models\NotificationLog::query()
        ->where('user_id', $uid)
        ->whereNotIn('id', \App\Models\NotificationLogRead::query()->select('notification_log_id')->where('user_id', $uid))
        ->count();
@endphp

<div class="navbar">
    <div class="navbar-user">
        <button type="button" class="global-menu-toggle" id="global-menu-toggle" aria-label="Menu">&#9776;</button>
        <strong>{{ auth()->user()->name ?? 'Misafir' }}</strong>
    </div>

    <div class="navbar-actions">
        @if(auth()->user()?->hasRole('admin', 'teacher'))
            <a href="{{ route('notifications.index') }}" class="notif-bell" aria-label="Bildirimler">
                <span class="notif-bell-icon">&#128276;</span>
                @if($navUnreadCount > 0)
                    <span class="notif-bell-count">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                @endif
            </a>
        @else
            <button type="button" id="studentNotifBell" class="notif-bell" aria-label="Bildirimler" aria-expanded="false">
                <span class="notif-bell-icon">&#128276;</span>
                @if($navUnreadCount > 0)
                    <span id="studentNotifCount" class="notif-bell-count">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                @else
                    <span id="studentNotifCount" class="notif-bell-count" style="display:none;">0</span>
                @endif
            </button>
            <div id="studentNotifPopup" style="display:none;position:absolute;right:12px;top:58px;z-index:1000003;width:min(92vw,420px);max-height:min(70vh,560px);overflow:auto;border:1px solid #cbd5e1;border-radius:14px;background:#fff;box-shadow:0 18px 42px rgba(2,6,23,.24);padding:10px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;">
                    <strong style="font-size:14px;color:#0f172a;">Bildirimler</strong>
                    <div style="display:flex;gap:6px;">
                        <button type="button" id="studentNotifDeleteAll" class="btn btn-danger" style="padding:6px 10px;font-size:12px;">Tumunu Sil</button>
                        <button type="button" id="studentNotifClose" class="btn btn-secondary" style="padding:6px 10px;font-size:12px;">Kapat</button>
                    </div>
                </div>
                <div id="studentNotifList" style="display:grid;gap:8px;">
                    <p style="margin:0;color:#64748b;font-size:13px;">Yukleniyor...</p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-logout" type="submit">
                <span class="logout-icon">&#x23FB;</span> Cikis Yap
            </button>
        </form>
    </div>
</div>

@if(auth()->user()?->hasRole('student'))
<script>
(() => {
    const bell = document.getElementById('studentNotifBell');
    const popup = document.getElementById('studentNotifPopup');
    const listEl = document.getElementById('studentNotifList');
    const closeBtn = document.getElementById('studentNotifClose');
    const deleteAllBtn = document.getElementById('studentNotifDeleteAll');
    const countEl = document.getElementById('studentNotifCount');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!bell || !popup || !listEl) return;

    const setCount = (n) => {
        const safe = Math.max(0, Number(n || 0));
        if (!countEl) return;
        if (safe <= 0) {
            countEl.style.display = 'none';
            countEl.textContent = '0';
            return;
        }
        countEl.style.display = 'inline-flex';
        countEl.textContent = safe > 99 ? '99+' : String(safe);
    };

    const closePopup = () => {
        popup.style.display = 'none';
        bell.setAttribute('aria-expanded', 'false');
    };

    const renderItems = (items) => {
        const rows = Array.isArray(items) ? items : [];
        setCount(rows.filter((x) => !x.read).length);
        if (!rows.length) {
            listEl.innerHTML = '<p style="margin:0;color:#64748b;font-size:13px;">Bildirim yok.</p>';
            return;
        }
        listEl.innerHTML = rows.map((item) => {
            const title = String(item?.title || 'Bildirim');
            const body = String(item?.body || '');
            const id = Number(item?.id || 0);
            const read = !!item?.read;
            return `
                <article data-id="${id}" style="border:1px solid ${read ? '#e2e8f0' : '#93c5fd'};background:${read ? '#fff' : '#eff6ff'};border-radius:10px;padding:10px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                        <strong style="font-size:13px;color:#0f172a;line-height:1.2;">${title}</strong>
                        <button type="button" class="js-notif-del" data-id="${id}" aria-label="Bildirimi sil" style="border:1px solid #fecaca;background:#fff;color:#dc2626;border-radius:999px;width:24px;height:24px;line-height:1;cursor:pointer;">&times;</button>
                    </div>
                    <p style="margin:6px 0 0;color:#334155;font-size:12px;line-height:1.35;white-space:pre-wrap;">${body}</p>
                </article>
            `;
        }).join('');
    };

    const loadNotifications = async () => {
        listEl.innerHTML = '<p style="margin:0;color:#64748b;font-size:13px;">Yukleniyor...</p>';
        try {
            const res = await fetch('{{ route('notifications.mine') }}', {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error('Bildirimler yuklenemedi.');
            const data = await res.json().catch(() => ({}));
            if (!data?.ok) throw new Error(data?.message || 'Bildirimler yuklenemedi.');
            renderItems(data.items || []);
        } catch (e) {
            listEl.innerHTML = '<p style="margin:0;color:#b91c1c;font-size:13px;">Bildirimler yuklenemedi.</p>';
        }
    };

    bell.addEventListener('click', async () => {
        const isOpen = popup.style.display === 'block';
        if (isOpen) {
            closePopup();
            return;
        }
        popup.style.display = 'block';
        bell.setAttribute('aria-expanded', 'true');
        await loadNotifications();
    });

    closeBtn?.addEventListener('click', closePopup);

    deleteAllBtn?.addEventListener('click', async () => {
        if (!window.confirm('Tum bildirimler silinsin mi?')) return;
        try {
            const res = await fetch('{{ route('notifications.mine.destroy-all') }}', {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data?.ok) throw new Error();
            if (window.appToast) window.appToast('success', 'Tum bildirimler silindi.');
            await loadNotifications();
        } catch (_) {
            if (window.appToast) window.appToast('error', 'Tum bildirimler silinemedi.');
        }
    });

    listEl.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('.js-notif-del');
        if (!btn) return;
        const id = Number(btn.getAttribute('data-id') || 0);
        if (!id) return;
        try {
            const res = await fetch(`{{ url('/app-notifications/mine') }}/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data?.ok) throw new Error();
            const item = listEl.querySelector(`article[data-id="${id}"]`);
            if (item) item.remove();
            if (window.appToast) window.appToast('success', 'Bildirim silindi.');
            if (!listEl.querySelector('article')) {
                listEl.innerHTML = '<p style="margin:0;color:#64748b;font-size:13px;">Bildirim yok.</p>';
                setCount(0);
            } else {
                const unread = listEl.querySelectorAll('article[style*="#eff6ff"]').length;
                setCount(unread);
            }
        } catch (_) {
            if (window.appToast) window.appToast('error', 'Bildirim silinemedi.');
        }
    });

    document.addEventListener('click', (ev) => {
        if (popup.style.display !== 'block') return;
        const target = ev.target;
        if (!popup.contains(target) && !bell.contains(target)) closePopup();
    });
})();
</script>
@endif

