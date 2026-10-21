import { FlamestoneGame, BUILTIN_LEVELS } from './game.js';
import { LevelEditor } from './editor.js';

async function fetchJson(url, options = {}) {
  const res = await fetch(url, {
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      ...(options.method && options.method !== 'GET' ? { 'Content-Type': 'application/json' } : {}),
      ...(options.headers || {}),
    },
    ...options,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data?.ok === false) throw new Error(data?.message || `HTTP ${res.status}`);
  return data;
}

function uniqLevels(items) {
  const seen = new Set();
  const out = [];
  for (const lv of items) {
    const key = `${lv.name}::${JSON.stringify(lv.data || {})}`;
    if (seen.has(key)) continue;
    seen.add(key);
    out.push(lv);
  }
  return out;
}

function bindGamePage() {
  const root = document.getElementById('flamestone-game-root');
  if (!root) return;

  const canvas = document.getElementById('flame-canvas');
  const levelTitle = document.getElementById('flame-level-title');
  const levelMeta = document.getElementById('flame-level-meta');
  const stepsEl = document.getElementById('flame-steps');
  const timeEl = document.getElementById('flame-time');
  const pickerBtn = document.getElementById('flame-level-picker');
  const pickerName = document.getElementById('flame-picker-name');
  const pickerMeta = document.getElementById('flame-picker-meta');
  const prevLevelBtn = document.getElementById('flame-level-prev');
  const nextLevelBtn = document.getElementById('flame-level-next');
  const pickerModal = document.getElementById('flame-picker-modal');
  const pickerBody = document.getElementById('flame-picker-body');
  const restartBtn = document.getElementById('flame-restart-btn');
  const nextBtn = document.getElementById('flame-next-btn');
  const fsBtn = document.getElementById('flame-fullscreen-btn');
  const toastEl = document.getElementById('flame-toast');
  const controls = root.querySelectorAll('#flame-mobile-controls [data-dir]');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const levelsUrl = root.dataset.levelsUrl;
  const scoreUrl = root.dataset.scoreUrl;
  const storageKey = 'flamestone_last_level_id_v1';
  const params = new URLSearchParams(window.location.search);
  const fromRaw = Number(params.get('from') || params.get('levelStart') || 0);
  const hasQueryRange = Number.isFinite(fromRaw) && fromRaw > 0;
  const assignmentId = String(params.get('assignmentId') || '').trim();
  const needsGrant = hasQueryRange || !!assignmentId || params.get('grant') === '1' || params.get('enforceGrant') === '1';
  const isStudent = String(document.body.className || '').includes('role-student');

  let allLevels = [];
  let levels = [];
  let current = 0;
  let allowedRange = null; // 1-based
  const completedLevelNos = new Set();
  let levelStartedAt = Date.now();
  let levelTimer = null;

  const detectDifficulty = (name = '', index = 0) => {
    const n = String(name || '').toLowerCase();
    if (n.includes('kolay') || n.includes('easy')) return 'easy';
    if (n.includes('orta') || n.includes('medium')) return 'medium';
    if (n.includes('zor') || n.includes('hard')) return 'hard';
    if (index < 18) return 'easy';
    if (index < 35) return 'medium';
    return 'hard';
  };

  // Herkese açık sürüm: bölümler sırayla açılır, tamamlananlar tarayıcıda saklanır.
  const PUBLIC = !!window.FLAMESTONE_PUBLIC;
  const publicKey = 'flamestone_public_done_v1';
  const publicDone = new Set();
  if (PUBLIC) { try { JSON.parse(localStorage.getItem(publicKey) || '[]').forEach((id) => publicDone.add(String(id))); } catch (_) {} }
  const publicHighestDone = () => { let m = -1; allLevels.forEach((lv, i) => { if (publicDone.has(String(lv.id ?? i))) m = i; }); return m; };
  const publicLocked = (idx) => allLevels.indexOf(levels[idx]) > publicHighestDone() + 1;
  const isLockedIndex = (idx) => (PUBLIC ? publicLocked(idx) : (!!allowedRange && ((idx + 1) < allowedRange.start || (idx + 1) > allowedRange.end)));

  const showToast = (msg) => { toastEl.textContent = msg || ''; };

  const startLevelTimer = () => {
    levelStartedAt = Date.now();
    if (levelTimer) clearInterval(levelTimer);
    const tick = () => { if (timeEl) timeEl.textContent = `Süre: ${Math.max(0, Math.round((Date.now() - levelStartedAt) / 1000))} sn`; };
    tick();
    levelTimer = setInterval(tick, 1000);
  };

  const game = new FlamestoneGame({
    canvas,
    onMove: (n) => { stepsEl.textContent = `Adım: ${n}`; },
    onToast: showToast,
    onLevelComplete: async ({ level, moves }) => {
      showToast(`Basarili! ${moves} adim.`);
      const absLevelNo = current + 1;
      completedLevelNos.add(absLevelNo);
      if (PUBLIC && level) {
        publicDone.add(String(level.id ?? (allLevels.indexOf(level))));
        try { localStorage.setItem(publicKey, JSON.stringify(Array.from(publicDone))); } catch (_) {}
      }
      const durationSeconds = Math.max(0, Math.round((Date.now() - levelStartedAt) / 1000));
      try {
        window.parent?.postMessage({ type: 'LEVEL_COMPLETED', source: 'flamestone-game', levelNo: absLevelNo, levelId: absLevelNo, xp: 20, moves, completedLevelIds: Array.from(completedLevelNos).sort((a,b)=>a-b), elapsedSeconds: durationSeconds }, '*');
      } catch (_) {}
      if (allowedRange && absLevelNo >= allowedRange.end) {
        try { window.parent?.postMessage({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'flamestone-game', xp: completedLevelNos.size * 20, completedLevelIds: Array.from(completedLevelNos).sort((a,b)=>a-b), elapsedSeconds: durationSeconds }, '*'); } catch (_) {}
      }
      if (level?.id && !PUBLIC) {
        try { await fetchJson(scoreUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ level_id: level.id, moves, duration_seconds: durationSeconds }) }); } catch (_) {}
      }
      setTimeout(() => goNext(), 700);
    },
  });

  const DIFF_LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor' };
  const isDoneIndex = (i) => (PUBLIC
    ? publicDone.has(String(levels[i]?.id ?? i))
    : completedLevelNos.has(i + 1));

  function openPicker() {
    pickerBody.innerHTML = '';
    ['easy', 'medium', 'hard'].forEach((diff) => {
      const items = levels.map((lv, i) => ({ lv, i })).filter((x) => detectDifficulty(x.lv.name, x.i) === diff);
      if (!items.length) return;
      const sec = document.createElement('div');
      sec.className = 'fs-picker-group';
      const finished = items.filter((x) => isDoneIndex(x.i)).length;
      sec.innerHTML = `<h4>${DIFF_LABEL[diff]} <small>${finished} / ${items.length} seviye</small></h4>`;
      const grid = document.createElement('div');
      grid.className = 'fs-picker-grid';
      items.forEach(({ lv, i }) => {
        const locked = isLockedIndex(i);
        const b = document.createElement('button');
        b.type = 'button';
        b.className = `fs-tile${isDoneIndex(i) ? ' done' : ''}${i === current ? ' current' : ''}`;
        b.disabled = locked;
        b.innerHTML = `${locked ? '<span class="lock">🔒</span>' : ''}<span class="t-num">${i + 1}</span><span class="t-state">${isDoneIndex(i) ? '✓ Bitti' : (i === current ? 'Şimdi' : DIFF_LABEL[diff])}</span>`;
        b.addEventListener('click', () => { loadLevel(i); closePicker(); });
        grid.appendChild(b);
      });
      sec.appendChild(grid);
      pickerBody.appendChild(sec);
    });
    pickerModal.hidden = false;
  }
  function closePicker() { pickerModal.hidden = true; }

  function renderLevelInfo() {
    const lv = levels[current];
    if (!lv) return;
    levelTitle.textContent = lv.name || `Level ${current + 1}`;
    levelMeta.textContent = `Grid: ${lv.data.grid[0].length}x${lv.data.grid.length} | Level ${current + 1}/${levels.length}`;
    if (pickerName) pickerName.textContent = `Seviye ${current + 1}`;
    if (pickerMeta) pickerMeta.textContent = DIFF_LABEL[detectDifficulty(lv.name, current)] || '';
    if (prevLevelBtn) prevLevelBtn.disabled = current <= 0;
  }

  function loadLevel(index) {
    if (!levels.length) return;
    const next = Math.max(0, Math.min(levels.length - 1, Number(index || 0)));
    if (isLockedIndex(next)) {
      showToast(`Bu hesapta sadece Level ${allowedRange.start}-${allowedRange.end} acik.`);
      return;
    }
    current = next;
    game.loadLevel(levels[current]);
    startLevelTimer();
    renderLevelInfo();
    localStorage.setItem(storageKey, String(levels[current]?.id || current));
  }

  function goNext() {
    const next = current + 1;
    if (next >= levels.length) { showToast('Tum leveller tamamlandi.'); return; }
    if (isLockedIndex(next)) { showToast('Acilan level araligi tamamlandi.'); return; }
    loadLevel(next);
  }

  function handleDir(dir) {
    if (dir === 'up') game.tryMove(0, -1);
    if (dir === 'down') game.tryMove(0, 1);
    if (dir === 'left') game.tryMove(-1, 0);
    if (dir === 'right') game.tryMove(1, 0);
  }

  const applyFilter = () => {
    levels = allLevels.slice();
    if (!levels.length) {
      showToast('Level bulunamadı.');
      return;
    }

    if (current >= levels.length || isLockedIndex(current)) {
      current = PUBLIC ? Math.max(0, Math.min(levels.length - 1, publicHighestDone() + 1)) : (allowedRange ? Math.max(0, allowedRange.start - 1) : 0);
    }
    loadLevel(Math.min(current, levels.length - 1));
  };

  document.addEventListener('keydown', (e) => {
    const key = String(e.key || '').toLowerCase();
    if (['arrowup', 'w'].includes(key)) { e.preventDefault(); handleDir('up'); }
    if (['arrowdown', 's'].includes(key)) { e.preventDefault(); handleDir('down'); }
    if (['arrowleft', 'a'].includes(key)) { e.preventDefault(); handleDir('left'); }
    if (['arrowright', 'd'].includes(key)) { e.preventDefault(); handleDir('right'); }
  });

  controls.forEach((btn) => btn.addEventListener('click', () => handleDir(btn.dataset.dir)));
  restartBtn?.addEventListener('click', () => loadLevel(current));
  nextBtn?.addEventListener('click', () => goNext());
  pickerBtn?.addEventListener('click', openPicker);
  prevLevelBtn?.addEventListener('click', () => loadLevel(current - 1));
  nextLevelBtn?.addEventListener('click', () => goNext());
  pickerModal?.addEventListener('click', (e) => { if (e.target.closest('[data-close]')) closePicker(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && pickerModal && !pickerModal.hidden) closePicker(); });
  fsBtn?.addEventListener('click', async () => {
    if (!document.fullscreenElement) await root.requestFullscreen?.();
    else await document.exitFullscreen?.();
  });

  (async () => {
    if (isStudent && needsGrant) {
      try {
        const grant = await fetchJson('/runner-grant/flamestone-game');
        if (!grant?.ok || grant?.role !== 'student') throw new Error('Grant yok');
        const from = Math.max(1, Number(grant.from || 1));
        const to = Math.max(from, Number(grant.to || from));
        allowedRange = { start: from, end: to };
      } catch (_) {
        showToast('Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.');
        restartBtn.disabled = true; nextBtn.disabled = true; if (pickerBtn) pickerBtn.disabled = true;
        controls.forEach((btn) => { btn.disabled = true; });
        return;
      }
    }
    if (isStudent && !allowedRange) allowedRange = { start: 1, end: 2 };

    try {
      const data = await fetchJson(levelsUrl);
      allLevels = uniqLevels([...(data.items || []), ...BUILTIN_LEVELS.map((x) => ({ ...x, id: x.id }))]);
    } catch (_) {
      allLevels = BUILTIN_LEVELS.slice();
    }

    const remembered = localStorage.getItem(storageKey);
    if (remembered) {
      const found = allLevels.findIndex((lv, i) => String(lv.id || i) === remembered);
      if (found >= 0) current = found;
    }

    applyFilter();
  })();
}

function bindEditorPage() {
  const root = document.getElementById('flamestone-editor-root');
  if (!root) return;
  const gridEl = document.getElementById('editor-grid');
  const previewEl = document.getElementById('editor-json-preview');
  const nameEl = document.getElementById('editor-level-name');
  const saveBtn = document.getElementById('editor-save-btn');
  const clearBtn = document.getElementById('editor-clear-btn');
  const toolBtns = root.querySelectorAll('[data-tool]');
  const saveUrl = root.dataset.saveUrl;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const editor = new LevelEditor({ root, gridEl, previewEl, nameEl });

  toolBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      toolBtns.forEach((b) => b.classList.remove('btn-primary'));
      btn.classList.add('btn-primary');
      editor.setTool(btn.dataset.tool);
    });
  });

  clearBtn?.addEventListener('click', () => editor.reset());
  saveBtn?.addEventListener('click', async () => {
    const err = editor.validate();
    if (err) { if (window.appToast) window.appToast('error', err); return; }
    try {
      await fetchJson(saveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(editor.getPayload()) });
      if (window.appToast) window.appToast('success', 'Level kaydedildi.');
    } catch (e) {
      if (window.appToast) window.appToast('error', e.message || 'Kayit basarisiz.');
    }
  });
}

bindGamePage();
bindEditorPage();
