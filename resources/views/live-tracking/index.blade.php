@extends('layout.app')

@section('title', 'Canlı Takip')

@section('content')
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;font-size:1.5rem;font-weight:700;color:var(--app-text);">
            <span style="display:inline-flex;align-items:center;gap:8px;">
                <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;box-shadow:0 0 0 3px rgba(34,197,94,.25);animation:pulse-dot 1.5s infinite;"></span>
                Canlı Takip
            </span>
        </h1>
        <p style="margin:4px 0 0;color:var(--app-muted);font-size:.875rem;">
            Son 2 saatte sisteme giren öğrenciler · <span id="student-count">{{ $students->count() }}</span> öğrenci aktif
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span id="last-refresh" style="font-size:.75rem;color:var(--app-muted);">Şimdi güncellendi</span>
        <button id="auto-refresh-toggle" onclick="toggleAutoRefresh()" style="padding:6px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);cursor:pointer;font-size:.8rem;">
            ⏸ Otomatik Yenile: Açık
        </button>
    </div>
</div>

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
.student-link {
    color:var(--app-primary); text-decoration:none; font-weight:600;
}
.student-link:hover { text-decoration:underline; }
.empty-state {
    text-align:center; padding:60px 20px;
    color:var(--app-muted); font-size:.95rem;
}
</style>

<div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;overflow:hidden;">
    <table class="tracking-table" id="tracking-table">
        <thead>
            <tr>
                <th>Öğrenci</th>
                <th>Sınıf</th>
                <th>Son İşlem</th>
                <th>Son Görülme</th>
                <th>Sisteme Giriş</th>
                <th>İşlem Sayısı</th>
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
                <td>{{ $row['student']->schoolClass->name ?? '-' }}</td>
                <td style="max-width:260px;">{{ $row['last_action'] ?? '-' }}</td>
                <td>
                    @if($row['last_seen'])
                        <span class="badge-online">{{ $row['last_seen']->diffForHumans() }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $row['first_seen'] ? $row['first_seen']->format('H:i') : '-' }}</td>
                <td style="text-align:center;">
                    <strong>{{ $row['log_count'] }}</strong>
                </td>
                <td>
                    <a href="{{ route('live-tracking.show', $row['student']) }}"
                       style="padding:5px 12px;border-radius:7px;background:var(--app-primary);color:#fff;text-decoration:none;font-size:.78rem;font-weight:600;">
                        Detay →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-state">
                    Son 2 saatte sisteme giren öğrenci yok.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
let autoRefresh = true;
let refreshInterval = null;

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    const btn = document.getElementById('auto-refresh-toggle');
    if (autoRefresh) {
        btn.textContent = '⏸ Otomatik Yenile: Açık';
        startRefresh();
    } else {
        btn.textContent = '▶ Otomatik Yenile: Kapalı';
        clearInterval(refreshInterval);
    }
}

function startRefresh() {
    clearInterval(refreshInterval);
    refreshInterval = setInterval(fetchData, 15000);
}

async function fetchData() {
    try {
        const res = await fetch('{{ route("live-tracking.refresh") }}');
        const rows = await res.json();

        const countEl = document.getElementById('student-count');
        const bodyEl  = document.getElementById('tracking-body');
        const lastEl  = document.getElementById('last-refresh');

        countEl.textContent = rows.length;
        lastEl.textContent  = 'Son güncelleme: ' + new Date().toLocaleTimeString('tr-TR');

        if (rows.length === 0) {
            bodyEl.innerHTML = '<tr><td colspan="7" class="empty-state">Son 2 saatte sisteme giren öğrenci yok.</td></tr>';
            return;
        }

        bodyEl.innerHTML = rows.map(r => `
            <tr>
                <td><a class="student-link" href="${r.detail_url}">${r.name}</a></td>
                <td>${r.class}</td>
                <td style="max-width:260px;">${r.last_action}</td>
                <td><span class="badge-online">${r.last_seen}</span></td>
                <td>-</td>
                <td style="text-align:center;"><strong>-</strong></td>
                <td><a href="${r.detail_url}" style="padding:5px 12px;border-radius:7px;background:var(--app-primary);color:#fff;text-decoration:none;font-size:.78rem;font-weight:600;">Detay →</a></td>
            </tr>
        `).join('');
    } catch(e) {
        console.warn('Canlı takip güncelleme hatası:', e);
    }
}

if (autoRefresh) startRefresh();
</script>
@endsection
