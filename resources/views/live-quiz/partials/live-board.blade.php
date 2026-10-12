{{--
    Canlı yarışma panosu (admin + öğrenci ortak).
    Kullanım: @include('live-quiz.partials.live-board', ['boardRows' => $rows, 'boardMeId' => auth()->id(), 'boardKey' => $session->id])
    Güncelleme: window.LqBoard.render(rows)  (ek istek atmaz; mevcut durum sorgusunun verisini kullanır)
--}}
<style>
.lq-board{margin-bottom:12px;border-radius:18px;padding:16px;background:radial-gradient(circle at 15% 0%,#7c3aed,#4c1d95 55%,#2e1065);color:#fff;border:1px solid rgba(255,255,255,.18);box-shadow:0 10px 30px rgba(46,16,101,.35)}
.lq-board-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
.lq-board-title{margin:0;font-size:22px;font-weight:950;letter-spacing:.3px;display:flex;align-items:center;gap:10px;color:#fff}
.lq-board-live{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:900;padding:4px 10px;border-radius:999px;background:rgba(239,68,68,.9)}
.lq-board-live::before{content:"";width:8px;height:8px;border-radius:50%;background:#fff;animation:lqBoardBlink 1.1s ease-in-out infinite}
.lq-board-count{font-size:13px;font-weight:800;opacity:.85}
.lq-board-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
.lq-board-card{position:relative;border-radius:14px;padding:10px 10px 12px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);min-height:92px;display:grid;align-content:space-between;gap:6px;overflow:hidden;will-change:transform}
.lq-board-card.is-me{border-color:#fde047;box-shadow:0 0 0 2px rgba(253,224,71,.55)}
.lq-board-rank{position:absolute;top:8px;right:8px;min-width:28px;height:28px;padding:0 6px;border-radius:999px;display:grid;place-items:center;font-size:13px;font-weight:950;background:rgba(0,0,0,.28)}
.lq-board-card.rank-1{background:linear-gradient(145deg,#fbbf24,#f59e0b 55%,#ea580c);border-color:#fde68a}
.lq-board-card.rank-2{background:linear-gradient(145deg,#e2e8f0,#94a3b8 60%,#64748b);border-color:#f1f5f9;color:#0f172a}
.lq-board-card.rank-3{background:linear-gradient(145deg,#fdba74,#d97706 60%,#92400e);border-color:#fed7aa}
.lq-board-card.rank-1 .lq-board-rank,.lq-board-card.rank-2 .lq-board-rank,.lq-board-card.rank-3 .lq-board-rank{background:rgba(255,255,255,.55);color:#111827;font-size:15px}
.lq-board-card.rank-1::after{content:"";position:absolute;inset:-60% -40%;background:linear-gradient(115deg,transparent 40%,rgba(255,255,255,.45) 50%,transparent 60%);animation:lqBoardShine 3.2s ease-in-out infinite;pointer-events:none}
.lq-board-name{font-size:15px;font-weight:900;line-height:1.15;padding-right:34px;overflow-wrap:anywhere;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.lq-board-xp{font-size:24px;font-weight:950;line-height:1}
.lq-board-xp small{font-size:12px;font-weight:800;opacity:.8;margin-left:3px}
.lq-board-meta{display:flex;gap:8px;font-size:12px;font-weight:800;opacity:.92}
.lq-board-gain{position:absolute;left:10px;bottom:8px;font-size:15px;font-weight:950;color:#bbf7d0;text-shadow:0 2px 6px rgba(0,0,0,.45);animation:lqBoardGain 1.6s ease-out forwards;pointer-events:none}
.lq-board-card.rank-2 .lq-board-gain{color:#166534;text-shadow:none}
.lq-board-card.moved-up{animation:lqBoardUp 1.1s ease-out}
.lq-board-card.is-new{animation:lqBoardNew .6s cubic-bezier(.2,.8,.2,1) both}
.lq-board-card.bump .lq-board-xp{animation:lqBoardBump .6s ease-out}
.lq-board-empty{grid-column:1/-1;text-align:center;padding:18px;font-weight:800;opacity:.85}
@keyframes lqBoardBlink{50%{opacity:.25}}
@keyframes lqBoardShine{0%,60%{transform:translateX(-60%)}100%{transform:translateX(60%)}}
@keyframes lqBoardGain{0%{opacity:0;transform:translateY(10px) scale(.8)}20%{opacity:1;transform:translateY(0) scale(1.15)}100%{opacity:0;transform:translateY(-26px) scale(1)}}
@keyframes lqBoardUp{0%{box-shadow:0 0 0 0 rgba(74,222,128,.9)}100%{box-shadow:0 0 0 18px rgba(74,222,128,0)}}
@keyframes lqBoardNew{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:scale(1)}}
@keyframes lqBoardBump{0%{transform:scale(1)}35%{transform:scale(1.35)}100%{transform:scale(1)}}
@media (max-width:1280px){.lq-board-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media (max-width:900px){.lq-board-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:560px){.lq-board-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lq-board-title{font-size:18px}}
@media (prefers-reduced-motion:reduce){.lq-board *,.lq-board-card{animation:none!important}}
</style>

<section class="lq-board" id="lqBoard" aria-label="Canlı sıralama">
    <div class="lq-board-head">
        <h2 class="lq-board-title">🏆 Canlı Sıralama <span class="lq-board-live">CANLI</span></h2>
        <span class="lq-board-count" id="lqBoardCount"></span>
    </div>
    <div class="lq-board-grid" id="lqBoardGrid"></div>
</section>

@push('scripts')
<script>
window.LqBoard = (() => {
    const grid = document.getElementById('lqBoardGrid');
    const countEl = document.getElementById('lqBoardCount');
    const meId = Number(@json($boardMeId ?? 0));
    const storeKey = 'lqBoard:' + @json((string) ($boardKey ?? 'x'));
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cards = new Map();     // user_id -> element
    const state = new Map();     // user_id -> { rank, xp }
    let firstPaint = true;

    function build() {
        const el = document.createElement('div');
        el.className = 'lq-board-card is-new';
        el.innerHTML = '<span class="lq-board-rank"></span><div class="lq-board-name"></div>' +
            '<div class="lq-board-xp"><span data-xp>0</span><small>XP</small></div>' +
            '<div class="lq-board-meta"><span data-ok></span><span data-bad></span></div>';
        el.addEventListener('animationend', (e) => {
            if (e.target === el) el.classList.remove('is-new', 'moved-up');
        });
        return el;
    }

    function countUp(node, from, to) {
        if (reduceMotion || from === to) { node.textContent = String(to); return; }
        const start = performance.now();
        const dur = 700;
        const step = (now) => {
            const t = Math.min(1, (now - start) / dur);
            node.textContent = String(Math.round(from + (to - from) * (1 - Math.pow(1 - t, 3))));
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    function paint(rows, animate) {
        // FLIP: önce eski konumları kaydet
        const first = new Map();
        cards.forEach((el, id) => first.set(id, el.getBoundingClientRect()));

        const seen = new Set();
        rows.forEach((row) => {
            const id = Number(row.user_id);
            seen.add(id);
            let el = cards.get(id);
            const prev = state.get(id);
            if (!el) {
                el = build();
                cards.set(id, el);
                if (!animate) el.classList.remove('is-new');
            }
            el.className = el.className.replace(/\brank-\d+\b/g, '').trim();
            if (row.rank <= 3) el.classList.add('rank-' + row.rank);
            el.classList.toggle('is-me', meId > 0 && id === meId);
            el.querySelector('.lq-board-rank').textContent = row.rank <= 3 ? ['🥇', '🥈', '🥉'][row.rank - 1] : '#' + row.rank;
            el.querySelector('.lq-board-name').textContent = (row.student_name || 'Öğrenci') + (meId > 0 && id === meId ? ' (Sen)' : '');
            el.querySelector('[data-ok]').textContent = '✔ ' + row.correct;
            el.querySelector('[data-bad]').textContent = '✖ ' + row.wrong;

            const xpNode = el.querySelector('[data-xp]');
            const oldXp = prev ? prev.xp : 0;
            if (animate && prev && row.xp > oldXp) {
                countUp(xpNode, oldXp, row.xp);
                el.classList.remove('bump'); void el.offsetWidth; el.classList.add('bump');
                const gain = document.createElement('span');
                gain.className = 'lq-board-gain';
                gain.textContent = '+' + (row.xp - oldXp);
                el.appendChild(gain);
                setTimeout(() => gain.remove(), 1700);
            } else {
                xpNode.textContent = String(row.xp);
            }
            if (animate && prev && row.rank < prev.rank) {
                el.classList.remove('moved-up'); void el.offsetWidth; el.classList.add('moved-up');
            }
            state.set(id, { rank: row.rank, xp: row.xp });
            grid.appendChild(el); // DOM sırası = sıralama
        });

        cards.forEach((el, id) => {
            if (!seen.has(id)) { el.remove(); cards.delete(id); state.delete(id); }
        });

        const empty = grid.querySelector('.lq-board-empty');
        if (!rows.length) {
            if (!empty) {
                const e = document.createElement('div');
                e.className = 'lq-board-empty';
                e.textContent = 'Henüz katılan öğrenci yok.';
                grid.appendChild(e);
            }
        } else if (empty) {
            empty.remove();
        }
        countEl.textContent = rows.length ? rows.length + ' öğrenci' : '';

        // FLIP: yeni konumlara kayma animasyonu
        if (animate && !reduceMotion) {
            cards.forEach((el, id) => {
                const a = first.get(id);
                if (!a) return;
                const b = el.getBoundingClientRect();
                const dx = a.left - b.left, dy = a.top - b.top;
                if (!dx && !dy) return;
                el.animate(
                    [{ transform: 'translate(' + dx + 'px,' + dy + 'px)', zIndex: 5 }, { transform: 'translate(0,0)', zIndex: 5 }],
                    { duration: 800, easing: 'cubic-bezier(.2,.8,.2,1)' }
                );
            });
        }
    }

    function render(rows) {
        if (!Array.isArray(rows)) return;
        const normalized = rows.map((r, i) => Object.assign({ rank: i + 1, xp: 0, correct: 0, wrong: 0 }, r));
        if (firstPaint) {
            firstPaint = false;
            // Sayfa yenilenince (yeni soru/aşama) animasyon kaybolmasın: önceki sırayı çiz, sonra güncele kaydır.
            let before = null;
            try { before = JSON.parse(sessionStorage.getItem(storeKey) || 'null'); } catch (e) { before = null; }
            if (Array.isArray(before) && before.length && JSON.stringify(before) !== JSON.stringify(normalized)) {
                paint(before, false);
                setTimeout(() => paint(normalized, true), 450);
            } else {
                paint(normalized, false);
            }
        } else {
            paint(normalized, true);
        }
        try { sessionStorage.setItem(storeKey, JSON.stringify(normalized)); } catch (e) { /* kullanılamıyor */ }
    }

    render(@json($boardRows ?? []));
    return { render };
})();
</script>
@endpush
