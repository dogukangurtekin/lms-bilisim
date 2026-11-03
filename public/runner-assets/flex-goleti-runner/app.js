/* Flex Göleti: arayüz ve Flexbox motoru. Seviyeler: levels.js. Özgün tasarım (Türkçe Flexbox dili + gerçek CSS karşılığı). */
(function () {
    'use strict';

    const ALL = Array.isArray(window.FLEX_GOLETI_LEVELS) ? window.FLEX_GOLETI_LEVELS : [];
    const $ = (id) => document.getElementById(id);
    const STORE_KEY = 'flex_goleti_v1_' + (window.RUNNER_USER ? 'u' + window.RUNNER_USER : 'guest');
    const CAT_LABEL = { kolay: 'Kolay', orta: 'Orta', 'orta-zor': 'Orta-Zor', zor: 'Zor', uzman: 'Uzman' };
    const SLUG = 'flex-goleti-runner';

    // ------------------------------------------------------------------ ödev / rol / yarışma parametreleri (diğer runner'larla aynı sözleşme)
    const params = new URLSearchParams(window.location.search);
    const roleParam = (params.get('role') || '').toLowerCase();
    const runnerRole = (roleParam === 'teacher' || roleParam === 'admin') ? roleParam : 'student';
    const isStaff = runnerRole === 'teacher' || runnerRole === 'admin';
    const assignmentId = String(params.get('assignmentId') || '').trim();
    const assignmentTitle = String(params.get('assignmentTitle') || 'Flex Göleti Ödevi').trim();
    const isCompetition = /^competition-\d+$/.test(assignmentId);
    let rangeFrom = Math.max(1, Number(params.get('levelStart') || params.get('from') || 0));
    let rangeTo = Math.max(rangeFrom, Number(params.get('levelEnd') || params.get('to') || rangeFrom));
    const hasRange = !window.RUNNER_PUBLIC && Number.isFinite(rangeFrom) && rangeFrom > 0;
    const enforceGrant = params.get('grant') === '1' || params.get('enforceGrant') === '1';
    const needsGrantCheck = runnerRole === 'student' && (!!assignmentId || hasRange || enforceGrant);
    let grantDenied = false;
    let levelStart = hasRange ? rangeFrom : 1;
    let levelEndParam = hasRange ? rangeTo : levelStart;
    let isAssignmentMode = !!assignmentId || hasRange;
    if (!isStaff && !isAssignmentMode) { levelStart = 1; levelEndParam = window.RUNNER_PUBLIC ? (window.RUNNER_PUBLIC_LIMIT || 9999) : 6; }
    if (isStaff) { isAssignmentMode = false; levelStart = 1; levelEndParam = 9999; }
    const sessionStartedAt = Date.now();
    let rangeCompleteFired = false;

    // ------------------------------------------------------------------ Türkçe Flexbox sözlüğü
    const fold = (s) => String(s).toLocaleLowerCase('tr').replace(/ç/g, 'c').replace(/ğ/g, 'g').replace(/ı/g, 'i').replace(/ö/g, 'o').replace(/ş/g, 's').replace(/ü/g, 'u').trim();
    const ALIGN = [['baş', 'flex-start'], ['son', 'flex-end'], ['orta', 'center']];
    const SPACE = [['aralıklı', 'space-between'], ['çevre-aralıklı', 'space-around'], ['eşit-aralıklı', 'space-evenly']];
    const DIR = [['yatay', 'row'], ['yatay-ters', 'row-reverse'], ['dikey', 'column'], ['dikey-ters', 'column-reverse']];
    const WRAP = [['yok', 'nowrap'], ['var', 'wrap'], ['ters', 'wrap-reverse']];
    const PROPS = [
        { tr: 'ana-hiza', css: 'justify-content', kind: 'enum', values: [...ALIGN, ...SPACE] },
        { tr: 'çapraz-hiza', css: 'align-items', kind: 'enum', values: [...ALIGN, ['uzat', 'stretch']] },
        { tr: 'yön', css: 'flex-direction', kind: 'enum', values: DIR },
        { tr: 'sarma', css: 'flex-wrap', kind: 'enum', values: WRAP },
        { tr: 'yön-sarma', css: 'flex-flow', kind: 'flow' },
        { tr: 'satır-hiza', css: 'align-content', kind: 'enum', values: [...ALIGN, ['uzat', 'stretch'], ...SPACE] },
        { tr: 'kendi-hiza', css: 'align-self', kind: 'enum', values: [...ALIGN, ['uzat', 'stretch']] },
        { tr: 'sıra', css: 'order', kind: 'int', min: -9, max: 9 },
        { tr: 'büyüme', css: 'flex-grow', kind: 'int', min: 0, max: 9 },
        { tr: 'boşluk', css: 'gap', kind: 'px', min: 0, max: 60 },
    ];
    const PROP_BY_NAME = {};
    PROPS.forEach((p) => { PROP_BY_NAME[fold(p.tr)] = p; PROP_BY_NAME[p.css] = p; });
    const MANAGED = PROPS.map((p) => p.css);
    const NAMES = Object.keys(PROP_BY_NAME);

    function lev(a, b) {
        const d = Array.from({ length: a.length + 1 }, (_, i) => [i]);
        for (let j = 1; j <= b.length; j += 1) d[0][j] = j;
        for (let i = 1; i <= a.length; i += 1) for (let j = 1; j <= b.length; j += 1) d[i][j] = Math.min(d[i - 1][j] + 1, d[i][j - 1] + 1, d[i - 1][j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
        return d[a.length][b.length];
    }
    function enumLookup(p, token) {
        const f = fold(token);
        return p.values.find(([tr, css]) => fold(tr) === f || css === f) || null;
    }

    /** "ana-hiza: son; sıra: -1" → { decls: [{p, tr, css}], errors: [] } */
    function parseCode(text) {
        const out = { decls: [], errors: [] };
        const clean = String(text || '').replace(/\/\*[\s\S]*?\*\//g, '');
        clean.split('\n').forEach((line, li) => {
            line.split(';').forEach((seg) => {
                const s = seg.trim();
                if (!s || s === '{' || s === '}') return;
                const ci = s.indexOf(':');
                const at = (line.split(';').length > 1 || clean.split('\n').length > 1) ? `${li + 1}. satır: ` : '';
                if (ci < 0) { out.errors.push(`${at}"${s}" için iki nokta (:) eksik. Örnek: ana-hiza: orta;`); return; }
                const name = s.slice(0, ci).trim();
                const val = s.slice(ci + 1).trim();
                const p = PROP_BY_NAME[fold(name)];
                if (!p) {
                    const near = NAMES.map((n) => [n, lev(fold(name), n)]).sort((a, b) => a[1] - b[1])[0];
                    const sug = near && near[1] <= 3 ? ` Bunu mu demek istedin: "${PROP_BY_NAME[near[0]].tr}"?` : ` Kullanabileceklerin: ${PROPS.map((x) => x.tr).join(', ')}.`;
                    out.errors.push(`${at}"${name}" diye bir kural yok.${sug}`);
                    return;
                }
                if (!val) { out.errors.push(`${at}"${p.tr}" için bir değer yazmalısın.`); return; }
                if (p.kind === 'enum') {
                    const hit = enumLookup(p, val);
                    if (!hit) { out.errors.push(`${at}"${val}" değeri "${p.tr}" için geçerli değil. Kullanabileceklerin: ${p.values.map((v) => v[0]).join(', ')}.`); return; }
                    out.decls.push({ p, tr: hit[0], css: hit[1] });
                } else if (p.kind === 'int') {
                    if (!/^[-+]?\d+$/.test(val) || Number(val) < p.min || Number(val) > p.max) { out.errors.push(`${at}"${p.tr}" için ${p.min} ile ${p.max} arasında bir sayı yaz. Örnek: ${p.tr}: 1;`); return; }
                    out.decls.push({ p, tr: String(Number(val)), css: String(Number(val)) });
                } else if (p.kind === 'px') {
                    const m = /^(\d{1,2})(px)?$/.exec(val);
                    if (!m || Number(m[1]) < p.min || Number(m[1]) > p.max) { out.errors.push(`${at}"${p.tr}" için ${p.min}-${p.max} arası bir değer yaz. Örnek: ${p.tr}: 20px;`); return; }
                    out.decls.push({ p, tr: `${m[1]}px`, css: `${m[1]}px` });
                } else if (p.kind === 'flow') {
                    const toks = val.split(/\s+/);
                    let dir = null; let wrap = null; let bad = null;
                    toks.forEach((t) => {
                        const d = DIR.find(([tr, css]) => fold(tr) === fold(t) || css === fold(t));
                        const w = WRAP.find(([tr, css]) => fold(tr) === fold(t) || css === fold(t));
                        if (d && !dir) dir = d; else if (w && !wrap) wrap = w; else bad = bad || t;
                    });
                    if (bad || (!dir && !wrap)) { out.errors.push(`${at}"${val}" geçerli değil. "yön-sarma" için yön (${DIR.map((x) => x[0]).join(', ')}) ve sarma (${WRAP.map((x) => x[0]).join(', ')}) yaz. Örnek: yön-sarma: dikey var;`); return; }
                    out.decls.push({ p, tr: [dir && dir[0], wrap && wrap[0]].filter(Boolean).join(' '), css: [dir && dir[1], wrap && wrap[1]].filter(Boolean).join(' ') });
                }
            });
        });
        return out;
    }
    const declText = (decls) => decls.map((d) => `${d.p.tr}: ${d.tr};`).join('\n');
    const declCss = (decls) => decls.map((d) => `${d.p.css}: ${d.css};`).join('\n  ');
    function objToDecls(obj) {
        const r = parseCode(Object.entries(obj || {}).map(([k, v]) => `${k}: ${v};`).join('\n'));
        if (r.errors.length) console.error('Flex Göleti seviye verisi hatalı:', r.errors, obj);
        return r.decls;
    }

    // ------------------------------------------------------------------ sahne
    const frogsEl = $('fgFrogs');
    const padsEl = $('fgPads');
    const FROG_SVG = '<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" aria-hidden="true">' +
        '<ellipse cx="30" cy="38" rx="21" ry="16" fill="var(--c)" stroke="var(--d)" stroke-width="2.5"/>' +
        '<ellipse cx="30" cy="44" rx="13" ry="8" fill="rgba(255,255,255,.35)"/>' +
        '<circle cx="19" cy="22" r="8.5" fill="var(--c)" stroke="var(--d)" stroke-width="2.5"/><circle cx="41" cy="22" r="8.5" fill="var(--c)" stroke="var(--d)" stroke-width="2.5"/>' +
        '<circle cx="19" cy="21.5" r="5" fill="#fff"/><circle cx="41" cy="21.5" r="5" fill="#fff"/><circle cx="20.5" cy="22" r="2.6" fill="#0f172a"/><circle cx="42.5" cy="22" r="2.6" fill="#0f172a"/>' +
        '<path d="M21 38 Q30 46 39 38" fill="none" stroke="var(--d)" stroke-width="2.5" stroke-linecap="round"/></svg>';
    const PAD_SVG = '<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" aria-hidden="true">' +
        '<ellipse cx="30" cy="31" rx="28" ry="26" fill="#1f8f4e" stroke="var(--c)" stroke-width="4"/>' +
        '<path d="M30 31 L52 14" stroke="#0b3d3a" stroke-width="3.5" stroke-linecap="round"/><ellipse cx="24" cy="22" rx="10" ry="5" fill="rgba(255,255,255,.18)"/></svg>';

    let level = null; let idx = 0; let levels = [];
    let ctx = null;           // { base: {sel: decls}, sol: {sel: decls} }
    let runs = 0; let hintStage = 0; let solutionShown = false; let solved = false;
    const drafts = {};
    let store = { done: {}, last: 0, introSeen: false };

    function loadStore() { try { const p = JSON.parse(localStorage.getItem(STORE_KEY) || 'null'); if (p && typeof p === 'object') store = Object.assign(store, p); } catch (e) { /* yok say */ } }
    function saveStore() { if (isCompetition) return; try { localStorage.setItem(STORE_KEY, JSON.stringify(store)); } catch (e) { /* yok say */ } }
    const isDone = (l) => !!store.done[l.id];

    function elsFor(container, sel) {
        if (sel === 'gol') return [container];
        const m = /^kurbaga-(\d+)$/.exec(sel);
        if (m) return container.children[Number(m[1]) - 1] ? [container.children[Number(m[1]) - 1]] : [];
        return Array.from(container.children).filter((c) => c.classList.contains(sel));
    }
    function applySpec(container, specs) {
        [container, ...container.children].forEach((e) => MANAGED.forEach((c) => e.style.removeProperty(c)));
        specs.forEach((spec) => {
            Object.entries(spec).forEach(([sel, decls]) => elsFor(container, sel).forEach((e) => decls.forEach((d) => e.style.setProperty(d.p.css, d.css))));
        });
    }
    function buildScene(lv) {
        frogsEl.innerHTML = '';
        padsEl.innerHTML = '';
        lv.frogs.forEach((color, i) => {
            const size = (lv.sizes && lv.sizes[i]) || lv.size;
            ['kurbaga', 'nilufer'].forEach((kind) => {
                const el = document.createElement('div');
                el.className = `${kind} ${color}`;
                el.style.width = `${size[0]}px`;
                el.style.height = `${size[1]}px`;
                el.innerHTML = kind === 'kurbaga' ? FROG_SVG : PAD_SVG;
                (kind === 'kurbaga' ? frogsEl : padsEl).appendChild(el);
            });
        });
    }
    const snap = (container) => Array.from(container.children).map((e) => ({ l: e.offsetLeft, t: e.offsetTop, w: e.offsetWidth, h: e.offsetHeight }));
    function onPads(frogRects, padRects) {
        return frogRects.filter((f, i) => Math.abs(f.l - padRects[i].l) <= 1.5 && Math.abs(f.t - padRects[i].t) <= 1.5 && Math.abs(f.w - padRects[i].w) <= 1.5 && Math.abs(f.h - padRects[i].h) <= 1.5).length;
    }

    // ------------------------------------------------------------------ kod kutusu
    const blocksEl = $('fgCodeBlocks');
    let areas = [];   // [{sel, el}]
    function selLabel(sel) { return sel === 'gol' ? '.gol' : `.${sel}`; }
    function buildCode(lv) {
        blocksEl.innerHTML = '';
        areas = [];
        const sels = ['gol', ...Object.keys(Object.assign({}, lv.base, lv.sol)).filter((s) => s !== 'gol')];
        let n = 0;
        sels.forEach((sel) => {
            const block = document.createElement('div');
            block.className = 'fg-block';
            const mk = (cls, html) => { const d = document.createElement('div'); d.className = `fg-line ${cls || ''}`; d.innerHTML = html; return d; };
            n += 1;
            block.appendChild(mk('', `<span class="no">${n}</span><span><span class="fg-sel">${selLabel(sel)}</span> {</span>`));
            if (sel === 'gol') { n += 1; block.appendChild(mk('fixed', `<span class="no">${n}</span><span>  display: flex;<span class="tag">hazır</span></span>`)); }
            objToDecls((lv.base || {})[sel]).forEach((d) => { n += 1; block.appendChild(mk('fixed', `<span class="no">${n}</span><span>  ${d.p.tr}: ${d.tr};<span class="tag">hazır</span></span>`)); });
            if (lv.targets.includes(sel)) {
                const wrap = document.createElement('div');
                wrap.className = 'fg-edit';
                const gut = document.createElement('span');
                gut.className = 'no';
                const ta = document.createElement('textarea');
                ta.className = 'fg-area';
                ta.rows = 3;
                ta.spellcheck = false;
                ta.setAttribute('autocapitalize', 'off');
                ta.setAttribute('autocomplete', 'off');
                ta.setAttribute('aria-label', `${selLabel(sel)} için kurallar`);
                ta.placeholder = sel === 'gol' ? 'kuralını buraya yaz…' : 'bu kurbağaya kural yaz…';
                ta.value = (drafts[lv.id] && drafts[lv.id][sel]) || '';
                const startNo = n + 1;
                const upd = () => { const c = Math.max(3, ta.value.split('\n').length); gut.textContent = Array.from({ length: c }, (_, i) => startNo + i).join('\n'); ta.rows = Math.min(8, c); };
                ta.addEventListener('input', () => { upd(); (drafts[lv.id] = drafts[lv.id] || {})[sel] = ta.value; schedulePreview(); });
                ta.addEventListener('keydown', (e) => { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); run(true); } });
                upd();
                wrap.appendChild(gut);
                wrap.appendChild(ta);
                block.appendChild(wrap);
                areas.push({ sel, el: ta });
                n += Math.max(3, ta.value.split('\n').length);
            }
            n += 1;
            block.appendChild(mk('', `<span class="no">${n}</span><span>}</span>`));
            blocksEl.appendChild(block);
        });
    }
    function collect() {
        const user = {}; const errors = []; const all = [];
        areas.forEach(({ sel, el }) => {
            const r = parseCode(el.value);
            user[sel] = r.decls;
            r.errors.forEach((m) => errors.push(`${selLabel(sel)}: ${m}`));
            el.classList.toggle('err', r.errors.length > 0);
            r.decls.forEach((d) => all.push({ sel, d }));
        });
        return { user, errors, all };
    }
    function realCssText() {
        const lv = level;
        const { user } = collect();
        const sels = ['gol', ...Object.keys(Object.assign({}, lv.base, lv.sol)).filter((s) => s !== 'gol')];
        return sels.map((sel) => {
            const lines = [];
            if (sel === 'gol') lines.push('display: flex;');
            objToDecls((lv.base || {})[sel]).forEach((d) => lines.push(`${d.p.css}: ${d.css};`));
            (user[sel] || []).forEach((d) => lines.push(`${d.p.css}: ${d.css};`));
            return `${selLabel(sel)} {\n  ${lines.join('\n  ')}\n}`;
        }).join('\n\n');
    }

    // ------------------------------------------------------------------ çalıştırma
    let previewTimer = null;
    function schedulePreview() { clearTimeout(previewTimer); previewTimer = setTimeout(() => run(false), 280); }
    function applyUser(user, animate) {
        const before = animate ? snap(frogsEl) : null;
        applySpec(frogsEl, [ctx.base, user]);
        if (before) {
            const after = snap(frogsEl);
            Array.from(frogsEl.children).forEach((e, i) => {
                const dx = before[i].l - after[i].l; const dy = before[i].t - after[i].t;
                if ((dx || dy) && e.animate) e.animate([{ transform: `translate(${dx}px,${dy}px)` }, { transform: 'translate(0,0)' }], { duration: 420, easing: 'cubic-bezier(.2,.9,.3,1.15)' });
            });
        }
    }
    function setStatus(msg, cls) { const s = $('fgStatus'); s.textContent = msg || ''; s.className = `fg-status ${cls || ''}`; }

    function run(manual) {
        if (!level || solved) return;
        const { user, errors, all } = collect();
        applyUser(user, true);
        const total = level.frogs.length;
        const ok = onPads(snap(frogsEl), snap(padsEl));
        $('fgRealCss').textContent = realCssText();
        const reached = errors.length === 0 && ok === total;
        $('fgRunBtn').classList.toggle('pulse', reached);
        if (manual) {
            runs += 1;
            if (errors.length) { setStatus(errors[0], 'err'); return; }
            if (reached) { onSolved(); return; }
            setStatus(all.length === 0 ? 'Henüz bir kural yazmadın. Kod kutusuna bir kural yaz ve tekrar çalıştır.'
                : `${ok} / ${total} kurbağa nilüferinde. Kuralları gözden geçir, tekrar dene!`, '');
        } else if (errors.length) { setStatus(errors[0], 'err'); } else if (reached) { setStatus('Tüm kurbağalar nilüferde! Çalıştır\'a basarak görevi tamamla.', 'ok'); } else { setStatus('', ''); }
    }

    // ------------------------------------------------------------------ ilerleme
    function relIds() { const o = []; levels.forEach((l, i) => { if (isDone(l)) o.push(i + 1); }); return o; }
    function endEff() { return Math.max(levelStart, Math.min(levelEndParam, levelStart + levels.length - 1)); }
    function pct() { return levels.length ? Math.min(100, Math.round(levels.filter(isDone).length / levels.length * 100)) : 0; }
    function levelsList() { return levels.map((l) => ({ id: l.id, name: l.title, completed: isDone(l), stars: store.done[l.id] ? store.done[l.id].stars : 0 })); }
    function elapsed() { return Math.max(0, Math.round((Date.now() - sessionStartedAt) / 1000)); }
    function post(m) { try { if (window.parent && window.parent !== window) window.parent.postMessage(m, '*'); } catch (e) { /* yok say */ } }
    function emitGameUpdate() { post({ type: 'GAME_UPDATE', source: SLUG, assignmentId: assignmentId || null, assignmentTitle, levelStart, levelEnd: endEff(), levels: levelsList(), currentLevelIndex: idx, progressPercent: pct() }); }
    function emitLevelCompleted(stars, gained) {
        post({ type: 'LEVEL_COMPLETED', source: SLUG, assignmentId: assignmentId || null, assignmentTitle, levelId: String(level.id), levelNo: idx + 1, levelStart, levelEnd: endEff(), completedLevelIds: relIds(), currentLevelIndex: idx, percent: pct(), progressPercent: pct(), xp: gained, stars, elapsedSeconds: elapsed(), levels: levelsList() });
    }
    function emitRangeIfNeeded() {
        if (!isAssignmentMode || rangeCompleteFired || !levels.length || levels.filter(isDone).length < levels.length) return;
        rangeCompleteFired = true;
        post({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: SLUG, assignmentId, assignmentTitle, levelStart, levelEnd: endEff(), completedLevelIds: relIds(), xp: levels.reduce((s, l) => s + (store.done[l.id] ? store.done[l.id].xp : 0), 0), elapsedSeconds: elapsed(), currentLevelIndex: idx });
    }
    function unlockedMax() {
        if (isStaff) return levels.length - 1;
        let u = 0;
        while (u < levels.length - 1 && isDone(levels[u])) u += 1;
        return u;
    }
    const RANKS = [[0, 'Yavru Kurbağa'], [10, 'Gölet Zıpçısı'], [30, 'Nilüfer Ustası'], [55, 'Flex Kaptanı'], [80, 'Flex Bilgesi'], [100, 'Flex Efendisi']];
    function renderProgress() {
        const total = levels.length || 1;
        const done = levels.filter(isDone).length;
        const allDone = ALL.filter(isDone).length;
        const rank = RANKS.filter(([min]) => allDone >= min).pop()[1];
        $('fgRank').textContent = rank;
        $('fgProgressFill').style.width = `${Math.round(done / total * 100)}%`;
        $('fgProgressText').textContent = `${done} / ${total}`;
        $('fgPrevBtn').disabled = idx <= 0;
        $('fgNextBtn').disabled = idx >= levels.length - 1 || idx + 1 > unlockedMax();
        $('fgLevelLabel').textContent = level ? `Seviye ${level.id} / ${ALL.length}` : '';
    }

    function onSolved() {
        solved = true;
        $('fgRunBtn').classList.remove('pulse');
        const stars = solutionShown ? 1 : ((hintStage >= 1 || runs > 4) ? 2 : 3);
        const first = !isDone(level);
        const prev = store.done[level.id];
        store.done[level.id] = { stars: Math.max(stars, prev ? prev.stars : 0), xp: prev ? prev.xp : level.xp };
        if (first) store.last = idx;
        saveStore();
        Array.from(frogsEl.children).forEach((e, i) => setTimeout(() => e.classList.add('hop'), i * 90));
        emitLevelCompleted(stars, first ? level.xp : 0);
        emitRangeIfNeeded();
        emitGameUpdate();
        renderProgress();
        setTimeout(() => {
            $('fgWinStars').textContent = '⭐'.repeat(stars) + '☆'.repeat(3 - stars);
            $('fgWinTitle').textContent = idx >= levels.length - 1 ? 'Görev listesi tamamlandı! 🏆' : 'Harika! Hepsi nilüferde!';
            $('fgWinText').textContent = (first ? `+${level.xp} XP kazandın. ` : 'Bu seviyeyi daha önce de çözmüştün. ') + (stars === 3 ? 'Mükemmel, ipucu kullanmadın!' : (stars === 2 ? 'Güzel iş! Daha az denemeyle 3 yıldız alabilirsin.' : 'Çözüme ulaştın, bir dahaki sefere ipucu almadan dene!'));
            $('fgWinNext').hidden = idx >= levels.length - 1;
            $('fgWin').hidden = false;
        }, 900);
    }

    // ------------------------------------------------------------------ seviye açma
    function hintText(stage) {
        if (stage === 1) {
            return 'Bu görevde şu kuralları kullan:<br>' + Object.entries(level.sol).map(([sel, o]) => `<code>${selLabel(sel)}</code>: ${Object.keys(o).map((k) => `<code>${k}</code>`).join(', ')}`).join('<br>');
        }
        return 'Bir çözüm:<br>' + Object.entries(level.sol).map(([sel, o]) => `<code>${selLabel(sel)}</code> { ${objToDecls(o).map((d) => `${d.p.tr}: ${d.tr};`).join(' ')} }`).join('<br>');
    }
    function openLevel(i) {
        if (!levels.length) return;
        i = Math.max(0, Math.min(levels.length - 1, i));
        if (i > unlockedMax()) i = unlockedMax();
        idx = i;
        level = levels[i];
        ctx = { base: {}, sol: {} };
        Object.entries(level.base || {}).forEach(([sel, o]) => { ctx.base[sel] = objToDecls(o); });
        Object.entries(level.sol).forEach(([sel, o]) => { ctx.sol[sel] = objToDecls(o); });
        runs = 0; hintStage = 0; solutionShown = false; solved = false;
        $('fgWin').hidden = true;
        $('fgHint').hidden = true;
        $('fgHintBtn').textContent = '💡 İpucu';
        $('fgRunBtn').classList.remove('pulse');
        setStatus('', '');
        $('fgCat').textContent = CAT_LABEL[level.cat] || level.cat;
        $('fgCat').dataset.cat = level.cat;
        $('fgTitle').textContent = `${level.id}. ${level.title}`;
        $('fgStory').innerHTML = level.story;
        const learnBox = $('fgLearn');
        learnBox.hidden = !level.learn.length;
        $('fgLearnBody').innerHTML = level.learn.join('<hr style="border:0;border-top:1px solid rgba(255,255,255,.15);margin:8px 0">');
        buildScene(level);
        applySpec(padsEl, [ctx.base, ctx.sol]);
        buildCode(level);
        fit();
        run(false);
        $('fgRealCss').textContent = realCssText();
        renderProgress();
        emitGameUpdate();
    }

    // ------------------------------------------------------------------ bölüm seçici
    function openPicker() {
        const body = $('fgPickerBody');
        body.innerHTML = '';
        const groups = {};
        levels.forEach((l, i) => { (groups[l.cat] = groups[l.cat] || []).push([l, i]); });
        Object.keys(CAT_LABEL).filter((c) => groups[c]).forEach((cat) => {
            const g = document.createElement('div');
            g.className = 'fg-picker-group';
            const h = document.createElement('h4');
            const done = groups[cat].filter(([l]) => isDone(l)).length;
            h.innerHTML = `<span class="fg-cat" data-cat="${cat}">${CAT_LABEL[cat]}</span> ${done} / ${groups[cat].length}`;
            const grid = document.createElement('div');
            grid.className = 'fg-picker-grid';
            groups[cat].forEach(([l, i]) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'fg-pick' + (isDone(l) ? ' done' : '') + (i === idx ? ' cur' : '') + (i > unlockedMax() ? ' lock' : '');
                b.textContent = i > unlockedMax() ? '🔒' : String(l.id);
                const st = store.done[l.id];
                if (st) { const sm = document.createElement('small'); sm.textContent = '★'.repeat(st.stars); b.appendChild(sm); }
                b.addEventListener('click', () => { if (i > unlockedMax()) return; $('fgPicker').hidden = true; openLevel(i); });
                grid.appendChild(b);
            });
            g.appendChild(h);
            g.appendChild(grid);
            body.appendChild(g);
        });
        $('fgPicker').hidden = false;
    }

    // ------------------------------------------------------------------ yerleşim / olaylar
    function fit() {
        const wrap = $('fgPondWrap'); const sc = $('fgPondScale');
        const avail = wrap.clientWidth || 410;
        const s = Math.min(1, avail / 410);
        sc.style.transform = `translateX(-50%) scale(${s})`;
        wrap.style.height = `${410 * s}px`;
    }
    function bind() {
        window.addEventListener('resize', fit);
        $('fgPrevBtn').addEventListener('click', () => openLevel(idx - 1));
        $('fgNextBtn').addEventListener('click', () => openLevel(idx + 1));
        $('fgLevelsBtn').addEventListener('click', openPicker);
        $('fgRunBtn').addEventListener('click', () => run(true));
        $('fgResetBtn').addEventListener('click', () => { areas.forEach(({ sel, el }) => { el.value = ''; (drafts[level.id] = drafts[level.id] || {})[sel] = ''; el.dispatchEvent(new Event('input')); }); setStatus('', ''); });
        $('fgHintBtn').addEventListener('click', () => {
            if (hintStage >= 2) return;
            hintStage += 1;
            if (hintStage === 2) solutionShown = true;
            $('fgHint').innerHTML = hintText(hintStage);
            $('fgHint').hidden = false;
            $('fgHintBtn').textContent = hintStage === 1 ? '🧩 Çözümü göster' : '💡 İpucu verildi';
            $('fgHintBtn').disabled = hintStage >= 2;
        });
        $('fgRealToggle').addEventListener('change', (e) => { $('fgRealBox').hidden = !e.target.checked; if (e.target.checked) $('fgRealBox').open = true; });
        $('fgWinReplay').addEventListener('click', () => openLevel(idx));
        $('fgWinNext').addEventListener('click', () => openLevel(idx + 1));
        document.querySelectorAll('#fgPicker [data-close]').forEach((e) => e.addEventListener('click', () => { $('fgPicker').hidden = true; }));
        $('fgIntroStart').addEventListener('click', () => { $('fgIntro').hidden = true; store.introSeen = true; saveStore(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { $('fgPicker').hidden = true; } });
    }

    function resolveGrant() {
        if (isStaff || !needsGrantCheck) return Promise.resolve();
        const url = (window.RUNNER_APP_BASE || '').replace(/\/$/, '') + '/runner-grant/' + SLUG + window.location.search;
        return fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then((r) => { if (!r.ok) { grantDenied = true; return null; } return r.json(); })
            .then((g) => {
                if (!g) return;
                if (!g.ok) { grantDenied = true; return; }
                if (g.role === 'student') { levelStart = Math.max(1, Number(g.from || 1)); levelEndParam = Math.max(levelStart, Number(g.to || levelStart)); isAssignmentMode = true; }
                else if (g.role === 'staff') { levelStart = 1; levelEndParam = 9999; isAssignmentMode = false; }
            })
            .catch(() => { grantDenied = true; });
    }

    function boot() {
        loadStore();
        bind();
        resolveGrant().then(() => {
            if (grantDenied) {
                document.body.innerHTML = '<div style="padding:40px;text-align:center;color:#fff;font-family:system-ui"><h2>Bu görev sana atanmamış</h2><p>Öğretmeninin verdiği bağlantıdan gir.</p></div>';
                return;
            }
            const from = Math.max(1, levelStart); const to = Math.min(ALL.length, Math.max(from, levelEndParam));
            levels = ALL.slice(from - 1, to);
            if (!levels.length) levels = ALL.slice(0, 1);
            let start = 0;
            if (!isCompetition && !isStaff) { start = Math.min(unlockedMax(), Math.max(0, levels.findIndex((l) => !isDone(l)))); if (start < 0) start = 0; }
            openLevel(start);
            if (!store.introSeen && !isStaff) $('fgIntro').hidden = false;
        });
    }

    // Test ve doğrulama için küçük, zararsız bir yardımcı arayüz.
    window.__flexGoleti = {
        levels: () => ALL,
        verify(i) {
            const lv = ALL[i];
            const prev = { level, ctx, areas, idx };
            const c = { base: {}, sol: {} };
            Object.entries(lv.base || {}).forEach(([sel, o]) => { c.base[sel] = objToDecls(o); });
            Object.entries(lv.sol).forEach(([sel, o]) => { c.sol[sel] = objToDecls(o); });
            buildScene(lv);
            applySpec(padsEl, [c.base, c.sol]);
            applySpec(frogsEl, [c.base, {}]);
            const pads = snap(padsEl);
            const start = onPads(snap(frogsEl), pads);
            applySpec(frogsEl, [c.base, c.sol]);
            const sol = onPads(snap(frogsEl), pads);
            const outside = pads.filter((p) => p.l < -1 || p.t < -1 || p.l + p.w > 401 || p.t + p.h > 401).length;
            let overlap = 0;
            for (let a = 0; a < pads.length; a += 1) for (let b = a + 1; b < pads.length; b += 1) {
                const A = pads[a]; const B = pads[b];
                if (A.l < B.l + B.w - 1 && B.l < A.l + A.w - 1 && A.t < B.t + B.h - 1 && B.t < A.t + A.h - 1) overlap += 1;
            }
            const minSize = pads.filter((p) => p.w < 24 || p.h < 24).length;
            const startRects = snap(frogsEl);
            Object.assign(window.__flexGoleti, { _prev: prev });
            return { id: lv.id, n: lv.frogs.length, atStart: start, solved: sol, outside, overlap, minSize, startRects: startRects.length };
        },
    };

    boot();
})();
