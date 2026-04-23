@props([
    'saveUrl' => route('flamestone.levels.store'),
])

<section class="card" id="flamestone-editor-root" data-save-url="{{ $saveUrl }}">
    <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
        <h2 style="margin:0;color:#0f172a;">Level Editor</h2>
        <input id="editor-level-name" class="form-control" placeholder="Level adi" style="max-width:280px;" />
    </div>

    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn" type="button" data-tool="wall">Duvar</button>
        <button class="btn" type="button" data-tool="start">Baslangic</button>
        <button class="btn" type="button" data-tool="goal">Hedef</button>
        <button class="btn" type="button" data-tool="trap">Tuzak</button>
        <button class="btn" type="button" data-tool="key">Anahtar</button>
        <button class="btn" type="button" data-tool="door">Kapi</button>
        <button class="btn" type="button" data-tool="block">Blok</button>
        <button class="btn" type="button" data-tool="erase">Silgi</button>
    </div>

    <div id="editor-grid" style="margin-top:12px;display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:3px;"></div>

    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
        <button id="editor-clear-btn" class="btn btn-secondary" type="button">Temizle</button>
        <button id="editor-save-btn" class="btn" type="button">Kaydet</button>
    </div>

    <pre id="editor-json-preview" style="margin-top:12px;background:#0f172a;color:#e2e8f0;padding:10px;border-radius:10px;max-height:260px;overflow:auto;"></pre>
</section>
