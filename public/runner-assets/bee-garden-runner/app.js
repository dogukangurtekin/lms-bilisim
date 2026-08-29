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

    const STORAGE_KEY = 'bee_garden_progress_v1';

    function beeSvg() {
        return `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="22" cy="24" rx="10" ry="7" fill="rgba(0,0,0,.12)" />
            <ellipse cx="38" cy="24" rx="10" ry="7" fill="rgba(0,0,0,.12)" />
            <ellipse cx="22" cy="22" rx="10" ry="7" fill="#FFFDF5" stroke="#E7E2CE" stroke-width="1"/>
            <ellipse cx="38" cy="22" rx="10" ry="7" fill="#FFFDF5" stroke="#E7E2CE" stroke-width="1"/>
            <ellipse cx="30" cy="34" rx="18" ry="15" fill="#FFC93C" stroke="#1F2430" stroke-width="2.2"/>
            <path d="M14 30h32M17 38h26M22 45h16" stroke="#1F2430" stroke-width="4" stroke-linecap="round"/>
            <circle cx="24" cy="30" r="3.4" fill="#1F2430"/>
            <circle cx="36" cy="30" r="3.4" fill="#1F2430"/>
            <circle cx="25" cy="29" r="1" fill="#fff"/>
            <circle cx="37" cy="29" r="1" fill="#fff"/>
            <path d="M27 36q3 3 6 0" stroke="#1F2430" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M12 18c-4-4-4-10 2-11 4-1 6 4 4 8" fill="#1F2430" opacity=".15"/>
            <path d="M48 18c4-4 4-10-2-11-4-1-6 4-4 8" fill="#1F2430" opacity=".15"/>
        </svg>`;
    }

    function flowerSvg() {
        return `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
            <g stroke="#B8447A" stroke-width="1.5">
                <ellipse cx="30" cy="16" rx="9" ry="12" fill="#FF8FC0"/>
                <ellipse cx="30" cy="44" rx="9" ry="12" fill="#FF8FC0"/>
                <ellipse cx="16" cy="30" rx="12" ry="9" fill="#FFB1D3"/>
                <ellipse cx="44" cy="30" rx="12" ry="9" fill="#FFB1D3"/>
            </g>
            <circle cx="30" cy="30" r="9" fill="#FFD84D" stroke="#B8447A" stroke-width="1.5"/>
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

    const LEVELS = [
        {
            title: 'Ortaya Kondur',
            desc: 'Arıyı bahçenin yatayda tam ortasına kondur. "justify-content" özelliğini "center" yap.',
            beeCount: 1,
            properties: [prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC.slice(0, 3))],
            solution: { container: { justifyContent: 'center' } },
        },
        {
            title: 'Sona Kondur',
            desc: 'Arıyı bahçenin sağ tarafına (sonuna) kondur.',
            beeCount: 1,
            properties: [prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC.slice(0, 3))],
            solution: { container: { justifyContent: 'flex-end' } },
        },
        {
            title: 'Başa Kondur',
            desc: 'Arıyı bahçenin sol tarafına (başına) kondur.',
            beeCount: 1,
            properties: [prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC.slice(0, 3))],
            solution: { container: { justifyContent: 'flex-start' } },
        },
        {
            title: 'Yukarı-Aşağı Ortala',
            desc: 'Bu sefer dikeyde ortala. "align-items" özelliğini "center" yap.',
            beeCount: 1,
            properties: [prop('alignItems', 'align-items', 'Dikey Hizalama (align-items)', AI.slice(0, 3))],
            solution: { container: { alignItems: 'center' } },
        },
        {
            title: 'Aşağı Kondur',
            desc: 'Arıyı bahçenin altına kondur.',
            beeCount: 1,
            properties: [prop('alignItems', 'align-items', 'Dikey Hizalama (align-items)', AI.slice(0, 3))],
            solution: { container: { alignItems: 'flex-end' } },
        },
        {
            title: 'Tam Ortaya!',
            desc: 'Şimdi iki özelliği birden kullan: hem yatayda hem dikeyde tam ortala.',
            beeCount: 1,
            properties: [
                prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC.slice(0, 3)),
                prop('alignItems', 'align-items', 'Dikey Hizalama (align-items)', AI.slice(0, 3)),
            ],
            solution: { container: { justifyContent: 'center', alignItems: 'center' } },
        },
        {
            title: 'Sağ Alt Köşe',
            desc: 'Arıyı bahçenin sağ alt köşesine kondur.',
            beeCount: 1,
            properties: [
                prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC.slice(0, 3)),
                prop('alignItems', 'align-items', 'Dikey Hizalama (align-items)', AI.slice(0, 3)),
            ],
            solution: { container: { justifyContent: 'flex-end', alignItems: 'flex-end' } },
        },
        {
            title: 'Sol Alt Köşe',
            desc: 'Arıyı bahçenin sol alt köşesine kondur.',
            beeCount: 1,
            properties: [
                prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC.slice(0, 3)),
                prop('alignItems', 'align-items', 'Dikey Hizalama (align-items)', AI.slice(0, 3)),
            ],
            solution: { container: { justifyContent: 'flex-start', alignItems: 'flex-end' } },
        },
        {
            title: 'Üç Arı, Eşit Aralık',
            desc: '3 arı var. Onları aralarında eşit boşlukla, kenarlarda boşluk bırakmadan diz. "space-between" kullan.',
            beeCount: 3,
            properties: [prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC)],
            solution: { container: { justifyContent: 'space-between' } },
        },
        {
            title: 'Üç Arı, Kenarlar da Boşluklu',
            desc: 'Bu sefer kenarlarda da boşluk olsun. "space-around" kullan.',
            beeCount: 3,
            properties: [prop('justifyContent', 'justify-content', 'Yatay Hizalama (justify-content)', JC)],
            solution: { container: { justifyContent: 'space-around' } },
        },
        {
            title: 'Yön Değiştir: Dikey',
            desc: 'Arılar artık yukarıdan aşağıya doğru sıralanacak. "flex-direction" özelliğini "column" yap, sonra ortala.',
            beeCount: 1,
            properties: [
                prop('flexDirection', 'flex-direction', 'Yön (flex-direction)', FD.slice(0, 2)),
                prop('justifyContent', 'justify-content', 'Ana Eksen Hizalama (justify-content)', JC.slice(0, 3)),
            ],
            solution: { container: { flexDirection: 'column', justifyContent: 'center' } },
        },
        {
            title: 'Dikeyde Kenara Yasla',
            desc: 'Dikey yönde, arıyı sağ tarafa yasla.',
            beeCount: 1,
            properties: [
                prop('flexDirection', 'flex-direction', 'Yön (flex-direction)', FD.slice(0, 2)),
                prop('alignItems', 'align-items', 'Yan Eksen Hizalama (align-items)', AI.slice(0, 3)),
            ],
            solution: { container: { flexDirection: 'column', alignItems: 'flex-end' } },
        },
        {
            title: 'Farklı Çiçek, Farklı Kural',
            desc: '3 arı da üstte duruyor ama 2. arı farklı bir çiçeğe konacak. Ona özel bir kural (align-self) yaz.',
            beeCount: 3,
            properties: [
                prop('alignItems', 'align-items', 'Dikey Hizalama (align-items)', AI.slice(0, 3)),
            ],
            itemProperty: prop('alignSelf', 'align-self', 'Seçili Arının Kendi Hizalaması (align-self)', AI.slice(0, 3)),
            solution: { container: { alignItems: 'flex-start' }, items: { 2: { alignSelf: 'flex-end' } } },
        },
        {
            title: 'Son Görev: Ters ve Aralıklı',
            desc: 'En zor görev! Yönü ters çevir ve aralarında eşit boşluk bırak.',
            beeCount: 3,
            properties: [
                prop('flexDirection', 'flex-direction', 'Yön (flex-direction)', FD),
                prop('justifyContent', 'justify-content', 'Ana Eksen Hizalama (justify-content)', JC),
            ],
            solution: { container: { flexDirection: 'column-reverse', justifyContent: 'space-between' } },
        },
    ];

    let currentLevelIndex = 0;
    let currentState = {};
    let currentTargetBee = 1;
    let currentTargets = [];
    let currentMode = 'blok';

    function loadProgress() {
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

    function checkSolved() {
        const rect = gardenEl.getBoundingClientRect();
        const bees = Array.from(gardenEl.children).filter((el) => el.classList.contains('bg-bee'));
        let allMatch = true;
        bees.forEach((bee, i) => {
            const target = currentTargets[i];
            if (!target) return;
            const r = bee.getBoundingClientRect();
            const cx = ((r.left + r.width / 2 - rect.left) / rect.width) * 100;
            const cy = ((r.top + r.height / 2 - rect.top) / rect.height) * 100;
            const dx = Math.abs(cx - target.xPercent);
            const dy = Math.abs(cy - target.yPercent);
            const matched = dx < 4 && dy < 4;
            if (!matched) allMatch = false;
            bee.classList.toggle('is-landed', matched);
        });
        const solved = allMatch && bees.length > 0;
        if (solved) {
            onLevelSolved();
        }
        return solved;
    }

    let solvedForLevel = false;
    function onLevelSolved() {
        if (solvedForLevel) return;
        solvedForLevel = true;
        winToastEl.classList.add('show');
        saveProgress(Math.min(LEVELS.length - 1, currentLevelIndex + 1));
        updateNav();
        setTimeout(() => {
            if (currentLevelIndex < LEVELS.length - 1) {
                goToLevel(currentLevelIndex + 1);
            }
        }, 1400);
    }

    function generateCodeText() {
        const level = LEVELS[currentLevelIndex];
        let lines = [];
        (level.properties || []).forEach((p) => {
            if (currentState[p.key]) {
                lines.push('  ' + p.cssName + ': ' + currentState[p.key] + ';');
            }
        });
        let text = '.bahce {\n' + lines.join('\n') + (lines.length ? '\n' : '') + '}';
        if (level.itemProperty && currentState.items && Object.keys(currentState.items).length) {
            Object.keys(currentState.items).forEach((idx) => {
                const val = currentState.items[idx][level.itemProperty.key];
                if (val) {
                    text += '\n\n.ari-' + idx + ' {\n  ' + level.itemProperty.cssName + ': ' + val + ';\n}';
                }
            });
        }
        return text;
    }

    function refreshPreview() {
        codePreview.textContent = generateCodeText();
    }

    function applyAndCheck() {
        const level = LEVELS[currentLevelIndex];
        applyState(gardenEl, level, currentState);
        refreshPreview();
        return checkSolved();
    }

    // ---- Sürükle-bırak (pointer events ile, fare + dokunmatik ortak) ----
    let dragInfo = null;

    function makeChipDraggable(chip, value, text, onAssign) {
        chip.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            const ghost = document.createElement('div');
            ghost.className = 'bg-chip-ghost';
            ghost.textContent = text;
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
            dragInfo.onAssign(dragInfo.value);
        }
        dragInfo = null;
    }

    function buildDropzoneRow(p, currentValue) {
        const row = document.createElement('div');
        row.className = 'bg-dropzone-row';
        const label = document.createElement('span');
        label.className = 'bg-dropzone-label';
        label.textContent = p.cssName + ':';
        const zone = document.createElement('div');
        zone.className = 'bg-dropzone' + (currentValue ? ' has-value' : '');
        const optionText = p.options.find((o) => o.value === currentValue);
        zone.textContent = optionText ? optionText.text : 'Bir blok sürükle';
        row.appendChild(label);
        row.appendChild(zone);
        return row;
    }

    function renderBlockPanel() {
        const level = LEVELS[currentLevelIndex];
        blockPanel.innerHTML = '';

        (level.properties || []).forEach((p) => {
            const group = document.createElement('div');
            group.className = 'bg-block-group';
            const h3 = document.createElement('h3');
            h3.textContent = p.label;
            group.appendChild(h3);

            group.appendChild(buildDropzoneRow(p, currentState[p.key]));

            const chips = document.createElement('div');
            chips.className = 'bg-block-chips';
            p.options.forEach((opt) => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'bg-chip' + (currentState[p.key] === opt.value ? ' active' : '');
                chip.textContent = opt.text;
                makeChipDraggable(chip, opt.value, opt.text, (value) => {
                    currentState[p.key] = value;
                    renderBlockPanel();
                    applyAndCheck();
                });
                chips.appendChild(chip);
            });
            group.appendChild(chips);
            blockPanel.appendChild(group);
        });

        if (level.itemProperty) {
            const group = document.createElement('div');
            group.className = 'bg-block-group';
            const h3 = document.createElement('h3');
            h3.textContent = level.itemProperty.label;
            group.appendChild(h3);

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
            group.appendChild(targetRow);

            const currentVal = (currentState.items && currentState.items[currentTargetBee] && currentState.items[currentTargetBee][level.itemProperty.key]) || '';
            group.appendChild(buildDropzoneRow(level.itemProperty, currentVal));

            const chips = document.createElement('div');
            chips.className = 'bg-block-chips';
            level.itemProperty.options.forEach((opt) => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'bg-chip' + (currentVal === opt.value ? ' active' : '');
                chip.textContent = opt.text;
                makeChipDraggable(chip, opt.value, opt.text, (value) => {
                    currentState.items = currentState.items || {};
                    currentState.items[currentTargetBee] = currentState.items[currentTargetBee] || {};
                    currentState.items[currentTargetBee][level.itemProperty.key] = value;
                    renderBlockPanel();
                    applyAndCheck();
                });
                chips.appendChild(chip);
            });
            group.appendChild(chips);
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
                const value = m[2].trim();
                if (propByCssName[cssName]) {
                    currentState[propByCssName[cssName]] = value;
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
                    if (m2[1].trim() === level.itemProperty.cssName) {
                        currentState.items = currentState.items || {};
                        currentState.items[idx] = currentState.items[idx] || {};
                        currentState.items[idx][level.itemProperty.key] = m2[2].trim();
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
        }
    }

    modeTabs.forEach((tab) => {
        tab.addEventListener('click', () => setMode(tab.getAttribute('data-mode')));
    });

    codeArea.addEventListener('input', () => {
        runStatusEl.textContent = '';
        runStatusEl.className = 'bg-run-status';
    });

    runBtn.addEventListener('click', () => {
        const recognized = parseCodeIntoState(codeArea.value);
        if (!recognized) {
            runStatusEl.textContent = 'Kod tanınmadı. Örnek: justify-content: center;';
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
        levelLabelEl.textContent = 'Seviye ' + (currentLevelIndex + 1) + ' / ' + LEVELS.length;
        prevBtn.disabled = currentLevelIndex <= 0;
        nextBtn.disabled = currentLevelIndex >= unlockedIndex;
    }

    function goToLevel(index, forceReset) {
        currentLevelIndex = Math.max(0, Math.min(LEVELS.length - 1, index));
        const level = LEVELS[currentLevelIndex];
        solvedForLevel = false;
        winToastEl.classList.remove('show');
        currentState = {};
        currentTargetBee = 1;

        taskTitleEl.textContent = level.title;
        taskDescEl.textContent = level.desc;

        buildBees(gardenEl, level.beeCount);
        currentTargets = computeTargets(level);
        renderFlowers(currentTargets);
        applyState(gardenEl, level, currentState);
        renderBlockPanel();
        refreshPreview();
        if (currentMode === 'kod') codeArea.value = generateCodeText();
        runStatusEl.textContent = '';
        runStatusEl.className = 'bg-run-status';
        updateNav();
    }

    prevBtn.addEventListener('click', () => goToLevel(currentLevelIndex - 1));
    nextBtn.addEventListener('click', () => {
        const unlockedIndex = loadProgress();
        if (currentLevelIndex < unlockedIndex) goToLevel(currentLevelIndex + 1);
    });

    window.addEventListener('resize', () => {
        currentTargets = computeTargets(LEVELS[currentLevelIndex]);
        renderFlowers(currentTargets);
        checkSolved();
    });

    goToLevel(loadProgress());
})();
