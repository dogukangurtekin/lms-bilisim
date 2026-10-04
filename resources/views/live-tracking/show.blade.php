@extends('layout.app')

@section('title', ($student->user->name ?? 'Öğrenci') . ' · Canlı Takip')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('live-tracking.index') }}"
       style="color:var(--app-primary);text-decoration:none;font-size:.85rem;">
        ← Canlı Takip Listesine Dön
    </a>
</div>

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
        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--app-muted);margin-bottom:6px;">İşlem Sayısı</div>
        <div style="font-size:2rem;font-weight:800;color:var(--app-primary);">{{ $logs->count() }}</div>
        <div style="color:var(--app-muted);font-size:.75rem;">son 2 saat</div>
    </div>

    <div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;padding:20px 24px;min-width:200px;">
        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--app-muted);margin-bottom:6px;">Zaman Aralığı</div>
        @if($logs->isNotEmpty())
            <div style="font-size:.9rem;font-weight:600;color:var(--app-text);">
                {{ $logs->last()->logged_at->format('H:i') }} → {{ $logs->first()->logged_at->format('H:i') }}
            </div>
            <div style="color:var(--app-muted);font-size:.75rem;margin-top:4px;">
                {{ $logs->last()->logged_at->format('d.m.Y') }}
            </div>
        @else
            <div style="color:var(--app-muted);">Kayıt yok</div>
        @endif
    </div>
</div>

<div style="background:var(--app-panel);border:1px solid var(--app-border);border-radius:12px;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--app-border);display:flex;align-items:center;justify-content:space-between;">
        <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--app-text);">
            Adım Adım Aktivite Akışı
        </h2>
        <span style="font-size:.78rem;color:var(--app-muted);">En yeniden en eskiye</span>
    </div>

    <div style="padding:20px;">
        @if($logs->isEmpty())
            <div style="text-align:center;padding:40px;color:var(--app-muted);">
                Son 2 saatte aktivite kaydı bulunamadı.
            </div>
        @else
        <div class="timeline">
            @foreach($logs as $index => $log)
            @php
                $isFirst = $index === 0;
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
                            🕐 {{ $log->logged_at->format('H:i:s') }}
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
        @endif
    </div>
</div>

<style>
.timeline { padding-left: 0; }
</style>
@endsection
