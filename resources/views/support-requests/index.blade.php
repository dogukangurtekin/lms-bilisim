@extends('layout.app')
@section('title', 'Taleplerim')
@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    $isAdmin = (bool) ($isAdmin ?? false);
    $isTeacher = (bool) ($isTeacher ?? false);
    $selectedRequest = $selectedRequest ?? null;
    $requests = $requests ?? collect();
    $replyCount = (int) ($selectedRequest?->replies?->count() ?? 0);
    $statusLabels = [
        'open' => 'Beklemede',
        'read' => 'Okundu',
        'answered' => 'Cevaplandı',
        'closed' => 'İptal Edildi',
        'archived' => 'İptal Edildi',
    ];
    $priorityLabels = ['low' => 'Düşük', 'normal' => 'Normal', 'high' => 'Yüksek'];
    $categoryLabels = [
        'technical_support' => 'Teknik destek',
        'lesson_content' => 'Ders içeriği',
        'user_permission' => 'Kullanıcı yetkisi',
        'bug_report' => 'Hata bildirimi',
        'other' => 'Diğer',
    ];
@endphp

<style>
    .panel-section{border:1px solid var(--line,#e2e8f0);border-radius:14px;padding:16px;margin-bottom:16px;background:var(--surface,#fff)}
    .panel-section:last-child{margin-bottom:0}
    .panel-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:0}
    .panel-section-head h3{margin:0;font-family:var(--font-display);font-size:15px;color:var(--ink,#16182B)}
    .panel-section-head p{margin:2px 0 0;font-size:12.5px;color:var(--ink-soft,#585A72)}
    :root{
        --sr-bg: var(--paper, #F7F6F2);
        --sr-text: var(--ink, #16182B);
        --sr-muted: var(--ink-soft, #585A72);
        --sr-panel: var(--surface, #fff);
        --sr-surface: var(--surface, #fff);
        --sr-border: var(--line, #E4E1D8);
        --sr-primary: var(--violet, #5B3DF5);
        --sr-secondary: var(--violet-ink, #3E28B8);
    }
    .sr-shell{display:grid;gap:16px}
    .sr-grid{display:grid;grid-template-columns:320px minmax(0,1fr);gap:16px;align-items:start}
    .sr-card{background:var(--sr-surface);border:1px solid var(--sr-border);border-radius:14px;padding:16px;min-width:0;color:var(--sr-text)}
    .sr-list{display:grid;gap:10px;max-height:72vh;overflow:auto}
    .sr-item{display:flex;align-items:flex-start;gap:8px;padding:12px 14px;border-radius:14px;border:1px solid var(--sr-border);background:var(--sr-panel);transition:border-color .15s,background .15s}
    .sr-item:hover{border-color:var(--sr-primary)}
    .sr-item.is-active{border-color:var(--sr-primary);background:var(--violet-tint,#EEEBFD)}
    .sr-item-link{flex:1 1 auto;min-width:0;display:grid;gap:6px;text-decoration:none;color:inherit}
    .sr-item-delete{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;margin-top:1px;border-radius:9px;border:1px solid var(--sr-border);background:var(--sr-panel);color:#94a3b8;cursor:pointer;transition:color .15s,border-color .15s,background .15s}
    .sr-item-delete:hover{color:var(--signal,#FF7A45);border-color:var(--signal,#FF7A45);background:var(--signal-tint,#FFEEE4)}
    .sr-item-delete svg{width:16px;height:16px;display:block;pointer-events:none}
    .sr-meta{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .sr-badge{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800}
    .sr-badge.status-open{background:#fef3c7;color:#92400e}
    .sr-badge.status-read{background:#dbeafe;color:#1d4ed8}
    .sr-badge.status-answered{background:#dcfce7;color:#166534}
    .sr-badge.status-closed{background:#e2e8f0;color:#334155}
    .sr-badge.status-archived{background:#ede9fe;color:#6d28d9}
    .sr-badge.priority-low{background:#ecfeff;color:#0e7490}
    .sr-badge.priority-normal{background:#eef2ff;color:#3730a3}
    .sr-badge.priority-high{background:#fee2e2;color:#b91c1c}
    .sr-field{display:grid;gap:6px;margin-top:12px}
    .sr-field label{font-size:12.5px;font-weight:600;color:var(--sr-muted)}
    .sr-input,.sr-select,.sr-textarea{width:100%;border:1px solid var(--sr-border);border-radius:10px;padding:11px 12px;background:var(--sr-panel);box-sizing:border-box;color:var(--sr-text)}
    .sr-textarea{min-height:120px;resize:vertical}
    .sr-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border:1px solid transparent;border-radius:8px;font-weight:600;font-family:var(--font-display);text-decoration:none;cursor:pointer;transition:filter .15s}
    .sr-btn:hover{filter:brightness(.88)}
    .sr-btn.primary{background:var(--sr-primary);border-color:var(--sr-primary);color:#fff}
    .sr-btn.warning{background:var(--signal,#FF7A45);border-color:var(--signal,#FF7A45);color:#fff}
    .sr-btn.ghost{background:var(--sr-panel);color:var(--sr-text);border-color:var(--sr-border)}
    .sr-btn.danger{background:var(--signal,#FF7A45);border-color:var(--signal,#FF7A45);color:#fff}
    .sr-stack{display:grid;gap:14px}
    .sr-reply{display:grid;gap:10px;padding:12px;border:1px solid var(--sr-border);border-radius:14px;background:color-mix(in srgb, var(--sr-panel) 88%, var(--sr-primary) 12%)}
    .sr-reply.internal{background:color-mix(in srgb, var(--sr-panel) 84%, var(--signal,#FF7A45) 16%)}
    .sr-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.55);z-index:60}
    .sr-modal.is-open{display:flex}
    .sr-modal-panel{width:min(100%,980px);max-height:88vh;overflow:auto;background:var(--sr-panel);border-radius:20px;box-shadow:0 30px 80px rgba(15,23,42,.28);border:1px solid var(--sr-border)}
    .sr-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:18px 20px;border-bottom:1px solid var(--sr-border);position:sticky;top:0;background:var(--sr-panel);z-index:1}
    .sr-modal-body{padding:20px}
    @media (max-width: 1180px){.sr-grid{grid-template-columns:1fr}.sr-list{max-height:none}}
</style>

<div class="top"><h1>Taleplerim</h1></div>

<section class="sr-shell">
    <div class="panel-section">
        <div class="panel-section-head">
            <div>
                <h3>Destek ve İletişim</h3>
                <p>Öğretmen taleplerini gönderir, admin inceleyip cevaplar.</p>
            </div>
            <div class="sr-meta">
                <span class="sr-badge status-open">Açık: {{ $requests->where('status', 'open')->count() }}</span>
                <span class="sr-badge status-answered">Cevaplı: {{ $requests->where('status', 'answered')->count() }}</span>
                <span class="sr-badge status-closed">Kapalı: {{ $requests->where('status', 'closed')->count() }}</span>
            </div>
        </div>
    </div>

        <div class="sr-grid">
        <aside class="panel-section">
            <div class="panel-section-head" style="margin-bottom:12px">
                <div>
                    <h3>Talep Listesi</h3>
                    <p>Konuya veya duruma göre filtreleyin.</p>
                </div>
            </div>
            <form method="GET" class="sr-stack">
                <input class="sr-input" type="search" name="q" value="{{ $search ?? '' }}" placeholder="Öğretmen, konu, tarih">
                <select class="sr-select" name="status">
                    <option value="">Tüm durumlar</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected(($status ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="sr-btn primary" type="submit">Filtrele</button>
            </form>

            <div class="sr-list" style="margin-top:14px">
                @forelse($requests as $ticket)
                    @php
                        $active = (int) ($selectedRequest?->id ?? 0) === (int) $ticket->id;
                        $statusClass = 'status-' . ($ticket->status ?? 'open');
                        $priorityClass = 'priority-' . ($ticket->priority ?? 'normal');
                        $canDeleteTicket = $isAdmin || (int) $ticket->sender_user_id === (int) auth()->id();
                    @endphp
                    <div class="sr-item {{ $active ? 'is-active' : '' }}">
                        <a class="sr-item-link" href="{{ route('support-requests.index', array_merge(request()->except('page'), ['selected' => $ticket->id])) }}">
                            <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start">
                                <strong style="font-size:15px;line-height:1.35">{{ $ticket->subject }}</strong>
                                <span class="sr-badge {{ $statusClass }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                            </div>
                            <div style="color:#475569;font-size:13px">
                                {{ $ticket->guest_name ?: ($ticket->sender?->name ?? 'Bilinmiyor') }}
                                @if($ticket->guest_email)
                                    · {{ $ticket->guest_email }}
                                @endif
                                · {{ optional($ticket->created_at)->format('d.m.Y H:i') }}
                            </div>
                            <div class="sr-meta">
                                <span class="sr-badge {{ $priorityClass }}">{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
                                <span class="sr-badge" style="background:#e0f2fe;color:#0369a1">{{ $categoryLabels[$ticket->category] ?? $ticket->category }}</span>
                            </div>
                        </a>
                        @if($canDeleteTicket)
                            <form method="POST" action="{{ route('support-requests.destroy', $ticket) }}" data-confirm="'{{ $ticket->subject }}' talebi silinsin mi?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sr-item-delete" title="Talebi Sil" aria-label="Talebi Sil">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div style="padding:14px;border:1px dashed #cbd5e1;border-radius:14px;color:#64748b;background:#f8fafc">Henüz talep yok.</div>
                @endforelse
            </div>
            <div style="margin-top:12px">{{ $requests->links('partials.pagination') }}</div>
        </aside>

        <main class="sr-center">
            <div class="panel-section">
                @if($isTeacher)
                    <details style="border:1px solid var(--line,#E4E1D8);border-radius:14px;padding:14px;background:var(--surface,#fff)" open>
                        <summary style="cursor:pointer;font-size:16px;font-weight:700;font-family:var(--font-display);list-style:none;color:var(--ink,#16182B)">Yeni Talep Oluştur</summary>
                        <form method="POST" action="{{ route('support-requests.store') }}" enctype="multipart/form-data" class="sr-stack" style="margin-top:14px">
                            @csrf
                            <div class="sr-field">
                                <label>Başlık</label>
                                <input class="sr-input" name="subject" value="{{ old('subject') }}" required>
                            </div>
                            <div class="sr-field">
                                <label>Kategori</label>
                                <select class="sr-select" name="category" required>
                                    @foreach($categoryLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sr-field">
                                <label>Öncelik</label>
                                <select class="sr-select" name="priority" required>
                                    @foreach($priorityLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(old('priority', 'normal') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sr-field">
                                <label>Mesaj</label>
                                <textarea class="sr-textarea" name="message" required>{{ old('message') }}</textarea>
                            </div>
                            <button class="sr-btn primary" type="submit">Talep Gönder</button>
                        </form>
                    </details>
                @elseif($isAdmin)
                    <div style="padding:24px;border:1px dashed #cbd5e1;border-radius:14px;color:#64748b;background:#f8fafc">Soldaki taleplerden birine tıklayın. Sohbet popup içinde açılacak.</div>
                @elseif($isTeacher)
                    <div style="padding:24px;border:1px dashed #cbd5e1;border-radius:14px;color:#64748b;background:#f8fafc">Soldaki taleplerden birine tıklayın. Sohbet popup içinde açılacak.</div>
                @else
                    <div style="padding:24px;border:1px dashed #cbd5e1;border-radius:14px;color:#64748b;background:#f8fafc">Yeni talep oluşturma alanı öğretmen hesabında görünür.</div>
                @endif
            </div>
        </main>

    </div>
</section>

@if($selectedRequest)
    @php
        $attachmentUrl = $selectedRequest->attachment_path ? Storage::url($selectedRequest->attachment_path) : '';
        $modalTitle = $selectedRequest->subject;
    @endphp
    <div class="sr-modal is-open" id="supportRequestModal">
        <div class="sr-modal-panel" role="dialog" aria-modal="true" aria-labelledby="supportRequestModalTitle">
            <div class="sr-modal-head">
                <div>
                    <h2 id="supportRequestModalTitle" style="margin:0;font-size:24px;font-weight:900">{{ $selectedRequest->subject }}</h2>
                    <div style="margin-top:6px;color:#64748b">
                        {{ $selectedRequest->guest_name ?: ($selectedRequest->sender?->name ?? '-') }}
                        @if($selectedRequest->guest_email)
                            · {{ $selectedRequest->guest_email }}
                        @endif
                        · {{ optional($selectedRequest->created_at)->format('d.m.Y H:i') }}
                    </div>
                </div>
                <button type="button" class="sr-btn ghost" id="supportRequestModalClose">Kapat</button>
            </div>
            <div class="sr-modal-body">
                <div class="sr-stack">
                    <div class="sr-meta">
                        <span class="sr-badge status-{{ $selectedRequest->status }}">{{ $statusLabels[$selectedRequest->status] ?? $selectedRequest->status }}</span>
                        <span class="sr-badge priority-{{ $selectedRequest->priority }}">{{ $priorityLabels[$selectedRequest->priority] ?? $selectedRequest->priority }}</span>
                        <span class="sr-badge" style="background:#e0f2fe;color:#0369a1">{{ $categoryLabels[$selectedRequest->category] ?? $selectedRequest->category }}</span>
                    </div>

                    <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fbff;white-space:pre-wrap;line-height:1.7">{{ $selectedRequest->message }}</div>

                    @if($attachmentUrl !== '')
                        <a class="sr-btn ghost" href="{{ $attachmentUrl }}" target="_blank" rel="noopener">Ek Dosyayı Aç</a>
                    @endif

                    <div>
                        <h3 style="margin:0 0 10px;font-size:18px;font-weight:800">Sohbet Geçmişi</h3>
                        <div class="sr-stack">
                            @forelse($selectedRequest->replies as $reply)
                                <div class="sr-reply {{ $reply->sender_user_id === auth()->id() ? 'internal' : '' }}">
                                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
                                        <strong>{{ $reply->sender?->name ?? '-' }}</strong>
                                        <span style="font-size:12px;color:#64748b">{{ optional($reply->created_at)->format('d.m.Y H:i') }}</span>
                                    </div>
                                    <div style="white-space:pre-wrap;line-height:1.65">{{ $reply->message }}</div>
                                </div>
                            @empty
                                <div style="padding:12px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b">
                                    @if($selectedRequest->status === 'answered')
                                        Admin henüz mesaj bırakmadı.
                                    @else
                                        Henüz mesaj yok. Talep beklemede.
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if($isAdmin || $isTeacher)
                        <form method="POST" action="{{ route('support-requests.reply', $selectedRequest) }}" class="sr-stack">
                            @csrf
                            <textarea class="sr-textarea" name="message" placeholder="Mesajınızı yazın..." required></textarea>
                            @if($isAdmin)
                                <select class="sr-select" name="status">
                                    @foreach($statusLabels as $key => $label)
                                        <option value="{{ $key }}" @selected($selectedRequest->status === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="status" value="answered">
                            @endif
                            <button class="sr-btn primary" type="submit">Gönder</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('supportRequestModal');
            var closeBtn = document.getElementById('supportRequestModalClose');
            if (!modal) return;

            function closeModal() {
                var active = document.activeElement;
                if (active && modal.contains(active) && typeof active.blur === 'function') {
                    active.blur();
                }

                modal.classList.remove('is-open');
            }

            closeBtn && closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeModal();
            });
        })();
    </script>
@endif
@endsection