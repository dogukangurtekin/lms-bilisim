/* Kural Makinesi - görev ekranı: akıllı sera simülasyonu + EĞER/İSE kural editörü */
(function () {
    'use strict';
    var el = window.LabCore.el;

    var SENSORS = {
        nem: { label: 'Toprak nemi', unit: '%', min: 0, max: 100, def: 60, choices: [20, 30, 40, 50, 60, 70] },
        sicak: { label: 'Sıcaklık', unit: '°C', min: 0, max: 45, def: 24, choices: [20, 25, 28, 30, 32, 35] },
        isik: { label: 'Işık', unit: '%', min: 0, max: 100, def: 50, choices: [20, 30, 50, 70, 80] }
    };
    var DEVICES = { sulama: 'Sulama', fan: 'Fan', golge: 'Gölgelik' };
    var SENSOR_OPTS = Object.keys(SENSORS).map(function (k) { return { v: k, t: SENSORS[k].label }; });
    var DEVICE_OPTS = Object.keys(DEVICES).map(function (k) { return { v: k, t: DEVICES[k] }; });
    var OP_OPTS = [{ v: '<', t: '< küçük' }, { v: '>', t: '> büyük' }];
    var ACT_OPTS = [{ v: 'on', t: 'AÇ' }, { v: 'off', t: 'KAPAT' }];
    var JOIN_OPTS = [{ v: '', t: 'Tek koşul' }, { v: 'and', t: 'VE' }, { v: 'or', t: 'VEYA' }];
    var MAX_RULES = 6;

    // ------------------------------------------------------------ kural motoru
    function evalCond(c, env) {
        if (!c) return false;
        var x = env[c.s];
        return c.op === '<' ? x < c.v : x > c.v;
    }
    function ruleTrue(r, env) {
        var a = evalCond(r.c1, env);
        if (!r.join) return a;
        var b = evalCond(r.c2, env);
        return r.join === 'and' ? (a && b) : (a || b);
    }
    /** Kurallar yukarıdan aşağı çalışır; sonraki kural öncekinin kararını ezer. */
    function evalRules(rules, env) {
        var dev = { sulama: false, fan: false, golge: false };
        var fired = [];
        rules.forEach(function (r) {
            if (ruleTrue(r, env)) { dev[r.dev] = r.act === 'on'; fired.push(r.id); }
        });
        return { dev: dev, fired: fired };
    }
    function health(env, dev) {
        if (env.nem < 30 && !dev.sulama) return 'thirsty';
        if (env.isik > 70 && env.sicak > 30 && !dev.golge) return 'burn';
        if (env.sicak > 32 && !dev.fan) return 'hot';
        if (dev.sulama && env.isik < 20) return 'fungus';
        return 'ok';
    }
    var HEALTH_TEXT = {
        ok: '🌱 Bitkiler rahat',
        thirsty: '🥀 Toprak kurudu, bitkiler susuz',
        burn: '☀️ Güneş çok sert, yapraklar yanıyor',
        hot: '🥵 Sera çok sıcak',
        fungus: '🍄 Gece sulama: mantar riski'
    };
    function condText(c) { return SENSORS[c.s].label + ' ' + c.op + ' ' + c.v + SENSORS[c.s].unit; }
    function envText(e) { return 'Nem %' + e.nem + ' · ' + e.sicak + '°C · Işık %' + e.isik; }

    // ------------------------------------------------------------ sera sahnesi
    var SCENE = [
        '<svg viewBox="0 0 440 300" role="img" aria-label="Akıllı sera çizimi" class="km-svg">',
        '<defs><linearGradient id="kmSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#bfe4f7"/><stop offset="1" stop-color="#e8f6fb"/></linearGradient></defs>',
        '<rect width="440" height="300" fill="url(#kmSky)" class="km-sky"/>',
        '<g id="kmSun"><circle cx="60" cy="56" r="24" fill="#fbbf24"/><g stroke="#fbbf24" stroke-width="4" stroke-linecap="round"><path d="M60 18v8M60 86v8M22 56h8M90 56h8M33 29l6 6M81 77l6 6M33 83l6-6M81 35l6-6"/></g></g>',
        '<rect y="236" width="440" height="64" fill="#7c5a3c"/><rect y="236" width="440" height="8" fill="#6b4a30"/>',
        '<path d="M70 236V122L220 50l150 72v114z" fill="rgba(186,230,253,.5)" stroke="#0f766e" stroke-width="4" stroke-linejoin="round"/>',
        '<path d="M145 236V86M220 236V50M295 236V86" stroke="#0f766e" stroke-width="2" opacity=".45"/>',
        '<g id="kmShade" class="km-shade"><path d="M70 122L220 50l150 72v20L220 70 70 142z" fill="#1f2937" opacity=".88"/></g>',
        '<line x1="96" y1="136" x2="344" y2="136" stroke="#64748b" stroke-width="4" stroke-linecap="round"/>',
        sprinkler(140), sprinkler(220), sprinkler(300),
        plant(140), plant(220), plant(300),
        '<g id="kmFan"><circle cx="352" cy="186" r="26" fill="#fff" stroke="#0f766e" stroke-width="3"/><g class="km-blades"><path d="M352 186c0-14 8-20 14-16-3 8-6 12-14 16z" fill="#0f766e"/><path d="M352 186c12 7 12 15 5 18-5-6-6-10-5-18z" fill="#0f766e"/><path d="M352 186c-12 7-20 4-20-3 8 0 12 0 20 3z" fill="#0f766e"/></g><circle cx="352" cy="186" r="4" fill="#134e4a"/></g>',
        '</svg>'
    ].join('');
    function sprinkler(x) {
        return '<g class="km-spr" transform="translate(' + x + ' 136)"><rect x="-6" y="0" width="12" height="9" rx="3" fill="#475569"/>' +
            '<g class="km-drops" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"><path class="d1" d="M-6 14l-8 22"/><path class="d2" d="M0 14v26"/><path class="d3" d="M6 14l8 22"/></g></g>';
    }
    function plant(x) {
        return '<g transform="translate(' + x + ' 236)"><path d="M0 0V-54" stroke="#15803d" stroke-width="5" stroke-linecap="round"/>' +
            '<ellipse class="leaf" cx="-15" cy="-40" rx="15" ry="7" transform="rotate(-28 -15 -40)"/><ellipse class="leaf" cx="15" cy="-30" rx="15" ry="7" transform="rotate(28 15 -30)"/>' +
            '<circle class="bloom" cy="-60" r="8"/></g>';
    }

    // ------------------------------------------------------------ yardımcılar
    function sel(options, value, onchange, label, disabled) {
        var s = el('select', { 'aria-label': label, class: 'km-sel' });
        options.forEach(function (o) { var op = el('option', { value: String(o.v), text: o.t }); if (String(o.v) === String(value)) op.selected = true; s.appendChild(op); });
        if (disabled) s.disabled = true;
        s.addEventListener('change', function () { onchange(s.value); });
        return s;
    }
    function choiceOpts(sensor) { return SENSORS[sensor].choices.map(function (v) { return { v: v, t: String(v) + SENSORS[sensor].unit }; }); }
    var nextId = 1;

    // ------------------------------------------------------------ görev ekranı
    function renderTask(host, task, api) {
        var env = { nem: SENSORS.nem.def, sicak: SENSORS.sicak.def, isik: SENSORS.isik.def };
        var rules = api.draft.get('rules', null);
        if (!Array.isArray(rules)) {
            rules = JSON.parse(JSON.stringify(task.starter || [])).map(function (r) { r.id = nextId++; return r; });
        }
        rules.forEach(function (r) { if (r.id >= nextId) nextId = r.id + 1; });
        var firedAll = api.draft.get('fired', []);
        var tested = false;
        var passed = false;
        var answer = api.draft.get('answer', '');
        var shownHints = 0;

        function persist() { api.draft.set('rules', rules); }

        // --- sol: sahne + ortam
        var sceneBox = el('div', { class: 'km-scene', html: SCENE, 'data-health': 'ok' });
        var chip = el('div', { class: 'km-health', role: 'status' });
        var devChips = el('div', { class: 'km-devs' });
        var sliders = el('div', { class: 'km-env' }, el('h3', { text: 'Ortam' }));
        Object.keys(SENSORS).forEach(function (k) {
            var S = SENSORS[k];
            var out = el('output', { text: env[k] + S.unit });
            var input = el('input', { type: 'range', min: S.min, max: S.max, value: env[k], 'aria-label': S.label });
            input.addEventListener('input', function () { env[k] = Number(input.value); out.textContent = env[k] + S.unit; tested = true; refresh(); });
            sliders.appendChild(el('label', { class: 'km-slider' }, el('span', { text: S.label }), input, out));
        });
        var left = el('div', { class: 'km-left' }, el('div', { class: 'km-card km-sim' }, sceneBox, chip, devChips), el('div', { class: 'km-card' }, sliders));

        // --- sağ: kurallar
        var rulesBox = el('div', { class: 'km-rules' });
        var addBtn = el('button', { class: 'lab-btn', type: 'button' }, '＋ Kural ekle');
        addBtn.addEventListener('click', function () {
            if (rules.length >= MAX_RULES) { api.toast('En fazla ' + MAX_RULES + ' kural ekleyebilirsin.'); return; }
            rules.push({ id: nextId++, c1: { s: 'nem', op: '<', v: 30 }, join: '', c2: null, dev: 'sulama', act: 'on', locked: false });
            persist(); drawRules(); refresh();
        });
        var checkBtn = el('button', { class: 'lab-btn primary', type: 'button' }, '✔ Kontrol et');
        var hintBtn = el('button', { class: 'lab-btn', type: 'button' }, '💡 İpucu al');
        var hintBox = el('div', { class: 'km-hint', hidden: true });
        var results = el('div', { class: 'km-results', 'aria-live': 'polite' });
        var question = null;
        if (task.kind === 'open') {
            var ta = el('textarea', { rows: 3, maxlength: 400, placeholder: 'Buraya yaz…', 'aria-label': task.question });
            ta.value = answer;
            ta.addEventListener('input', function () { answer = ta.value; api.draft.set('answer', answer); updateProgress(); });
            question = el('div', { class: 'km-q' }, el('label', { text: task.question }), ta);
        }
        var right = el('div', { class: 'km-right' }, el('div', { class: 'km-card' }, el('div', { class: 'km-rules-head' }, el('h3', { text: 'Kurallar' }), el('span', { class: 'lab-chip', id: 'kmCount' })), rulesBox, el('div', { class: 'km-actions' }, addBtn)),
            question ? el('div', { class: 'km-card' }, question) : null,
            el('div', { class: 'km-card' }, el('div', { class: 'km-actions' }, checkBtn, hintBtn), hintBox, results));
        host.appendChild(el('div', { class: 'km-layout' }, left, right));

        // --- kural kartları
        function condRow(c, who, locked, onchange) {
            return el('span', { class: 'km-cond' },
                sel(SENSOR_OPTS, c.s, function (v) { c.s = v; if (SENSORS[v].choices.indexOf(c.v) < 0) c.v = SENSORS[v].choices[0]; onchange(true); }, who + ' sensör', locked),
                sel(OP_OPTS, c.op, function (v) { c.op = v; onchange(); }, who + ' karşılaştırma', locked),
                sel(choiceOpts(c.s), c.v, function (v) { c.v = Number(v); onchange(); }, who + ' değer', locked));
        }
        function move(i, d) {
            var j = i + d;
            if (j < 0 || j >= rules.length) return;
            var t = rules[i]; rules[i] = rules[j]; rules[j] = t;
            persist(); drawRules(); refresh();
        }
        function drawRules() {
            rulesBox.innerHTML = '';
            if (!rules.length) {
                rulesBox.appendChild(el('p', { class: 'km-empty', text: 'Henüz kural yok. "Kural ekle" ile başla; sonra EĞER ve İSE bölümlerini doldur.' }));
            }
            rules.forEach(function (r, i) {
                var locked = !!r.locked;
                function changed(rebuild) { persist(); if (rebuild) drawRules(); refresh(); }
                var line1 = el('div', { class: 'km-line' }, el('span', { class: 'km-tag if', text: 'EĞER' }), condRow(r.c1, '1. koşul', locked, changed));
                var joinSel = sel(JOIN_OPTS, r.join, function (v) {
                    r.join = v;
                    if (v && !r.c2) r.c2 = { s: 'sicak', op: '>', v: 30 };
                    if (!v) r.c2 = null;
                    changed(true);
                }, 'Koşulları bağla', locked);
                var line1b = el('div', { class: 'km-line' }, el('span', { class: 'km-tag join', text: 'BAĞLAÇ' }), joinSel, r.join && r.c2 ? condRow(r.c2, '2. koşul', locked, changed) : null);
                var line2 = el('div', { class: 'km-line' }, el('span', { class: 'km-tag do', text: 'İSE' }),
                    sel(DEVICE_OPTS, r.dev, function (v) { r.dev = v; changed(); }, 'Araç', locked),
                    sel(ACT_OPTS, r.act, function (v) { r.act = v; changed(); }, 'İşlem', locked));
                var tools = el('div', { class: 'km-tools' },
                    el('button', { class: 'lab-btn lab-icon-btn', type: 'button', 'aria-label': 'Kuralı yukarı al', onclick: function () { move(i, -1); } }, '↑'),
                    el('button', { class: 'lab-btn lab-icon-btn', type: 'button', 'aria-label': 'Kuralı aşağı al', onclick: function () { move(i, 1); } }, '↓'),
                    locked ? el('span', { class: 'km-lock', title: 'Hazır kural: değiştirilemez ve silinemez', text: '🔒' })
                        : el('button', { class: 'lab-btn lab-icon-btn', type: 'button', 'aria-label': 'Kuralı sil', onclick: function () { rules.splice(i, 1); persist(); drawRules(); refresh(); } }, '✕'));
                rulesBox.appendChild(el('div', { class: 'km-rule', 'data-id': r.id }, el('div', { class: 'km-num', text: String(i + 1) }), el('div', { class: 'km-body' }, line1, line1b, line2), tools));
            });
            document.getElementById('kmCount').textContent = rules.length + ' / ' + MAX_RULES;
        }

        // --- canlı güncelleme
        function refresh() {
            var res = evalRules(rules, env);
            var h = health(env, res.dev);
            sceneBox.setAttribute('data-health', h);
            chip.textContent = HEALTH_TEXT[h];
            chip.className = 'km-health ' + (h === 'ok' ? 'ok' : 'bad');
            sceneBox.querySelector('#kmShade').classList.toggle('on', res.dev.golge);
            sceneBox.querySelectorAll('.km-spr').forEach(function (n) { n.classList.toggle('on', res.dev.sulama); });
            sceneBox.querySelector('#kmFan').classList.toggle('on', res.dev.fan);
            var sun = sceneBox.querySelector('#kmSun');
            sun.style.opacity = String(0.35 + env.isik / 100 * 0.65);
            sun.style.transform = 'scale(' + (0.8 + env.isik / 100 * 0.4) + ')';
            sun.style.transformOrigin = '60px 56px';
            devChips.innerHTML = '';
            Object.keys(DEVICES).forEach(function (k) {
                devChips.appendChild(el('span', { class: 'km-dev ' + (res.dev[k] ? 'on' : ''), text: DEVICES[k] + ': ' + (res.dev[k] ? 'AÇIK' : 'KAPALI') }));
            });
            var changed = false;
            rulesBox.querySelectorAll('.km-rule').forEach(function (node) {
                var id = Number(node.getAttribute('data-id'));
                var on = res.fired.indexOf(id) >= 0;
                node.classList.toggle('live', on);
                if (on && firedAll.indexOf(id) < 0) { firedAll.push(id); changed = true; }
            });
            if (changed) api.draft.set('fired', firedAll);
            updateProgress();
        }

        function openChecks() {
            var devs = {};
            rules.forEach(function (r) { devs[r.dev] = true; });
            var unfired = rules.filter(function (r) { return firedAll.indexOf(r.id) < 0; }).length;
            return [
                { ok: rules.length >= 3, text: 'En az 3 kural yaz (' + rules.length + ' / 3)' },
                { ok: Object.keys(devs).length >= 3, text: 'Sulama, Fan ve Gölgelik için birer kural olsun (' + Object.keys(devs).length + ' / 3 araç)' },
                { ok: rules.length > 0 && unfired === 0, text: 'Her kuralı kaydırıcılarla en az bir kez çalıştır' + (unfired ? ' (' + unfired + ' kural henüz çalışmadı)' : '') },
                { ok: answer.trim().length >= 15, text: 'Tasarım açıklamanı yaz (en az 15 karakter)' }
            ];
        }
        function updateProgress() {
            if (task.kind === 'open') {
                var c = openChecks();
                api.setProgress(c.filter(function (x) { return x.ok; }).length, c.length);
            } else {
                api.setProgress((rules.length > (task.starter || []).length ? 1 : 0) + (tested ? 1 : 0) + (passed ? 1 : 0), 3);
            }
        }

        // --- kontrol
        function scenarioRows() {
            var allOk = true;
            var rows = task.scenarios.map(function (sc) {
                var res = evalRules(rules, sc.env);
                var bad = task.watch.filter(function (d) { return res.dev[d] !== sc.expect[d]; });
                if (bad.length) allOk = false;
                var parts = task.watch.map(function (d) { return DEVICES[d] + ': ' + (sc.expect[d] ? 'AÇIK' : 'KAPALI') + ' olmalı, sende ' + (res.dev[d] ? 'AÇIK' : 'KAPALI'); });
                return el('li', { class: bad.length ? 'bad' : 'ok' }, el('b', { text: (bad.length ? '✗ ' : '✓ ') + envText(sc.env) }), el('span', { text: bad.length ? parts.join('; ') : 'Doğru' }));
            });
            return { ok: allOk, rows: rows };
        }
        checkBtn.addEventListener('click', function () {
            results.innerHTML = '';
            if (task.kind === 'open') {
                var checks = openChecks();
                var ul = el('ul', { class: 'km-list' });
                checks.forEach(function (c) { ul.appendChild(el('li', { class: c.ok ? 'ok' : 'bad' }, el('b', { text: (c.ok ? '✓ ' : '✗ ') + c.text }))); });
                results.appendChild(ul);
                if (checks.every(function (c) { return c.ok; })) { passed = true; finish(); }
                else { results.appendChild(el('p', { class: 'km-note', text: 'Henüz bitmedi; eksik maddeleri tamamla ve tekrar dene.' })); }
                return;
            }
            if (rules.length <= (task.starter || []).length) {
                results.appendChild(el('p', { class: 'km-note', text: 'Önce kendi kuralını ekle: "Kural ekle"ye bas ve EĞER / İSE bölümlerini doldur.' }));
                return;
            }
            var out = scenarioRows();
            results.appendChild(el('p', { class: 'km-note', text: out.ok ? 'Tüm senaryolar doğru çalıştı!' : 'Bazı senaryolarda sonuç beklenenden farklı. Kırmızı satırlara bak.' }));
            results.appendChild(el('ul', { class: 'km-list' }, out.rows));
            if (out.ok) { passed = true; finish(); }
        });
        function finish() {
            updateProgress();
            var h = api.hintsUsed();
            api.complete(h === 0 ? 3 : (h === 1 ? 2 : 1));
        }

        hintBtn.addEventListener('click', function () {
            if (shownHints >= task.hints.length) { api.toast('Bu görev için başka ipucu kalmadı.'); return; }
            shownHints += 1;
            api.hintUsed();
            hintBox.hidden = false;
            hintBox.innerHTML = '';
            hintBox.appendChild(el('div', { class: 'km-hint-title', text: '⚙️ Dişli\'den ipucu ' + shownHints + ' / ' + task.hints.length }));
            for (var i = 0; i < shownHints; i++) hintBox.appendChild(el('p', { text: (i + 1) + '. ' + task.hints[i] }));
        });

        drawRules();
        refresh();
        if (api.alreadyDone) results.appendChild(el('p', { class: 'km-note', text: 'Bu görevi daha önce çözmüştün. İstersen yeniden dene.' }));
    }

    window.LabCore.start({
        slug: 'lab-kural-makinesi',
        title: 'Kural Makinesi',
        tagline: 'Sensörleri oku, kuralları kur, akıllı serayı yönet',
        tasks: window.KM_TASKS,
        renderTask: renderTask
    });
})();
