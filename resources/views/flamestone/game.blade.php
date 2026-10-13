@extends(request()->header('Sec-Fetch-Dest') === 'iframe' ? 'layout.embed' : 'layout.app')
@section('title', 'Flamestone Oyun')
@section('content')
<?php
    $fsEmbedded = request()->header('Sec-Fetch-Dest') === 'iframe';
    $fsUrl = static fn (string $f): string => asset('js/flamestone/' . $f) . '?v=' . (@filemtime(public_path('js/flamestone/' . $f)) ?: 1);
?>
@if(! $fsEmbedded)
<div class="top"><h1>Flamestone Oyun</h1></div>
@endif
<x-game-canvas />
{{-- ui.js içindeki import'ların da önbelleğe takılmaması için sürümlü adresler --}}
<script type="importmap">{!! json_encode(['imports' => [
    asset('js/flamestone/game.js') => $fsUrl('game.js'),
    asset('js/flamestone/editor.js') => $fsUrl('editor.js'),
]], JSON_UNESCAPED_SLASHES) !!}</script>
<script type="module" src="{{ $fsUrl('ui.js') }}"></script>
@endsection
