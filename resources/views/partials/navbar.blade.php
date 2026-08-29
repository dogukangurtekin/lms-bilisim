@php
    $uid = (int) (auth()->id() ?? 0);
    $navUnreadCount = \App\Models\NotificationLog::query()
        ->where('user_id', $uid)
        ->whereNotIn('id', \App\Models\NotificationLogRead::query()->select('notification_log_id')->where('user_id', $uid))
        ->count();
    $currentUser = auth()->user();
@endphp

<div class="navbar">
    <div class="navbar-user">
        <button type="button" class="global-menu-toggle" id="global-menu-toggle" aria-label="Menu">☰</button>
        <strong>{{ $currentUser->name ?? 'Misafir' }}</strong>
    </div>

    <div class="navbar-actions">
        <div class="notif-menu" id="navNotifMenu">
            <button type="button" class="notif-bell" id="navNotifBellBtn" aria-label="Bildirimler">
                <span class="notif-bell-icon">🔔</span>
                <span class="notif-bell-count" id="navNotifCount" @if($navUnreadCount <= 0) hidden @endif>{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
            </button>
            <div class="notif-panel" id="navNotifPanel" hidden>
                <div class="notif-panel-head">
                    <strong>Bildirimler</strong>
                    @if($currentUser?->hasRole('admin'))
                        <a href="{{ route('notifications.index') }}">Yönet</a>
                    @endif
                </div>
                <div class="notif-list" id="navNotifList">
                    <div class="notif-empty">Yükleniyor…</div>
                </div>
            </div>
        </div>

        <a class="btn btn-logout" href="{{ route('logout.get') }}">
            <span class="logout-icon">⏻</span> Çıkış Yap
        </a>
    </div>
</div>

<script>
(function () {
    var menu = document.getElementById('navNotifMenu');
    var btn = document.getElementById('navNotifBellBtn');
    var panel = document.getElementById('navNotifPanel');
    var list = document.getElementById('navNotifList');
    var countEl = document.getElementById('navNotifCount');
    if (!menu || !btn || !panel || !list) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var loaded = false;

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function timeAgo(iso) {
        if (!iso) return '';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        var diff = Math.max(0, (Date.now() - d.getTime()) / 1000);
        if (diff < 60) return 'az önce';
        if (diff < 3600) return Math.floor(diff / 60) + ' dk önce';
        if (diff < 86400) return Math.floor(diff / 3600) + ' sa önce';
        return Math.floor(diff / 86400) + ' gün önce';
    }

    function updateCount(n) {
        if (n > 0) {
            countEl.hidden = false;
            countEl.textContent = n > 99 ? '99+' : String(n);
        } else {
            countEl.hidden = true;
        }
    }

    function renderItems(items) {
        if (!items.length) {
            list.innerHTML = '<div class="notif-empty">Henüz bir bildirimin yok.</div>';
            return;
        }
        list.innerHTML = items.map(function (item) {
            var cls = 'notif-item' + (item.read ? '' : ' is-unread');
            var href = item.url ? escapeHtml(item.url) : '#';
            return '<a class="' + cls + '" href="' + href + '" data-id="' + item.id + '" data-unread="' + (item.read ? '0' : '1') + '">' +
                '<strong>' + escapeHtml(item.title || 'Bildirim') + '</strong>' +
                '<span>' + escapeHtml(item.body || '') + '</span>' +
                '<span style="color:#94a3b8;font-size:11px;">' + timeAgo(item.created_at) + '</span>' +
                '</a>';
        }).join('');
    }

    function loadNotifications() {
        fetch('{{ route('notifications.mine') }}?limit=20', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    list.innerHTML = '<div class="notif-empty">Bildirimler yüklenemedi.</div>';
                    return;
                }
                var items = data.items || [];
                renderItems(items);
                var unread = items.filter(function (i) { return !i.read; }).length;
                updateCount(unread);
            })
            .catch(function () {
                list.innerHTML = '<div class="notif-empty">Bildirimler yüklenemedi.</div>';
            });
    }

    function markRead(id) {
        fetch('/app-notifications/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).catch(function () {});
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var willOpen = panel.hidden;
        panel.hidden = !willOpen;
        if (willOpen && !loaded) {
            loaded = true;
            loadNotifications();
        }
    });

    list.addEventListener('click', function (e) {
        var item = e.target.closest('.notif-item');
        if (!item) return;
        if (item.getAttribute('data-unread') === '1') {
            var id = item.getAttribute('data-id');
            markRead(id);
            item.classList.remove('is-unread');
            item.setAttribute('data-unread', '0');
            var current = parseInt(countEl.textContent, 10) || 0;
            updateCount(Math.max(0, current - 1));
        }
        if (item.getAttribute('href') === '#') {
            e.preventDefault();
        }
    });

    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target)) {
            panel.hidden = true;
        }
    });
})();
</script>