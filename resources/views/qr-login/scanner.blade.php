@extends('layout.app')
@section('title','QR Okut')
@section('content')
<div class="top"><h1>QR Okut</h1></div>
<div class="card">
    <p><b>Ogrenci:</b> {{ $student->user?->name ?? ('#'.$student->id) }}</p>
    <video id="cam" autoplay playsinline muted style="width:100%;max-width:520px;border:1px solid #dbeafe;border-radius:12px;background:#000;margin-bottom:10px"></video>
    <div style="margin-bottom:10px">
        <button class="btn" id="startCamBtn" type="button">Kamerayi Ac</button>
    </div>
    <p id="status" style="margin-top:10px"></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
(() => {
    const startCamBtn = document.getElementById('startCamBtn');
    const video = document.getElementById('cam');
    const statusEl = document.getElementById('status');
    let scanTimer = null;
    let stream = null;
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    async function verifyToken(token) {
        const res = await fetch('{{ route('qr.verify') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token, student_id: {{ (int) $student->id }} })
        });
        const data = await res.json().catch(() => ({}));
        statusEl.textContent = res.ok ? 'Onaylandi' : (data.message || 'Basarisiz');
        return res.ok;
    }

    startCamBtn?.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            video.srcObject = stream;
            statusEl.textContent = 'Kamera acildi, QR bekleniyor...';
            if (scanTimer) clearInterval(scanTimer);
            scanTimer = setInterval(async () => {
                try {
                    if (!video.videoWidth || !video.videoHeight) return;
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = window.jsQR ? window.jsQR(imageData.data, imageData.width, imageData.height) : null;
                    const val = (code?.data || '').trim();
                    if (!val) return;
                    const ok = await verifyToken(val);
                    if (ok && scanTimer) {
                        statusEl.textContent = 'Giris paneline yonlendiriliyorsunuz...';
                        clearInterval(scanTimer);
                        scanTimer = null;
                        if (stream) stream.getTracks().forEach((t) => t.stop());
                    }
                } catch (_) {}
            }, 450);
        } catch (e) {
            statusEl.textContent = 'Kamera acilamadi.';
        }
    });
})();
</script>
@endsection
