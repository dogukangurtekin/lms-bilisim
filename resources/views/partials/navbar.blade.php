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
        @php
            $notifTargetUrl = auth()->user()?->hasRole('admin', 'teacher')
                ? route('notifications.index')
                : url('/ogrenci/panelim');
        @endphp
        <a href="{{ $notifTargetUrl }}" class="notif-bell" aria-label="Bildirimler">
            <span class="notif-bell-icon">&#128276;</span>
            @if($navUnreadCount > 0)
                <span class="notif-bell-count">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
            @endif
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-logout" type="submit">
                <span class="logout-icon">&#x23FB;</span> Cikis Yap
            </button>
        </form>
    </div>
</div>

