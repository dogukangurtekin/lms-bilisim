@extends('layout.app')
@section('title','Öğrenci Mesajları')
@section('content')
<div class="top"><h1>💬 Öğrenci Mesajları</h1></div>

<style>
    .inbox{display:grid;gap:16px;max-width:900px}
    .im-card{border:1px solid #dbe5f2;border-radius:16px;background:#fff;padding:14px 16px;display:grid;gap:10px}
    .im-card.pending{border-color:#fde68a;background:#fffbeb}
    .im-head{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:13px;color:#64748b}
    .im-head strong{color:#0f172a;font-size:15px}
    .im-body{white-space:pre-wrap;word-break:break-word;line-height:1.5;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px}
    .im-reply{background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;padding:10px 12px;white-space:pre-wrap;word-break:break-word}
    .im-form{display:grid;gap:8px}
    .im-form textarea{width:100%;min-height:80px;border:1px solid #dbe5f2;border-radius:12px;padding:10px;font:inherit;resize:vertical}
    .im-del{display:inline;margin:0}
    .im-del button{border:0;background:#fee2e2;color:#b91c1c;border-radius:10px;padding:6px 12px;font:inherit;font-weight:700;cursor:pointer}
    .im-del button:hover{background:#fecaca}
    .im-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .im-tag{display:inline-block;padding:2px 10px;border-radius:999px;background:#eeebfd;color:#4c1d95;font-size:12px;font-weight:700}
</style>

@php($who = fn ($m) => $m->recipient_type === 'admin' ? 'Yöneticiye' : ('Öğretmene: '.($m->recipient?->name ?? '-')))
<div class="inbox">
    <div>
        <h3 style="margin:0 0 8px">Cevap Bekleyenler ({{ $pending->count() }})</h3>
        <div style="display:grid;gap:12px">
        @forelse($pending as $m)
            <div class="im-card pending">
                <div class="im-head">
                    <span><strong>{{ $m->student?->user?->name ?? 'Öğrenci' }}</strong> · {{ $m->student?->schoolClass?->name }}{{ $m->student?->schoolClass?->section ? '/'.$m->student->schoolClass->section : '' }}</span>
                    <span><span class="im-tag">{{ $who($m) }}</span> · {{ $m->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="im-body">{{ $m->body }}</div>
                <form method="POST" action="{{ route('staff.messages.reply', $m) }}" class="im-form">
                    @csrf
                    <textarea name="reply_body" maxlength="1000" placeholder="Geri dönütünü yaz… (en fazla 1000 karakter)" required></textarea>
                    <div class="im-actions"><button type="submit" class="btn">↩️ Cevapla</button></div>
                </form>
                @if($isAdmin)
                    <form method="POST" action="{{ route('staff.messages.destroy', $m) }}" class="im-del" onsubmit="return confirm('Bu mesajı silmek istediğine emin misin?')">
                        @csrf @method('DELETE')
                        <button type="submit">🗑 Mesajı sil</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="card" style="color:#64748b">Cevap bekleyen mesaj yok. 🎉</div>
        @endforelse
        </div>
    </div>

    <div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 8px">
            <h3 style="margin:0">Cevaplananlar</h3>
            @if($isAdmin && $answered->count())
                <form method="POST" action="{{ route('staff.messages.clear-answered') }}" class="im-del" onsubmit="return confirm('Cevaplanmış tüm mesajlar gelen kutusundan silinsin mi?')">
                    @csrf @method('DELETE')
                    <button type="submit">🗑 Cevaplananların hepsini sil</button>
                </form>
            @endif
        </div>
        <div style="display:grid;gap:12px">
        @forelse($answered as $m)
            <div class="im-card">
                <div class="im-head">
                    <span><strong>{{ $m->student?->user?->name ?? 'Öğrenci' }}</strong> · {{ $m->student?->schoolClass?->name }}{{ $m->student?->schoolClass?->section ? '/'.$m->student->schoolClass->section : '' }}</span>
                    <span><span class="im-tag">{{ $who($m) }}</span> · {{ $m->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="im-body">{{ $m->body }}</div>
                <div class="im-reply"><small style="color:#64748b">{{ $m->replier?->name ?? 'Cevap' }} · {{ $m->replied_at?->format('d.m.Y H:i') }}</small><br>{{ $m->reply_body }}</div>
                @if($isAdmin)
                    <form method="POST" action="{{ route('staff.messages.destroy', $m) }}" class="im-del" onsubmit="return confirm('Bu mesajı silmek istediğine emin misin?')">
                        @csrf @method('DELETE')
                        <button type="submit">🗑 Sil</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="card" style="color:#64748b">Henüz cevaplanmış mesaj yok.</div>
        @endforelse
        </div>
    </div>
</div>
@endsection
