<aside class="sidebar">
    @if(auth()->user()?->hasRole('student'))
        @php
            $currentStudent = \App\Models\Student::where('user_id', auth()->id())->first();
            $timeStat = $currentStudent ? \App\Models\StudentTimeStat::where('student_id', $currentStudent->id)->first() : null;
            $initialSeconds = (int) ($timeStat?->total_seconds ?? 0);
        @endphp
        <div class="student-sidebar-top sidebar-brand">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="sidebar-logo">
            <p>Bilişim Platformu</p>
        </div>
        <nav class="student-sidebar-nav">
            <a class="{{ request()->routeIs('student.portal.dashboard') ? 'active' : '' }}" href="{{ route('student.portal.dashboard') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11.5L12 4l9 7.5V21h-6v-5h-6v5H3z"/></svg></span>Panelim
            </a>
            <a class="{{ request()->routeIs('student.portal.courses') ? 'active' : '' }}" href="{{ route('student.portal.courses') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h9a3 3 0 013 3v11H7a3 3 0 00-3 3V5zm16 0h-4a3 3 0 00-3 3v11h7V5z"/></svg></span>Derslerim
            </a>
            <a class="{{ request()->routeIs('student.portal.assignments') ? 'active' : '' }}" href="{{ route('student.portal.assignments') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10v3h3v15H4V6h3V3zm2 0v3h6V3H9zm-2 9h10v2H7v-2zm0 4h10v2H7v-2z"/></svg></span>Ödevlerim
            </a>
            <a class="{{ request()->routeIs('activities.*') || request()->routeIs('keyboard-race.*') ? 'active' : '' }}" href="{{ route('activities.index') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8h3v3H7V8zm7 0h3v3h-3V8zM5 5h14a2 2 0 012 2v10a2 2 0 01-2 2h-4l-2-2h-2l-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg></span>Oyun ve Etkinlikler
            </a>
            <a class="{{ request()->routeIs('student.portal.friends') ? 'active' : '' }}" href="{{ route('student.portal.friends') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 100-8 4 4 0 000 8zM8 12a4 4 0 100-8 4 4 0 000 8zm8 2c-3.314 0-6 2.239-6 5v1h12v-1c0-2.761-2.686-5-6-5zM8 14c-3.314 0-6 2.239-6 5v1h6v-1c0-1.908.81-3.648 2.121-5A7.8 7.8 0 008 14z"/></svg></span>Arkadaşlarım
            </a>
            <a class="{{ request()->routeIs('student.portal.class-board*') ? 'active' : '' }}" href="{{ route('student.portal.class-board') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4V5zm2 2v10h12V7H6zm1 2h10v2H7V9zm0 3h6v2H7v-2z"/></svg></span>Sınıf Panosu
            </a>
            <a class="{{ request()->routeIs('student.portal.progress') ? 'active' : '' }}" href="{{ route('student.portal.progress') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16v2H4zM6 10h3v7H6zm5-4h3v11h-3zm5 2h3v9h-3z"/></svg></span>Gelişim Karnem
            </a>
            <a class="{{ request()->routeIs('student.portal.avatars*') ? 'active' : '' }}" href="{{ route('student.portal.avatars') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a6 6 0 016 6v2h1a3 3 0 013 3v4a5 5 0 01-5 5H7a5 5 0 01-5-5v-4a3 3 0 013-3h1V8a6 6 0 016-6zm0 2a4 4 0 00-4 4v2h8V8a4 4 0 00-4-4z"/></svg></span>Avatarlarım
            </a>
            <a class="{{ request()->routeIs('student.portal.badges') ? 'active' : '' }}" href="{{ route('student.portal.badges') }}">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.4 4.9L20 8l-4 3.9.9 5.6L12 15l-4.9 2.5.9-5.6L4 8l5.6-.8L12 2z"/></svg></span>Rozetlerim
            </a>
        </nav>
        <div class="student-sidebar-time" id="student-sidebar-time" data-initial-seconds="{{ $initialSeconds }}" data-ping-url="{{ route('student.portal.time.ping') }}">
            <span>Sistemde Geçen Süre</span>
            <strong id="student-live-time">0s 0dk</strong>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
                @csrf
                <button class="btn" type="submit" style="width:100%">Çıkış Yap</button>
            </form>
        </div>
    @else
        <div class="sidebar-top sidebar-brand">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="sidebar-logo">
            <p>Bilişim Platformu</p>
        </div>
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11.5L12 4l9 7.5V21h-6v-5h-6v5H3z"/></svg></span>Panel
        </a>
        <a class="{{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 1114 0H5z"/></svg></span>Öğrenciler
        </a>
        <a class="{{ request()->routeIs('classes.*') ? 'active' : '' }}" href="{{ route('classes.index') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9l9-5 9 5v10l-9 5-9-5V9zm9-2.7L6 9.1l6 3.3 6-3.3-6-2.8z"/></svg></span>Sınıflar
        </a>
        <a class="{{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h9a3 3 0 013 3v11H7a3 3 0 00-3 3V5zm16 0h-4a3 3 0 00-3 3v11h7V5z"/></svg></span>Dersler
        </a>
        <a class="{{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}" href="{{ route('teacher.assignments.index') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h12a2 2 0 012 2v16l-4-2-4 2-4-2-4 2V4a2 2 0 012-2zm2 5v2h8V7H8zm0 4v2h8v-2H8z"/></svg></span>Ödevler
        </a>
        <a class="{{ request()->routeIs('student-data.*') ? 'active' : '' }}" href="{{ route('student-data.index') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4zM7 8h10v2H7zm0 4h10v2H7zm0 4h6v2H7z"/></svg></span>Öğrenci Verileri
        </a>
        <a class="{{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8h3v3H7V8zm7 0h3v3h-3V8zM5 5h14a2 2 0 012 2v10a2 2 0 01-2 2h-4l-2-2h-2l-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg></span>Oyun ve Etkinlikler
        </a>
    @endif
</aside>
