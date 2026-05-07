(function () {
  const canvas = document.getElementById("game-canvas");
  const ctx = canvas.getContext("2d");
  const toastEl = document.getElementById("toast");
  const levelIdEl = document.getElementById("level-id");
  const levelTitleEl = document.getElementById("level-title");
  const btnRun = document.getElementById("btn-run");
  const btnStop = document.getElementById("btn-stop");
  const btnReset = document.getElementById("btn-reset-level");
  const btnPrev = document.getElementById("btn-prev-level");
  const trackEls = {
    main: document.getElementById("program-track-main"),
    proc1: document.getElementById("program-track-proc1"),
    proc2: document.getElementById("program-track-proc2")
  };
  const commandBar = document.getElementById("command-bar");
  const tabBtns = Array.from(document.querySelectorAll(".tab-btn"));

  const params = new URLSearchParams(window.location.search);
  const roleParam = (params.get("role") || "").toLowerCase();
  const runnerRole = (roleParam === "teacher" || roleParam === "admin") ? roleParam : "student";
  const assignmentId = String(params.get("assignmentId") || "").trim();
  const assignmentTitle = String(params.get("assignmentTitle") || "Code Robot Lab Odevi").trim();
  const rangeFrom = Math.max(1, Number(params.get("levelStart") || params.get("from") || 0));
  const rangeTo = Math.max(rangeFrom, Number(params.get("levelEnd") || params.get("to") || rangeFrom));
  const hasRange = Number.isFinite(rangeFrom) && rangeFrom > 0;
  const enforceGrant = params.get("grant") === "1" || params.get("enforceGrant") === "1";
  const needsGrantCheck = runnerRole === "student" && (!!assignmentId || hasRange || enforceGrant);
  const isStaff = runnerRole === "teacher" || runnerRole === "admin";
  let grantDenied = false;
  let levelStart = hasRange ? rangeFrom : 1;
  let levelEndParam = hasRange ? rangeTo : levelStart;
  let isAssignmentMode = !!assignmentId || hasRange;
  if (!isStaff && !isAssignmentMode) {
    isAssignmentMode = true;
    levelStart = 1;
    levelEndParam = 2;
  }
  if (isStaff) {
    isAssignmentMode = false;
    levelStart = 1;
    levelEndParam = 9999;
    grantDenied = false;
  }
  const sessionStartedAt = Date.now();
  let rangeCompleteFired = false;

  const DIRS = [
    { x: 1, y: 0 },
    { x: 0, y: 1 },
    { x: -1, y: 0 },
    { x: 0, y: -1 }
  ];
  const COMMANDS = [
    { id: "F", icon: "↑", title: "Ileri", accent: true },
    { id: "L", icon: "↺", title: "Sola Don" },
    { id: "R", icon: "↻", title: "Saga Don" },
    { id: "J", icon: "⤒", title: "Zipla", accent: true },
    { id: "B", icon: "◉", title: "Isik Yak", accent: true },
    { id: "P1", icon: "P1", title: "Prosedur 1" },
    { id: "P2", icon: "P2", title: "Prosedur 2" },
    { id: "X", icon: "⌫", title: "Sil" }
  ];
  const TRACK_ORDER = ["main", "proc1", "proc2"];
  const TRACK_TITLE = { main: "MAIN", proc1: "PROC1", proc2: "PROC2" };
  const STEP_DELAY = 260;
  const XP_PER_LEVEL = 20;

  let levels = [];
  let levelIndex = 0;
  let activeTrack = "main";
  let activeStepKey = "";
  let runState = null;
  let robot = null;
  let litMap = new Set();
  let currentProgram = { main: [], proc1: [], proc2: [] };
  let completedLevelNos = new Set();

  function elapsedSeconds() {
    return Math.max(0, Math.round((Date.now() - sessionStartedAt) / 1000));
  }

  function tileKey(x, y) {
    return `${x},${y}`;
  }

  function mkStepCommandSeq(path) {
    const cmds = [];
    let dir = path.startDir;
    let prev = path.points[0];
    for (let i = 1; i < path.points.length; i += 1) {
      const next = path.points[i];
      const dx = next.x - prev.x;
      const dy = next.y - prev.y;
      const targetDir = dx > 0 ? 0 : dx < 0 ? 2 : dy > 0 ? 1 : 3;
      while (dir !== targetDir) {
        const rightDelta = (targetDir - dir + 4) % 4;
        if (rightDelta === 1 || rightDelta === 2) {
          cmds.push("R");
          dir = (dir + 1) % 4;
        } else {
          cmds.push("L");
          dir = (dir + 3) % 4;
        }
      }
      cmds.push(next.h !== prev.h ? "J" : "F");
      if (next.light) cmds.push("B");
      prev = next;
    }
    if (path.points[0].light) cmds.unshift("B");
    return cmds;
  }

  function generateBand(bandName, bandNo, count, options) {
    const out = [];
    const { startMainSlots, startProcSlots, useProc1At, useProc2At, jumpCount } = options;
    for (let i = 0; i < count; i += 1) {
      if (bandNo === 1 && i < 5) {
        const tutorialDefs = [
          {
            title: "Ogretici 1 - Ileri Git ve Isik Yak",
            start: { x: 0, y: 2, h: 0, dir: 0 },
            path: [
              { x: 0, y: 2, h: 0, light: false },
              { x: 1, y: 2, h: 0, light: false },
              { x: 2, y: 2, h: 0, light: true }
            ],
            allowJump: false
          },
          {
            title: "Ogretici 2 - Duz Ilerleme",
            start: { x: 0, y: 3, h: 0, dir: 0 },
            path: [
              { x: 0, y: 3, h: 0, light: false },
              { x: 1, y: 3, h: 0, light: true },
              { x: 2, y: 3, h: 0, light: true }
            ],
            allowJump: false
          },
          {
            title: "Ogretici 3 - Saga Don",
            start: { x: 0, y: 2, h: 0, dir: 0 },
            path: [
              { x: 0, y: 2, h: 0, light: false },
              { x: 1, y: 2, h: 0, light: false },
              { x: 1, y: 3, h: 0, light: true }
            ],
            allowJump: false
          },
          {
            title: "Ogretici 4 - Donusleri Birlestir",
            start: { x: 0, y: 2, h: 0, dir: 0 },
            path: [
              { x: 0, y: 2, h: 0, light: false },
              { x: 1, y: 2, h: 0, light: false },
              { x: 1, y: 3, h: 0, light: true },
              { x: 2, y: 3, h: 0, light: true }
            ],
            allowJump: false
          },
          {
            title: "Ogretici 5 - Zipla ve Don",
            start: { x: 0, y: 2, h: 0, dir: 0 },
            path: [
              { x: 0, y: 2, h: 0, light: false },
              { x: 1, y: 2, h: 0, light: false },
              { x: 2, y: 2, h: 1, light: true },
              { x: 2, y: 3, h: 1, light: true }
            ],
            allowJump: true
          }
        ];
        const td = tutorialDefs[i];
        const sampleSolution = mkStepCommandSeq({ points: td.path, startDir: td.start.dir });
        const mainSlots = Math.max(8 + i, sampleSolution.length + 2);
        out.push({
          id: `${bandNo}-${i + 1}`,
          title: `${bandName} - ${i + 1} (${td.title})`,
          maxSlots: { main: mainSlots, proc1: 0, proc2: 0 },
          allow: { jump: td.allowJump, proc1: false, proc2: false },
          board: {
            start: { ...td.start },
            tiles: td.path.map((t) => ({ x: t.x, y: t.y, h: t.h, light: !!t.light })),
            endHint: { x: td.path[td.path.length - 1].x, y: td.path[td.path.length - 1].y }
          },
          sampleSolution
        });
        continue;
      }

      const isEarlyEasy = bandNo === 1 && i < 6;
      const baseY = 2 + (i % 3);
      const pathPoints = [];
      const len = isEarlyEasy ? (4 + Math.floor(i / 2)) : (5 + (i % 4) + (bandNo > 1 ? 1 : 0));
      for (let p = 0; p < len; p += 1) {
        const x = p;
        const y = isEarlyEasy ? baseY : (baseY + ((p % 2 === 0 && bandNo > 1) ? 1 : 0));
        const h = (bandNo === 1 ? 0 : Math.min(2, Math.floor((p + i) % jumpCount)));
        const light = isEarlyEasy ? (p === len - 1) : (p > 0 && p % 2 === 0);
        pathPoints.push({ x, y, h, light });
      }
      const extra = isEarlyEasy ? [] : [
        { x: len - 2, y: baseY + 2, h: Math.min(2, bandNo), light: true },
        { x: len - 1, y: baseY + 2, h: Math.min(2, bandNo), light: bandNo > 1 }
      ];
      extra.forEach((t) => pathPoints.push(t));
      const uniqueMap = new Map();
      pathPoints.forEach((t) => uniqueMap.set(tileKey(t.x, t.y), t));
      const points = Array.from(uniqueMap.values());
      points.sort((a, b) => (a.x + a.y) - (b.x + b.y));
      const start = points[0];
      const end = points[points.length - 1];
      const path = { points, startDir: 0 };
      const sampleSolution = mkStepCommandSeq(path);
      const useP1 = i >= useProc1At;
      const useP2 = i >= useProc2At;
      const mainSlotsBase = startMainSlots + Math.min(6, Math.floor(i / 2));
      const mainSlots = Math.max(
        isEarlyEasy ? (startMainSlots + 6) : mainSlotsBase,
        sampleSolution.length + 2
      );
      const procSlots = startProcSlots + Math.min(5, Math.floor(i / 3));
      out.push({
        id: `${bandNo}-${i + 1}`,
        title: `${bandName} - ${i + 1}`,
        maxSlots: { main: mainSlots, proc1: useP1 ? procSlots : 0, proc2: useP2 ? procSlots : 0 },
        allow: { jump: (bandNo >= 2) || isEarlyEasy, proc1: useP1, proc2: useP2 },
        board: {
          start: { x: start.x, y: start.y, h: start.h, dir: 0 },
          tiles: points.map((t) => ({ x: t.x, y: t.y, h: t.h, light: !!t.light })),
          endHint: { x: end.x, y: end.y }
        },
        sampleSolution
      });
    }
    return out;
  }

  function buildLevels() {
    return []
      .concat(generateBand("Kolay", 1, 12, { startMainSlots: 8, startProcSlots: 0, useProc1At: 99, useProc2At: 99, jumpCount: 1 }))
      .concat(generateBand("Orta", 2, 12, { startMainSlots: 9, startProcSlots: 5, useProc1At: 3, useProc2At: 99, jumpCount: 2 }))
      .concat(generateBand("Zor", 3, 12, { startMainSlots: 10, startProcSlots: 6, useProc1At: 2, useProc2At: 5, jumpCount: 3 }));
  }

  function applyAssignmentRange(fullLevels) {
    if (runnerRole === "teacher" || runnerRole === "admin") return fullLevels.slice();
    if (!isAssignmentMode) return fullLevels.slice();
    const start = Math.max(1, levelStart);
    const end = Math.max(start, Math.min(levelEndParam, fullLevels.length));
    return fullLevels.slice(start - 1, end);
  }

  async function resolveAssignmentRange() {
    if (isStaff) return;
    if (!needsGrantCheck) return;
    try {
      const grantUrl = (window.RUNNER_APP_BASE || "").replace(/\/$/, "") + "/runner-grant/lightbot-runner";
      const res = await fetch(grantUrl, {
        credentials: "same-origin",
        headers: { Accept: "application/json" }
      });
      if (!res.ok) {
        grantDenied = true;
        return;
      }
      const grant = await res.json();
      if (!grant || !grant.ok) {
        grantDenied = true;
        return;
      }
      if (grant.role === "student") {
        const from = Math.max(1, Number(grant.from || 1));
        const to = Math.max(from, Number(grant.to || from));
        levelStart = from;
        levelEndParam = to;
        isAssignmentMode = true;
      } else if (runnerRole === "student") {
        grantDenied = true;
      }
    } catch (e) {
      grantDenied = true;
    }
  }

  function getLevel() {
    return levels[levelIndex];
  }

  function getTileMap(level) {
    const map = new Map();
    (level.board.tiles || []).forEach((t) => map.set(tileKey(t.x, t.y), t));
    return map;
  }

  function resetLevelState() {
    const level = getLevel();
    const start = level.board.start;
    robot = { x: start.x, y: start.y, h: start.h, dir: start.dir };
    litMap = new Set();
    activeStepKey = "";
    runState = null;
    currentProgram = { main: [], proc1: [], proc2: [] };
    renderProgramTracks();
    updateLevelHeader();
    draw();
    emitGameUpdate();
  }

  function showToast(text, ms) {
    toastEl.textContent = text;
    toastEl.classList.remove("hidden");
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => toastEl.classList.add("hidden"), ms || 1500);
  }

  function appendCommand(cmd) {
    const level = getLevel();
    if (cmd === "P1" && !level.allow.proc1) return;
    if (cmd === "P2" && !level.allow.proc2) return;
    if (cmd === "J" && !level.allow.jump) return;
    if (cmd === "X") {
      currentProgram[activeTrack].pop();
      renderProgramTracks();
      return;
    }
    const maxLen = Number(level.maxSlots[activeTrack] || 0);
    if (maxLen <= 0) return;
    if (currentProgram[activeTrack].length >= maxLen) {
      showToast("Bu satir dolu");
      return;
    }
    currentProgram[activeTrack].push(cmd);
    renderProgramTracks();
  }

  function setActiveTrack(track) {
    activeTrack = TRACK_ORDER.includes(track) ? track : "main";
    tabBtns.forEach((btn) => btn.classList.toggle("active", btn.dataset.track === activeTrack));
    Object.keys(trackEls).forEach((k) => trackEls[k].classList.toggle("hidden", k !== activeTrack));
  }

  function renderProgramTracks() {
    const level = getLevel();
    TRACK_ORDER.forEach((track) => {
      const wrap = trackEls[track];
      const maxLen = Number(level.maxSlots[track] || 0);
      wrap.innerHTML = "";
      if (maxLen <= 0) {
        wrap.innerHTML = `<div class="slot" style="grid-column:1/-1;cursor:default;opacity:.6;">${TRACK_TITLE[track]} kapali</div>`;
        return;
      }
      for (let i = 0; i < maxLen; i += 1) {
        const cmd = currentProgram[track][i] || "";
        const slot = document.createElement("button");
        slot.type = "button";
        slot.className = `slot${cmd ? " filled" : ""}${activeStepKey === `${track}:${i}` ? " active-step" : ""}`;
        slot.textContent = cmd || "+";
        slot.title = cmd ? "Silmek icin tikla" : "Komut eklemek icin alttan sec";
        slot.addEventListener("click", () => {
          if (runState) return;
          if (cmd) {
            currentProgram[track].splice(i, 1);
            renderProgramTracks();
          }
        });
        wrap.appendChild(slot);
      }
    });
  }

  function updateLevelHeader() {
    const level = getLevel();
    levelIdEl.textContent = level.id;
    levelTitleEl.textContent = level.title;
    btnPrev.disabled = levelIndex <= 0;
  }

  function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;
    canvas.width = Math.max(600, Math.floor(rect.width * ratio));
    canvas.height = Math.max(360, Math.floor(rect.height * ratio));
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    draw();
  }

  function project(x, y, h, scale, offsetX, offsetY) {
    const tw = 56 * scale;
    const th = 30 * scale;
    const eh = 24 * scale;
    const sx = (x - y) * (tw / 2) + offsetX;
    const sy = (x + y) * (th / 2) + offsetY - h * eh;
    return { sx, sy, tw, th, eh };
  }

  function drawTile(tile, scale, ox, oy, lit) {
    const p = project(tile.x, tile.y, tile.h, scale, ox, oy);
    const { sx, sy, tw, th, eh } = p;
    ctx.beginPath();
    ctx.moveTo(sx, sy);
    ctx.lineTo(sx + tw / 2, sy + th / 2);
    ctx.lineTo(sx, sy + th);
    ctx.lineTo(sx - tw / 2, sy + th / 2);
    ctx.closePath();
    ctx.fillStyle = lit ? "#ffe15d" : (tile.light ? "#4ea4da" : "#d7e3ef");
    ctx.fill();
    ctx.strokeStyle = "#6d7d8e";
    ctx.lineWidth = 2;
    ctx.stroke();

    ctx.beginPath();
    ctx.moveTo(sx - tw / 2, sy + th / 2);
    ctx.lineTo(sx, sy + th);
    ctx.lineTo(sx, sy + th + eh);
    ctx.lineTo(sx - tw / 2, sy + th / 2 + eh);
    ctx.closePath();
    ctx.fillStyle = "#b7c6d6";
    ctx.fill();
    ctx.stroke();

    ctx.beginPath();
    ctx.moveTo(sx + tw / 2, sy + th / 2);
    ctx.lineTo(sx, sy + th);
    ctx.lineTo(sx, sy + th + eh);
    ctx.lineTo(sx + tw / 2, sy + th / 2 + eh);
    ctx.closePath();
    ctx.fillStyle = "#a8b9cb";
    ctx.fill();
    ctx.stroke();
  }

  function drawRobot(scale, ox, oy) {
    if (!robot) return;
    const p = project(robot.x, robot.y, robot.h, scale, ox, oy);
    const cx = p.sx;
    // Karakter biraz daha yukari alinmis gorunsun
    const cy = p.sy + p.th * 0.28;
    const r = 14 * scale;
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0, Math.PI * 2);
    ctx.fillStyle = "#d6dbe6";
    ctx.fill();
    ctx.strokeStyle = "#5f6d7d";
    ctx.lineWidth = 2;
    ctx.stroke();

    const dirVec = DIRS[robot.dir] || DIRS[0];
    const ang = Math.atan2(dirVec.y, dirVec.x);
    const eyeX = cx + Math.cos(ang) * r * 0.58;
    const eyeY = cy + Math.sin(ang) * r * 0.46;
    const backEyeX = cx + Math.cos(ang + Math.PI) * r * 0.35;
    const backEyeY = cy + Math.sin(ang + Math.PI) * r * 0.25;
    ctx.beginPath();
    ctx.arc(eyeX, eyeY, Math.max(3, r * 0.23), 0, Math.PI * 2);
    ctx.fillStyle = "#0f172a";
    ctx.fill();
    ctx.beginPath();
    ctx.arc(backEyeX, backEyeY, Math.max(2, r * 0.14), 0, Math.PI * 2);
    ctx.fillStyle = "#94a3b8";
    ctx.fill();
    ctx.beginPath();
    ctx.moveTo(cx, cy - r - 8 * scale);
    ctx.lineTo(cx, cy - r - 2 * scale);
    ctx.strokeStyle = "#64748b";
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(cx, cy - r - 9 * scale, 3.5 * scale, 0, Math.PI * 2);
    ctx.fillStyle = "#fb7185";
    ctx.fill();
  }

  function draw() {
    const level = getLevel();
    const w = canvas.clientWidth || 920;
    const h = canvas.clientHeight || 520;
    ctx.clearRect(0, 0, w, h);
    const tiles = level.board.tiles || [];
    const xs = tiles.map((t) => t.x);
    const ys = tiles.map((t) => t.y);
    const minX = Math.min.apply(null, xs);
    const maxX = Math.max.apply(null, xs);
    const minY = Math.min.apply(null, ys);
    const maxY = Math.max.apply(null, ys);
    const span = Math.max(maxX - minX + 1, maxY - minY + 1);
    const scale = Math.max(0.62, Math.min(1.08, 8 / span));
    const ox = w * 0.48;
    const oy = h * 0.2 + span * 12;
    tiles
      .slice()
      .sort((a, b) => (a.x + a.y + a.h) - (b.x + b.y + b.h))
      .forEach((tile) => {
        const lit = litMap.has(tileKey(tile.x, tile.y));
        drawTile(tile, scale, ox, oy, lit);
      });
    drawRobot(scale, ox, oy);
  }

  function executeOne(cmd) {
    const level = getLevel();
    const map = getTileMap(level);
    if (cmd === "L") {
      robot.dir = (robot.dir + 3) % 4;
      return { ok: true };
    }
    if (cmd === "R") {
      robot.dir = (robot.dir + 1) % 4;
      return { ok: true };
    }
    if (cmd === "B") {
      const k = tileKey(robot.x, robot.y);
      const tile = map.get(k);
      if (!tile || !tile.light) return { ok: false, reason: "Isik yakilacak karede degilsin" };
      litMap.add(k);
      return { ok: true };
    }
    const d = DIRS[robot.dir] || DIRS[0];
    const nx = robot.x + d.x;
    const ny = robot.y + d.y;
    const next = map.get(tileKey(nx, ny));
    if (!next) return { ok: false, reason: "Bosta kaldi" };
    if (cmd === "F") {
      if (next.h !== robot.h) return { ok: false, reason: "Ileri icin ayni yukseklik olmali" };
      robot.x = nx;
      robot.y = ny;
      robot.h = next.h;
      return { ok: true };
    }
    if (cmd === "J") {
      if (Math.abs(next.h - robot.h) > 1 || next.h === robot.h) return { ok: false, reason: "Zipla komutu sadece seviye farkinda calisir" };
      robot.x = nx;
      robot.y = ny;
      robot.h = next.h;
      return { ok: true };
    }
    return { ok: false, reason: "Gecersiz komut" };
  }

  function allLightsOn() {
    const level = getLevel();
    const targets = (level.board.tiles || []).filter((t) => t.light);
    return targets.length > 0 && targets.every((t) => litMap.has(tileKey(t.x, t.y)));
  }

  function buildRuntime() {
    if (currentProgram.main.length === 0) return null;
    return { stack: [{ track: "main", index: 0 }], steps: 0, timer: null };
  }

  function stopRun(resetStep) {
    if (runState?.timer) window.clearTimeout(runState.timer);
    runState = null;
    if (resetStep) activeStepKey = "";
    renderProgramTracks();
  }

  function getCurrentProgressPercent() {
    if (!levels.length) return 0;
    const done = Math.max(0, completedLevelNos.size);
    return Math.min(100, Math.round((done / levels.length) * 100));
  }

  function emitGameUpdate() {
    try {
      window.parent?.postMessage({
        type: "GAME_UPDATE",
        source: "lightbot",
        assignmentId: assignmentId || null,
        assignmentTitle,
        levelStart,
        levelEnd: Math.max(levelStart, Math.min(levelEndParam, levelStart + levels.length - 1)),
        levels: levels.map((lv, idx) => ({
          id: lv.id,
          name: lv.title,
          completed: completedLevelNos.has(idx + 1)
        })),
        currentLevelIndex: Number(levelIndex || 0),
        progressPercent: getCurrentProgressPercent()
      }, "*");
    } catch (e) {}
  }

  function emitLevelCompleted() {
    const levelNo = levelIndex + 1;
    try {
      window.parent?.postMessage({
        type: "LEVEL_COMPLETED",
        source: "lightbot",
        assignmentId: assignmentId || null,
        assignmentTitle,
        levelId: String(getLevel()?.id || `lightbot_${levelNo}`),
        levelNo,
        levelStart,
        levelEnd: Math.max(levelStart, Math.min(levelEndParam, levelStart + levels.length - 1)),
        completedLevelIds: Array.from(completedLevelNos).sort((a, b) => a - b),
        currentLevelIndex: Number(levelIndex || 0),
        percent: getCurrentProgressPercent(),
        progressPercent: getCurrentProgressPercent(),
        xp: XP_PER_LEVEL,
        elapsedSeconds: elapsedSeconds(),
        levels: levels.map((lv, idx) => ({ id: lv.id, name: lv.title, completed: completedLevelNos.has(idx + 1) }))
      }, "*");
    } catch (e) {}
  }

  function emitRangeCompletedIfNeeded() {
    if (!isAssignmentMode || rangeCompleteFired) return;
    if (completedLevelNos.size < levels.length) return;
    rangeCompleteFired = true;
    try {
      window.parent?.postMessage({
        type: "ASSIGNMENT_RANGE_COMPLETED",
        source: "lightbot",
        assignmentId,
        assignmentTitle,
        levelStart,
        levelEnd: Math.max(levelStart, Math.min(levelEndParam, levelStart + levels.length - 1)),
        completedLevelIds: Array.from(completedLevelNos).sort((a, b) => a - b),
        xp: completedLevelNos.size * XP_PER_LEVEL,
        elapsedSeconds: elapsedSeconds(),
        currentLevelIndex: Number(levelIndex || 0)
      }, "*");
    } catch (e) {}
  }

  function tickRun() {
    if (!runState) return;
    if (runState.steps > 500) {
      stopRun(true);
      showToast("Komut limiti asildi");
      return;
    }
    while (runState.stack.length > 0) {
      const frame = runState.stack[runState.stack.length - 1];
      const seq = currentProgram[frame.track] || [];
      if (frame.index >= seq.length) {
        runState.stack.pop();
        continue;
      }
      const cmd = seq[frame.index];
      activeStepKey = `${frame.track}:${frame.index}`;
      frame.index += 1;
      renderProgramTracks();
      runState.steps += 1;

      if (cmd === "P1" || cmd === "P2") {
        const target = cmd === "P1" ? "proc1" : "proc2";
        if (!currentProgram[target] || currentProgram[target].length === 0) {
          stopRun(true);
          showToast(`${target.toUpperCase()} bos`);
          return;
        }
        runState.stack.push({ track: target, index: 0 });
      } else {
        const res = executeOne(cmd);
        draw();
        if (!res.ok) {
          stopRun(true);
          showToast(res.reason || "Hata");
          return;
        }
      }

      if (allLightsOn()) {
        const justFinished = levelIndex + 1;
        completedLevelNos.add(justFinished);
        emitLevelCompleted();
        emitGameUpdate();
        emitRangeCompletedIfNeeded();
        stopRun(true);
        draw();
        showToast("Seviye tamamlandi. Sonraki bolume geciliyor...", 1200);
        window.setTimeout(() => {
          if (justFinished !== levelIndex + 1) return;
          if (levelIndex < levels.length - 1) {
            levelIndex += 1;
            resetLevelState();
          } else {
            showToast("Tum bolumler tamamlandi", 1800);
          }
        }, 1100);
        return;
      }

      runState.timer = window.setTimeout(tickRun, STEP_DELAY);
      return;
    }
    stopRun(true);
    showToast("Program bitti ama tum isiklar acilmadi");
  }

  function startRun() {
    if (runState) return;
    runState = buildRuntime();
    if (!runState) {
      showToast("MAIN bos");
      return;
    }
    tickRun();
  }

  function renderCommandBar() {
    const level = getLevel();
    commandBar.innerHTML = "";
    COMMANDS.forEach((cmd) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = `cmd-btn${cmd.accent ? " accent" : ""}`;
      btn.textContent = cmd.icon;
      btn.title = cmd.title || cmd.id;
      const forceJumpOpen = String(level?.id || "") === "1-5";
      const disabled = (cmd.id === "P1" && !level.allow.proc1) || (cmd.id === "P2" && !level.allow.proc2) || (cmd.id === "J" && !level.allow.jump && !forceJumpOpen);
      btn.disabled = !!disabled;
      btn.addEventListener("click", () => appendCommand(cmd.id));
      commandBar.appendChild(btn);
    });
  }

  function bindEvents() {
    tabBtns.forEach((btn) => btn.addEventListener("click", () => setActiveTrack(btn.dataset.track)));
    btnRun.addEventListener("click", startRun);
    btnStop.addEventListener("click", () => stopRun(true));
    btnReset.addEventListener("click", () => {
      stopRun(true);
      resetLevelState();
    });
    btnPrev.addEventListener("click", () => {
      if (levelIndex <= 0) return;
      stopRun(true);
      levelIndex -= 1;
      resetLevelState();
      renderCommandBar();
    });
    window.addEventListener("resize", resizeCanvas);
  }

  async function init() {
    await resolveAssignmentRange();
    if (grantDenied) {
      showToast("Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.", 2600);
      btnRun.disabled = true;
      btnStop.disabled = true;
      btnReset.disabled = true;
      btnPrev.disabled = true;
      return;
    }
    const baseLevels = buildLevels();
    levels = isStaff ? baseLevels.slice() : applyAssignmentRange(baseLevels);
    if (!levels.length) levels = baseLevels.slice(0, 1);
    bindEvents();
    setActiveTrack("main");
    resetLevelState();
    renderCommandBar();
    resizeCanvas();
  }

  init();
})();

