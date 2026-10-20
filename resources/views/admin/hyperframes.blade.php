@extends('layout.app')
@section('title','HyperFrames')
@section('content')
<div class="card" style="padding:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px">
        <div>
            <h2 style="margin:0">🎬 HyperFrames Studio</h2>
            <p style="margin:2px 0 0;color:#64748b;font-size:13px">HTML, CSS ve animasyonlardan MP4 video üretimi. Yalnızca yöneticilere görünür; çalışma hyperframes.dev üzerinde yapılır.</p>
        </div>
        <a class="btn" href="https://www.hyperframes.dev/studio" target="_blank" rel="noopener noreferrer">Yeni sekmede aç ↗</a>
    </div>
    <iframe
        src="https://www.hyperframes.dev/studio"
        title="HyperFrames Studio"
        allow="clipboard-read; clipboard-write; fullscreen"
        sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-downloads allow-modals"
        referrerpolicy="no-referrer"
        style="width:100%;height:calc(100vh - 190px);min-height:620px;border:1px solid #d1d5db;border-radius:12px;display:block"
    ></iframe>
</div>
@endsection
