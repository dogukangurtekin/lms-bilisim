@extends('layout.app')
@section('title','QR Ogrenci Giris')
@section('content')
<div class="top"><h1>QR ile Ogrenci Giris</h1></div>
<div class="card">
    <p>Asagidaki kodu ogretmene okutun:</p>
    <h3 id="token" style="letter-spacing:1px">Hazirlaniyor...</h3>
    <p id="status">Bekleniyor...</p>
</div>
<script>
(() => {
    const tokenEl = document.getElementById('token');
    const statusEl = document.getElementById('status');
    let token = '';
    async function start() {
        const res = await fetch('{{ route('qr.guest.generate') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
        });
        const data = await res.json();
        token = data.token || '';
        tokenEl.textContent = token || 'Token yok';
        if (!token) return;
        setInterval(async () => {
            const r = await fetch('{{ url('/qr/guest/status') }}/' + encodeURIComponent(token));
            const s = await r.json().catch(() => ({}));
            if (s.approved && s.redirect) window.location.href = s.redirect;
        }, 1200);
    }
    start().catch(() => statusEl.textContent = 'QR baslatilamadi');
})();
</script>
@endsection

