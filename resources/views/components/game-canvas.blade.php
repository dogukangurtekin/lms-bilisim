@props([
    'levelsUrl' => route('flamestone.levels.index'),
    'scoreUrl' => route('flamestone.scores.store'),
])

<section class="card" id="flamestone-game-root" data-levels-url="{{ $levelsUrl }}" data-score-url="{{ $scoreUrl }}">
    <div class="flame-header" style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
        <div>
            <h2 id="flame-level-title" style="margin:0;font-size:20px;color:#0f172a;">Flamestone</h2>
            <p id="flame-level-meta" style="margin:4px 0 0;color:#475569;font-size:13px;">Hazirlaniyor...</p>
            <p style="margin:8px 0 0;font-size:12px;line-height:1.4;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;max-width:420px;">
                Mantik: Oyuncuyu hedefe (G) ulastir. Duvarlardan gecemezsin, bloklari (B) itebilirsin, tuzaga (T) basarsan bolum sifirlanir.
                Bloklari once hedef noktalara (X) koy, sonra finish'e git. Anahtari (K) alip kapiyi (D) acman gereken bolumler vardir.
                En az adim ve kisa sure en iyi skorudur.
            </p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <select id="flame-difficulty-filter" class="form-control" style="min-width:120px;">
                <option value="all">Tum</option>
                <option value="easy">Kolay</option>
                <option value="medium">Orta</option>
                <option value="hard">Zor</option>
            </select>
            <select id="flame-level-select" class="form-control" style="min-width:200px;"></select>
            <button id="flame-fullscreen-btn" class="btn" type="button">Tam Ekran</button>
        </div>
    </div>

    <div style="margin-top:12px;display:flex;justify-content:center;">
        <canvas id="flame-canvas" width="720" height="720" style="max-width:min(92vw,760px);width:100%;border-radius:16px;border:1px solid #cbd5e1;background:#0b1220;"></canvas>
    </div>

    <div style="margin-top:10px;display:flex;justify-content:center;gap:8px;flex-wrap:wrap;">
        <button class="btn" id="flame-restart-btn" type="button">Restart</button>
        <button class="btn" id="flame-next-btn" type="button">Sonraki Level</button>
        <span id="flame-steps" class="chip" style="padding:8px 10px;background:#e2e8f0;border-radius:999px;font-weight:700;">Adim: 0</span>
        <span id="flame-time" class="chip" style="padding:8px 10px;background:#e2e8f0;border-radius:999px;font-weight:700;">Sure: 0 sn</span>
    </div>

    <div id="flame-mobile-controls" style="margin-top:12px;display:grid;gap:6px;justify-content:center;grid-template-columns:repeat(3,56px);">
        <span></span>
        <button type="button" class="btn" data-dir="up" style="padding:8px;">^</button>
        <span></span>
        <button type="button" class="btn" data-dir="left" style="padding:8px;">‹</button>
        <button type="button" class="btn" data-dir="down" style="padding:8px;">v</button>
        <button type="button" class="btn" data-dir="right" style="padding:8px;">›</button>
    </div>

    <div id="flame-toast" style="margin-top:10px;color:#1f2937;font-weight:600;min-height:22px;"></div>
    <div id="flame-level-previews" style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;"></div>
</section>
