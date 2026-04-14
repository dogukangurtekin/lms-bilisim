(function () {
  "use strict";

  const STORAGE_KEY = "line_trace_progress_v2";
  const query = new URLSearchParams(window.location.search);
  const runnerRole = (query.get("role") || "").toLowerCase() === "teacher" ? "teacher" : "student";
  const rangeStartRaw = Math.max(1, Number(query.get("levelStart") || query.get("from") || 0));
  const rangeEndRaw = Math.max(rangeStartRaw, Number(query.get("levelEnd") || query.get("to") || rangeStartRaw));
  let assignmentRange = (runnerRole !== "teacher" && Number.isFinite(rangeStartRaw) && rangeStartRaw > 0)
    ? { start: rangeStartRaw - 1, end: rangeEndRaw - 1 }
    : null;
  let assignmentRangeCompletedSent = false;

  const bgCanvas = document.getElementById("bg-canvas");
  const drawCanvas = document.getElementById("draw-canvas");
  const board = document.getElementById("board");
  const boardWrap = document.querySelector(".board-wrap");
  const hud = document.querySelector(".hud");
  const levelSelect = document.getElementById("level-select");
  const levelGridEl = document.getElementById("level-grid");
  const levelsXpLabelEl = document.getElementById("levels-xp-label");

  const timerEl = document.getElementById("timer");
  const levelLabelEl = document.getElementById("level-label");
  const difficultyLabelEl = document.getElementById("difficulty-label");
  const currentLabelEl = document.getElementById("current-label");
  const assistLabelEl = document.getElementById("assist-label");
  const bestLabelEl = document.getElementById("best-label");
  const xpLabelEl = document.getElementById("xp-label");
  const rewardLabelEl = document.getElementById("reward-label");
  const statusEl = document.getElementById("status");
  const accuracyBarEl = document.getElementById("accuracy-bar");
  const retryBtn = document.getElementById("btn-retry");
  const nextBtn = document.getElementById("btn-next");
  const openLevelsBtn = document.getElementById("btn-open-levels");
  const resetAllBtn = document.getElementById("btn-reset-all");

  const bctx = bgCanvas.getContext("2d");
  const dctx = drawCanvas.getContext("2d");

  const levels = [
    { name: "Cizgi", difficulty: "easy", points: [[0.08, 0.50], [0.92, 0.50]], pass: 52, baseTol: 16 },
    { name: "Zikzak", difficulty: "easy", points: [[0.08, 0.58], [0.26, 0.42], [0.44, 0.58], [0.62, 0.42], [0.80, 0.58], [0.92, 0.50]], pass: 56, baseTol: 16 },
    { name: "Ucgen", difficulty: "easy", points: [[0.12, 0.78], [0.50, 0.20], [0.88, 0.78], [0.26, 0.78]], pass: 58, baseTol: 15 },
    { name: "Kare", difficulty: "medium", points: [[0.18, 0.78], [0.82, 0.78], [0.82, 0.22], [0.18, 0.22], [0.18, 0.78], [0.30, 0.78]], pass: 60, baseTol: 15 },
    { name: "Elmas", difficulty: "medium", points: [[0.50, 0.12], [0.82, 0.50], [0.50, 0.88], [0.18, 0.50], [0.50, 0.12], [0.63, 0.26]], pass: 63, baseTol: 14 },
    { name: "Dalga", difficulty: "medium", points: [[0.06, 0.55], [0.18, 0.30], [0.30, 0.70], [0.42, 0.32], [0.54, 0.68], [0.66, 0.30], [0.78, 0.64], [0.92, 0.45]], pass: 65, baseTol: 14 },
    { name: "Spiral", difficulty: "medium", points: [[0.85, 0.50], [0.75, 0.30], [0.45, 0.24], [0.26, 0.40], [0.30, 0.66], [0.52, 0.74], [0.66, 0.60], [0.62, 0.46], [0.48, 0.43], [0.44, 0.52], [0.52, 0.56], [0.58, 0.51]], pass: 68, baseTol: 13 },
    { name: "Yildiz", difficulty: "hard", points: [[0.50, 0.10], [0.62, 0.40], [0.92, 0.40], [0.68, 0.58], [0.78, 0.88], [0.50, 0.70], [0.22, 0.88], [0.32, 0.58], [0.08, 0.40], [0.38, 0.40], [0.50, 0.10], [0.60, 0.30]], pass: 72, baseTol: 12 },
    { name: "S-Kivrim", difficulty: "hard", points: [[0.10, 0.28], [0.32, 0.24], [0.50, 0.40], [0.68, 0.58], [0.90, 0.54], [0.80, 0.72], [0.58, 0.78], [0.36, 0.66], [0.14, 0.52]], pass: 74, baseTol: 12 },
    { name: "Usta Parkur", difficulty: "hard", points: [[0.08, 0.80], [0.24, 0.54], [0.16, 0.24], [0.42, 0.18], [0.58, 0.40], [0.72, 0.20], [0.90, 0.34], [0.76, 0.54], [0.90, 0.78], [0.66, 0.84], [0.52, 0.66], [0.34, 0.84], [0.12, 0.70]], pass: 78, baseTol: 11 }
  ];

  const XP_BY_DIFFICULTY = {
    easy: 6,
    medium: 9,
    hard: 17
  };

  const state = {
    level: 0,
    unlockedLevel: 0,
    completed: {},
    assist: 1.0,
    best: 0,
    startedAt: 0,
    ticking: 0,
    drawing: false,
    stroke: [],
    levelData: null,
    totalXp: 0,
    lastRewardXp: 0,
    canAdvance: false
  };

  function getRangeStartIndex() {
    if (!assignmentRange) return 0;
    return clamp(Number(assignmentRange.start || 0), 0, levels.length - 1);
  }

  async function resolveAssignmentRangeFromGrant() {
    if (runnerRole === "teacher") return;
    try {
      const res = await fetch("/runner-grant/line-trace-runner", {
        credentials: "same-origin",
        headers: { Accept: "application/json" }
      });
      if (!res.ok) return;
      const grant = await res.json();
      if (!grant || !grant.ok || grant.role !== "student") return;
      const from = Math.max(1, Number(grant.from || 1));
      const to = Math.max(from, Number(grant.to || from));
      assignmentRange = { start: from - 1, end: to - 1 };
    } catch (e) {}
  }

  function getRangeEndIndex() {
    if (!assignmentRange) return levels.length - 1;
    return clamp(Number(assignmentRange.end || levels.length - 1), getRangeStartIndex(), levels.length - 1);
  }

  function isLevelInAllowedRange(idx) {
    if (!assignmentRange) return true;
    const n = Number(idx || 0);
    return n >= getRangeStartIndex() && n <= getRangeEndIndex();
  }

  function clamp(v, min, max) {
    return Math.max(min, Math.min(max, v));
  }

  function getDifficultyLabel(token) {
    if (token === "easy") return "Kolay";
    if (token === "hard") return "Zor";
    return "Orta";
  }

  function getLevelRewardXp(level) {
    return XP_BY_DIFFICULTY[String(level?.difficulty || "medium")] || 9;
  }

  function dist(a, b) {
    const dx = a.x - b.x;
    const dy = a.y - b.y;
    return Math.hypot(dx, dy);
  }

  function nearestOnSegment(p, a, b) {
    const abx = b.x - a.x;
    const aby = b.y - a.y;
    const apx = p.x - a.x;
    const apy = p.y - a.y;
    const denom = (abx * abx) + (aby * aby) || 1;
    const t = clamp((apx * abx + apy * aby) / denom, 0, 1);
    return { x: a.x + abx * t, y: a.y + aby * t, t };
  }

  function samplePolyline(points, stepPx) {
    const sampled = [];
    if (!points || points.length < 2) return sampled;
    for (let i = 0; i < points.length - 1; i++) {
      const a = points[i];
      const b = points[i + 1];
      const segLen = Math.max(1, dist(a, b));
      const n = Math.max(1, Math.ceil(segLen / stepPx));
      for (let s = 0; s <= n; s++) {
        const t = s / n;
        sampled.push({ x: a.x + (b.x - a.x) * t, y: a.y + (b.y - a.y) * t });
      }
    }
    return sampled;
  }

  function buildPathData(rawPoints, w, h) {
    const points = rawPoints.map((p) => ({ x: p[0] * w, y: p[1] * h }));
    let totalLen = 0;
    const segs = [];
    for (let i = 0; i < points.length - 1; i++) {
      const a = points[i];
      const b = points[i + 1];
      const len = dist(a, b);
      segs.push({ a, b, len, startLen: totalLen });
      totalLen += len;
    }
    return {
      points,
      segs,
      totalLen,
      start: points[0],
      end: points[points.length - 1],
      dots: samplePolyline(points, 22)
    };
  }

  function getPointerPos(ev) {
    const rect = drawCanvas.getBoundingClientRect();
    return { x: ev.clientX - rect.left, y: ev.clientY - rect.top };
  }

  function stopTimer() {
    clearInterval(state.ticking);
    state.ticking = 0;
  }

  function startTimer() {
    if (state.startedAt) return;
    state.startedAt = Date.now();
    stopTimer();
    state.ticking = setInterval(() => {
      const sec = Math.max(0, (Date.now() - state.startedAt) / 1000);
      timerEl.textContent = `${sec.toFixed(1)} sn`;
    }, 50);
  }

  function setupCanvasSize() {
    const rect = board.getBoundingClientRect();
    const dpr = Math.max(1, window.devicePixelRatio || 1);
    [bgCanvas, drawCanvas].forEach((cv) => {
      cv.width = Math.round(rect.width * dpr);
      cv.height = Math.round(rect.height * dpr);
      cv.style.width = rect.width + "px";
      cv.style.height = rect.height + "px";
    });
    bctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    dctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    state.levelData = buildPathData(levels[state.level].points, rect.width, rect.height);
    redrawAll();
  }

  function drawTarget() {
    const level = levels[state.level];
    const d = state.levelData;
    const corr = level.baseTol * state.assist;
    bctx.clearRect(0, 0, bgCanvas.clientWidth, bgCanvas.clientHeight);
    bctx.lineCap = "round";
    bctx.lineJoin = "round";

    bctx.strokeStyle = "rgba(59,130,246,0.20)";
    bctx.lineWidth = Math.max(2, corr * 2);
    bctx.beginPath();
    bctx.moveTo(d.points[0].x, d.points[0].y);
    d.points.slice(1).forEach((p) => bctx.lineTo(p.x, p.y));
    bctx.stroke();

    bctx.fillStyle = "rgba(157,184,220,0.35)";
    d.dots.forEach((p) => {
      bctx.beginPath();
      bctx.arc(p.x, p.y, 6, 0, Math.PI * 2);
      bctx.fill();
    });

    bctx.fillStyle = "#10b981";
    bctx.beginPath();
    bctx.arc(d.start.x, d.start.y, 18, 0, Math.PI * 2);
    bctx.fill();

    bctx.fillStyle = "#ef4444";
    bctx.beginPath();
    bctx.arc(d.end.x, d.end.y, 18, 0, Math.PI * 2);
    bctx.fill();
  }

  function drawStroke() {
    dctx.clearRect(0, 0, drawCanvas.clientWidth, drawCanvas.clientHeight);
    if (state.stroke.length < 2) return;
    dctx.lineWidth = 9;
    dctx.lineJoin = "round";
    dctx.lineCap = "round";
    dctx.strokeStyle = "#4ade80";
    dctx.beginPath();
    dctx.moveTo(state.stroke[0].x, state.stroke[0].y);
    for (let i = 1; i < state.stroke.length; i++) dctx.lineTo(state.stroke[i].x, state.stroke[i].y);
    dctx.stroke();
  }

  function redrawAll() {
    drawTarget();
    drawStroke();
  }

  function setStatus(text, color) {
    statusEl.textContent = text;
    statusEl.style.color = color || "#8ea2bf";
  }

  function updateHud(accuracy, barValue = accuracy) {
    const l = levels[state.level];
    levelLabelEl.textContent = `Seviye ${state.level + 1} - ${l.name}`;
    difficultyLabelEl.textContent = `Zorluk: ${getDifficultyLabel(l.difficulty)}`;
    assistLabelEl.textContent = `Yardim: x${state.assist.toFixed(2)}`;
    bestLabelEl.textContent = `En iyi: %${Math.round(state.best)}`;
    if (currentLabelEl) currentLabelEl.textContent = `Anlik: %${Math.round(clamp(accuracy || 0, 0, 100))}`;
    xpLabelEl.textContent = `Toplam XP: ${state.totalXp}`;
    rewardLabelEl.textContent = `Son Odul: +${state.lastRewardXp} XP`;
    levelsXpLabelEl.textContent = `Toplam XP: ${state.totalXp}`;
    accuracyBarEl.style.width = `${clamp(barValue || 0, 0, 100)}%`;
    nextBtn.disabled = !state.canAdvance;
  }

  function getProjection(p, d) {
    let best = { dist: Infinity, prog: 0 };
    d.segs.forEach((seg) => {
      const q = nearestOnSegment(p, seg.a, seg.b);
      const distance = dist(p, q);
      if (distance < best.dist) {
        best = { dist: distance, prog: seg.startLen + seg.len * q.t };
      }
    });
    return best;
  }

  function evaluateStroke() {
    const level = levels[state.level];
    const d = state.levelData;
    const tol = level.baseTol * state.assist;
    if (state.stroke.length < 6) {
      return { accuracy: 0, pass: false, hudProgress: 0 };
    }

    let onPathCount = 0;
    let recentOnPathCount = 0;
    let maxProg = 0;
    let strokeLen = 0;
    const recentWindow = Math.min(14, state.stroke.length);
    const recentStart = state.stroke.length - recentWindow;

    for (let i = 0; i < state.stroke.length; i++) {
      if (i > 0) strokeLen += dist(state.stroke[i - 1], state.stroke[i]);
      const proj = getProjection(state.stroke[i], d);
      const onTrack = proj.dist <= tol;
      if (onTrack) {
        onPathCount++;
        if (proj.prog > maxProg) maxProg = proj.prog;
      }
      if (i >= recentStart && onTrack) recentOnPathCount++;
    }

    const onPath = onPathCount / state.stroke.length;
    const recentOnPath = recentOnPathCount / recentWindow;
    const coverageByProjection = clamp(maxProg / (d.totalLen || 1), 0, 1);
    const coverageByStrokeLen = clamp(strokeLen / ((d.totalLen || 1) * 1.15), 0, 1);
    // Projection alone can inflate bar too early, so limit by stroke length.
    const coverage = Math.min(coverageByProjection, coverageByStrokeLen);
    const reachedEnd = dist(state.stroke[state.stroke.length - 1], d.end) <= (tol * 1.5);
    const lengthFactor = clamp(1 - Math.abs(strokeLen - d.totalLen) / (d.totalLen * 1.7), 0, 1);
    const endFactor = reachedEnd ? 1 : 0.35;
    const accuracy = clamp(((onPath * 0.54) + (coverage * 0.30) + (lengthFactor * 0.08) + (endFactor * 0.08)) * 100, 0, 100);

    // Outside guide: bar slows heavily. Inside guide: bar fills normally.
    const trackFactor = clamp((onPath * 0.45) + (recentOnPath * 0.55), 0, 1);
    const hudBase = (coverage * 100 * 0.9) + (accuracy * 0.1);
    const hudProgress = clamp(hudBase * (0.2 + (0.8 * trackFactor)), 0, 100);

    const pass = accuracy >= 90;
    return { accuracy, pass, hudProgress };
  }

  function computeTotalXp() {
    let xp = 0;
    for (let idx = 0; idx < levels.length; idx++) {
      if (state.completed[`${idx}_pass`]) xp += getLevelRewardXp(levels[idx]);
    }
    return xp;
  }

  function saveProgress() {
    const payload = {
      unlockedLevel: state.unlockedLevel,
      completed: state.completed,
      assist: state.assist
    };
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch (e) {}
  }

  function loadProgress() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      const data = raw ? JSON.parse(raw) : null;
      if (!data || typeof data !== "object") return;
      state.unlockedLevel = clamp(Number(data.unlockedLevel || 0), 0, levels.length - 1);
      state.completed = data.completed && typeof data.completed === "object" ? data.completed : {};
      state.assist = clamp(Number(data.assist || 1), 1, 1.45);
    } catch (e) {}
    state.totalXp = computeTotalXp();
  }

  function resetAllProgress() {
    const ok = window.confirm("Tum cizgi oyunu verileri sifirlansin mi?");
    if (!ok) return;
    try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    state.unlockedLevel = 0;
    state.completed = {};
    state.assist = 1.0;
    state.totalXp = 0;
    state.lastRewardXp = 0;
    state.best = 0;
    state.canAdvance = false;
    renderLevelGrid();
    showLevelSelect(true);
    updateHud(0);
    setStatus("Veriler sifirlandi. 1. seviyeden baslayabilirsin.", "#f59e0b");
  }

  function showLevelSelect(show) {
    levelSelect.style.display = show ? "block" : "none";
    boardWrap.style.display = show ? "none" : "block";
    hud.style.display = show ? "none" : "grid";
  }

  function renderLevelGrid() {
    levelGridEl.innerHTML = "";
    levels.forEach((level, idx) => {
      if (!isLevelInAllowedRange(idx)) return;
      const unlocked = idx <= state.unlockedLevel;
      const done = !!state.completed[`${idx}_pass`];
      const card = document.createElement("button");
      card.type = "button";
      card.className = `level-card${unlocked ? "" : " locked"}${done ? " done" : ""}`;
      card.disabled = !unlocked;
      card.innerHTML = `
        <div class="name">${idx + 1}. ${level.name}${done ? " ✓" : ""}${!unlocked ? " 🔒" : ""}</div>
        <div class="meta">
          <span>${getDifficultyLabel(level.difficulty)}</span>
          <span>+${getLevelRewardXp(level)} XP</span>
        </div>
      `;
      card.addEventListener("click", () => {
        if (!unlocked) return;
        startLevel(idx);
      });
      levelGridEl.appendChild(card);
    });
    levelsXpLabelEl.textContent = `Toplam XP: ${state.totalXp}`;
  }

  function startLevel(levelIndex) {
    if (!isLevelInAllowedRange(levelIndex)) {
      setStatus("Bu seviyeye erisim yok. Sadece atanan odev araligini oynayabilirsiniz.", "#ef4444");
      return;
    }
    state.level = clamp(Number(levelIndex || 0), 0, levels.length - 1);
    state.best = Number(state.completed[`${state.level}_best`] || 0);
    state.stroke = [];
    state.startedAt = 0;
    state.canAdvance = false;
    timerEl.textContent = "0.0 sn";
    stopTimer();
    showLevelSelect(false);
    setupCanvasSize();
    updateHud(0);
    setStatus("Baslamak icin yesil noktadan ciz.", "#8ea2bf");
  }

  function resetCurrentLevel() {
    state.stroke = [];
    state.startedAt = 0;
    timerEl.textContent = "0.0 sn";
    stopTimer();
    drawStroke();
    updateHud(0);
    setStatus("Tekrar dene. Yesil noktadan ciz.", "#8ea2bf");
  }

  function onPointerDown(ev) {
    ev.preventDefault();
    const p = getPointerPos(ev);
    if (dist(p, state.levelData.start) > 30) {
      setStatus("Cizime yesil noktadan basla.", "#f59e0b");
      return;
    }
    state.drawing = true;
    state.stroke = [p];
    startTimer();
    drawStroke();
  }

  function onPointerMove(ev) {
    if (!state.drawing) return;
    const p = getPointerPos(ev);
    const prev = state.stroke[state.stroke.length - 1];
    if (!prev || dist(prev, p) >= 1.8) {
      state.stroke.push(p);
      drawStroke();
      const live = evaluateStroke();
      updateHud(live.accuracy, live.hudProgress);
    }
  }

  function onPointerUp(ev) {
    if (!state.drawing) return;
    state.drawing = false;
    const p = getPointerPos(ev);
    state.stroke.push(p);
    drawStroke();
    stopTimer();

    const result = evaluateStroke();
    state.best = Math.max(state.best, result.accuracy);
    state.completed[`${state.level}_best`] = Math.max(Number(state.completed[`${state.level}_best`] || 0), Math.round(state.best));
    updateHud(result.accuracy);

    if (result.pass) {
      const sec = Math.max(0, (Date.now() - state.startedAt) / 1000);
      const firstPass = !state.completed[state.level + "_pass"];
      const rewardXp = getLevelRewardXp(levels[state.level]);
      state.lastRewardXp = firstPass ? rewardXp : 0;
      state.completed[state.level + "_pass"] = true;
      const maxAllowedNext = getRangeEndIndex();
      if (state.level < levels.length - 1 && state.level < maxAllowedNext) {
        state.unlockedLevel = Math.max(state.unlockedLevel, state.level + 1);
      }
      state.totalXp = computeTotalXp();
      state.canAdvance = true;
      saveProgress();
      renderLevelGrid();
      updateHud(result.accuracy);
      setStatus(`Gectin! Basari %${Math.round(result.accuracy)} - hedef tamamlandi.`, "#34d399");
      try {
        window.parent.postMessage({
          type: "LINE_TRACE_LEVEL_COMPLETE",
          level: state.level + 1,
          accuracy: Math.round(result.accuracy),
          seconds: Number(sec.toFixed(2)),
          xp: state.lastRewardXp,
          difficulty: String(levels[state.level].difficulty || "medium")
        }, "*");
      } catch (e) {}

      if (
        assignmentRange &&
        !assignmentRangeCompletedSent &&
        Number(state.level) >= Number(getRangeEndIndex())
      ) {
        assignmentRangeCompletedSent = true;
        try {
          window.parent.postMessage({
            type: "ASSIGNMENT_RANGE_COMPLETED",
            source: "line-trace",
            xp: Number(state.totalXp || 0),
            elapsedSeconds: Math.max(0, Number((Date.now() - (state.startedAt || Date.now())) / 1000))
          }, "*");
        } catch (e) {}
      }
    } else {
      state.canAdvance = false;
      saveProgress();
      setStatus(`Yetersiz: %${Math.round(result.accuracy)} (hedef %90) - tekrar dene.`, "#ef4444");
      setTimeout(() => {
        resetCurrentLevel();
      }, 800);
    }
  }

  drawCanvas.addEventListener("pointerdown", onPointerDown);
  drawCanvas.addEventListener("pointermove", onPointerMove);
  drawCanvas.addEventListener("pointerup", onPointerUp);
  drawCanvas.addEventListener("pointercancel", onPointerUp);
  retryBtn.addEventListener("click", resetCurrentLevel);
  nextBtn.addEventListener("click", () => {
    if (
      state.canAdvance &&
      state.level < levels.length - 1 &&
      state.level + 1 <= state.unlockedLevel &&
      isLevelInAllowedRange(state.level + 1)
    ) {
      startLevel(state.level + 1);
      return;
    }
    showLevelSelect(true);
  });
  openLevelsBtn.addEventListener("click", () => {
    showLevelSelect(true);
    renderLevelGrid();
  });
  resetAllBtn.addEventListener("click", resetAllProgress);
  window.addEventListener("resize", () => {
    if (boardWrap.style.display !== "none") setupCanvasSize();
  });

  resolveAssignmentRangeFromGrant().finally(() => {
  loadProgress();
  if (assignmentRange) {
    const startIdx = getRangeStartIndex();
    const endIdx = getRangeEndIndex();
    state.unlockedLevel = clamp(Math.max(state.unlockedLevel, startIdx), startIdx, endIdx);
    state.level = startIdx;
  }
  renderLevelGrid();
  showLevelSelect(true);
  updateHud(0);
  });
})();
