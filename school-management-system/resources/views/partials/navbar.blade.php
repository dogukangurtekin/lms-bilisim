<div class="navbar">
    <div class="navbar-user">
        <button type="button" class="global-menu-toggle" id="global-menu-toggle" aria-label="Menü">☰</button>
        <strong>{{ auth()->user()->name ?? 'Misafir' }}</strong>
        <span class="badge">{{ auth()->user()?->role?->slug ?? '-' }}</span>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-logout" type="submit">
            <span class="logout-icon">⎋</span> Çıkış Yap
        </button>
    </form>
</div>
