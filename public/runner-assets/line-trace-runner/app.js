(function () {
  "use strict";

  const STORAGE_KEY = "line_trace_progress_v3";
  const query = new URLSearchParams(window.location.search);
  const roleParam = (query.get("role") || "").toLowerCase();
  const runnerRole = (roleParam === "teacher" || roleParam === "admin") ? roleParam : "student";
  const assignmentId = String(query.get("assignmentId") || "").trim();
  const rangeStartRaw = Math.max(1, Number(query.get("levelStart") || query.get("from") || 0));
  const rangeEndRaw = Math.max(rangeStartRaw, Number(query.get("levelEnd") || query.get("to") || rangeStartRaw));
  const hasRange = Number.isFinite(rangeStartRaw) && rangeStartRaw > 0;
  const enforceGrant = query.get("grant") === "1" || query.get("enforceGrant") === "1";
  const needsGrantCheck = runnerRole === "student" && (!!assignmentId || hasRange || enforceGrant);
  let assignmentRange = ((runnerRole !== "teacher" && runnerRole !== "admin") && Number.isFinite(rangeStartRaw) && rangeStartRaw > 0)
    ? { start: rangeStartRaw - 1, end: rangeEndRaw - 1 }
    : { start: 0, end: 1 };
  let grantDenied = false;
  let assignmentRangeCompletedSent = false;
  const isStaff = runnerRole === "teacher" || runnerRole === "admin";

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

  // 100 bölümlük kategorili set (Kolay / Orta / Zor / Uzman) levels.js içinde tanımlıdır (tools/learn-levels/line-trace.gen.mjs ile üretilir).
  const levels = Array.isArray(window.LINE_TRACE_LEVELS) ? window.LINE_TRACE_LEVELS : [];

  const XP_BY_DIFFICULTY = {
    easy: 6,
    medium: 9,
    hard: 17,
    expert: 25
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
    if (!needsGrantCheck) return;
    if (runnerRole === "teacher" || runnerRole === "admin") return;
    try {
      const grantUrl = (window.RUNNER_APP_BASE || "").replace(/\/$/, "") + "/runner-grant/line-trace-runner" + window.location.search;
      const res = await fetch(grantUrl, {
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
      const from = Math.max(1, Number(grant.from || 1));
      const to = Math.max(from, Number(grant.to || from));
      assignmentRange = { start: from - 1, end: to - 1 };
    } catch (e) {
      grantDenied = true;
    }
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
    if (token === "expert") return "Uzman";
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

  function buildPathData(rawPoints, w, h, units) {
    let points;
    if (units === "ar2") {
      const s = Math.min(w / 2, h);
      const ox = (w - 2 * s) / 2;
      const oy = (h - s) / 2;
      points = rawPoints.map((p) => ({ x: ox + p[0] * s, y: oy + p[1] * s }));
    } else {
      points = rawPoints.map((p) => ({ x: p[0] * w, y: p[1] * h }));
    }
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
    state.levelData = buildPathData(levels[state.level].points, rect.width, rect.height, levels[state.level].units);
    redrawAll();
  }

  function drawTarget() {
    const level = levels[state.level];
    const d = state.levelData;
    const corr = level.baseTol * state.assist;
    const w = bgCanvas.clientWidth;
    const h = bgCanvas.clientHeight;
    bctx.clearRect(0, 0, w, h);
    bctx.lineCap = "round";
    bctx.lineJoin = "round";

    const tracePath = () => {
      bctx.beginPath();
      bctx.moveTo(d.points[0].x, d.points[0].y);
      d.points.slice(1).forEach((p) => bctx.lineTo(p.x, p.y));
    };

    // izin verilen koridor (yumuşak ışıma)
    bctx.save();
    bctx.strokeStyle = "rgba(96,165,250,0.16)";
    bctx.lineWidth = Math.max(2, corr * 2);
    tracePath();
    bctx.stroke();
    bctx.strokeStyle = "rgba(147,197,253,0.18)";
    bctx.lineWidth = Math.max(2, corr * 1.1);
    tracePath();
    bctx.stroke();
    bctx.restore();

    // hedef yolun ortası: kesikli ışıklı kılavuz
    bctx.save();
    bctx.setLineDash([10, 9]);
    bctx.strokeStyle = "rgba(224,236,255,0.85)";
    bctx.shadowColor = "rgba(147,197,253,0.9)";
    bctx.shadowBlur = 8;
    bctx.lineWidth = 3;
    tracePath();
    bctx.stroke();
    bctx.restore();

    // ara noktalar
    d.dots.forEach((p) => {
      bctx.fillStyle = "rgba(147,197,253,0.25)";
      bctx.beginPath();
      bctx.arc(p.x, p.y, 10, 0, Math.PI * 2);
      bctx.fill();
      bctx.fillStyle = "#e0ecff";
      bctx.beginPath();
      bctx.arc(p.x, p.y, 5, 0, Math.PI * 2);
      bctx.fill();
    });

    const marker = (pt, color, glow, label) => {
      bctx.save();
      bctx.shadowColor = glow;
      bctx.shadowBlur = 22;
      bctx.fillStyle = color;
      bctx.beginPath();
      bctx.arc(pt.x, pt.y, 18, 0, Math.PI * 2);
      bctx.fill();
      bctx.restore();
      bctx.strokeStyle = "rgba(255,255,255,0.9)";
      bctx.lineWidth = 3;
      bctx.beginPath();
      bctx.arc(pt.x, pt.y, 18, 0, Math.PI * 2);
      bctx.stroke();
      bctx.fillStyle = "#ffffff";
      bctx.font = "900 11px Nunito, Segoe UI, sans-serif";
      bctx.textAlign = "center";
      bctx.textBaseline = "middle";
      bctx.fillText(label, pt.x, pt.y + 1);
    };
    marker(d.start, "#16a34a", "rgba(74,222,128,0.95)", "▶");
    marker(d.end, "#dc2626", "rgba(248,113,113,0.95)", "🏁");
  }

  function drawStroke() {
    dctx.clearRect(0, 0, drawCanvas.clientWidth, drawCanvas.clientHeight);
    if (state.stroke.length < 2) return;
    dctx.lineJoin = "round";
    dctx.lineCap = "round";
    const path = () => {
      dctx.beginPath();
      dctx.moveTo(state.stroke[0].x, state.stroke[0].y);
      for (let i = 1; i < state.stroke.length; i++) dctx.lineTo(state.stroke[i].x, state.stroke[i].y);
    };
    dctx.save();
    dctx.shadowColor = "rgba(74,222,128,0.9)";
    dctx.shadowBlur = 16;
    dctx.strokeStyle = "#4ade80";
    dctx.lineWidth = 9;
    path();
    dctx.stroke();
    dctx.restore();
    dctx.strokeStyle = "#bbf7d0";
    dctx.lineWidth = 3;
    path();
    dctx.stroke();
  }

  function redrawAll() {
    drawTarget();
    drawStroke();
  }

  function setStatus(text, color) {
    statusEl.textContent = text;
    statusEl.style.color = color || "#5b7096";
  }

  function updateHud(accuracy, barValue = accuracy) {
    const l = levels[state.level];
    levelLabelEl.textContent = `Seviye ${state.level + 1} - ${l.name}`;
    difficultyLabelEl.textContent = `Zorluk: ${getDifficultyLabel(l.difficulty)}`;
    assistLabelEl.textContent = `Yardım: x${state.assist.toFixed(2)}`;
    bestLabelEl.textContent = `En iyi: %${Math.round(state.best)}`;
    if (currentLabelEl) currentLabelEl.textContent = `Anlık: %${Math.round(clamp(accuracy || 0, 0, 100))}`;
    xpLabelEl.textContent = `Toplam XP: ${state.totalXp}`;
    rewardLabelEl.textContent = `Son Ödül: +${state.lastRewardXp} XP`;
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
      if (!isStaff) state.unlockedLevel = Math.max(state.unlockedLevel, 1);
      state.completed = data.completed && typeof data.completed === "object" ? data.completed : {};
      state.assist = clamp(Number(data.assist || 1), 1, 1.45);
    } catch (e) {}
    state.totalXp = computeTotalXp();
  }

  function resetAllProgress() {
    const ok = window.confirm("Tüm çizgi oyunu verileri sıfırlansın mı?");
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
    setStatus("Veriler sıfırlandı. 1. seviyeden başlayabilirsin.", "#b45309");
  }

  function showLevelSelect(show) {
    levelSelect.style.display = show ? "block" : "none";
    boardWrap.style.display = show ? "none" : "block";
    hud.style.display = show ? "none" : "grid";
  }

  function renderLevelGrid() {
    levelGridEl.innerHTML = "";
    let lastGroup = "";
    levels.forEach((level, idx) => {
      if (!isLevelInAllowedRange(idx)) return;
      if (level.difficulty !== lastGroup) {
        lastGroup = level.difficulty;
        const count = levels.filter((l, i) => l.difficulty === lastGroup && isLevelInAllowedRange(i)).length;
        const doneInGroup = levels.filter((l, i) => l.difficulty === lastGroup && isLevelInAllowedRange(i) && state.completed[`${i}_pass`]).length;
        const head = document.createElement("div");
        head.className = `level-group-title grp-${lastGroup}`;
        head.textContent = `${getDifficultyLabel(lastGroup)} · ${doneInGroup} / ${count} bölüm · +${XP_BY_DIFFICULTY[lastGroup] || 9} XP`;
        levelGridEl.appendChild(head);
      }
      const unlocked = (runnerRole === "teacher" || runnerRole === "admin") ? true : (idx <= state.unlockedLevel);
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
    if (grantDenied) {
      setStatus("Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.", "#dc2626");
      return;
    }
    if (!isLevelInAllowedRange(levelIndex)) {
      setStatus("Bu seviyeye erişim yok. Sadece atanan ödev aralığını oynayabilirsiniz.", "#dc2626");
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
    setStatus("Başlamak için yeşil noktadan çiz.", "#5b7096");
  }

  function resetCurrentLevel() {
    state.stroke = [];
    state.startedAt = 0;
    timerEl.textContent = "0.0 sn";
    stopTimer();
    drawStroke();
    updateHud(0);
    setStatus("Tekrar dene. Yeşil noktadan çiz.", "#5b7096");
  }

  function onPointerDown(ev) {
    ev.preventDefault();
    const p = getPointerPos(ev);
    if (dist(p, state.levelData.start) > 30) {
      setStatus("Çizime yeşil noktadan başla.", "#b45309");
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
      setStatus(`Geçtin! Başarı %${Math.round(result.accuracy)} - hedef tamamlandı.`, "#15803d");
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
      setStatus(`Yetersiz: %${Math.round(result.accuracy)} (hedef %90) - tekrar dene.`, "#dc2626");
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
      ((runnerRole === "teacher" || runnerRole === "admin") || state.level + 1 <= state.unlockedLevel) &&
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
  if (grantDenied) {
    setStatus("Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.", "#dc2626");
    retryBtn.disabled = true;
    nextBtn.disabled = true;
    openLevelsBtn.disabled = true;
    resetAllBtn.disabled = true;
    levelGridEl.innerHTML = "";
    showLevelSelect(true);
    return;
  }
  loadProgress();
  if (isStaff) {
    assignmentRange = null;
    grantDenied = false;
    state.unlockedLevel = levels.length - 1;
  }
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
