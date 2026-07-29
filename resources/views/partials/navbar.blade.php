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
        <button type="button" class="global-menu-toggle" id="global-menu-toggle" aria-label="Menu">&#9776;</button>
        <strong>{{ $currentUser->name ?? 'Misafir' }}</strong>
    </div>

    <div class="navbar-actions">
        @if($currentUser?->hasRole('admin'))
            <a href="{{ route('notifications.index') }}" class="notif-bell" aria-label="Bildirimler">
                <span class="notif-bell-icon">&#128276;</span>
                @if($navUnreadCount > 0)
                    <span class="notif-bell-count">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                @endif
            </a>
        @endif

        <a class="btn btn-logout" href="{{ route('logout.get') }}">
            <span class="logout-icon">&#x23FB;</span> Çıkış Yap
        </a>
    </div>
</div>
