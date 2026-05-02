const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");
const statusEl = document.getElementById("status");
const levelSelect = document.getElementById("level-select");
const runBtn = document.getElementById("btn-run");
const resetBtn = document.getElementById("btn-reset");
const closeBtn = document.getElementById("btn-close");
const addLevelBtn = document.getElementById("btn-add-level");
const editLevelBtn = document.getElementById("btn-edit-level");
const deleteLevelBtn = document.getElementById("btn-delete-level");
const assignHomeworkBtn = document.getElementById("btn-assign-homework");
const sessionTimerEl = document.getElementById("session-timer");
const sessionXpEl = document.getElementById("session-xp");
const blocklyEl = document.getElementById("blockly");
const designerModal = document.getElementById("designer-modal");
const designerBoard = document.getElementById("designer-board");
const designerPalette = document.getElementById("designer-palette");
const designerWidthEl = document.getElementById("designer-width");
const designerHeightEl = document.getElementById("designer-height");
const designerOrderEl = document.getElementById("designer-order");
const designerXpEl = document.getElementById("designer-xp");
const designerSaveBtn = document.getElementById("designer-save");
const designerCancelBtn = document.getElementById("designer-cancel");
const notifyPopup = document.getElementById("notify-popup");
const confirmOverlay = document.getElementById("confirm-overlay");
const confirmText = document.getElementById("confirm-text");
const confirmYesBtn = document.getElementById("confirm-yes");
const confirmNoBtn = document.getElementById("confirm-no");
const winOverlay = document.getElementById("win-overlay");
const winTitle = document.getElementById("win-title");
const winText = document.getElementById("win-text");
const winStars = document.getElementById("win-stars");
const winContinueBtn = document.getElementById("win-continue");
const winRetryBtn = document.getElementById("win-retry");

const DEFAULT_GRID_SIZE = 10;
const DEFAULT_LEVEL_XP = 12;
const EASY_LEVEL_XP = 5;
const MEDIUM_LEVEL_XP = 12;
const HARD_LEVEL_XP = 21;
const TILE_DEPTH = 24;
const CUSTOM_LEVELS_KEY = "block3d_custom_levels_v1";
const DELETED_BASE_LEVELS_KEY = "block3d_deleted_base_levels_v1";

const DIRS = [
  { x: 1, y: 0 },
  { x: 0, y: 1 },
  { x: -1, y: 0 },
  { x: 0, y: -1 }
];
const query = new URLSearchParams(window.location.search);
const roleParam = String(query.get("role") || "").toLowerCase();
const roleStorage = String((() => { try { return localStorage.getItem("runner_role") || ""; } catch (e) { return ""; } })()).toLowerCase();
const resolvedRole = (roleParam === "teacher" || roleParam === "admin") ? roleParam : ((roleStorage === "teacher" || roleStorage === "admin") ? roleStorage : "student");
const runnerRole = resolvedRole;
const assignmentId = String(query.get("assignmentId") || "");
const hasQueryRange = Number.isFinite(Number(query.get("levelStart") || query.get("from") || 0))
  && Number(query.get("levelStart") || query.get("from") || 0) > 0;
const enforceGrant = query.get("grant") === "1" || query.get("enforceGrant") === "1";
const needsGrantCheck = runnerRole === "student" && (!!assignmentId || hasQueryRange || enforceGrant);
let assignmentRange = (() => {
  const s = Number(query.get("levelStart") || 0);
  const e = Number(query.get("levelEnd") || 0);
  if (!Number.isFinite(s) || s < 1) return null;
  const start = Math.floor(s);
  const end = Math.max(start, Math.floor(Number.isFinite(e) && e > 0 ? e : start));
  return { start, end };
})();
const assignmentTitle = String(query.get("assignmentTitle") || "3D Blok Kodlama Odevi");
let grantDenied = false;
let sessionBaseSeconds = 0;
let sessionStartedAt = null;
let sessionTickTimer = null;
let updatePingTimer = null;
let nextLevelAfterWin = -1;
let rangeCompletionEmitted = false;
let sessionEarnedXp = 0;

function normalizeLevelXp(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return DEFAULT_LEVEL_XP;
  return Math.max(1, Math.min(500, Math.floor(n)));
}

function isIndexAllowedForRole(idx) {
  if ((runnerRole === "teacher" || runnerRole === "admin") || !assignmentRange) return true;
  const levelNo = Number(idx || 0) + 1;
  return levelNo >= assignmentRange.start && levelNo <= assignmentRange.end;
}

function formatSeconds(totalSeconds) {
  const sec = Math.max(0, Math.floor(Number(totalSeconds || 0)));
  const mm = String(Math.floor(sec / 60)).padStart(2, "0");
  const ss = String(sec % 60).padStart(2, "0");
  return `${mm}:${ss}`;
}

function getSessionElapsedSeconds() {
  if (!sessionStartedAt) return Math.max(0, Math.floor(sessionBaseSeconds));
  return Math.max(0, Math.floor(sessionBaseSeconds + (Date.now() - sessionStartedAt) / 1000));
}

function updateSessionTimerUI() {
  if (!sessionTimerEl) return;
  sessionTimerEl.textContent = formatSeconds(getSessionElapsedSeconds());
}

function updateSessionXpUI() {
  if (!sessionXpEl) return;
  sessionXpEl.textContent = `XP: ${Math.max(0, Number(sessionEarnedXp || 0))}`;
}

function startSessionTimer(initialSeconds = 0) {
  sessionBaseSeconds = Math.max(0, Math.floor(Number(initialSeconds || 0)));
  sessionStartedAt = Date.now();
  if (sessionTickTimer) clearInterval(sessionTickTimer);
  sessionTickTimer = setInterval(updateSessionTimerUI, 1000);
  updateSessionTimerUI();
  updateSessionXpUI();
}

function getCompletedLevelIds() {
  return LEVELS
    .filter((lv) => !!lv?.completed)
    .map((lv, idx) => Number(lv?.order || idx + 1))
    .filter((v) => Number.isFinite(v));
}

function getRangeProgressSnapshot() {
  if (!assignmentRange) return null;
  const start = Math.max(1, Number(assignmentRange.start || 1));
  const end = Math.max(start, Number(assignmentRange.end || start));
  let completed = 0;
  for (let i = start - 1; i <= end - 1 && i < LEVELS.length; i++) {
    if (LEVELS[i]?.completed) completed += 1;
  }
  const total = Math.max(0, end - start + 1);
  const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
  return { start, end, completed, total, percent, done: total > 0 && completed >= total };
}

function emitGameUpdate() {
  try {
    window.parent?.postMessage({
      type: "GAME_UPDATE",
      source: "block-3d",
      levels: LEVELS,
      currentLevelIndex: Number(state.levelIndex || 0),
      elapsedSeconds: getSessionElapsedSeconds(),
      assignmentId,
      assignmentTitle,
      levelStart: assignmentRange?.start || null,
      levelEnd: assignmentRange?.end || null,
      completedLevelIds: getCompletedLevelIds()
    }, "*");
  } catch {}
}

function maybeEmitRangeComplete() {
  if (!assignmentId || !assignmentRange || rangeCompletionEmitted || runnerRole !== "student") return;
  const s = getRangeProgressSnapshot();
  if (!s?.done) return;
  rangeCompletionEmitted = true;
  try {
    window.parent?.postMessage({
      type: "ASSIGNMENT_RANGE_COMPLETED",
      source: "block-3d",
      assignmentId,
      levelStart: s.start,
      levelEnd: s.end
    }, "*");
  } catch {}
}

function hideWinModal() {
  if (winOverlay) winOverlay.style.display = "none";
  nextLevelAfterWin = -1;
}

function getNextPlayableLevelIndex() {
  for (let i = Number(state.levelIndex || 0) + 1; i < LEVELS.length; i++) {
    if (isIndexAllowedForRole(i)) return i;
  }
  return -1;
}

function showWinModal(starsEarned, levelXpEarned, sessionTotalXp) {
  if (!winOverlay) return;
  nextLevelAfterWin = getNextPlayableLevelIndex();
  if (winTitle) winTitle.textContent = "Tebrikler! Seviye Tamamlandi";
  if (winStars) winStars.textContent = starsEarned > 0 ? "*".repeat(starsEarned) : "*";
  if (winText) {
    const levelGain = Math.max(0, Number(levelXpEarned || 0));
    const sessionGain = Math.max(0, Number(sessionTotalXp || 0));
    if (levelGain <= 0) {
      winText.textContent = nextLevelAfterWin >= 0
        ? "Bu seviyeyi daha once tamamlamissin. Devam Et ile sonraki seviyeye gecebilirsin."
        : "Bu araliktaki tum seviyeler zaten tamamli.";
      if (winContinueBtn) winContinueBtn.textContent = nextLevelAfterWin >= 0 ? "Devam Et" : "Kapat";
      winOverlay.style.display = "flex";
      return;
    }
    if (nextLevelAfterWin >= 0) {
      winText.textContent = `Bu seviyede +${levelGain} XP kazandin. Toplam oturum XP: ${sessionGain}. Devam Et ile sonraki seviyeye gecebilirsin.`;
    } else {
      winText.textContent = `Bu seviyede +${levelGain} XP kazandin. Toplam oturum XP: ${sessionGain}. Bu araliktaki seviyeleri tamamladin.`;
    }
  }
  if (winContinueBtn) winContinueBtn.textContent = nextLevelAfterWin >= 0 ? "Devam Et" : "Kapat";
  winOverlay.style.display = "flex";
}

function handleLevelCompleted() {
  const current = getCurrentLevel();
  if (!current) return;
  const firstCompletion = !current.completed;
  const starsEarned = firstCompletion ? 1 : 0;
  const levelXp = normalizeLevelXp(current?.xp);
  const xpEarned = firstCompletion ? levelXp : 0;
  if (xpEarned > 0) {
    sessionEarnedXp += xpEarned;
    updateSessionXpUI();
  }
  current.completed = true;
  current.stars = Math.max(Number(current.stars || 0), starsEarned);
  current.xp = levelXp;
  current.completedAt = Date.now();
  setStatus("Tebrikler, hedefe ulastin!", "success");
  emitGameUpdate();
  maybeEmitRangeComplete();
  try {
    window.parent?.postMessage({
      type: "LEVEL_COMPLETED",
      source: "block-3d",
      levelId: current.id || `lvl_${Number(state.levelIndex || 0) + 1}`,
      levelNo: Number(state.levelIndex || 0) + 1,
      stars: starsEarned,
      xp: xpEarned,
      duration: getSessionElapsedSeconds(),
      percent: 100,
      levels: LEVELS,
      currentLevelIndex: Number(state.levelIndex || 0),
      assignmentId,
      assignmentTitle,
      levelStart: assignmentRange?.start || null,
      levelEnd: assignmentRange?.end || null,
      completedLevelIds: getCompletedLevelIds()
    }, "*");
  } catch {}
  showWinModal(starsEarned, xpEarned, Math.max(0, Number(sessionEarnedXp || 0)));
}

function buildPathCells(start, goal) {
  const cells = new Set([`${start.x},${start.y}`]);
  let x = start.x;
  let y = start.y;
  const stepX = goal.x >= x ? 1 : -1;
  while (x !== goal.x) {
    x += stepX;
    cells.add(`${x},${y}`);
  }
  const stepY = goal.y >= y ? 1 : -1;
  while (y !== goal.y) {
    y += stepY;
    cells.add(`${x},${y}`);
  }
  return cells;
}

function buildObstacles(seed, reservedCells, obstacleCount) {
  const out = [];
  for (let y = 0; y < DEFAULT_GRID_SIZE; y++) {
    for (let x = 0; x < DEFAULT_GRID_SIZE; x++) {
      const key = `${x},${y}`;
      if (reservedCells.has(key)) continue;
      const score = (x * 7 + y * 5 + seed * 3) % 11;
      if (score <= 2 || (x + y + seed) % 9 === 0) {
        out.push({ x, y });
        reservedCells.add(key);
        if (out.length >= obstacleCount) return out;
      }
    }
  }
  return out;
}

function buildTrees(seed, reservedCells, treeCount) {
  const out = [];
  for (let y = 0; y < DEFAULT_GRID_SIZE; y++) {
    for (let x = 0; x < DEFAULT_GRID_SIZE; x++) {
      const key = `${x},${y}`;
      if (reservedCells.has(key)) continue;
      const score = (x * 3 + y * 11 + seed * 5) % 13;
      if (score <= 2) {
        out.push({ x, y });
        reservedCells.add(key);
        if (out.length >= treeCount) return out;
      }
    }
  }
  return out;
}

function createLevel(name, start, goal, seed, obstacleCount, treeCount, xpValue = DEFAULT_LEVEL_XP) {
  const safePath = buildPathCells(start, goal);
  const reserved = new Set(safePath);
  reserved.add(`${start.x},${start.y}`);
  reserved.add(`${goal.x},${goal.y}`);
  const obstacles = buildObstacles(seed, reserved, obstacleCount);
  const trees = buildTrees(seed + 17, reserved, treeCount);
  return {
    id: null,
    name,
    gridWidth: DEFAULT_GRID_SIZE,
    gridHeight: DEFAULT_GRID_SIZE,
    start: { x: start.x, y: start.y, dir: start.dir },
    goal: { x: goal.x, y: goal.y },
    xp: normalizeLevelXp(xpValue),
    obstacles,
    trees
  };
}

function createBandLevels(label, goals, start, obstacleBase, treeBase, seedBase, bandXp = DEFAULT_LEVEL_XP) {
  const out = [];
  for (let i = 0; i < 5; i++) {
    out.push(
      createLevel(
        `${label} ${i + 1}`,
        start,
        goals[i],
        seedBase + i * 7,
        obstacleBase + i,
        treeBase + (i % 3),
        bandXp
      )
    );
  }
  return out;
}

const BASE_LEVELS = [
  ...createBandLevels(
    "Kolay",
    [{ x: 5, y: 4 }, { x: 6, y: 4 }, { x: 6, y: 3 }, { x: 7, y: 3 }, { x: 7, y: 2 }],
    { x: 1, y: 8, dir: 0 },
    6,
    5,
    10,
    EASY_LEVEL_XP
  ),
  ...createBandLevels(
    "Orta",
    [{ x: 6, y: 3 }, { x: 7, y: 3 }, { x: 7, y: 2 }, { x: 8, y: 2 }, { x: 8, y: 1 }],
    { x: 0, y: 9, dir: 0 },
    10,
    6,
    100,
    MEDIUM_LEVEL_XP
  ),
  ...createBandLevels(
    "Zor",
    [{ x: 7, y: 2 }, { x: 8, y: 2 }, { x: 8, y: 1 }, { x: 9, y: 1 }, { x: 9, y: 0 }],
    { x: 0, y: 9, dir: 0 },
    14,
    7,
    200,
    HARD_LEVEL_XP
  )
];

function normalizeLevel(raw, fallbackId) {
  const w = Math.max(4, Math.min(20, Number(raw?.gridWidth || raw?.gridSize || DEFAULT_GRID_SIZE)));
  const h = Math.max(4, Math.min(20, Number(raw?.gridHeight || raw?.gridSize || DEFAULT_GRID_SIZE)));
  const level = {
    id: String(raw?.id || fallbackId || `lvl_${Date.now()}`),
    name: String(raw?.name || "Yeni Seviye"),
    isCustom: !!raw?.isCustom,
    order: Math.max(1, Number(raw?.order || 1)),
    xp: normalizeLevelXp(raw?.xp),
    gridWidth: w,
    gridHeight: h,
    start: {
      x: Math.max(0, Math.min(w - 1, Number(raw?.start?.x || 0))),
      y: Math.max(0, Math.min(h - 1, Number(raw?.start?.y || 0))),
      dir: [0, 1, 2, 3].includes(Number(raw?.start?.dir)) ? Number(raw.start.dir) : 0
    },
    goal: {
      x: Math.max(0, Math.min(w - 1, Number(raw?.goal?.x || w - 1))),
      y: Math.max(0, Math.min(h - 1, Number(raw?.goal?.y || h - 1)))
    },
    obstacles: Array.isArray(raw?.obstacles) ? raw.obstacles : [],
    trees: Array.isArray(raw?.trees) ? raw.trees : []
  };
  level.obstacles = level.obstacles
    .map((o) => ({ x: Number(o?.x), y: Number(o?.y) }))
    .filter((o) => Number.isFinite(o.x) && Number.isFinite(o.y) && o.x >= 0 && o.y >= 0 && o.x < w && o.y < h)
    .filter((o) => !(o.x === level.start.x && o.y === level.start.y) && !(o.x === level.goal.x && o.y === level.goal.y));
  level.trees = level.trees
    .map((o) => ({ x: Number(o?.x), y: Number(o?.y) }))
    .filter((o) => Number.isFinite(o.x) && Number.isFinite(o.y) && o.x >= 0 && o.y >= 0 && o.x < w && o.y < h)
    .filter((o) => !(o.x === level.start.x && o.y === level.start.y) && !(o.x === level.goal.x && o.y === level.goal.y));
  return level;
}

function normalizeLevelOrders() {
  LEVELS.forEach((lv, idx) => {
    lv.order = idx + 1;
  });
}

function loadCustomLevels() {
  try {
    const raw = localStorage.getItem(CUSTOM_LEVELS_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) return [];
    return parsed.map((lv, idx) => normalizeLevel({ ...lv, isCustom: true }, `custom_${idx + 1}`));
  } catch {
    return [];
  }
}

function saveCustomLevels() {
  try {
    const custom = LEVELS.filter((lv) => lv.isCustom).map((lv) => ({
      id: lv.id,
      name: lv.name,
      isCustom: true,
      order: Number(lv.order || 1),
      xp: normalizeLevelXp(lv.xp),
      gridWidth: lv.gridWidth,
      gridHeight: lv.gridHeight,
      start: lv.start,
      goal: lv.goal,
      obstacles: lv.obstacles,
      trees: lv.trees
    }));
    localStorage.setItem(CUSTOM_LEVELS_KEY, JSON.stringify(custom));
  } catch {}
}

function loadDeletedBaseLevelIds() {
  try {
    const raw = localStorage.getItem(DELETED_BASE_LEVELS_KEY);
    if (!raw) return new Set();
    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) return new Set();
    return new Set(parsed.map((v) => String(v)));
  } catch {
    return new Set();
  }
}

function saveDeletedBaseLevelIds(idSet) {
  try {
    localStorage.setItem(DELETED_BASE_LEVELS_KEY, JSON.stringify(Array.from(idSet)));
  } catch {}
}

const normalizedBaseLevels = BASE_LEVELS.map((lv, idx) =>
  normalizeLevel({ ...lv, id: `base_${idx + 1}`, isCustom: false, order: idx + 1 }, `base_${idx + 1}`)
);
const deletedBaseLevelIds = loadDeletedBaseLevelIds();
const customLevelsLoaded = loadCustomLevels();
const customById = new Map(customLevelsLoaded.map((lv) => [lv.id, lv]));
const mergedBase = normalizedBaseLevels
  .filter((lv) => !deletedBaseLevelIds.has(String(lv.id)))
  .map((lv) => customById.get(lv.id) || lv);
const extraCustom = customLevelsLoaded.filter((lv) => !normalizedBaseLevels.some((base) => base.id === lv.id));
let LEVELS = [...mergedBase, ...extraCustom];
if (!LEVELS.length) {
  LEVELS = [normalizeLevel({ ...BASE_LEVELS[0], id: "base_1", isCustom: false, order: 1 }, "base_1")];
}
LEVELS.sort((a, b) => Number(a.order || 0) - Number(b.order || 0));
normalizeLevelOrders();

let state = {
  levelIndex: 0,
  x: 0,
  y: 0,
  dir: 0,
  running: false,
  won: false
};

let designerMode = "add";
let selectedPaletteItem = "obstacle";
let editingLevelId = null;
let designerDraft = null;
let popupTimer = null;
let confirmResolve = null;

let workspace = null;
let blocklyReady = false;
let tileW = 80;
let tileH = 40;
let originX = 0;
let originY = 0;

function setStatus(msg, type = "info") {
  statusEl.textContent = msg;
  if (type === "success") {
    statusEl.style.borderColor = "#16a34a";
    statusEl.style.background = "#f0fdf4";
  } else if (type === "error") {
    statusEl.style.borderColor = "#dc2626";
    statusEl.style.background = "#fef2f2";
  } else {
    statusEl.style.borderColor = "#94a3b8";
    statusEl.style.background = "#f1f5f9";
  }
}

function showPopup(message, type = "info", durationMs = 2200) {
  if (!notifyPopup) return;
  notifyPopup.textContent = String(message || "");
  notifyPopup.classList.remove("success", "error", "show");
  if (type === "success") notifyPopup.classList.add("success");
  else if (type === "error") notifyPopup.classList.add("error");
  notifyPopup.classList.add("show");
  if (popupTimer) clearTimeout(popupTimer);
  popupTimer = setTimeout(() => {
    notifyPopup.classList.remove("show");
  }, Math.max(800, Number(durationMs || 2200)));
}

function closeConfirmDialog(result) {
  if (confirmOverlay) confirmOverlay.style.display = "none";
  if (confirmResolve) {
    const resolve = confirmResolve;
    confirmResolve = null;
    resolve(!!result);
  }
}

function confirmDialog(message) {
  return new Promise((resolve) => {
    if (!confirmOverlay || !confirmText || !confirmYesBtn || !confirmNoBtn) {
      resolve(false);
      return;
    }
    confirmResolve = resolve;
    confirmText.textContent = String(message || "Bu islemi onayliyor musun?");
    confirmOverlay.style.display = "flex";
  });
}

function getCurrentLevel() {
  return LEVELS[state.levelIndex];
}

function getCurrentGridWidth() {
  return Math.max(4, Number(getCurrentLevel()?.gridWidth || DEFAULT_GRID_SIZE));
}

function getCurrentGridHeight() {
  return Math.max(4, Number(getCurrentLevel()?.gridHeight || DEFAULT_GRID_SIZE));
}

function hasObstacle(x, y) {
  return getCurrentLevel().obstacles.some((o) => o.x === x && o.y === y);
}

function isInside(x, y) {
  return x >= 0 && y >= 0 && x < getCurrentGridWidth() && y < getCurrentGridHeight();
}

function isGoal(x, y) {
  const g = getCurrentLevel().goal;
  return g.x === x && g.y === y;
}

function resetState() {
  const lvl = getCurrentLevel();
  hideWinModal();
  state.x = lvl.start.x;
  state.y = lvl.start.y;
  state.dir = lvl.start.dir;
  state.running = false;
  state.won = false;
  runBtn.disabled = false;
  levelSelect.disabled = false;
  setStatus("Karakteri hedef kareye gotur.");
  draw();
  emitGameUpdate();
}

function resizeCanvas() {
  const dpr = window.devicePixelRatio || 1;
  const rect = canvas.getBoundingClientRect();
  canvas.width = Math.max(1, Math.round(rect.width * dpr));
  canvas.height = Math.max(1, Math.round(rect.height * dpr));
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

  const w = rect.width;
  const h = rect.height;
  const gw = getCurrentGridWidth();
  const gh = getCurrentGridHeight();
  tileW = Math.min((w * 0.78) / gw, (h * 0.85) / (gh * 0.56));
  tileH = tileW * 0.5;
  originX = w * 0.5;
  originY = Math.max(120, (h - gh * tileH) * 0.22 + 42);
  draw();
}

function isoToScreen(x, y) {
  return {
    x: originX + (x - y) * (tileW / 2),
    y: originY + (x + y) * (tileH / 2)
  };
}

function drawDiamond(cx, cy, w, h, color, stroke = "rgba(0,0,0,0.15)") {
  ctx.beginPath();
  ctx.moveTo(cx, cy - h / 2);
  ctx.lineTo(cx + w / 2, cy);
  ctx.lineTo(cx, cy + h / 2);
  ctx.lineTo(cx - w / 2, cy);
  ctx.closePath();
  ctx.fillStyle = color;
  ctx.fill();
  ctx.strokeStyle = stroke;
  ctx.stroke();
}

function fillRoundedRect(x, y, w, h, r, color) {
  const rr = Math.max(0, Math.min(r, Math.min(w, h) / 2));
  ctx.beginPath();
  ctx.moveTo(x + rr, y);
  ctx.arcTo(x + w, y, x + w, y + h, rr);
  ctx.arcTo(x + w, y + h, x, y + h, rr);
  ctx.arcTo(x, y + h, x, y, rr);
  ctx.arcTo(x, y, x + w, y, rr);
  ctx.closePath();
  ctx.fillStyle = color;
  ctx.fill();
}

function drawTile(x, y, isGoalTile = false) {
  const p = isoToScreen(x, y);
  const gw = getCurrentGridWidth();
  const gh = getCurrentGridHeight();
  const shade = (x + y) % 2 === 0 ? "#84cc16" : "#65a30d";
  const topColor = isGoalTile ? "#f59e0b" : shade;

  drawDiamond(p.x, p.y, tileW, tileH, topColor);

  if (y === gh - 1) {
    ctx.beginPath();
    ctx.moveTo(p.x - tileW / 2, p.y);
    ctx.lineTo(p.x, p.y + tileH / 2);
    ctx.lineTo(p.x, p.y + tileH / 2 + TILE_DEPTH);
    ctx.lineTo(p.x - tileW / 2, p.y + TILE_DEPTH);
    ctx.closePath();
    ctx.fillStyle = "#f59e0b";
    ctx.fill();
    ctx.strokeStyle = "rgba(0,0,0,0.2)";
    ctx.stroke();
  }

  if (x === gw - 1) {
    ctx.beginPath();
    ctx.moveTo(p.x + tileW / 2, p.y);
    ctx.lineTo(p.x, p.y + tileH / 2);
    ctx.lineTo(p.x, p.y + tileH / 2 + TILE_DEPTH);
    ctx.lineTo(p.x + tileW / 2, p.y + TILE_DEPTH);
    ctx.closePath();
    ctx.fillStyle = "#d97706";
    ctx.fill();
    ctx.strokeStyle = "rgba(0,0,0,0.2)";
    ctx.stroke();
  }
}

function drawObstacle(x, y) {
  const p = isoToScreen(x, y);
  const size = tileW * 0.35;
  const h = size * 0.62;
  const z = 20;

  drawDiamond(p.x, p.y - z, size, h, "#78350f", "rgba(0,0,0,0.25)");

  ctx.beginPath();
  ctx.moveTo(p.x + size / 2, p.y - z);
  ctx.lineTo(p.x, p.y - z + h / 2);
  ctx.lineTo(p.x, p.y + h / 2);
  ctx.lineTo(p.x + size / 2, p.y);
  ctx.closePath();
  ctx.fillStyle = "#5b2f0b";
  ctx.fill();

  ctx.beginPath();
  ctx.moveTo(p.x - size / 2, p.y - z);
  ctx.lineTo(p.x, p.y - z + h / 2);
  ctx.lineTo(p.x, p.y + h / 2);
  ctx.lineTo(p.x - size / 2, p.y);
  ctx.closePath();
  ctx.fillStyle = "#6b3a12";
  ctx.fill();
}

function drawTree(x, y) {
  const p = isoToScreen(x, y);
  const trunkW = Math.max(8, tileW * 0.12);
  const trunkH = Math.max(20, tileH * 0.9);

  ctx.fillStyle = "#7c3f14";
  ctx.fillRect(p.x - trunkW / 2, p.y - trunkH - 14, trunkW, trunkH);

  ctx.fillStyle = "#fb923c";
  ctx.beginPath();
  ctx.arc(p.x - 12, p.y - trunkH - 22, 16, 0, Math.PI * 2);
  ctx.arc(p.x + 6, p.y - trunkH - 26, 18, 0, Math.PI * 2);
  ctx.arc(p.x + 20, p.y - trunkH - 20, 14, 0, Math.PI * 2);
  ctx.fill();
}

function drawGoalMark() {
  const g = getCurrentLevel().goal;
  const p = isoToScreen(g.x, g.y);
  ctx.strokeStyle = "#0ea5e9";
  ctx.lineWidth = 3;
  ctx.beginPath();
  ctx.ellipse(p.x, p.y, tileW * 0.15, tileH * 0.18, 0, 0, Math.PI * 2);
  ctx.stroke();
  ctx.lineWidth = 1;
}

function getDirScreenVector(dir) {
  const d = ((Number(dir) % 4) + 4) % 4;
  if (d === 0) return { x: 1, y: 1 };   // +x
  if (d === 1) return { x: -1, y: 1 };  // +y
  if (d === 2) return { x: -1, y: -1 }; // -x
  return { x: 1, y: -1 };               // -y
}

function drawCharacter() {
  const p = isoToScreen(state.x, state.y);
  const bodyY = p.y - 17;
  const facing = state.dir;
  const v = getDirScreenVector(facing);

  // New character: little robot explorer
  fillRoundedRect(p.x - 14, bodyY - 16, 28, 26, 8, "#22d3ee");
  fillRoundedRect(p.x - 11, bodyY - 28, 22, 12, 5, "#cbd5e1");

  ctx.fillStyle = "#0f172a";
  ctx.fillRect(p.x - 2, bodyY - 34, 4, 6);
  ctx.fillStyle = "#38bdf8";
  ctx.beginPath();
  ctx.arc(p.x, bodyY - 36, 4, 0, Math.PI * 2);
  ctx.fill();

  const eyeOffsetX = v.x * 1.9;
  const eyeOffsetY = v.y * 0.8;
  ctx.fillStyle = "#ffffff";
  ctx.beginPath();
  ctx.arc(p.x - 5 + eyeOffsetX, bodyY - 12 + eyeOffsetY, 3.2, 0, Math.PI * 2);
  ctx.arc(p.x + 5 + eyeOffsetX, bodyY - 12 + eyeOffsetY, 3.2, 0, Math.PI * 2);
  ctx.fill();

  ctx.fillStyle = "#111827";
  ctx.beginPath();
  ctx.arc(p.x - 5 + eyeOffsetX * 1.2, bodyY - 12 + eyeOffsetY, 1.5, 0, Math.PI * 2);
  ctx.arc(p.x + 5 + eyeOffsetX * 1.2, bodyY - 12 + eyeOffsetY, 1.5, 0, Math.PI * 2);
  ctx.fill();

  // Front visor to emphasize heading direction.
  const visorCx = p.x + v.x * 7;
  const visorCy = bodyY - 6 + v.y * 3;
  fillRoundedRect(visorCx - 7, visorCy - 3, 14, 6, 2, "#0f172a");

  // Bold direction arrow (character nose) so left/right turns are obvious.
  const tipX = p.x + v.x * 20;
  const tipY = bodyY - 7 + v.y * 10;
  const baseX = p.x + v.x * 10;
  const baseY = bodyY - 7 + v.y * 5;
  const nx = -v.y;
  const ny = v.x;
  ctx.beginPath();
  ctx.moveTo(tipX, tipY);
  ctx.lineTo(baseX + nx * 4, baseY + ny * 4);
  ctx.lineTo(baseX - nx * 4, baseY - ny * 4);
  ctx.closePath();
  ctx.fillStyle = "#f59e0b";
  ctx.fill();
  ctx.strokeStyle = "#b45309";
  ctx.lineWidth = 1.5;
  ctx.stroke();

  ctx.strokeStyle = "#0f172a";
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(p.x - 5, bodyY - 4);
  ctx.lineTo(p.x + 5, bodyY - 4);
  ctx.stroke();
  ctx.lineWidth = 1;
}

function drawWaterAroundBoard() {
  const rect = canvas.getBoundingClientRect();
  const g = ctx.createLinearGradient(0, 0, rect.width, rect.height);
  g.addColorStop(0, "#14b8a6");
  g.addColorStop(0.5, "#2dd4bf");
  g.addColorStop(1, "#22d3ee");
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, rect.width, rect.height);
}

function draw() {
  const level = getCurrentLevel();
  if (!level) return;
  const gw = getCurrentGridWidth();
  const gh = getCurrentGridHeight();
  drawWaterAroundBoard();

  for (let y = 0; y < gh; y++) {
    for (let x = 0; x < gw; x++) {
      drawTile(x, y, isGoal(x, y));
    }
  }

  for (const t of level.trees) drawTree(t.x, t.y);
  for (const o of level.obstacles) drawObstacle(o.x, o.y);
  drawGoalMark();
  drawCharacter();
}

function getRepeatCount(block) {
  const fieldVal = Number(block.getFieldValue("TIMES"));
  if (Number.isFinite(fieldVal) && fieldVal > 0) return fieldVal;
  const timesBlock = block.getInputTargetBlock("TIMES");
  if (!timesBlock) return 0;
  const numVal = Number(timesBlock.getFieldValue("NUM"));
  return Number.isFinite(numVal) && numVal > 0 ? numVal : 0;
}

function collectCommandsFromBlock(block) {
  const out = [];
  let current = block;
  while (current) {
    if (current.type === "program_start") {
      const doBlock = current.getInputTargetBlock("DO");
      if (doBlock) out.push(...collectCommandsFromBlock(doBlock));
    } else if (current.type === "move_forward") {
      out.push({ type: "forward", steps: 1, blockId: current.id });
    } else if (current.type === "turn_left") {
      out.push({ type: "left", blockId: current.id });
    } else if (current.type === "turn_right") {
      out.push({ type: "right", blockId: current.id });
    } else if (current.type === "controls_repeat_ext") {
      const times = Math.max(0, getRepeatCount(current));
      const doBlock = current.getInputTargetBlock("DO");
      if (doBlock && times > 0) {
        const nested = collectCommandsFromBlock(doBlock);
        for (let i = 0; i < times; i++) out.push(...nested);
      }
    }
    current = current.getNextBlock();
  }
  return out;
}

function buildCommands() {
  if (!workspace || !blocklyReady) return [];
  const topBlocks = workspace.getTopBlocks(true);
  const startBlock = topBlocks.find((b) => b.type === "program_start");
  if (startBlock) return collectCommandsFromBlock(startBlock);
  const out = [];
  for (const b of topBlocks) out.push(...collectCommandsFromBlock(b));
  return out;
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function stepForward() {
  const d = DIRS[state.dir];
  const nx = state.x + d.x;
  const ny = state.y + d.y;

  if (!isInside(nx, ny)) {
    setStatus("Sinirin disina cikamazsin.", "error");
    return false;
  }
  if (hasObstacle(nx, ny)) {
    setStatus("Engele carptin. Rotani degistir.", "error");
    return false;
  }
  state.x = nx;
  state.y = ny;
  draw();
  return true;
}

function checkWin() {
  if (isGoal(state.x, state.y)) {
    state.won = true;
    handleLevelCompleted();
    return true;
  }
  return false;
}

async function runProgram() {
  if (state.running) return;
  if (!blocklyReady || !workspace) {
    setStatus("Blok editor yuklenemedi. Sayfayi yenileyin.", "error");
    return;
  }
  const commands = buildCommands();
  if (!commands.length) {
    setStatus("Calistirmadan once blok ekle.", "error");
    return;
  }

  state.running = true;
  state.won = false;
  runBtn.disabled = true;
  levelSelect.disabled = true;
  setStatus("Program calisiyor...");

  try {
    for (const cmd of commands) {
      workspace.highlightBlock(cmd.blockId);

      if (cmd.type === "left") {
        state.dir = (state.dir + 3) % 4;
        draw();
        await sleep(260);
      } else if (cmd.type === "right") {
        state.dir = (state.dir + 1) % 4;
        draw();
        await sleep(260);
      } else if (cmd.type === "forward") {
        for (let i = 0; i < cmd.steps; i++) {
          const ok = await stepForward();
          await sleep(260);
          if (!ok) throw new Error("blocked");
          if (checkWin()) break;
        }
      }

      if (state.won) break;
    }

    if (!state.won) {
      setStatus("Program bitti. Hedefe ulasilamadi.", "error");
    }
  } catch (e) {
    if (e?.message !== "blocked") {
      setStatus("Program calistirilirken hata oldu.", "error");
    }
  } finally {
    workspace.highlightBlock(null);
    state.running = false;
    runBtn.disabled = false;
    levelSelect.disabled = false;
  }
}

function rebuildLevelSelect(keepIndex = 0) {
  LEVELS.sort((a, b) => Number(a.order || 0) - Number(b.order || 0));
  normalizeLevelOrders();
  levelSelect.innerHTML = "";
  LEVELS.forEach((level, idx) => {
    const opt = document.createElement("option");
    opt.value = String(idx);
    const customTag = level.isCustom ? " [Ozel]" : "";
    opt.textContent = `${idx + 1}. ${level.name}${customTag}`;
    if (!isIndexAllowedForRole(idx)) {
      opt.disabled = true;
      opt.textContent += " [Kilitli]";
    }
    levelSelect.appendChild(opt);
  });
  let safeIndex = Math.max(0, Math.min(LEVELS.length - 1, Number(keepIndex || 0)));
  if (!isIndexAllowedForRole(safeIndex)) {
    safeIndex = Math.max(0, Math.min(LEVELS.length - 1, Number((assignmentRange?.start || 1) - 1)));
  }
  state.levelIndex = safeIndex;
  levelSelect.value = String(safeIndex);
}

function createEmptyDesignerDraft(width, height) {
  const w = Math.max(4, Math.min(20, Number(width || DEFAULT_GRID_SIZE)));
  const h = Math.max(4, Math.min(20, Number(height || DEFAULT_GRID_SIZE)));
  return {
    gridWidth: w,
    gridHeight: h,
    start: { x: 0, y: h - 1, dir: 0 },
    goal: { x: w - 1, y: 0 },
    obstacles: [],
    trees: []
  };
}

function isDraftStart(x, y) {
  return designerDraft && designerDraft.start.x === x && designerDraft.start.y === y;
}

function isDraftGoal(x, y) {
  return designerDraft && designerDraft.goal.x === x && designerDraft.goal.y === y;
}

function draftHasObstacle(x, y) {
  return !!designerDraft?.obstacles?.some((o) => o.x === x && o.y === y);
}

function draftHasTree(x, y) {
  return !!designerDraft?.trees?.some((o) => o.x === x && o.y === y);
}

function removeDraftCell(listName, x, y) {
  if (!designerDraft || !Array.isArray(designerDraft[listName])) return;
  designerDraft[listName] = designerDraft[listName].filter((o) => !(o.x === x && o.y === y));
}

function placeDraftItem(x, y, itemType) {
  if (!designerDraft) return;
  if (x < 0 || y < 0 || x >= designerDraft.gridWidth || y >= designerDraft.gridHeight) return;
  if (itemType === "erase") {
    if (isDraftStart(x, y)) designerDraft.start = { x: 0, y: designerDraft.gridHeight - 1, dir: 0 };
    if (isDraftGoal(x, y)) designerDraft.goal = { x: designerDraft.gridWidth - 1, y: 0 };
    removeDraftCell("obstacles", x, y);
    removeDraftCell("trees", x, y);
    return;
  }
  if (itemType === "start") {
    designerDraft.start = { x, y, dir: 0 };
    removeDraftCell("obstacles", x, y);
    removeDraftCell("trees", x, y);
    if (isDraftGoal(x, y)) designerDraft.goal = { x: designerDraft.gridWidth - 1, y: 0 };
    return;
  }
  if (itemType === "goal") {
    designerDraft.goal = { x, y };
    removeDraftCell("obstacles", x, y);
    removeDraftCell("trees", x, y);
    if (isDraftStart(x, y)) designerDraft.start = { x: 0, y: designerDraft.gridHeight - 1, dir: 0 };
    return;
  }
  if (itemType === "obstacle") {
    if (isDraftStart(x, y) || isDraftGoal(x, y)) return;
    if (!draftHasObstacle(x, y)) designerDraft.obstacles.push({ x, y });
    removeDraftCell("trees", x, y);
    return;
  }
  if (itemType === "tree") {
    if (isDraftStart(x, y) || isDraftGoal(x, y)) return;
    if (!draftHasTree(x, y)) designerDraft.trees.push({ x, y });
    removeDraftCell("obstacles", x, y);
  }
}

function refreshPaletteActive() {
  const items = designerPalette?.querySelectorAll(".palette-item") || [];
  items.forEach((el) => {
    el.classList.toggle("active", el.dataset.item === selectedPaletteItem);
  });
}

function renderDesignerBoard() {
  if (!designerDraft || !designerBoard) return;
  designerBoard.innerHTML = "";
  designerBoard.style.gridTemplateColumns = `repeat(${designerDraft.gridWidth}, 34px)`;
  designerBoard.style.gridTemplateRows = `repeat(${designerDraft.gridHeight}, 34px)`;

  for (let y = 0; y < designerDraft.gridHeight; y++) {
    for (let x = 0; x < designerDraft.gridWidth; x++) {
      const cell = document.createElement("div");
      cell.className = "designer-cell";
      cell.dataset.x = String(x);
      cell.dataset.y = String(y);
      if (isDraftGoal(x, y)) {
        cell.classList.add("goal");
        cell.textContent = "⭐";
      } else if (isDraftStart(x, y)) {
        cell.classList.add("start");
        cell.textContent = "🤖";
      } else if (draftHasObstacle(x, y)) {
        cell.classList.add("obstacle");
        cell.textContent = "🪵";
      } else if (draftHasTree(x, y)) {
        cell.classList.add("tree");
        cell.textContent = "🌳";
      } else {
        cell.textContent = "";
      }

      cell.addEventListener("dragover", (ev) => {
        ev.preventDefault();
        cell.classList.add("drag-over");
      });
      cell.addEventListener("dragleave", () => cell.classList.remove("drag-over"));
      cell.addEventListener("drop", (ev) => {
        ev.preventDefault();
        cell.classList.remove("drag-over");
        const type = ev.dataTransfer?.getData("text/plain") || selectedPaletteItem;
        placeDraftItem(x, y, type);
        renderDesignerBoard();
      });
      cell.addEventListener("click", () => {
        placeDraftItem(x, y, selectedPaletteItem);
        renderDesignerBoard();
      });
      designerBoard.appendChild(cell);
    }
  }
}

function openDesigner(mode) {
  if (state.running) return;
  designerMode = mode === "edit" ? "edit" : "add";
  editingLevelId = null;
  const current = getCurrentLevel();
  if (designerMode === "edit" && current) {
    editingLevelId = current.id;
    designerDraft = normalizeLevel({ ...current, isCustom: true }, current.id);
  } else {
    designerDraft = createEmptyDesignerDraft(Number(designerWidthEl.value), Number(designerHeightEl.value));
  }
  designerWidthEl.value = String(designerDraft.gridWidth);
  designerHeightEl.value = String(designerDraft.gridHeight);
  const orderValue = designerMode === "edit" && current
    ? Math.max(1, state.levelIndex + 1)
    : Math.max(1, LEVELS.length + 1);
  if (designerOrderEl) designerOrderEl.value = String(orderValue);
  if (designerXpEl) designerXpEl.value = String(normalizeLevelXp(designerDraft?.xp));
  selectedPaletteItem = "obstacle";
  refreshPaletteActive();
  renderDesignerBoard();
  designerModal.style.display = "flex";
}

function closeDesigner() {
  designerModal.style.display = "none";
  designerDraft = null;
  editingLevelId = null;
}

function saveDesignerLevel() {
  if (!designerDraft) return;
  const maxOrderForAdd = LEVELS.length + (editingLevelId ? 0 : 1);
  const requestedOrder = Math.max(1, Math.min(maxOrderForAdd, Number(designerOrderEl?.value || 1)));
  const requestedXp = normalizeLevelXp(designerXpEl?.value);
  const levelPayload = normalizeLevel({
    id: editingLevelId || `custom_${Date.now()}`,
    isCustom: true,
    name: editingLevelId ? `${getCurrentLevel()?.name || "Duzenlenen Seviye"} (Duzenlendi)` : `Ozel Seviye ${Date.now().toString().slice(-5)}`,
    order: requestedOrder,
    xp: requestedXp,
    gridWidth: designerDraft.gridWidth,
    gridHeight: designerDraft.gridHeight,
    start: designerDraft.start,
    goal: designerDraft.goal,
    obstacles: designerDraft.obstacles,
    trees: designerDraft.trees
  }, editingLevelId || `custom_${Date.now()}`);
  const currentEditIndex = editingLevelId ? LEVELS.findIndex((lv) => lv.id === editingLevelId) : -1;
  if (currentEditIndex >= 0) LEVELS.splice(currentEditIndex, 1);
  const insertIndex = Math.max(0, Math.min(LEVELS.length, requestedOrder - 1));
  LEVELS.splice(insertIndex, 0, levelPayload);
  state.levelIndex = insertIndex;
  normalizeLevelOrders();
  saveCustomLevels();
  rebuildLevelSelect(state.levelIndex);
  resetState();
  closeDesigner();
  emitGameUpdate();
}

async function deleteCurrentLevel() {
  if (state.running) return;
  if (!Array.isArray(LEVELS) || LEVELS.length <= 1) {
    showPopup("En az bir seviye kalmali.", "error");
    return;
  }
  const current = getCurrentLevel();
  if (!current) return;
  const approved = await confirmDialog(`"${current.name}" seviyesini silmek istiyor musun?`);
  if (!approved) {
    showPopup("Silme iptal edildi.", "info", 1300);
    return;
  }

  if (current.isCustom) {
    LEVELS = LEVELS.filter((lv) => lv.id !== current.id);
  } else {
    deletedBaseLevelIds.add(String(current.id));
    saveDeletedBaseLevelIds(deletedBaseLevelIds);
    LEVELS = LEVELS.filter((lv) => lv.id !== current.id);
  }

  normalizeLevelOrders();
  const nextIndex = Math.max(0, Math.min(state.levelIndex, LEVELS.length - 1));
  saveCustomLevels();
  rebuildLevelSelect(nextIndex);
  resetState();
  showPopup("Seviye silindi.", "success");
  emitGameUpdate();
}

function initBlockly() {
  if (typeof Blockly === "undefined" || !Blockly?.inject) {
    blocklyReady = false;
    setStatus("Blockly yuklenemedi. Internet baglantisini kontrol et.", "error");
    return;
  }

  Blockly.defineBlocksWithJsonArray([
    {
      type: "program_start",
      message0: "START %1 %2",
      args0: [
        { type: "input_dummy" },
        { type: "input_statement", name: "DO" }
      ],
      colour: 122,
      tooltip: "Program buradan baslar",
      nextStatement: null
    },
    {
      type: "move_forward",
      message0: "Ileri git",
      previousStatement: null,
      nextStatement: null,
      colour: 188,
      tooltip: "Karakteri ileri gotur"
    },
    {
      type: "turn_left",
      message0: "Sola don",
      previousStatement: null,
      nextStatement: null,
      colour: 268,
      tooltip: "Karakteri sola cevir"
    },
    {
      type: "turn_right",
      message0: "Saga don",
      previousStatement: null,
      nextStatement: null,
      colour: 268,
      tooltip: "Karakteri saga cevir"
    }
  ]);

  const toolbox = `
    <xml xmlns="https://developers.google.com/blockly/xml">
      <block type="program_start"></block>
      <block type="move_forward"></block>
      <block type="turn_left"></block>
      <block type="turn_right"></block>
      <block type="controls_repeat_ext">
        <value name="TIMES">
          <shadow type="math_number">
            <field name="NUM">2</field>
          </shadow>
        </value>
      </block>
    </xml>
  `;

  workspace = Blockly.inject(blocklyEl, {
    toolbox,
    trashcan: true,
    grid: { spacing: 20, length: 3, colour: "#cbd5e1", snap: true },
    move: { scrollbars: true, drag: true, wheel: true }
  });
  blocklyReady = true;

  const initialXml = `
    <xml xmlns="https://developers.google.com/blockly/xml">
      <block type="program_start" x="20" y="20"></block>
    </xml>
  `;
  try {
    const textToDomFn =
      (Blockly.Xml && typeof Blockly.Xml.textToDom === "function" && Blockly.Xml.textToDom.bind(Blockly.Xml)) ||
      (Blockly.utils?.xml && typeof Blockly.utils.xml.textToDom === "function" && Blockly.utils.xml.textToDom.bind(Blockly.utils.xml));
    const domToWorkspaceFn =
      (Blockly.Xml && typeof Blockly.Xml.domToWorkspace === "function" && Blockly.Xml.domToWorkspace.bind(Blockly.Xml)) ||
      (Blockly.Xml && typeof Blockly.Xml.appendDomToWorkspace === "function" && Blockly.Xml.appendDomToWorkspace.bind(Blockly.Xml));

    if (textToDomFn && domToWorkspaceFn) {
      const xmlDom = textToDomFn(initialXml);
      domToWorkspaceFn(xmlDom, workspace);
    } else {
      const startBlock = workspace.newBlock("program_start");
      startBlock.initSvg?.();
      startBlock.render?.();
      startBlock.moveBy?.(20, 20);
    }
  } catch (e) {
    console.warn("3D runner initial xml load failed:", e);
    try {
      const startBlock = workspace.newBlock("program_start");
      startBlock.initSvg?.();
      startBlock.render?.();
      startBlock.moveBy?.(20, 20);
    } catch {}
  }
}

function resetWorkspaceForLevel() {
  if (!workspace || !blocklyReady) return;
  try {
    workspace.clear();
    const startBlock = workspace.newBlock("program_start");
    startBlock.initSvg?.();
    startBlock.render?.();
    startBlock.moveBy?.(20, 20);
  } catch (e) {
    console.warn("3D runner workspace reset failed:", e);
  }
}

function bindUI() {
  levelSelect.addEventListener("change", () => {
    if (state.running) return;
    const nextIndex = Number(levelSelect.value || 0);
    if (!isIndexAllowedForRole(nextIndex)) {
      setStatus("Bu seviye bu odev araliginda degil.", "error");
      levelSelect.value = String(state.levelIndex);
      return;
    }
    state.levelIndex = nextIndex;
    resetWorkspaceForLevel();
    resetState();
  });

  runBtn.addEventListener("click", runProgram);
  resetBtn.addEventListener("click", () => {
    if (state.running) return;
    resetState();
  });
  closeBtn?.addEventListener("click", () => {
    window.parent?.postMessage({ type: "CLOSE_ACTIVITY_MODAL" }, "*");
  });
  if (runnerRole !== "admin") {
    if (addLevelBtn) addLevelBtn.style.display = "none";
    if (editLevelBtn) editLevelBtn.style.display = "none";
    if (deleteLevelBtn) deleteLevelBtn.style.display = "none";
    if (assignHomeworkBtn) assignHomeworkBtn.style.display = "none";
  }
  addLevelBtn?.addEventListener("click", () => openDesigner("add"));
  editLevelBtn?.addEventListener("click", () => openDesigner("edit"));
  deleteLevelBtn?.addEventListener("click", deleteCurrentLevel);
  assignHomeworkBtn?.addEventListener("click", () => {
    if (runnerRole !== "admin") return;
    const currentLevelNo = Number(state.levelIndex || 0) + 1;
    const currentLevel = getCurrentLevel();
    window.parent?.postMessage({
      type: "OPEN_BLOCK_HOMEWORK_ASSIGN",
      source: "block-3d",
      levelStart: currentLevelNo,
      levelEnd: currentLevelNo,
      levelName: String(currentLevel?.name || `3D Seviye ${currentLevelNo}`),
      title: `3D Blok Kodlama - ${String(currentLevel?.name || `Seviye ${currentLevelNo}`)}`
    }, "*");
  });
  winRetryBtn?.addEventListener("click", () => {
    hideWinModal();
    resetState();
  });
  winContinueBtn?.addEventListener("click", () => {
    if (nextLevelAfterWin >= 0) {
      state.levelIndex = nextLevelAfterWin;
      levelSelect.value = String(nextLevelAfterWin);
      hideWinModal();
      resetWorkspaceForLevel();
      resetState();
      return;
    }
    hideWinModal();
  });
  winOverlay?.addEventListener("click", (ev) => {
    if (ev.target === winOverlay) hideWinModal();
  });
  confirmYesBtn?.addEventListener("click", () => closeConfirmDialog(true));
  confirmNoBtn?.addEventListener("click", () => closeConfirmDialog(false));
  confirmOverlay?.addEventListener("click", (ev) => {
    if (ev.target === confirmOverlay) closeConfirmDialog(false);
  });
  designerCancelBtn?.addEventListener("click", closeDesigner);
  designerSaveBtn?.addEventListener("click", saveDesignerLevel);
  designerWidthEl?.addEventListener("change", () => {
    if (!designerDraft) return;
    designerDraft = createEmptyDesignerDraft(Number(designerWidthEl.value), Number(designerHeightEl.value));
    renderDesignerBoard();
  });
  designerHeightEl?.addEventListener("change", () => {
    if (!designerDraft) return;
    designerDraft = createEmptyDesignerDraft(Number(designerWidthEl.value), Number(designerHeightEl.value));
    renderDesignerBoard();
  });
  designerPalette?.querySelectorAll(".palette-item").forEach((item) => {
    item.addEventListener("click", () => {
      selectedPaletteItem = item.dataset.item || "obstacle";
      refreshPaletteActive();
    });
    item.addEventListener("dragstart", (ev) => {
      const itemType = item.dataset.item || "obstacle";
      selectedPaletteItem = itemType;
      refreshPaletteActive();
      ev.dataTransfer?.setData("text/plain", itemType);
      ev.dataTransfer.effectAllowed = "copy";
    });
  });
  designerModal?.addEventListener("click", (ev) => {
    if (ev.target === designerModal) closeDesigner();
  });

  window.addEventListener("resize", resizeCanvas);
  window.addEventListener("beforeunload", () => {
    emitGameUpdate();
  });
}

async function resolveAssignmentRangeFromGrant() {
  if (!needsGrantCheck) return;
  if (runnerRole !== "student") return;
  try {
    const res = await fetch("/runner-grant/block-3d-runner", {
      credentials: "same-origin",
      headers: { Accept: "application/json" }
    });
    if (!res.ok) {
      grantDenied = true;
      return;
    }
    const grant = await res.json();
    if (!grant || !grant.ok || grant.role !== "student") {
      grantDenied = true;
      return;
    }
    const start = Math.max(1, Number(grant.from || 1));
    const end = Math.max(start, Number(grant.to || start));
    assignmentRange = { start, end };
  } catch (e) {
    grantDenied = true;
  }
}

async function init() {
  await resolveAssignmentRangeFromGrant();
  if (grantDenied) {
    setStatus("Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.", "error");
    levelSelect.disabled = true;
    runBtn.disabled = true;
    return;
  }
  rebuildLevelSelect(0);
  try {
    initBlockly();
  } catch (e) {
    blocklyReady = false;
    setStatus("Blok editor baslatilamadi.", "error");
    console.error("3D runner Blockly init error:", e);
  }
  bindUI();
  if (runnerRole === "student" && assignmentId) {
    sessionEarnedXp = 0;
    if (sessionTimerEl) sessionTimerEl.style.display = "block";
    if (sessionXpEl) sessionXpEl.style.display = "block";
    startSessionTimer(0);
    if (updatePingTimer) clearInterval(updatePingTimer);
    updatePingTimer = setInterval(() => emitGameUpdate(), 4000);
  } else {
    if (sessionTimerEl) sessionTimerEl.style.display = "none";
    if (sessionXpEl) sessionXpEl.style.display = "none";
  }
  resetState();
  if (runnerRole !== "teacher" && runnerRole !== "admin" && assignmentRange) {
    setStatus(`Odev araligi: Seviye ${assignmentRange.start}-${assignmentRange.end}`);
  }
  resizeCanvas();
  requestAnimationFrame(resizeCanvas);
  setTimeout(resizeCanvas, 120);
  setTimeout(resizeCanvas, 400);
  emitGameUpdate();
}

init();





