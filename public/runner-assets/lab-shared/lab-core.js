/* Kod Laboratuvarı - ortak çatı: görev seçimi, ilerleme, ödev/XP olayları, tema.
   Her laboratuvar LabCore.start({...}) ile açılır ve yalnızca kendi görev ekranını çizer. */
(function () {
    'use strict';

    function el(tag, attrs) {
        var node = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                var v = attrs[k];
                if (v === null || v === undefined || v === false) return;
                if (k === 'class') node.className = v;
                else if (k === 'text') node.textContent = v;
                else if (k === 'html') node.innerHTML = v;
                else if (k.slice(0, 2) === 'on' && typeof v === 'function') node.addEventListener(k.slice(2), v);
                else node.setAttribute(k, v === true ? '' : v);
            });
        }
        for (var i = 2; i < arguments.length; i++) {
            var kid = arguments[i];
            if (kid === null || kid === undefined || kid === false) continue;
            var kids = Array.isArray(kid) ? kid : [kid];
            kids.forEach(function (k) {
                if (k === null || k === undefined || k === false) return;
                node.appendChild(typeof k === 'string' ? document.createTextNode(k) : k);
            });
        }
        return node;
    }

    function safeGet(key) { try { return window.localStorage.getItem(key); } catch (e) { return null; } }
    function safeSet(key, val) { try { window.localStorage.setItem(key, val); } catch (e) { /* kayıt yoksa sessizce devam */ } }

    function start(cfg) {
        var SLUG = cfg.slug;
        var params = new URLSearchParams(window.location.search);
        var roleParam = params.get('role');
        var isStaff = roleParam === 'teacher' || roleParam === 'admin';
        var assignmentId = String(params.get('assignmentId') || '').trim();
        var assignmentTitle = String(params.get('assignmentTitle') || (cfg.title + ' Ödevi')).trim();
        var rangeFrom = Math.max(0, Number(params.get('levelStart') || params.get('from') || 0));
        var rangeTo = Math.max(rangeFrom, Number(params.get('levelEnd') || params.get('to') || rangeFrom));
        var hasRange = !window.RUNNER_PUBLIC && rangeFrom > 0;
        var needsGrant = !isStaff && (!!assignmentId || hasRange || params.get('grant') === '1' || params.get('enforceGrant') === '1');
        var levelStart = hasRange ? rangeFrom : 1;
        var levelEnd = hasRange ? rangeTo : cfg.tasks.length;
        var assignmentMode = !!assignmentId || hasRange;
        if (isStaff) { assignmentMode = false; levelStart = 1; levelEnd = 9999; }
        var startedAt = Date.now();
        var rangeFired = false;
        var tasks = [];
        var current = -1;

        var storeKey = 'kodlab:' + SLUG + ':' + (window.RUNNER_USER || 'anon');
        var store = { done: {}, drafts: {}, theme: '' };
        try { var raw = safeGet(storeKey); if (raw) store = Object.assign(store, JSON.parse(raw)); } catch (e) { /* bozuk kayıt yok sayılır */ }
        function save() { safeSet(storeKey, JSON.stringify(store)); }

        var root = document.getElementById('labApp');
        var theme = store.theme || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);

        function post(m) { try { if (window.parent && window.parent !== window) window.parent.postMessage(m, '*'); } catch (e) { /* iframe dışında */ } }
        function isDone(t) { return !!store.done[t.id]; }
        function relIds() { var o = []; tasks.forEach(function (t, i) { if (isDone(t)) o.push(i + 1); }); return o; }
        function pct() { return tasks.length ? Math.min(100, Math.round(tasks.filter(isDone).length / tasks.length * 100)) : 0; }
        function list() { return tasks.map(function (t) { return { id: t.id, name: t.title, completed: isDone(t), stars: store.done[t.id] ? store.done[t.id].stars : 0 }; }); }
        function elapsed() { return Math.max(0, Math.round((Date.now() - startedAt) / 1000)); }
        function endEff() { return Math.max(levelStart, Math.min(levelEnd, levelStart + tasks.length - 1)); }
        function emitUpdate() {
            post({ type: 'GAME_UPDATE', source: SLUG, assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: endEff(), levels: list(), currentLevelIndex: current, progressPercent: pct() });
        }
        function emitCompleted(task, stars, gained) {
            post({ type: 'LEVEL_COMPLETED', source: SLUG, assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelId: String(task.id), levelNo: current + 1, levelStart: levelStart, levelEnd: endEff(), completedLevelIds: relIds(), currentLevelIndex: current, percent: pct(), progressPercent: pct(), xp: gained, stars: stars, elapsedSeconds: elapsed(), levels: list() });
            if (assignmentMode && !rangeFired && tasks.length && tasks.every(isDone)) {
                rangeFired = true;
                post({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: SLUG, assignmentId: assignmentId, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: endEff(), completedLevelIds: relIds(), xp: tasks.reduce(function (s, t) { return s + (store.done[t.id] ? store.done[t.id].xp : 0); }, 0), elapsedSeconds: elapsed(), currentLevelIndex: current });
            }
        }

        // ---------- kabuk
        var toastEl = el('div', { class: 'lab-toast', hidden: true, role: 'status' });
        var modal = el('div', { class: 'lab-modal', hidden: true });
        var stage = el('div', { class: 'lab-stage' });
        var taskLabel = el('small', { text: '' });
        var brand = el('div', { class: 'lab-brand' }, el('span', { text: cfg.title }), taskLabel);
        function iconBtn(label, glyph, handler, extra) {
            return el('button', { class: 'lab-btn lab-icon-btn' + (extra ? ' ' + extra : ''), type: 'button', 'aria-label': label, title: label, onclick: handler }, el('span', { 'aria-hidden': 'true', text: glyph }), extra && extra.indexOf('keep') >= 0 ? el('span', { class: 'lbl', text: ' ' + label }) : null);
        }
        var tasksBtn = el('button', { class: 'lab-btn keep', type: 'button', onclick: function () { showPicker(); } }, el('span', { 'aria-hidden': 'true', text: '▦' }), el('span', { class: 'lbl', text: 'Görevler' }));
        var resetBtn = el('button', { class: 'lab-btn', type: 'button' }, el('span', { 'aria-hidden': 'true', text: '↻' }), el('span', { class: 'lbl', text: 'Baştan başla' }));
        var armTimer = null;
        resetBtn.addEventListener('click', function () {
            if (!resetBtn.classList.contains('armed')) {
                resetBtn.classList.add('armed');
                resetBtn.querySelector('.lbl').textContent = 'Emin misin? Tekrar bas';
                resetBtn.querySelector('.lbl').style.display = 'inline';
                armTimer = setTimeout(disarm, 4000);
                return;
            }
            disarm();
            store.done = {}; store.drafts = {}; save();
            toast('İlerleme sıfırlandı.');
            showPicker();
        });
        function disarm() {
            clearTimeout(armTimer);
            resetBtn.classList.remove('armed');
            var l = resetBtn.querySelector('.lbl'); l.textContent = 'Baştan başla'; l.style.display = '';
        }
        var fsBtn = iconBtn('Tam ekran', '⛶', function () {
            try { if (document.fullscreenElement) document.exitFullscreen(); else document.documentElement.requestFullscreen(); } catch (e) { /* desteklenmiyor */ }
        });
        var themeBtn = iconBtn('Koyu / açık tema', theme === 'dark' ? '☀' : '☾', function () {
            theme = theme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            themeBtn.firstChild.textContent = theme === 'dark' ? '☀' : '☾';
            store.theme = theme; save();
        });
        var top = el('div', { class: 'lab-top' }, brand, tasksBtn, el('div', { class: 'lab-spacer' }), resetBtn, fsBtn, themeBtn);
        var shell = el('div', { class: 'lab-shell' }, top, stage);
        root.innerHTML = '';
        root.appendChild(shell); root.appendChild(modal); root.appendChild(toastEl);

        var toastTimer = null;
        function toast(msg) {
            toastEl.textContent = msg; toastEl.hidden = false;
            clearTimeout(toastTimer);
            toastTimer = setTimeout(function () { toastEl.hidden = true; }, 2600);
        }
        function closeModal() { modal.hidden = true; modal.innerHTML = ''; }
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

        // ---------- görev seçimi
        function diffClass(d) { return d === 'Kolay' ? 'easy' : (d === 'Zor' ? 'hard' : 'mid'); }
        function showPicker() {
            current = -1;
            closeModal();
            taskLabel.textContent = 'Kod Laboratuvarı';
            stage.innerHTML = '';
            var done = tasks.filter(isDone).length;
            stage.appendChild(el('div', { class: 'lab-hero' }, el('h2', { text: 'Görev seç' }), el('p', { text: cfg.tagline + ' · ' + done + ' / ' + tasks.length + ' görev tamamlandı' })));
            var grid = el('div', { class: 'lab-grid' });
            tasks.forEach(function (t, i) {
                var rec = store.done[t.id];
                var card = el('button', { class: 'lab-card' + (rec ? ' done' : ''), type: 'button', onclick: function () { openTask(i); } },
                    el('span', { class: 'kicker', text: (i + 1) + '. görev · ' + t.topic }),
                    el('h3', { text: t.title }),
                    el('p', { text: t.summary }),
                    el('div', { class: 'lab-chips' },
                        el('span', { class: 'lab-chip', text: t.age + '+' }),
                        el('span', { class: 'lab-chip ' + diffClass(t.level), text: t.level }),
                        rec ? el('span', { class: 'lab-chip star', text: '★'.repeat(rec.stars) + '☆'.repeat(Math.max(0, 3 - rec.stars)) }) : null));
                grid.appendChild(card);
            });
            stage.appendChild(grid);
            emitUpdate();
        }

        // ---------- görev ekranı
        function openTask(i) {
            closeModal();
            current = i;
            var t = tasks[i];
            var hintCount = 0;
            taskLabel.textContent = (i + 1) + '. görev';
            stage.innerHTML = '';
            var fill = el('i');
            var count = el('b', { text: '0 / 0' });
            var head = el('div', { class: 'lab-task-head' },
                el('div', { class: 'txt' }, el('strong', { text: t.brief }), t.tip ? el('span', { class: 'sub', text: t.tip }) : null),
                el('div', { class: 'lab-prog', title: 'Görev ilerlemesi' }, el('div', { class: 'bar' }, fill), count));
            var host = el('div', { class: 'lab-stage' });
            stage.appendChild(head); stage.appendChild(host);
            var api = {
                el: el, task: t, index: i, isStaff: isStaff,
                alreadyDone: isDone(t),
                setProgress: function (d, total) { fill.style.width = (total ? Math.round(d / total * 100) : 0) + '%'; count.textContent = d + ' / ' + total; },
                hintUsed: function () { hintCount += 1; return hintCount; },
                hintsUsed: function () { return hintCount; },
                draft: {
                    get: function (k, def) { var v = store.drafts[t.id + ':' + k]; return v === undefined ? def : v; },
                    set: function (k, v) { store.drafts[t.id + ':' + k] = v; save(); }
                },
                toast: toast,
                complete: function (stars) { finish(t, Math.max(1, Math.min(3, stars || 1))); }
            };
            cfg.renderTask(host, t, api);
            emitUpdate();
        }

        function finish(t, stars) {
            var prev = store.done[t.id];
            var first = !prev;
            var gained = first ? t.xp : 0;
            store.done[t.id] = { stars: Math.max(stars, prev ? prev.stars : 0), xp: prev ? prev.xp : t.xp };
            save();
            emitCompleted(t, stars, gained);
            var nextIdx = current + 1 < tasks.length ? current + 1 : -1;
            modal.innerHTML = '';
            modal.appendChild(el('div', { class: 'box', role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Görev tamamlandı' },
                el('div', { class: 'lab-stars', text: '★'.repeat(stars) + '☆'.repeat(3 - stars) }),
                el('h3', { text: 'Görev tamam!' }),
                el('p', { text: first ? ('+' + t.xp + ' XP kazandın. ' + (stars === 3 ? 'İpucu kullanmadan çözdün, harika!' : 'Bir dahaki sefere daha az ipucuyla dene.')) : 'Bu görevi daha önce de çözmüştün.' }),
                el('div', { class: 'row' },
                    nextIdx >= 0 ? el('button', { class: 'lab-btn primary', type: 'button', onclick: function () { openTask(nextIdx); } }, 'Sonraki görev →') : null,
                    el('button', { class: 'lab-btn', type: 'button', onclick: function () { showPicker(); } }, 'Görevlere dön'))));
            modal.hidden = false;
            var firstBtn = modal.querySelector('button'); if (firstBtn) firstBtn.focus();
        }

        // ---------- ödev izni (öğrenci için)
        function resolveGrant() {
            if (isStaff || !needsGrant) return Promise.resolve(true);
            var url = (window.RUNNER_APP_BASE || '').replace(/\/$/, '') + '/runner-grant/' + SLUG + window.location.search;
            return fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (g) {
                    if (!g || !g.ok) return false;
                    if (g.role === 'student') { levelStart = Math.max(1, Number(g.from || 1)); levelEnd = Math.max(levelStart, Number(g.to || levelStart)); assignmentMode = true; }
                    else if (g.role === 'staff') { levelStart = 1; levelEnd = 9999; assignmentMode = false; }
                    return true;
                })
                .catch(function () { return false; });
        }

        resolveGrant().then(function (ok) {
            if (!ok) {
                root.innerHTML = '';
                root.appendChild(el('div', { class: 'lab-denied' }, el('h2', { text: 'Bu laboratuvar sana atanmamış' }), el('p', { text: 'Öğretmeninin verdiği bağlantıdan gir.' })));
                return;
            }
            var from = Math.max(1, levelStart);
            var to = Math.min(cfg.tasks.length, Math.max(from, levelEnd));
            tasks = cfg.tasks.slice(from - 1, to);
            if (!tasks.length) tasks = cfg.tasks.slice(0, 1);
            showPicker();
        });
    }

    window.LabCore = { start: start, el: el };
})();
