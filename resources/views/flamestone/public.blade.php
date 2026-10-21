@extends('layout.embed')
@section('title', 'Flamestone Puzzle | Bilişim Kod')
@section('content')
<?php
    $fsUrl = static fn (string $f): string => asset('js/flamestone/' . $f) . '?v=' . (@filemtime(public_path('js/flamestone/' . $f)) ?: 1);
?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;max-width:1180px;margin:0 auto 10px">
    <strong style="font-size:20px">🔥 Flamestone Puzzle</strong>
    <a href="{{ route('public.games') }}" style="font-weight:800;color:#1d4ed8;text-decoration:none">← Oyunlara dön</a>
</div>
<div style="max-width:1180px;margin:0 auto">
    <x-game-canvas :levels-url="route('public.flamestone.levels')" score-url="" />
</div>
<script>window.FLAMESTONE_PUBLIC = true;</script>
<script type="importmap">{!! json_encode(['imports' => [
    asset('js/flamestone/game.js') => $fsUrl('game.js'),
    asset('js/flamestone/editor.js') => $fsUrl('editor.js'),
]], JSON_UNESCAPED_SLASHES) !!}</script>
<script type="module" src="{{ $fsUrl('ui.js') }}"></script>
@endsection
