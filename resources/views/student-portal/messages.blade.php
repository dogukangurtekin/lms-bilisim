@extends('layout.app')
@section('title','Öğretmene Mesaj')
@section('content')
<div class="top"><h1>💬 Öğretmene Mesaj</h1></div>

<style>
    .msg-wrap{display:grid;gap:16px;max-width:820px}
    .msg-status{display:flex;gap:12px;align-items:center;padding:14px 16px;border-radius:16px;font-weight:700}
    .msg-status.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
    .msg-status.wait{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
    .msg-status .emo{font-size:28px}
    .msg-form{display:grid;gap:12px}
    .msg-choices{display:flex;gap:10px;flex-wrap:wrap}
    .msg-choice{flex:1;min-width:200px;border:2px solid #dbe5f2;border-radius:14px;padding:12px 14px;cursor:pointer;background:#fff;display:flex;gap:10px;align-items:center;font-weight:700}
    .msg-choice:has(input:checked){border-color:#5b3df5;background:#eeebfd}
    .msg-choice.disabled{opacity:.5;cursor:not-allowed}
    .msg-choice small{display:block;font-weight:500;color:#64748b}
    .msg-text{width:100%;min-height:120px;border:1px solid #dbe5f2;border-radius:12px;padding:12px;font:inherit;resize:vertical}
    .msg-count{text-align:right;font-size:12px;color:#64748b}
    .msg-bubble{border-radius:14px;padding:12px 14px;line-height:1.5;white-space:pre-wrap;word-break:break-word}
    .msg-bubble.me{background:#eeebfd;margin-left:36px}
    .msg-bubble.them{background:#ecfdf5;border:1px solid #a7f3d0;margin-right:36px}
    .msg-bubble.none{background:#f8fafc;border:1px dashed #cbd5e1;color:#64748b;margin-right:36px}
    .msg-meta{font-size:12px;color:#64748b;margin:0 0 4px}
    .msg-item{display:grid;gap:8px;padding:14px 0;border-top:1px solid #e2e8f0}
    .msg-item:first-child{border-top:0}
</style>

<div class="msg-wrap">
    @if($canSend)
        <div class="msg-status ok"><span class="emo">✅</span><span>Mesaj hakkın var! Tek bir mesaj yazabilirsin. Cevap gelince yeni bir mesaj hakkın olacak.</span></div>

        <div class="card">
            <form method="POST" action="{{ route('student.messages.send') }}" class="msg-form" id="msgForm">
                @csrf
                <strong>Kime göndermek istiyorsun?</strong>
                <div class="msg-choices">
                    <label class="msg-choice {{ $hasTeacher ? '' : 'disabled' }}">
                        <input type="radio" name="recipient_type" value="teacher" @checked(old('recipient_type', $hasTeacher ? 'teacher' : 'admin') === 'teacher') @disabled(! $hasTeacher)>
                        <span>👩‍🏫 Öğretmenim<small>{{ $hasTeacher ? $teacherName : 'Sana atanmış öğretmen yok' }}</small></span>
                    </label>
                    <label class="msg-choice">
                        <input type="radio" name="recipient_type" value="admin" @checked(old('recipient_type', $hasTeacher ? 'teacher' : 'admin') === 'admin')>
                        <span>🛡️ Yönetici<small>Okul yöneticisine yaz</small></span>
                    </label>
                </div>
                <textarea class="msg-text" name="body" id="msgBody" maxlength="500" placeholder="Mesajını buraya yaz… (en fazla 500 karakter)" required>{{ old('body') }}</textarea>
                <div class="msg-count"><span id="msgCount">0</span>/500</div>
                <div><button type="submit" class="btn" id="msgSubmit">📨 Mesajı Gönder</button></div>
            </form>
        </div>
    @else
        <div class="msg-status wait"><span class="emo">⏳</span><span>Mesajın gönderildi. Öğretmenin ya da yöneticinin cevabını bekliyorsun; cevap gelince yeni mesaj yazabilirsin.</span></div>
    @endif

    <div class="card">
        <h3 style="margin:0 0 6px">Mesajlarım</h3>
        @forelse($messages as $m)
            <div class="msg-item">
                <div>
                    <p class="msg-meta">Sen → {{ $m->recipient_type === 'admin' ? 'Yönetici' : ($m->recipient?->name ?? 'Öğretmen') }} · {{ $m->created_at->format('d.m.Y H:i') }}</p>
                    <div class="msg-bubble me">{{ $m->body }}</div>
                </div>
                <div>
                    @if($m->replied_at)
                        <p class="msg-meta">{{ $m->replier?->name ?? 'Cevap' }} · {{ $m->replied_at->format('d.m.Y H:i') }}</p>
                        <div class="msg-bubble them">{{ $m->reply_body }}</div>
                    @else
                        <div class="msg-bubble none">Cevap bekleniyor…</div>
                    @endif
                </div>
            </div>
        @empty
            <p style="margin:8px 0 0;color:#64748b">Henüz mesaj yazmadın.</p>
        @endforelse
    </div>
</div>

<script>
(function () {
    var body = document.getElementById('msgBody'), count = document.getElementById('msgCount');
    var form = document.getElementById('msgForm'), btn = document.getElementById('msgSubmit');
    if (body && count) { var u = function () { count.textContent = body.value.length; }; body.addEventListener('input', u); u(); }
    // Çift tıklamayla iki kez gönderilmesin.
    if (form && btn) form.addEventListener('submit', function () { btn.disabled = true; });
})();
</script>
@endsection
