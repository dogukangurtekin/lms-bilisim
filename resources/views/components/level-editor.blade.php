@props([
    'saveUrl' => route('flamestone.levels.store'),
])

@once
    <link rel="stylesheet" href="{{ asset('css/flamestone.css') }}?v={{ @filemtime(public_path('css/flamestone.css')) }}">
@endonce

<section class="fs fs-card" id="flamestone-editor-root" data-save-url="{{ $saveUrl }}">
    <div class="fs-ed-top">
        <h2>🛠 Level Editör</h2>
        <input id="editor-level-name" class="fs-input" placeholder="Level adı" />
    </div>

    <div class="fs-tools">
        <button class="fs-btn" type="button" data-tool="wall">Duvar</button>
        <button class="fs-btn" type="button" data-tool="start">Başlangıç</button>
        <button class="fs-btn" type="button" data-tool="goal">Hedef</button>
        <button class="fs-btn" type="button" data-tool="trap">Tuzak</button>
        <button class="fs-btn" type="button" data-tool="key">Anahtar</button>
        <button class="fs-btn" type="button" data-tool="door">Kapı</button>
        <button class="fs-btn" type="button" data-tool="block">Blok</button>
        <button class="fs-btn" type="button" data-tool="erase">Silgi</button>
    </div>

    <div id="editor-grid" class="fs-ed-grid"></div>

    <div class="fs-ed-actions">
        <button id="editor-clear-btn" class="fs-btn fs-btn-ghost" type="button">Temizle</button>
        <button id="editor-save-btn" class="fs-btn fs-btn-go" type="button">Kaydet</button>
    </div>

    <pre id="editor-json-preview" class="fs-json"></pre>
</section>
