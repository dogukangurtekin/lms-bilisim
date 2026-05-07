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

    function cameraErrorMessage(error) {
        const name = String(error?.name || '');
        if (!window.isSecureContext) {
            return 'Kamera icin guvenli baglanti gerekli (HTTPS).';
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return 'Bu tarayici kamera API desteklemiyor.';
        }
        if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
            return 'Kamera izni reddedildi. Tarayici ayarindan izin verin.';
        }
        if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
            return 'Kamera bulunamadi.';
        }
        if (name === 'NotReadableError' || name === 'TrackStartError') {
            return 'Kamera baska bir uygulama tarafindan kullaniliyor.';
        }
        if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError') {
            return 'Kamera ayarlari cihazla uyumlu degil.';
        }
        if (name === 'SecurityError') {
            return 'Guvenlik politikasi kamerayi engelliyor.';
        }
        return 'Kamera acilamadi. Lutfen izinleri ve baglantiyi kontrol edin.';
    }

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
            if (!window.isSecureContext) {
                statusEl.textContent = 'Kamera icin HTTPS gerekli.';
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusEl.textContent = 'Tarayici kamera API desteklemiyor.';
                return;
            }
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
                        statusEl.textContent = 'Onaylandi. Sinif listesine donuluyor...';
                        clearInterval(scanTimer);
                        scanTimer = null;
                        if (stream) stream.getTracks().forEach((t) => t.stop());
                        setTimeout(() => {
                            window.location.href = @json(route('qr.login.menu', ['class_id' => (int) $student->school_class_id]));
                        }, 500);
                    }
                } catch (_) {}
            }, 450);
        } catch (e) {
            statusEl.textContent = cameraErrorMessage(e);
        }
    });
})();
</script>
@endsection
