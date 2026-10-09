@extends('layout.app')

@section('title', 'Canlı Takip')

@section('content')
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
    <div>
        <h1 style="margin:0;font-size:1.5rem;font-weight:700;color:var(--app-text);">
            <span style="display:inline-flex;align-items:center;gap:8px;">
                <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;box-shadow:0 0 0 3px rgba(34,197,94,.25);animation:pulse-dot 1.5s infinite;"></span>
                Canlı Takip
            </span>
        </h1>
        <p style="margin:4px 0 0;color:var(--app-muted);font-size:.875rem;">
            Son 10 günde aktif · <span id="student-count">{{ $students->count() }}</span> öğrenci
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span id="last-refresh" style="font-size:.75rem;color:var(--app-muted);">Şimdi yüklendi</span>
        <button id="auto-refresh-toggle" onclick="toggleAutoRefresh()" style="padding:6px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);cursor:pointer;font-size:.8rem;">
            ⏸ Otomatik: Açık
        </button>
    </div>
</div>

{{-- Filtreler --}}
<form id="filter-form" method="GET" action="{{ route('live-tracking.index') }}"
      style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;padding:14px 18px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:16px;">
    <div style="flex:1;min-width:160px;">
        <label style="font-size:.75rem;color:var(--app-muted);display:block;margin-bottom:4px;font-weight:600;">Sınıf</label>
        <select name="class_id" onchange="document.getElementById('filter-form').submit()"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-surface);color:var(--app-text);font-size:.875rem;">
            <option value="">Tüm Sınıflar</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ (string)$classId === (string)$class->id ? 'selected' : '' }}>
                    {{ $class->name }}{{ $class->section ? '-'.$class->section : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div style="flex:2;min-width:200px;">
        <label style="font-size:.75rem;color:var(--app-muted);display:block;margin-bottom:4px;font-weight:600;">Öğrenci Adı</label>
        <input type="text" name="search" id="search-input" value="{{ $search }}" placeholder="İsme göre ara..."
               style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-surface);color:var(--app-text);font-size:.875rem;box-sizing:border-box;">
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;">
        @if($classId || $search)
        <a href="{{ route('live-tracking.index') }}"
           style="padding:8px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.875rem;">
            Temizle
        </a>
        @endif
    </div>
</form>

<style>
@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 3px rgba(34,197,94,.25); }
    50%      { box-shadow: 0 0 0 6px rgba(34,197,94,.05); }
}
.tracking-table { width:100%; border-collapse:collapse; }
.tracking-table th {
    text-align:left; padding:10px 14px;
    background:var(--app-surface); border-bottom:2px solid var(--app-border);
    font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;
    color:var(--app-muted); font-weight:600;
}
.tracking-table td {
    padding:12px 14px; border-bottom:1px solid var(--app-border);
    font-size:.875rem; color:var(--app-text); vertical-align:middle;
}
.tracking-table tr:hover td { background:var(--app-surface); }
.badge-online {
    display:inline-flex; align-items:center; gap:5px;
    padding:2px 8px; border-radius:999px;
    background:rgba(34,197,94,.12); color:#16a34a;
    font-size:.7rem; font-weight:600;
}
.badge-online::before {
    content:''; width:6px; height:6px; border-radius:50%; background:#22c55e;
    animation:pulse-dot 1.5s infinite;
}
.student-link { color:var(--app-primary); text-decoration:none; font-weight:600; }
.student-link:hover { text-decoration:underline; }
.empty-state { text-align:center; padding:60px 20px; color:var(--app-muted); font-size:.95rem; }
</style>

<div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;overflow:hidden;">
    <table class="tracking-table" id="tracking-table">
        <thead>
            <tr>
                <th>Öğrenci</th>
                <th>Sınıf</th>
                <th>Son İşlem</th>
                <th>Son Görülme</th>
                <th>Giriş Saati</th>
                <th style="text-align:center;">İşlem</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="tracking-body">
            @forelse($students as $row)
            <tr>
                <td>
                    <a class="student-link" href="{{ route('live-tracking.show', $row['student']) }}">
                        {{ $row['student']->user->name ?? '-' }}
                    </a>
                </td>
                <td>{{ ($row['student']->schoolClass->name ?? '') . ($row['student']->schoolClass->section ? '-'.$row['student']->schoolClass->section : '') ?: '-' }}</td>
                <td style="max-width:260px;">{{ $row['last_action'] ?? '-' }}</td>
                <td>
                    @if($row['last_seen'])
                        <span class="badge-online">{{ $row['last_seen']->diffForHumans() }}</span>
                    @else -
                    @endif
                </td>
                <td>{{ $row['first_seen'] ? $row['first_seen']->copy()->setTimezone('Europe/Istanbul')->format('H:i') : '-' }}</td>
                <td style="text-align:center;"><strong>{{ $row['log_count'] }}</strong></td>
                <td>
                    <a href="{{ route('live-tracking.show', $row['student']) }}"
                       style="padding:5px 12px;border-radius:7px;background:var(--app-primary);color:#fff;text-decoration:none;font-size:.78rem;font-weight:600;">
                        Detay →
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-state">Son 10 günde aktif öğrenci bulunamadı.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Sayfalama --}}
@if($students->lastPage() > 1)
<div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
    @if($students->onFirstPage())
        <span style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);color:var(--app-muted);font-size:.85rem;cursor:not-allowed;">← Önceki</span>
    @else
        <a href="{{ $students->previousPageUrl() }}"
           style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">← Önceki</a>
    @endif

    @php
        $start = max(1, $students->currentPage() - 2);
        $end   = min($students->lastPage(), $students->currentPage() + 2);
    @endphp

    @if($start > 1)
        <a href="{{ $students->url(1) }}"
           style="padding:7px 12px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">1</a>
        @if($start > 2)<span style="color:var(--app-muted);padding:0 4px;">…</span>@endif
    @endif

    @for($p = $start; $p <= $end; $p++)
        @if($p === $students->currentPage())
            <span style="padding:7px 12px;border-radius:8px;background:var(--app-primary);color:#fff;font-size:.85rem;font-weight:700;">{{ $p }}</span>
        @else
            <a href="{{ $students->url($p) }}"
               style="padding:7px 12px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">{{ $p }}</a>
        @endif
    @endfor

    @if($end < $students->lastPage())
        @if($end < $students->lastPage() - 1)<span style="color:var(--app-muted);padding:0 4px;">…</span>@endif
        <a href="{{ $students->url($students->lastPage()) }}"
           style="padding:7px 12px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">{{ $students->lastPage() }}</a>
    @endif

    @if($students->hasMorePages())
        <a href="{{ $students->nextPageUrl() }}"
           style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">Sonraki →</a>
    @else
        <span style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);color:var(--app-muted);font-size:.85rem;cursor:not-allowed;">Sonraki →</span>
    @endif
</div>
@endif

<script>
let autoRefresh = true;
let refreshInterval = null;
const currentClassId = '{{ $classId ?? "" }}';
const currentSearch  = '{{ addslashes($search) }}';

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    const btn = document.getElementById('auto-refresh-toggle');
    if (autoRefresh) {
        btn.textContent = '⏸ Otomatik: Açık';
        startRefresh();
    } else {
        btn.textContent = '▶ Otomatik: Kapalı';
        clearInterval(refreshInterval);
    }
}

function startRefresh() {
    clearInterval(refreshInterval);
    refreshInterval = setInterval(fetchData, 15000);
}

async function fetchData() {
    try {
        const params = new URLSearchParams();
        if (currentClassId) params.set('class_id', currentClassId);
        if (currentSearch)  params.set('search', currentSearch);

        const res  = await fetch('{{ route("live-tracking.refresh") }}?' + params.toString());
        const rows = await res.json();

        document.getElementById('student-count').textContent = rows.length;
        document.getElementById('last-refresh').textContent  = 'Son güncelleme: ' + new Intl.DateTimeFormat('tr-TR', {
            timeZone: 'Europe/Istanbul',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).format(new Date());

        const bodyEl = document.getElementById('tracking-body');

        if (rows.length === 0) {
            bodyEl.innerHTML = '<tr><td colspan="7" class="empty-state">Son 10 günde aktif öğrenci bulunamadı.</td></tr>';
            return;
        }

        bodyEl.innerHTML = rows.map(r => `
            <tr>
                <td><a class="student-link" href="${r.detail_url}">${r.name}</a></td>
                <td>${r.class}</td>
                <td style="max-width:260px;">${r.last_action}</td>
                <td><span class="badge-online">${r.last_seen}</span></td>
                <td>${r.first_seen}</td>
                <td style="text-align:center;"><strong>${r.log_count}</strong></td>
                <td><a href="${r.detail_url}" style="padding:5px 12px;border-radius:7px;background:var(--app-primary);color:#fff;text-decoration:none;font-size:.78rem;font-weight:600;">Detay →</a></td>
            </tr>
        `).join('');
    } catch(e) {
        console.warn('Canlı takip güncelleme hatası:', e);
    }
}

if (autoRefresh) startRefresh();
// Sayfalama varsa ve 1. sayfada değilsek otomatik yenilemeyi kapat
const currentPage = {{ $students->currentPage() }};
if (currentPage > 1) {
    autoRefresh = false;
    document.getElementById('auto-refresh-toggle').textContent = '▶ Otomatik: Kapalı';
    document.getElementById('auto-refresh-toggle').title = 'Sayfalama aktifken otomatik yenileme devre dışı';
}

// Öğrenci adı arama — yazarken 500ms debounce ile form submit
let searchDebounce = null;
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function() {
            document.getElementById('filter-form').submit();
        }, 500);
    });
}
</script>
@endsection
