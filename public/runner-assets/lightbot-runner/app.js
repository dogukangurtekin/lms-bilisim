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

  // 100 bölümlük kategorili set (Kolay / Orta / Zor) levels.js içinde tanımlıdır (tools/learn-levels/lightbot.gen.mjs ile üretilir).
  const CATEGORY_LABEL = { easy: "Kolay", medium: "Orta", hard: "Zor" };

  function buildLevels() {
    return (window.LIGHTBOT_LEVELS || []).map((lv) => JSON.parse(JSON.stringify(lv)));
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
      const grantUrl = (window.RUNNER_APP_BASE || "").replace(/\/$/, "") + "/runner-grant/lightbot-runner" + window.location.search;
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
        slot.className = `slot${cmd ? " filled" : ""}`;
        slot.textContent = cmd ? (CMD_ICON[cmd] || cmd) : "+";
        if (cmd) slot.dataset.cmd = cmd;
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

  // =====================================================================
  //  GÖRSEL KATMAN (yalnızca çizim; oyun mantığı yukarıda/aşağıda değişmedi)
  // =====================================================================
  const CMD_ICON = {};
  COMMANDS.forEach((c) => { CMD_ICON[c.id] = c.icon; });
  let animT = 0;                                   // animasyon zamanı (sn)
  const vis = { x: 0, y: 0, h: 0, ang: 0, level: -1, ready: false }; // robotun GÖRÜNEN (yumuşak) konumu

  function hash01(a, b) {
    let h = (a * 374761393 + b * 668265263) | 0;
    h = (h ^ (h >>> 13)) * 1274126177;
    return ((h ^ (h >>> 16)) >>> 0) / 4294967296;
  }
  function angleOf(dir) { const v = DIRS[dir] || DIRS[0]; return Math.atan2(v.y, v.x); }

  // Robotun görünen konumunu gerçek konuma doğru yumuşakça kaydırır (adımlar "tık" diye zıplamasın).
  function stepVisual(dt) {
    if (!robot) return;
    const dist = Math.hypot(robot.x - vis.x, robot.y - vis.y);
    if (!vis.ready || vis.level !== levelIndex || dist > 1.7) {
      vis.x = robot.x; vis.y = robot.y; vis.h = robot.h; vis.ang = angleOf(robot.dir); vis.level = levelIndex; vis.ready = true;
      return;
    }
    const k = 1 - Math.exp(-dt * 13);
    vis.x += (robot.x - vis.x) * k;
    vis.y += (robot.y - vis.y) * k;
    vis.h += (robot.h - vis.h) * k;
    const ta = angleOf(robot.dir);
    const d = ((ta - vis.ang + Math.PI) % (Math.PI * 2) + Math.PI * 2) % (Math.PI * 2) - Math.PI;
    vis.ang += d * (1 - Math.exp(-dt * 16));
  }

  function face(pts, fill, stroke) {
    ctx.beginPath();
    ctx.moveTo(pts[0][0], pts[0][1]);
    for (let i = 1; i < pts.length; i += 1) ctx.lineTo(pts[i][0], pts[i][1]);
    ctx.closePath();
    ctx.fillStyle = fill;
    ctx.fill();
    if (stroke) { ctx.strokeStyle = stroke; ctx.lineWidth = 1.2; ctx.stroke(); }
  }

  function drawBackdrop(w, h) {
    const g = ctx.createLinearGradient(0, 0, 0, h);
    g.addColorStop(0, "#0a1738");
    g.addColorStop(0.55, "#16327a");
    g.addColorStop(1, "#1d4ed8");
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, w, h);
    for (let i = 0; i < 70; i += 1) { // yıldızlar
      const tw = 0.5 + 0.5 * Math.sin(animT * 2 + i * 1.7);
      ctx.fillStyle = "rgba(255,255,255," + (0.15 + 0.5 * tw) + ")";
      ctx.beginPath();
      ctx.arc(hash01(i, 1) * w, hash01(i, 2) * h * 0.75, 0.6 + hash01(i, 3) * 1.4, 0, Math.PI * 2);
      ctx.fill();
    }
    const glow = ctx.createRadialGradient(w * 0.48, h * 0.55, 0, w * 0.48, h * 0.55, w * 0.55);
    glow.addColorStop(0, "rgba(96,165,250,0.35)");
    glow.addColorStop(1, "rgba(96,165,250,0)");
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, w, h);
  }

  function drawTile(tile, scale, ox, oy, lit) {
    const p = project(tile.x, tile.y, tile.h, scale, ox, oy);
    const { sx, sy, tw, th, eh } = p;
    const depth = ((tile.h || 0) + 1) * eh; // sütun zemine kadar uzanır
    const midY = sy + th / 2;
    // yan yüzler
    let g = ctx.createLinearGradient(0, midY, 0, midY + depth);
    g.addColorStop(0, "#90a8c6");
    g.addColorStop(1, "#506b8c");
    face([[sx - tw / 2, midY], [sx, sy + th], [sx, sy + th + depth], [sx - tw / 2, midY + depth]], g, "rgba(20,40,80,0.35)");
    g = ctx.createLinearGradient(0, midY, 0, midY + depth);
    g.addColorStop(0, "#7a93b4");
    g.addColorStop(1, "#3f5878");
    face([[sx + tw / 2, midY], [sx, sy + th], [sx, sy + th + depth], [sx + tw / 2, midY + depth]], g, "rgba(20,40,80,0.35)");
    // üst yüz
    const top = ctx.createLinearGradient(sx - tw / 2, sy, sx + tw / 2, sy + th);
    if (lit) { top.addColorStop(0, "#fff3a3"); top.addColorStop(1, "#fbbf24"); }
    else if (tile.light) { top.addColorStop(0, "#9ddcfb"); top.addColorStop(1, "#3b9be0"); }
    else { top.addColorStop(0, "#f4f9ff"); top.addColorStop(1, "#cbddf1"); }
    face([[sx, sy], [sx + tw / 2, midY], [sx, sy + th], [sx - tw / 2, midY]], top, "rgba(255,255,255,0.6)");
    // ışık yuvası: ring + parıltı
    if (tile.light) {
      const cx = sx, cy = midY, rx = tw * 0.26, ry = th * 0.26;
      if (lit) {
        const gl = ctx.createRadialGradient(cx, cy, 0, cx, cy, tw * 0.55);
        gl.addColorStop(0, "rgba(255,240,150," + (0.75 + 0.2 * Math.sin(animT * 5)) + ")");
        gl.addColorStop(1, "rgba(255,240,150,0)");
        ctx.fillStyle = gl;
        ctx.beginPath(); ctx.ellipse(cx, cy, tw * 0.55, th * 0.55, 0, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = "#fffbe0";
        ctx.beginPath(); ctx.ellipse(cx, cy, rx * 0.7, ry * 0.7, 0, 0, Math.PI * 2); ctx.fill();
      } else {
        ctx.strokeStyle = "rgba(255,255,255," + (0.65 + 0.3 * Math.sin(animT * 3 + tile.x)) + ")";
        ctx.lineWidth = Math.max(2, 3 * scale);
        ctx.beginPath(); ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI * 2); ctx.stroke();
        ctx.fillStyle = "rgba(255,255,255,0.35)";
        ctx.beginPath(); ctx.ellipse(cx, cy, rx * 0.55, ry * 0.55, 0, 0, Math.PI * 2); ctx.fill();
      }
    }
  }

  function drawRobot(scale, ox, oy) {
    if (!robot) return;
    const p = project(vis.x, vis.y, vis.h, scale, ox, oy);
    const bx = p.sx;
    const by = p.sy + p.th / 2;                       // karo yüzeyinin merkezi
    const moving = Math.min(1, Math.abs(robot.x - vis.x) + Math.abs(robot.y - vis.y) + Math.abs(robot.h - vis.h));
    const bob = Math.sin(animT * 3.2) * 1.6 * scale + moving * 7 * scale; // hafif süzülme + hareket sırasında zıplama
    const u = scale;
    // gölge
    ctx.fillStyle = "rgba(0,0,0," + (0.28 - moving * 0.1) + ")";
    ctx.beginPath(); ctx.ellipse(bx, by + 1, 13 * u, 6.5 * u, 0, 0, Math.PI * 2); ctx.fill();
    const cy = by - 17 * u - bob;                     // gövde merkezi
    // gövde
    const body = ctx.createLinearGradient(bx - 12 * u, cy - 12 * u, bx + 12 * u, cy + 12 * u);
    body.addColorStop(0, "#ffffff");
    body.addColorStop(1, "#b9c8dc");
    ctx.fillStyle = body;
    ctx.strokeStyle = "#51657f";
    ctx.lineWidth = 2;
    ctx.beginPath(); ctx.ellipse(bx, cy + 4 * u, 12 * u, 13 * u, 0, 0, Math.PI * 2); ctx.fill(); ctx.stroke();
    // kafa
    const hy = cy - 9 * u;
    const head = ctx.createLinearGradient(bx - 11 * u, hy - 10 * u, bx + 11 * u, hy + 10 * u);
    head.addColorStop(0, "#9fe3ff");
    head.addColorStop(1, "#38a8e8");
    ctx.fillStyle = head;
    ctx.beginPath(); ctx.arc(bx, hy, 11 * u, 0, Math.PI * 2); ctx.fill(); ctx.stroke();
    // yüz: bakılan yönde (ekran düzleminde izometrik vektör)
    const dv = { x: Math.cos(vis.ang), y: Math.sin(vis.ang) };
    const fx = (dv.x - dv.y) * 0.5, fy = (dv.x + dv.y) * 0.25;
    ctx.fillStyle = "#0f172a";
    ctx.beginPath(); ctx.ellipse(bx + fx * 8 * u, hy + fy * 8 * u + 1 * u, 8 * u, 6 * u, 0, 0, Math.PI * 2); ctx.fill();
    ctx.fillStyle = "#67e8f9";
    ctx.beginPath(); ctx.arc(bx + fx * 8 * u - 2.6 * u, hy + fy * 8 * u + 1 * u, 1.9 * u, 0, Math.PI * 2); ctx.fill();
    ctx.beginPath(); ctx.arc(bx + fx * 8 * u + 2.6 * u, hy + fy * 8 * u + 1 * u, 1.9 * u, 0, Math.PI * 2); ctx.fill();
    // anten
    ctx.strokeStyle = "#51657f";
    ctx.lineWidth = 2;
    ctx.beginPath(); ctx.moveTo(bx, hy - 10 * u); ctx.lineTo(bx, hy - 17 * u); ctx.stroke();
    ctx.fillStyle = "#fb7185";
    ctx.beginPath(); ctx.arc(bx, hy - 18.5 * u, (3.2 + Math.sin(animT * 6) * 0.5) * u, 0, Math.PI * 2); ctx.fill();
  }

  function draw() {
    const level = getLevel();
    if (!level) return;
    const w = canvas.clientWidth || 920;
    const h = canvas.clientHeight || 520;
    ctx.clearRect(0, 0, w, h);
    drawBackdrop(w, h);
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
    const sorted = tiles.slice().sort((a, b) => (a.x + a.y + a.h) - (b.x + b.y + b.h));
    // robot, kendi karesinden önceki karolardan sonra, öndeki karolardan önce çizilir (doğru örtüşme)
    const robotDepth = robot ? vis.x + vis.y + vis.h : Infinity;
    let robotDrawn = false;
    sorted.forEach((tile) => {
      if (!robotDrawn && robot && (tile.x + tile.y + tile.h) > robotDepth + 0.6) {
        drawRobot(scale, ox, oy);
        robotDrawn = true;
      }
      drawTile(tile, scale, ox, oy, litMap.has(tileKey(tile.x, tile.y)));
    });
    if (!robotDrawn) drawRobot(scale, ox, oy);
  }

  let lastFrameTs = 0;
  function visualLoop(ts) {
    const dt = Math.min(0.05, ((ts || 0) - lastFrameTs) / 1000) || 0.016;
    lastFrameTs = ts || 0;
    animT += dt;
    try { stepVisual(dt); draw(); } catch (e) { // çizim hatası oyunu durdurmasın; ilk hata konsola yazılır
      if (!visualLoop.failed) { visualLoop.failed = true; console.error('Lightbot çizim hatası:', e); }
    }
    window.requestAnimationFrame(visualLoop);
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
            renderCommandBar();
            setActiveTrack("main");
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
      btn.dataset.cmd = cmd.id;
      btn.title = cmd.title || cmd.id;
      const disabled = (cmd.id === "P1" && !level.allow.proc1) || (cmd.id === "P2" && !level.allow.proc2) || (cmd.id === "J" && !level.allow.jump);
      btn.disabled = !!disabled;
      btn.addEventListener("click", () => appendCommand(cmd.id));
      commandBar.appendChild(btn);
    });
  }

  // ---------------- bölüm seçici (Kolay / Orta / Zor) ----------------
  const pickerEl = document.getElementById("level-picker");
  const pickerBodyEl = document.getElementById("picker-body");
  const btnLevels = document.getElementById("btn-levels");

  function unlockedMaxIndex() {
    if (isStaff) return levels.length - 1;
    let maxDone = 0;
    completedLevelNos.forEach((n) => { if (n > maxDone) maxDone = n; });
    return Math.min(levels.length - 1, maxDone); // tamamlanan en son bölümün bir sonrakine kadar açık (indeks = sıra numarası)
  }

  function gotoLevel(idx) {
    if (idx < 0 || idx >= levels.length || idx > unlockedMaxIndex()) return;
    stopRun(true);
    levelIndex = idx;
    resetLevelState();
    renderCommandBar();
    setActiveTrack("main");
  }

  function openPicker() {
    if (!pickerEl || !pickerBodyEl) return;
    pickerBodyEl.innerHTML = "";
    const groups = [];
    levels.forEach((lv, i) => {
      const cat = lv.category || "easy";
      let g = groups.find((x) => x.cat === cat);
      if (!g) { g = { cat, items: [] }; groups.push(g); }
      g.items.push({ lv, i });
    });
    const maxOpen = unlockedMaxIndex();
    groups.forEach((g) => {
      const doneCount = g.items.filter((x) => completedLevelNos.has(x.i + 1)).length;
      const sec = document.createElement("div");
      sec.className = "picker-group cat-" + g.cat;
      const h = document.createElement("h4");
      h.innerHTML = (CATEGORY_LABEL[g.cat] || g.cat) + " <small>" + doneCount + " / " + g.items.length + " bölüm</small>";
      sec.appendChild(h);
      const grid = document.createElement("div");
      grid.className = "picker-grid";
      g.items.forEach((x) => {
        const b = document.createElement("button");
        b.type = "button";
        const open = x.i <= maxOpen;
        b.className = "tile" + (completedLevelNos.has(x.i + 1) ? " done" : "") + (x.i === levelIndex ? " current" : "");
        b.disabled = !open;
        b.innerHTML = (open ? "" : '<span class="lock">🔒</span>') + '<span class="t-num">' + (levelStart + x.i) + '</span><span class="t-size">' + x.lv.id + "</span>";
        b.addEventListener("click", () => { gotoLevel(x.i); closePicker(); });
        grid.appendChild(b);
      });
      sec.appendChild(grid);
      pickerBodyEl.appendChild(sec);
    });
    pickerEl.classList.remove("hidden");
    const cur = pickerBodyEl.querySelector(".tile.current");
    if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: "center" });
  }

  function closePicker() {
    if (pickerEl) pickerEl.classList.add("hidden");
  }

  function bindEvents() {
    if (btnLevels) btnLevels.addEventListener("click", openPicker);
    if (pickerEl) pickerEl.addEventListener("click", (e) => { if (e.target && e.target.hasAttribute && e.target.hasAttribute("data-close")) closePicker(); });
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
      setActiveTrack("main");
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
    window.requestAnimationFrame(visualLoop);
  }

  init();
})();

