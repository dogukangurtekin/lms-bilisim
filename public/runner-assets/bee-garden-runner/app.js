(() => {
    const gardenEl = document.getElementById('bgGarden');
    const refGardenEl = document.getElementById('bgRefGarden');
    const winToastEl = document.getElementById('bgWinToast');
    const taskTitleEl = document.getElementById('bgTaskTitle');
    const taskDescEl = document.getElementById('bgTaskDesc');
    const levelLabelEl = document.getElementById('bgLevelLabel');
    const prevBtn = document.getElementById('bgPrevBtn');
    const nextBtn = document.getElementById('bgNextBtn');
    const resetBtn = document.getElementById('bgResetBtn');
    const modeTabs = document.querySelectorAll('.bg-mode-tab');
    const blockPanel = document.getElementById('bgBlockPanel');
    const codePanel = document.getElementById('bgCodePanel');
    const codeArea = document.getElementById('bgCodeArea');
    const codePreview = document.getElementById('bgCodePreview');
    const runBtn = document.getElementById('bgRunBtn');
    const runStatusEl = document.getElementById('bgRunStatus');
    const tutorialCard = document.getElementById('bgTutorialCard');
    const tutorialText = document.getElementById('bgTutorialText');
    const introOverlay = document.getElementById('bgIntroOverlay');
    const introStartBtn = document.getElementById('bgIntroStartBtn');
    const codeGutter = document.getElementById('bgCodeGutter');

    const STORAGE_KEY = 'bee_garden_progress_v2';
    const query = new URLSearchParams(window.location.search);
    const competitionId = query.get('assignmentId') || '';
    const competitionMode = /^competition-\d+$/.test(competitionId);
    const roleParam = String(query.get('role') || '').toLowerCase();
    const isStaff = roleParam === 'admin' || roleParam === 'teacher';

    // Blockly/Scratch tarzı blok parçalarının kural kategorisine göre rengi.
    const PROP_COLORS = {
        justifyContent: { name: 'orange', hex: '#FF9F43' },
        alignItems: { name: 'blue', hex: '#3E7BFA' },
        flexDirection: { name: 'purple', hex: '#8B5CF6' },
        alignSelf: { name: 'green', hex: '#22B07D' },
    };

    // Bu oyun gercek CSS ozelliklerini (justify-content, align-items, ...)
    // kullanmiyor; ayni MANTIGI ogreten ama farkli kelimelerle yazilan
    // kendi basit "Ari Dili" komutlarimiz var. Kod metninde bu kelimeler
    // gorunur; arka planda gercek flexbox degerine cevrilip uygulanir.
    const VALUE_TO_CODE = {
        'flex-start': 'basa',
        'center': 'ortaya',
        'flex-end': 'sona',
        'space-between': 'esit-arada',
        'space-around': 'esit-kenarli',
        'space-evenly': 'tam-esit',
        'stretch': 'uzat',
        'row': 'yatay',
        'column': 'dikey',
        'row-reverse': 'yatay-ters',
        'column-reverse': 'dikey-ters',
    };
    const CODE_TO_VALUE = Object.fromEntries(Object.entries(VALUE_TO_CODE).map(([css, code]) => [code, css]));

    function beeSvg() {
        return `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="beeBody" cx="38%" cy="30%" r="80%">
                    <stop offset="0" stop-color="#FFE98A"/>
                    <stop offset="1" stop-color="#FFB800"/>
                </radialGradient>
            </defs>
            <path d="M16 33 L6 36 L16 38 Z" fill="#1F2430"/>
            <g class="bee-wing"><ellipse cx="22" cy="20" rx="9" ry="12" transform="rotate(-24 22 20)" fill="#EAF6FF" fill-opacity=".92" stroke="#9CC6E8" stroke-width="1.2"/></g>
            <g class="bee-wing r"><ellipse cx="39" cy="20" rx="9" ry="12" transform="rotate(24 39 20)" fill="#EAF6FF" fill-opacity=".92" stroke="#9CC6E8" stroke-width="1.2"/></g>
            <path d="M25 17 Q22 8 18 6" stroke="#1F2430" stroke-width="1.8" fill="none" stroke-linecap="round"/>
            <path d="M35 17 Q38 8 42 6" stroke="#1F2430" stroke-width="1.8" fill="none" stroke-linecap="round"/>
            <circle cx="18" cy="6" r="2" fill="#FF7A45"/>
            <circle cx="42" cy="6" r="2" fill="#FF7A45"/>
            <ellipse cx="30" cy="35" rx="19" ry="16" fill="url(#beeBody)" stroke="#1F2430" stroke-width="2.4"/>
            <path d="M13.5 32 Q30 38 46.5 32 M15 41 Q30 47 45 41 M20 48 Q30 52 40 48" stroke="#1F2430" stroke-width="4.2" fill="none" stroke-linecap="round"/>
            <ellipse cx="23" cy="27" rx="6" ry="3.4" fill="#fff" opacity=".4"/>
            <circle cx="24" cy="30" r="4" fill="#fff" stroke="#1F2430" stroke-width="1.4"/>
            <circle cx="36" cy="30" r="4" fill="#fff" stroke="#1F2430" stroke-width="1.4"/>
            <circle cx="25" cy="30.5" r="2.1" fill="#1F2430"/>
            <circle cx="37" cy="30.5" r="2.1" fill="#1F2430"/>
            <circle cx="25.7" cy="29.6" r=".8" fill="#fff"/>
            <circle cx="37.7" cy="29.6" r=".8" fill="#fff"/>
            <ellipse cx="19" cy="35" rx="2.6" ry="1.6" fill="#FF8FA3" opacity=".7"/>
            <ellipse cx="41" cy="35" rx="2.6" ry="1.6" fill="#FF8FA3" opacity=".7"/>
            <path d="M27 36q3 3 6 0" stroke="#1F2430" stroke-width="2" fill="none" stroke-linecap="round"/>
        </svg>`;
    }

    function flowerSvg() {
        return `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="petalG" cx="50%" cy="30%" r="80%">
                    <stop offset="0" stop-color="#FFC2DC"/>
                    <stop offset="1" stop-color="#FF7FB5"/>
                </radialGradient>
                <radialGradient id="coreG" cx="40%" cy="35%" r="75%">
                    <stop offset="0" stop-color="#FFF08A"/>
                    <stop offset="1" stop-color="#FFB400"/>
                </radialGradient>
            </defs>
            <g stroke="#C2457F" stroke-width="1.3" fill="url(#petalG)">
                <ellipse cx="30" cy="14" rx="8" ry="11"/>
                <ellipse cx="30" cy="46" rx="8" ry="11"/>
                <ellipse cx="14" cy="30" rx="11" ry="8"/>
                <ellipse cx="46" cy="30" rx="11" ry="8"/>
                <ellipse cx="18.5" cy="18.5" rx="8" ry="10" transform="rotate(-45 18.5 18.5)"/>
                <ellipse cx="41.5" cy="18.5" rx="8" ry="10" transform="rotate(45 41.5 18.5)"/>
                <ellipse cx="18.5" cy="41.5" rx="8" ry="10" transform="rotate(45 18.5 41.5)"/>
                <ellipse cx="41.5" cy="41.5" rx="8" ry="10" transform="rotate(-45 41.5 41.5)"/>
            </g>
            <circle cx="30" cy="30" r="9.5" fill="url(#coreG)" stroke="#C2457F" stroke-width="1.3"/>
            <circle cx="27" cy="27" r="2.2" fill="#fff" opacity=".7"/>
            <g fill="#B46A00" opacity=".6"><circle cx="31" cy="31" r="1"/><circle cx="34" cy="28" r=".9"/><circle cx="27" cy="34" r=".9"/></g>
        </svg>`;
    }

    const JC = [
        ['flex-start', 'Başa Yasla'],
        ['center', 'Ortala'],
        ['flex-end', 'Sona Yasla'],
        ['space-between', 'Aralıklı (Kenar Boş)'],
        ['space-around', 'Aralıklı (Kenar Var)'],
        ['space-evenly', 'Eşit Aralıklı'],
    ];
    const AI = [
        ['flex-start', 'Yukarı Yasla'],
        ['center', 'Ortala'],
        ['flex-end', 'Aşağı Yasla'],
        ['stretch', 'Uzat'],
    ];
    const FD = [
        ['row', 'Yatay ➜'],
        ['column', 'Dikey ⬇'],
        ['row-reverse', 'Yatay Ters ⟵'],
        ['column-reverse', 'Dikey Ters ⬆'],
    ];

    function prop(key, cssName, label, opts) {
        return { key, cssName, label, options: opts.map(([value, text]) => ({ value, text })) };
    }

    // 100 bölümlük kategorili set (Kolay / Orta / Zor / Uzman) levels.js içinde tanımlıdır (tools/learn-levels/bee-garden.gen.mjs ile üretilir).
    const CATEGORY_LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
    const LEVELS = Array.isArray(window.BEE_GARDEN_LEVELS) ? window.BEE_GARDEN_LEVELS : [];

    const requestedStart = Math.floor(Number(query.get('from') || 1));
    const requestedEnd = Math.floor(Number(query.get('to') || LEVELS.length));
    const competitionStartIndex = competitionMode
        ? Math.max(0, Math.min(LEVELS.length - 1, requestedStart - 1))
        : 0;
    const competitionEndIndex = competitionMode
        ? Math.max(competitionStartIndex, Math.min(LEVELS.length - 1, requestedEnd - 1))
        : LEVELS.length - 1;
    let currentLevelIndex = competitionStartIndex;
    let levelStartedAt = Date.now();
    let rangeCompletionSent = false;
    let currentState = {};
    let currentTargetBee = 1;
    let currentTargets = [];
    let currentMode = 'blok';

    function loadProgress() {
        if (isStaff) return Math.max(0, LEVELS.length - 1);   // yönetici ve öğretmen: tüm bölümler açık
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : {};
            return Math.max(0, Math.min(LEVELS.length - 1, parsed.unlockedIndex || 0));
        } catch (_) {
            return 0;
        }
    }

    function saveProgress(index) {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : {};
            const unlockedIndex = Math.max(parsed.unlockedIndex || 0, index);
            localStorage.setItem(STORAGE_KEY, JSON.stringify({ unlockedIndex }));
        } catch (_) {}
    }

    function buildBees(container, count) {
        container.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            const bee = document.createElement('div');
            bee.className = 'bg-bee';
            bee.dataset.beeIndex = String(i);
            bee.innerHTML = beeSvg();
            container.appendChild(bee);
        }
    }

    function applyState(container, level, state) {
        container.style.display = 'flex';
        container.style.flexDirection = state.flexDirection || 'row';
        container.style.justifyContent = state.justifyContent || 'flex-start';
        container.style.alignItems = state.alignItems || 'flex-start';

        Array.from(container.children).forEach((child) => {
            child.style.alignSelf = '';
        });
        if (state.items) {
            Object.keys(state.items).forEach((idx) => {
                const child = container.querySelector('.bg-bee[data-bee-index="' + idx + '"]');
                if (child && state.items[idx].alignSelf) {
                    child.style.alignSelf = state.items[idx].alignSelf;
                }
            });
        }
    }

    function computeTargets(level) {
        buildBees(refGardenEl, level.beeCount);
        const solutionState = Object.assign({}, level.solution.container, { items: level.solution.items || {} });
        applyState(refGardenEl, level, solutionState);

        const refRect = refGardenEl.getBoundingClientRect();
        const targets = [];
        Array.from(refGardenEl.children).forEach((child) => {
            const r = child.getBoundingClientRect();
            const cx = r.left + r.width / 2 - refRect.left;
            const cy = r.top + r.height / 2 - refRect.top;
            targets.push({
                xPercent: (cx / refRect.width) * 100,
                yPercent: (cy / refRect.height) * 100,
            });
        });
        return targets;
    }

    function renderFlowers(targets) {
        gardenEl.querySelectorAll('.bg-flower').forEach((el) => el.remove());
        targets.forEach((t) => {
            const flower = document.createElement('div');
            flower.className = 'bg-flower';
            flower.style.left = t.xPercent + '%';
            flower.style.top = t.yPercent + '%';
            flower.innerHTML = flowerSvg();
            gardenEl.appendChild(flower);
        });
    }

    function evalSolved(containerRect, beeRects, beeEls) {
        const matches = [];
        let allMatch = true;
        beeEls.forEach((bee, i) => {
            const target = currentTargets[i];
            const r = beeRects[i];
            let matched = false;
            if (target && r) {
                const cx = ((r.left + r.width / 2 - containerRect.left) / containerRect.width) * 100;
                const cy = ((r.top + r.height / 2 - containerRect.top) / containerRect.height) * 100;
                matched = Math.abs(cx - target.xPercent) < 4 && Math.abs(cy - target.yPercent) < 4;
            }
            matches.push(matched);
            if (!matched) allMatch = false;
        });
        return { solved: allMatch && beeEls.length > 0, matches };
    }

    function commitSolved(beeEls, ev) {
        beeEls.forEach((bee, i) => bee.classList.toggle('is-landed', !!ev.matches[i]));
        if (ev.solved) onLevelSolved();
    }

    function checkSolved(containerRect, beeRects, beeEls) {
        if (!containerRect) {
            containerRect = gardenEl.getBoundingClientRect();
            beeEls = Array.from(gardenEl.children).filter((el) => el.classList.contains('bg-bee'));
            beeRects = beeEls.map((el) => el.getBoundingClientRect());
        }
        const ev = evalSolved(containerRect, beeRects, beeEls);
        commitSolved(beeEls, ev);
        return ev.solved;
    }

    // FLIP tekniği: arı(lar)ın eski konumunu kaydet, yeni CSS kuralını uygula
    // (flexbox anında yeniden yerleşir), sonra eski konumdan yeni konuma
    // yumuşak bir transform geçişiyle "uçarak" gitmesini sağla.
    function applyStateAnimated(container, level, state) {
        const bees = Array.from(container.children).filter((el) => el.classList.contains('bg-bee'));
        const firstRects = bees.map((b) => b.getBoundingClientRect());

        applyState(container, level, state);

        const containerRect = container.getBoundingClientRect();
        const lastRects = bees.map((b) => b.getBoundingClientRect());
        let flightMs = 0;
        const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        bees.forEach((bee, i) => {
            const dx = firstRects[i].left - lastRects[i].left;     // başlangıç konumu (şu anki yerleşime göre)
            const dy = firstRects[i].top - lastRects[i].top;
            const dist = Math.hypot(dx, dy);
            if (dist < 1 || reduce || typeof bee.animate !== 'function') return;

            const dur = Math.round(520 + Math.min(780, dist * 1.25));
            const lift = Math.min(70, 22 + dist * 0.2);              // yay yüksekliği
            const tilt = Math.max(-18, Math.min(18, -dx * 0.06));    // gidilen yöne doğru eğil
            bee.classList.add('is-flying');
            const anim = bee.animate([
                { transform: 'translate(' + dx + 'px,' + dy + 'px) rotate(0deg) scale(1)', offset: 0 },
                { transform: 'translate(' + (dx * 0.5) + 'px,' + (dy * 0.5 - lift) + 'px) rotate(' + tilt + 'deg) scale(1.12)', offset: 0.5 },
                { transform: 'translate(0px,0px) rotate(0deg) scale(1)', offset: 1 },
            ], { duration: dur, easing: 'cubic-bezier(.45,.05,.3,1)' });
            anim.onfinish = anim.oncancel = () => bee.classList.remove('is-flying');
            flightMs = Math.max(flightMs, dur);

            // uçuş izi: yay boyunca sönen ışıltılı noktalar
            const sx = firstRects[i].left + firstRects[i].width / 2 - containerRect.left;
            const sy = firstRects[i].top + firstRects[i].height / 2 - containerRect.top;
            const ex = lastRects[i].left + lastRects[i].width / 2 - containerRect.left;
            const ey = lastRects[i].top + lastRects[i].height / 2 - containerRect.top;
            const cx = (sx + ex) / 2, cy = (sy + ey) / 2 - lift * 2;  // ikinci derece Bézier kontrol noktası
            const N = 9;
            for (let k = 1; k <= N; k++) {
                const t = k / (N + 1), u = 1 - t;
                const px = u * u * sx + 2 * u * t * cx + t * t * ex;
                const py = u * u * sy + 2 * u * t * cy + t * t * ey;
                const dot = document.createElement('div');
                dot.className = 'bg-trail';
                dot.style.left = px + 'px';
                dot.style.top = py + 'px';
                container.appendChild(dot);
                const a2 = dot.animate([{ opacity: 0, transform: 'translate(-50%,-50%) scale(.4)' }, { opacity: 0.95, transform: 'translate(-50%,-50%) scale(1)', offset: 0.25 }, { opacity: 0, transform: 'translate(-50%,-50%) scale(.3)' }], { duration: 900, delay: t * dur * 0.85, fill: 'backwards' });
                a2.onfinish = a2.oncancel = () => dot.remove();
            }
        });

        return { bees, containerRect, lastRects, flightMs };
    }

    let solvedForLevel = false;
    function onLevelSolved() {
        if (solvedForLevel) return;
        solvedForLevel = true;
        winToastEl.classList.add('show');
        if (competitionMode) {
            const level = LEVELS[currentLevelIndex];
            const levelXp = Math.max(0, Number(level?.xp ?? 0));
            window.parent?.postMessage({
                type: 'LEVEL_COMPLETED',
                source: 'bee-garden-runner',
                levelId: currentLevelIndex + 1,
                currentLevelIndex,
                xp: levelXp,
                duration: Math.max(0, Date.now() - levelStartedAt),
            }, '*');
            if (currentLevelIndex >= competitionEndIndex && !rangeCompletionSent) {
                rangeCompletionSent = true;
                window.parent?.postMessage({
                    type: 'ASSIGNMENT_RANGE_COMPLETED',
                    source: 'bee-garden-runner',
                    currentLevelIndex,
                    xp: 0,
                }, '*');
            }
        }
        saveProgress(Math.min(LEVELS.length - 1, currentLevelIndex + 1));
        updateNav();
        setTimeout(() => {
            if (currentLevelIndex < competitionEndIndex) {
                goToLevel(currentLevelIndex + 1);
            }
        }, 1400);
    }

    function generateCodeText() {
        const level = LEVELS[currentLevelIndex];
        let lines = [];
        (level.properties || []).forEach((p) => {
            if (currentState[p.key]) {
                const codeValue = VALUE_TO_CODE[currentState[p.key]] || currentState[p.key];
                lines.push('  ' + p.cssName + ': ' + codeValue + ';');
            }
        });
        let text = '.bahce {\n' + lines.join('\n') + (lines.length ? '\n' : '') + '}';
        if (level.itemProperty && currentState.items && Object.keys(currentState.items).length) {
            Object.keys(currentState.items).forEach((idx) => {
                const val = currentState.items[idx][level.itemProperty.key];
                if (val) {
                    const codeValue = VALUE_TO_CODE[val] || val;
                    text += '\n\n.ari-' + idx + ' {\n  ' + level.itemProperty.cssName + ': ' + codeValue + ';\n}';
                }
            });
        }
        return text;
    }

    function refreshPreview() {
        codePreview.textContent = generateCodeText();
    }

    let checkToken = 0;
    function applyAndCheck() {
        const level = LEVELS[currentLevelIndex];
        const { bees, containerRect, lastRects, flightMs } = applyStateAnimated(gardenEl, level, currentState);
        refreshPreview();
        const ev = evalSolved(containerRect, lastRects, bees);
        const token = ++checkToken;
        bees.forEach((b) => b.classList.remove('is-landed'));
        if (flightMs > 0) setTimeout(() => { if (token === checkToken) commitSolved(bees, ev); }, flightMs + 60);
        else commitSolved(bees, ev);
        return ev.solved;
    }

    // ---- Sürükle-bırak (pointer events ile, fare + dokunmatik ortak) ----
    // Blockly/Scratch mantığı: her blok kendi kategorisinin rengini taşır ve
    // bırakıldığı yuvaya (dropzone) "snap" (klik) hissi veren kısa bir animasyonla oturur.
    let dragInfo = null;

    function makeChipDraggable(chip, value, text, color, onAssign) {
        chip.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            const ghost = document.createElement('div');
            ghost.className = 'bg-chip-ghost';
            ghost.textContent = text;
            ghost.style.background = color;
            document.body.appendChild(ghost);
            dragInfo = { value, onAssign, ghost, chip };
            chip.classList.add('is-dragging');
            positionGhost(e.clientX, e.clientY);
            window.addEventListener('pointermove', onDragMove);
            window.addEventListener('pointerup', onDragEnd, { once: true });
        });
        // Klavye/erişilebilirlik icin: tikla da atansin.
        chip.addEventListener('click', () => onAssign(value));
    }

    function positionGhost(x, y) {
        if (!dragInfo) return;
        dragInfo.ghost.style.left = x + 'px';
        dragInfo.ghost.style.top = y + 'px';
    }

    function onDragMove(e) {
        if (!dragInfo) return;
        positionGhost(e.clientX, e.clientY);
        document.querySelectorAll('.bg-dropzone').forEach((dz) => dz.classList.remove('drag-over'));
        const el = document.elementFromPoint(e.clientX, e.clientY);
        const dz = el ? el.closest('.bg-dropzone') : null;
        if (dz) dz.classList.add('drag-over');
    }

    function onDragEnd(e) {
        if (!dragInfo) return;
        const el = document.elementFromPoint(e.clientX, e.clientY);
        const dz = el ? el.closest('.bg-dropzone') : null;
        document.querySelectorAll('.bg-dropzone').forEach((d) => d.classList.remove('drag-over'));
        dragInfo.chip.classList.remove('is-dragging');
        dragInfo.ghost.remove();
        window.removeEventListener('pointermove', onDragMove);
        if (dz) {
            dz.classList.add('snap-pulse');
            setTimeout(() => dz.classList.remove('snap-pulse'), 400);
            dragInfo.onAssign(dragInfo.value);
        }
        dragInfo = null;
    }

    function buildDropzoneRow(p, currentValue) {
        const color = (PROP_COLORS[p.key] || {}).hex || 'var(--violet)';
        const row = document.createElement('div');
        row.className = 'bg-dropzone-row';
        const label = document.createElement('span');
        label.className = 'bg-dropzone-label';
        label.textContent = p.cssName + ':';
        const zone = document.createElement('div');
        zone.className = 'bg-dropzone' + (currentValue ? ' has-value' : '');
        if (currentValue) {
            zone.style.background = color;
            zone.style.borderColor = color;
        }
        const optionText = p.options.find((o) => o.value === currentValue);
        zone.textContent = optionText ? optionText.text : 'Bir blok sürükle';
        row.appendChild(label);
        row.appendChild(zone);
        return row;
    }

    function buildChipGroup(p, currentValue, onAssign) {
        const color = (PROP_COLORS[p.key] || {}).hex || '#5B3DF5';
        const group = document.createElement('div');
        group.className = 'bg-block-group';
        group.dataset.propColor = (PROP_COLORS[p.key] || {}).name || '';
        const h3 = document.createElement('h3');
        h3.textContent = p.label;
        group.appendChild(h3);

        group.appendChild(buildDropzoneRow(p, currentValue));

        const chips = document.createElement('div');
        chips.className = 'bg-block-chips';
        p.options.forEach((opt) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'bg-chip' + (currentValue === opt.value ? ' active' : '');
            chip.textContent = opt.text;
            makeChipDraggable(chip, opt.value, opt.text, color, onAssign);
            chips.appendChild(chip);
        });
        group.appendChild(chips);
        return group;
    }

    function renderBlockPanel() {
        const level = LEVELS[currentLevelIndex];
        blockPanel.innerHTML = '';

        (level.properties || []).forEach((p) => {
            const group = buildChipGroup(p, currentState[p.key], (value) => {
                currentState[p.key] = value;
                renderBlockPanel();
                applyAndCheck();
            });
            blockPanel.appendChild(group);
        });

        if (level.itemProperty) {
            const currentVal = (currentState.items && currentState.items[currentTargetBee] && currentState.items[currentTargetBee][level.itemProperty.key]) || '';
            const group = buildChipGroup(level.itemProperty, currentVal, (value) => {
                currentState.items = currentState.items || {};
                currentState.items[currentTargetBee] = currentState.items[currentTargetBee] || {};
                currentState.items[currentTargetBee][level.itemProperty.key] = value;
                renderBlockPanel();
                applyAndCheck();
            });

            const targetRow = document.createElement('div');
            targetRow.className = 'bg-block-target';
            const label = document.createElement('span');
            label.textContent = 'Hangi arı?';
            const select = document.createElement('select');
            for (let i = 1; i <= level.beeCount; i++) {
                const o = document.createElement('option');
                o.value = String(i);
                o.textContent = i + '. arı';
                if (i === currentTargetBee) o.selected = true;
                select.appendChild(o);
            }
            select.addEventListener('change', () => {
                currentTargetBee = parseInt(select.value, 10);
                renderBlockPanel();
            });
            targetRow.appendChild(label);
            targetRow.appendChild(select);
            // "Hangi arı?" seçicisi başlıktan hemen sonra, yuvadan önce gelmeli.
            group.insertBefore(targetRow, group.children[1]);
            blockPanel.appendChild(group);
        }
    }

    function parseCodeIntoState(text) {
        const level = LEVELS[currentLevelIndex];
        const propByCssName = {};
        (level.properties || []).forEach((p) => { propByCssName[p.cssName] = p.key; });

        let recognizedCount = 0;

        const containerMatch = text.match(/\.bahce\s*{([^}]*)}/);
        if (containerMatch) {
            const body = containerMatch[1];
            const declRe = /([a-zA-Z-]+)\s*:\s*([a-zA-Z0-9-]+)\s*;?/g;
            let m;
            while ((m = declRe.exec(body))) {
                const cssName = m[1].trim();
                const codeValue = m[2].trim();
                if (propByCssName[cssName] && CODE_TO_VALUE[codeValue]) {
                    currentState[propByCssName[cssName]] = CODE_TO_VALUE[codeValue];
                    recognizedCount++;
                }
            }
        }

        if (level.itemProperty) {
            const itemRe = /\.ari-(\d+)\s*{([^}]*)}/g;
            let im;
            while ((im = itemRe.exec(text))) {
                const idx = im[1];
                const body = im[2];
                const declRe = /([a-zA-Z-]+)\s*:\s*([a-zA-Z0-9-]+)\s*;?/g;
                let m2;
                while ((m2 = declRe.exec(body))) {
                    const itemCodeValue = m2[2].trim();
                    if (m2[1].trim() === level.itemProperty.cssName && CODE_TO_VALUE[itemCodeValue]) {
                        currentState.items = currentState.items || {};
                        currentState.items[idx] = currentState.items[idx] || {};
                        currentState.items[idx][level.itemProperty.key] = CODE_TO_VALUE[itemCodeValue];
                        recognizedCount++;
                    }
                }
            }
        }

        return recognizedCount > 0;
    }

    function setMode(mode) {
        currentMode = mode;
        modeTabs.forEach((t) => t.classList.toggle('active', t.getAttribute('data-mode') === mode));
        blockPanel.hidden = mode !== 'blok';
        codePanel.hidden = mode !== 'kod';
        if (mode === 'kod') {
            codeArea.value = generateCodeText();
            runStatusEl.textContent = '';
            runStatusEl.className = 'bg-run-status';
            syncCodeGutter();
        }
    }

    modeTabs.forEach((tab) => {
        tab.addEventListener('click', () => setMode(tab.getAttribute('data-mode')));
    });

    function syncCodeGutter() {
        if (!codeGutter) return;
        const lineCount = Math.max(1, codeArea.value.split('\n').length);
        let lines = '';
        for (let i = 1; i <= lineCount; i++) lines += (i > 1 ? '\n' : '') + i;
        codeGutter.textContent = lines;
        codeGutter.scrollTop = codeArea.scrollTop;
    }

    codeArea.addEventListener('input', () => {
        runStatusEl.textContent = '';
        runStatusEl.className = 'bg-run-status';
        syncCodeGutter();
    });
    codeArea.addEventListener('scroll', () => {
        if (codeGutter) codeGutter.scrollTop = codeArea.scrollTop;
    });

    runBtn.addEventListener('click', () => {
        const recognized = parseCodeIntoState(codeArea.value);
        if (!recognized) {
            runStatusEl.textContent = 'Kod tanınmadı. Örnek: yatay-hizala: ortaya;';
            runStatusEl.className = 'bg-run-status is-fail';
            return;
        }
        const solved = applyAndCheck();
        if (solved) {
            runStatusEl.textContent = '✅ Doğru! Arı çiçeğe kondu.';
            runStatusEl.className = 'bg-run-status is-ok';
        } else {
            runStatusEl.textContent = 'Çalıştı ama henüz doğru değil, tekrar dene.';
            runStatusEl.className = 'bg-run-status is-fail';
        }
    });

    resetBtn.addEventListener('click', () => {
        goToLevel(currentLevelIndex, true);
    });

    function updateNav() {
        const unlockedIndex = loadProgress();
        levelLabelEl.textContent = 'Seviye ' + (currentLevelIndex + 1) + ' / ' + LEVELS.length + (LEVELS[currentLevelIndex] && LEVELS[currentLevelIndex].category ? ' · ' + (CATEGORY_LABEL[LEVELS[currentLevelIndex].category] || '') : '');
        prevBtn.disabled = currentLevelIndex <= competitionStartIndex;
        nextBtn.disabled = currentLevelIndex >= Math.min(unlockedIndex, competitionEndIndex);
    }

    function goToLevel(index, forceReset) {
        checkToken++;
        currentLevelIndex = Math.max(competitionStartIndex, Math.min(competitionEndIndex, index));
        levelStartedAt = Date.now();
        const level = LEVELS[currentLevelIndex];
        solvedForLevel = false;
        winToastEl.classList.remove('show');
        currentState = {};
        currentTargetBee = 1;

        taskTitleEl.textContent = level.title;
        taskDescEl.textContent = level.desc;

        if (level.tutorial) {
            tutorialText.innerHTML = level.tutorial;
            tutorialCard.hidden = false;
        } else {
            tutorialCard.hidden = true;
        }

        buildBees(gardenEl, level.beeCount);
        currentTargets = computeTargets(level);
        renderFlowers(currentTargets);
        applyState(gardenEl, level, currentState);
        renderBlockPanel();
        refreshPreview();
        if (currentMode === 'kod') {
            codeArea.value = generateCodeText();
            syncCodeGutter();
        }
        runStatusEl.textContent = '';
        runStatusEl.className = 'bg-run-status';
        updateNav();
    }

    // ---- bölüm seçici (Kolay / Orta / Zor / Uzman) ----
    const pickerEl = document.getElementById('bgPicker');
    const pickerBodyEl = document.getElementById('bgPickerBody');
    const levelsBtn = document.getElementById('bgLevelsBtn');

    function openPicker() {
        if (!pickerEl || !pickerBodyEl) return;
        pickerBodyEl.innerHTML = '';
        const unlocked = competitionMode ? competitionEndIndex : loadProgress();
        const groups = [];
        LEVELS.forEach((lv, i) => {
            if (i < competitionStartIndex || i > competitionEndIndex) return;
            const cat = lv.category || 'easy';
            let g = groups.find((x) => x.cat === cat);
            if (!g) { g = { cat, items: [] }; groups.push(g); }
            g.items.push({ lv, i });
        });
        groups.forEach((g) => {
            const sec = document.createElement('div');
            sec.className = 'bg-pk-group cat-' + g.cat;
            const h = document.createElement('h4');
            h.textContent = (CATEGORY_LABEL[g.cat] || g.cat) + ' · ' + g.items.length + ' bölüm';
            sec.appendChild(h);
            const grid = document.createElement('div');
            grid.className = 'bg-pk-grid';
            g.items.forEach((x) => {
                const b = document.createElement('button');
                b.type = 'button';
                const open = competitionMode ? true : x.i <= unlocked;
                const done = !competitionMode && x.i < unlocked;
                b.className = 'bg-pk-tile' + (done ? ' done' : '') + (x.i === currentLevelIndex ? ' current' : '');
                b.disabled = !open;
                b.innerHTML = (open ? '' : '<span class="bg-pk-lock">🔒</span>') + '<span class="bg-pk-num">' + (x.i + 1) + '</span><span class="bg-pk-bees">🐝 ' + x.lv.beeCount + '</span>';
                b.addEventListener('click', () => { goToLevel(x.i); closePicker(); });
                grid.appendChild(b);
            });
            sec.appendChild(grid);
            pickerBodyEl.appendChild(sec);
        });
        pickerEl.hidden = false;
        const cur = pickerBodyEl.querySelector('.bg-pk-tile.current');
        if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'center' });
    }
    function closePicker() { if (pickerEl) pickerEl.hidden = true; }
    if (levelsBtn) levelsBtn.addEventListener('click', openPicker);
    if (pickerEl) pickerEl.addEventListener('click', (e) => { if (e.target && e.target.hasAttribute && e.target.hasAttribute('data-close')) closePicker(); });

    prevBtn.addEventListener('click', () => goToLevel(currentLevelIndex - 1));
    nextBtn.addEventListener('click', () => {
        const unlockedIndex = loadProgress();
        if (currentLevelIndex < Math.min(unlockedIndex, competitionEndIndex)) goToLevel(currentLevelIndex + 1);
    });

    window.addEventListener('resize', () => {
        currentTargets = computeTargets(LEVELS[currentLevelIndex]);
        renderFlowers(currentTargets);
        checkSolved();
    });

    goToLevel(competitionMode ? competitionStartIndex : loadProgress());

    // Açılış tanıtım kutusu: oyun her açıldığında animasyonlu şekilde belirir
    // ve kullanıcı "Anladım, Başla!" demeden altındaki oyunla etkileşime
    // izin vermez (overlay tüm ekranı kaplayıp pointer-events'i yakalar).
    if (introOverlay && introStartBtn) {
        requestAnimationFrame(() => introOverlay.classList.add('show'));
        introStartBtn.addEventListener('click', () => {
            introOverlay.classList.remove('show');
        });
    }
})();
