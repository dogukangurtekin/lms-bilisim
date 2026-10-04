@extends('layout.app')

@section('title', ($student->user->name ?? 'Öğrenci') . ' · Canlı Takip')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('live-tracking.index') }}"
       style="color:var(--app-primary);text-decoration:none;font-size:.85rem;">
        ← Canlı Takip Listesine Dön
    </a>
</div>

{{-- Üst bilgi kartları --}}
<div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap;margin-bottom:28px;">
    <div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;padding:20px 24px;flex:1;min-width:240px;">
        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--app-muted);margin-bottom:6px;">Öğrenci</div>
        <div style="font-size:1.25rem;font-weight:700;color:var(--app-text);">{{ $student->user->name ?? '-' }}</div>
        <div style="color:var(--app-muted);font-size:.85rem;margin-top:4px;">
            {{ $student->schoolClass->name ?? 'Sınıf atanmamış' }}
            @if($student->student_no)
                · No: {{ $student->student_no }}
            @endif
        </div>
    </div>

    <div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;padding:20px 24px;min-width:160px;text-align:center;">
        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--app-muted);margin-bottom:6px;">Toplam Kayıt</div>
        <div style="font-size:2rem;font-weight:800;color:var(--app-primary);">{{ $totalLogs }}</div>
        <div style="color:var(--app-muted);font-size:.75rem;">son 10 gün</div>
    </div>

    <div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;padding:20px 24px;min-width:200px;">
        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--app-muted);margin-bottom:6px;">Sayfa</div>
        <div style="font-size:1.1rem;font-weight:700;color:var(--app-text);">
            {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
        </div>
        <div style="color:var(--app-muted);font-size:.75rem;margin-top:2px;">
            Sayfa başına 20 kayıt · en yeni önce
        </div>
    </div>
</div>

{{-- Filtreler --}}
<form method="GET" action="{{ route('live-tracking.show', $student) }}"
      style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;padding:14px 18px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:16px;">
    <div style="flex:1;min-width:150px;">
        <label style="font-size:.75rem;color:var(--app-muted);display:block;margin-bottom:4px;font-weight:600;">Başlangıç Tarihi</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}"
               style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-surface);color:var(--app-text);font-size:.875rem;box-sizing:border-box;">
    </div>
    <div style="flex:1;min-width:150px;">
        <label style="font-size:.75rem;color:var(--app-muted);display:block;margin-bottom:4px;font-weight:600;">Bitiş Tarihi</label>
        <input type="date" name="date_to" value="{{ $dateTo }}"
               style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-surface);color:var(--app-text);font-size:.875rem;box-sizing:border-box;">
    </div>
    <div style="flex:2;min-width:200px;">
        <label style="font-size:.75rem;color:var(--app-muted);display:block;margin-bottom:4px;font-weight:600;">İşlem Ara</label>
        <input type="text" name="action_search" value="{{ $actionSearch }}" placeholder="ör: quiz, ders, ödev..."
               style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-surface);color:var(--app-text);font-size:.875rem;box-sizing:border-box;">
    </div>
    <div style="display:flex;gap:8px;">
        <button type="submit"
                style="padding:8px 18px;border-radius:8px;background:var(--app-primary);color:#fff;border:none;cursor:pointer;font-size:.875rem;font-weight:600;">
            Filtrele
        </button>
        @if($dateFrom || $dateTo || $actionSearch)
        <a href="{{ route('live-tracking.show', $student) }}"
           style="padding:8px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.875rem;">
            Temizle
        </a>
        @endif
    </div>
</form>

{{-- Timeline --}}
<div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--app-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--app-text);">
            Adım Adım Aktivite Akışı
        </h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:.78rem;color:var(--app-muted);">En yeniden en eskiye</span>
            @if($logs->total() > 0)
            <span style="font-size:.78rem;color:var(--app-muted);">
                {{ ($logs->currentPage() - 1) * 20 + 1 }}–{{ min($logs->currentPage() * 20, $logs->total()) }} / {{ $logs->total() }} kayıt
            </span>
            @endif
        </div>
    </div>

    <div style="padding:20px;">
        @if($logs->isEmpty())
            <div style="text-align:center;padding:40px;color:var(--app-muted);">
                Bu filtreye uygun aktivite kaydı bulunamadı.
            </div>
        @else
        <div class="timeline">
            @foreach($logs as $index => $log)
            @php
                $isFirst = $index === 0 && $logs->currentPage() === 1;
                $dotColor = match(true) {
                    str_contains($log->action_label ?? '', 'quiz')      => '#8b5cf6',
                    str_contains($log->action_label ?? '', 'Etkinlik')  => '#f59e0b',
                    str_contains($log->action_label ?? '', 'Ders')      => '#3b82f6',
                    str_contains($log->action_label ?? '', 'Ödev')      => '#ef4444',
                    str_contains($log->action_label ?? '', 'cevap')     => '#10b981',
                    $isFirst                                            => '#22c55e',
                    default                                             => 'var(--app-muted)',
                };
            @endphp
            <div class="timeline-item" style="display:flex;gap:14px;padding-bottom:4px;">
                <div class="timeline-left" style="display:flex;flex-direction:column;align-items:center;width:40px;flex-shrink:0;">
                    <div style="width:12px;height:12px;border-radius:50%;background:{{ $dotColor }};flex-shrink:0;margin-top:4px;
                        {{ $isFirst ? 'box-shadow:0 0 0 4px rgba(34,197,94,.2);' : '' }}"></div>
                    @if(!$loop->last)
                    <div style="width:2px;flex:1;min-height:20px;background:var(--app-border);margin-top:4px;"></div>
                    @endif
                </div>
                <div style="flex:1;padding-bottom:16px;">
                    <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap;">
                        <span style="font-weight:600;color:var(--app-text);font-size:.9rem;">
                            {{ $log->action_label ?? 'Sayfa ziyareti' }}
                        </span>
                        @if($isFirst)
                            <span style="font-size:.7rem;padding:1px 7px;border-radius:999px;background:rgba(34,197,94,.12);color:#16a34a;font-weight:600;">
                                En son
                            </span>
                        @endif
                    </div>
                    <div style="margin-top:3px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <span style="font-size:.78rem;color:var(--app-muted);">
                            🕐 {{ $log->logged_at->format('d.m.Y H:i:s') }}
                            · {{ $log->logged_at->diffForHumans() }}
                        </span>
                        <span style="font-size:.75rem;color:var(--app-muted);font-family:monospace;opacity:.7;">
                            /{{ $log->url }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sayfalama --}}
        <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:24px;padding-top:16px;border-top:1px solid var(--app-border);flex-wrap:wrap;">
            {{-- Önceki --}}
            @if($logs->onFirstPage())
                <span style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);color:var(--app-muted);font-size:.85rem;cursor:not-allowed;">← Önceki</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}"
                   style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">← Önceki</a>
            @endif

            {{-- Sayfa numaraları --}}
            @php
                $start = max(1, $logs->currentPage() - 2);
                $end   = min($logs->lastPage(), $logs->currentPage() + 2);
            @endphp

            @if($start > 1)
                <a href="{{ $logs->url(1) }}"
                   style="padding:7px 12px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">1</a>
                @if($start > 2)
                    <span style="color:var(--app-muted);padding:0 4px;">…</span>
                @endif
            @endif

            @for($p = $start; $p <= $end; $p++)
                @if($p === $logs->currentPage())
                    <span style="padding:7px 12px;border-radius:8px;background:var(--app-primary);color:#fff;font-size:.85rem;font-weight:700;">{{ $p }}</span>
                @else
                    <a href="{{ $logs->url($p) }}"
                       style="padding:7px 12px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">{{ $p }}</a>
                @endif
            @endfor

            @if($end < $logs->lastPage())
                @if($end < $logs->lastPage() - 1)
                    <span style="color:var(--app-muted);padding:0 4px;">…</span>
                @endif
                <a href="{{ $logs->url($logs->lastPage()) }}"
                   style="padding:7px 12px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">{{ $logs->lastPage() }}</a>
            @endif

            {{-- Sonraki --}}
            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}"
                   style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);text-decoration:none;font-size:.85rem;">Sonraki →</a>
            @else
                <span style="padding:7px 14px;border-radius:8px;border:1px solid var(--app-border);color:var(--app-muted);font-size:.85rem;cursor:not-allowed;">Sonraki →</span>
            @endif
        </div>

        @endif
    </div>
</div>

<style>
.timeline { padding-left: 0; }
</style>
@endsection
