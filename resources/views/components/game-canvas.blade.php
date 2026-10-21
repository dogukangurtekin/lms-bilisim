@props([
    'levelsUrl' => route('flamestone.levels.index'),
    'scoreUrl' => route('flamestone.scores.store'),
])

@once
    <link rel="stylesheet" href="{{ asset('css/flamestone.css') }}?v={{ @filemtime(public_path('css/flamestone.css')) }}">
@endonce

<section class="fs fs-card" id="flamestone-game-root" data-levels-url="{{ $levelsUrl }}" data-score-url="{{ $scoreUrl }}">
    <div class="fs-top">
        <div class="fs-title-block">
            <div class="fs-badge">🔥</div>
            <div>
                <h2 id="flame-level-title" class="fs-title">Flamestone</h2>
                <p id="flame-level-meta" class="fs-meta">Hazırlanıyor...</p>
            </div>
        </div>
        <div class="fs-stats">
            <span id="flame-steps" class="fs-chip">Adım: 0</span>
            <span id="flame-time" class="fs-chip">Süre: 0 sn</span>
        </div>
        <div class="fs-filters">
            <div class="fs-level-nav" role="group" aria-label="Seviye seçimi">
                <button id="flame-level-prev" class="fs-btn fs-btn-ghost fs-icon" type="button" aria-label="Önceki seviye" title="Önceki seviye">‹</button>
                <button id="flame-level-picker" class="fs-level-picker" type="button" aria-haspopup="dialog" aria-label="Seviye listesini aç">
                    <span id="flame-picker-name">Seviye 1</span>
                    <small id="flame-picker-meta">Kolay</small>
                </button>
                <button id="flame-level-next" class="fs-btn fs-btn-ghost fs-icon" type="button" aria-label="Sonraki seviye" title="Sonraki seviye">›</button>
            </div>
            <button id="flame-fullscreen-btn" class="fs-btn fs-btn-ghost" type="button">Tam Ekran</button>
        </div>
    </div>

    <div class="fs-body">
        <div class="fs-stage">
            <canvas id="flame-canvas" class="fs-canvas" width="720" height="720"></canvas>
        </div>

        <aside class="fs-side">
            <div class="fs-actions">
                <button class="fs-btn fs-btn-ghost" id="flame-restart-btn" type="button">Yeniden Başlat</button>
                <button class="fs-btn fs-btn-go" id="flame-next-btn" type="button">Sonraki Level</button>
            </div>

            <div id="flame-toast" class="fs-toast"></div>

            <div id="flame-mobile-controls" class="fs-dpad">
                <span></span>
                <button type="button" class="fs-btn" data-dir="up" aria-label="Yukarı">▲</button>
                <span></span>
                <button type="button" class="fs-btn" data-dir="left" aria-label="Sol">◀</button>
                <button type="button" class="fs-btn" data-dir="down" aria-label="Aşağı">▼</button>
                <button type="button" class="fs-btn" data-dir="right" aria-label="Sağ">▶</button>
            </div>

            <div class="fs-panel fs-help">
                <h3>Nasıl oynanır?</h3>
                <ul class="fs-legend">
                    <li><span class="fs-sw player"></span><span>Oyuncuyu hedefe (G) ulaştır.</span></li>
                    <li><span class="fs-sw crate"></span><span>Blokları (B) itebilirsin, duvardan geçemezsin.</span></li>
                    <li><span class="fs-sw slot"></span><span>Blokları önce hedef noktalara (X) koy.</span></li>
                    <li><span class="fs-sw key">🗝</span><span>Anahtarı (K) alıp kapıyı (D) aç.</span></li>
                    <li><span class="fs-sw trap">✖</span><span>Tuzağa (T) basarsan bölüm sıfırlanır.</span></li>
                    <li><span class="fs-sw goal">🏁</span><span>Bitiş noktası (G).</span></li>
                </ul>
                <p class="fs-tip">En az adım ve kısa süre en iyi skordur. Yön tuşları veya WASD ile oyna.</p>
            </div>
        </aside>
    </div>

    <div id="flame-picker-modal" class="fs-modal" hidden role="dialog" aria-modal="true" aria-labelledby="flame-picker-title">
        <div class="fs-modal-backdrop" data-close></div>
        <div class="fs-modal-card">
            <div class="fs-modal-head">
                <h3 id="flame-picker-title">Seviyeler</h3>
                <button class="fs-btn fs-btn-ghost fs-icon" type="button" data-close aria-label="Kapat">✕</button>
            </div>
            <div id="flame-picker-body" class="fs-picker-body"></div>
        </div>
    </div>
</section>
