@extends('layouts.public', ['title' => 'Sayfa bulunamadı', 'description' => 'Aradığınız sayfa bulunamadı.', 'robots' => 'noindex, nofollow'])

@section('content')
<div style="text-align:center;padding:40px 0">
    <p style="font-size:64px;font-weight:700;margin:0;color:var(--violet)">404</p>
    <h1>Aradığınız sayfa bulunamadı</h1>
    <p class="meta">Bağlantı hatalı olabilir ya da sayfa taşınmış olabilir.</p>
    <p><a href="/" class="btn">Ana sayfaya dön</a></p>
</div>
@endsection
