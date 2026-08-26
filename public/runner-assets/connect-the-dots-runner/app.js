(function () {
  const params = new URLSearchParams(window.location.search);
  const roleParam = (params.get("role") || "student").toLowerCase();
  const role = (roleParam === "teacher" || roleParam === "admin") ? roleParam : "student";
  const isStaff = role === "teacher" || role === "admin";
  const initialRangeStart = Math.max(1, Number(params.get("levelStart") || params.get("from") || 0));
  const initialRangeEndRaw = Math.max(initialRangeStart, Number(params.get("levelEnd") || params.get("to") || initialRangeStart));
  const hasInitialRange = Number.isFinite(initialRangeStart) && initialRangeStart > 0;
  const enforceGrant = params.get("grant") === "1" || params.get("enforceGrant") === "1";
  const needsGrantCheck = role === "student" && (hasInitialRange || enforceGrant);
  const appBase = (window.RUNNER_APP_BASE || "").replace(/\/$/, "");

  const DIR_VECTOR = {
    up: { dx: 0, dy: -1 },
    right: { dx: 1, dy: 0 },
    down: { dx: 0, dy: 1 },
    left: { dx: -1, dy: 0 },
  };
  const DIR_ROTATE = { up: 0, right: 90, down: 180, left: 270 };
  const CMD_DIR = { move_up: "up", move_right: "right", move_down: "down", move_left: "left" };
  const CMD_ICON = {
    move_up: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>',
    move_right: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
    move_down: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>',
    move_left: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 5 5 12 12 19"></polyline></svg>',
    repeat: '<span style="font-size:11px;font-weight:800">x2</span>',
  };

  const targetGridEl = document.getElementById("target-grid");
  const playGridEl = document.getElementById("play-grid");
  const levelNoEl = document.getElementById("level-no");
  const doneNoEl = document.getElementById("done-no");
  const totalNoEl = document.getElementById("total-no");
  const slotTrayEl = document.getElementById("slot-tray");
  const feedbackEl = document.getElementById("feedback");
  const btnGo = document.getElementById("btn-go");
  const btnClear = document.getElementById("btn-clear");
  const btnNext = document.getElementById("btn-next");
  const btnBack = document.getElementById("btn-back");
  const cmdButtons = Array.from(document.querySelectorAll(".cmd-btn"));

  let levels = [];
  let levelIndex = 0;
  let range = hasInitialRange ? { startIdx: initialRangeStart - 1, endIdx: initialRangeEndRaw - 1 } : null;
  let assignmentLocked = false;
  let startedAt = Date.now();
  let completedLevelIds = [];
  let queue = [];
  let running = false;

  // Persisted view state so we can redraw pixel-accurate lines after any
  // resize/orientation change (mobile browsers resize the viewport a lot).
  let targetView = null;
  let playView = null;
  let playState = { point: null, dir: "right", segments: [] };

  function showFatalError(html) {
    document.body.innerHTML = '<div style="padding:32px;max-width:520px;margin:40px auto;text-align:center;font-weight:700;color:#7f1d1d;background:#fee2e2;border-radius:16px">' + html + '</div>';
  }

  function cmdLabel(cmd) {
    return { move_up: "Yukarı", move_right: "Sağ", move_down: "Aşağı", move_left: "Sol", repeat: "x2" }[cmd] || cmd;
  }

  function buildGrid(container, size) {
    container.innerHTML = "";
    container.style.gridTemplateColumns = `repeat(${size}, 1fr)`;
    container.style.gridTemplateRows = `repeat(${size}, 1fr)`;
    const dots = {};
    for (let y = 0; y < size; y++) {
      for (let x = 0; x < size; x++) {
        const dot = document.createElement("div");
        dot.className = "dot";
        dot.dataset.x = String(x);
        dot.dataset.y = String(y);
        container.appendChild(dot);
        dots[x + "," + y] = dot;
      }
    }
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("class", "segments");
    container.appendChild(svg);
    const pen = document.createElement("img");
    pen.className = "pen";
    pen.src = appBase + "/runner-assets/connect-the-dots-runner/pen.svg";
    pen.alt = "";
    container.appendChild(pen);
    return { container, dots, svg, pen };
  }

  // Reads the ACTUAL rendered position of a dot element (relative to its
  // container) rather than assuming an even grid pitch — CSS `gap` and
  // rounding make a naive width/size division drift off the real dots.
  function dotCenter(view, x, y) {
    const dot = view.dots[x + "," + y];
    const containerRect = view.container.getBoundingClientRect();
    const dotRect = dot.getBoundingClientRect();
    return {
      left: dotRect.left - containerRect.left + dotRect.width / 2,
      top: dotRect.top - containerRect.top + dotRect.height / 2,
    };
  }

  function drawLine(view, a, b) {
    const p1 = dotCenter(view, a.x, a.y);
    const p2 = dotCenter(view, b.x, b.y);
    const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
    line.setAttribute("x1", p1.left);
    line.setAttribute("y1", p1.top);
    line.setAttribute("x2", p2.left);
    line.setAttribute("y2", p2.top);
    line.setAttribute("stroke", "#ffffff");
    line.setAttribute("stroke-width", "4");
    line.setAttribute("stroke-linecap", "round");
    view.svg.appendChild(line);
  }

  function normalizeSegment(a, b) {
    const pa = `${a.x},${a.y}`;
    const pb = `${b.x},${b.y}`;
    return pa < pb ? `${pa}|${pb}` : `${pb}|${pa}`;
  }

  function redrawTargetLines() {
    const level = currentLevel();
    if (!level || !targetView) return;
    targetView.svg.innerHTML = "";
    const dots = level.targetDots || [];
    for (let i = 0; i < dots.length - 1; i++) {
      drawLine(targetView, dots[i], dots[i + 1]);
    }
  }

  function redrawPlayLines() {
    const level = currentLevel();
    if (!level || !playView) return;
    playView.svg.innerHTML = "";
    playState.segments.forEach(([a, b]) => drawLine(playView, a, b));
    if (playState.point) {
      placePen(playView, playState.point, playState.dir);
    }
  }

  function redrawAll() {
    redrawTargetLines();
    redrawPlayLines();
  }

  let resizeTimer = null;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(redrawAll, 120);
  });
  window.addEventListener("orientationchange", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(redrawAll, 220);
  });

  function renderTarget(level) {
    targetView = buildGrid(targetGridEl, level.gridSize);
    targetView.pen.style.display = "none";
    requestAnimationFrame(redrawTargetLines);
  }

  function placePen(view, point, dir) {
    const c = dotCenter(view, point.x, point.y);
    view.pen.style.left = c.left + "px";
    view.pen.style.top = c.top + "px";
    view.pen.style.transform = `translate(-50%,-70%) rotate(${DIR_ROTATE[dir]}deg)`;
  }

  function resetPlay(level) {
    playView = buildGrid(playGridEl, level.gridSize);
    playState = { point: { x: level.start.x, y: level.start.y }, dir: level.startDirection || "right", segments: [] };
    requestAnimationFrame(() => placePen(playView, playState.point, playState.dir));
    queue = [];
    renderSlots();
    setFeedback("", "");
  }

  function setFeedback(text, kind) {
    feedbackEl.textContent = text;
    feedbackEl.className = "feedback" + (kind ? " " + kind : "");
  }

  function currentLevel() {
    return levels[levelIndex] || null;
  }

  function renderSlots() {
    slotTrayEl.innerHTML = "";
    const level = currentLevel();
    const max = level ? level.maxCommands : 10;
    for (let i = 0; i < max; i++) {
      const slot = document.createElement("div");
      const cmd = queue[i];
      slot.className = "slot" + (cmd ? " filled " + cmd : "");
      if (cmd) {
        slot.innerHTML = CMD_ICON[cmd] || "";
        slot.title = cmdLabel(cmd) + " (kaldırmak için tıkla)";
        slot.addEventListener("click", () => {
          if (running) return;
          queue.splice(i, 1);
          renderSlots();
        });
      }
      slotTrayEl.appendChild(slot);
    }
  }

  function addCommand(cmd) {
    if (running) return;
    const level = currentLevel();
    if (!level) return;
    if (!level.allowedCommands.includes(cmd)) return;
    if (queue.length >= level.maxCommands) {
      setFeedback("Komut alanı dolu.", "bad");
      return;
    }
    if (cmd === "repeat" && (queue.length === 0 || queue[queue.length - 1] === "repeat")) {
      setFeedback("Tekrarla için önce bir yön komutu ekleyin.", "bad");
      return;
    }
    queue.push(cmd);
    renderSlots();
  }

  function expandQueue(rawQueue) {
    const expanded = [];
    for (let i = 0; i < rawQueue.length; i++) {
      const cmd = rawQueue[i];
      if (cmd === "repeat") {
        const prev = expanded[expanded.length - 1];
        if (prev) expanded.push(prev);
      } else {
        expanded.push(cmd);
      }
    }
    return expanded;
  }

  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  async function runQueue() {
    const level = currentLevel();
    if (!level || running || queue.length === 0) return;
    running = true;
    btnGo.disabled = true;
    setFeedback("Çalışıyor...", "");

    const size = level.gridSize;
    playState = { point: { x: level.start.x, y: level.start.y }, dir: level.startDirection || "right", segments: [] };
    if (playView) {
      playView.svg.innerHTML = "";
      placePen(playView, playState.point, playState.dir);
    }
    await sleep(220);

    const commands = expandQueue(queue);
    for (const cmd of commands) {
      const moveDir = CMD_DIR[cmd];
      if (!moveDir) continue;
      playState.dir = moveDir;
      const v = DIR_VECTOR[moveDir];
      const next = { x: playState.point.x + v.dx, y: playState.point.y + v.dy };
      if (next.x < 0 || next.y < 0 || next.x >= size || next.y >= size) {
        setFeedback("Kalem ızgara dışına çıktı, tekrar dene.", "bad");
        running = false;
        btnGo.disabled = false;
        return;
      }
      placePen(playView, playState.point, playState.dir);
      await sleep(160);
      drawLine(playView, playState.point, next);
      playState.segments.push([playState.point, next]);
      playState.point = next;
      placePen(playView, playState.point, playState.dir);
      await sleep(180);
    }

    running = false;
    btnGo.disabled = false;
    const drawn = playState.segments.map(([a, b]) => normalizeSegment(a, b));
    evaluateResult(level, drawn);
  }

  function evaluateResult(level, drawnSegments) {
    const target = new Set(level.targetSegments || []);
    const drawnSet = new Set(drawnSegments);
    const matches = target.size === drawnSet.size && [...target].every((s) => drawnSet.has(s));

    if (matches) {
      setFeedback("Harika! Şekli doğru çizdin. 🎉", "ok");
      if (!completedLevelIds.includes(level.id)) completedLevelIds.push(level.id);
      btnNext.disabled = levelIndex >= levels.length - 1;
      updateHeader();
      handleLevelCompleted(level);
    } else {
      setFeedback("Şekil hedefle eşleşmedi, tekrar dene.", "bad");
    }
  }

  function emitGameUpdate() {
    try {
      window.parent.postMessage({ type: "GAME_UPDATE", source: "connect-the-dots", currentLevelIndex: levelIndex, levelId: currentLevel()?.id }, "*");
    } catch (e) {}
  }

  function handleLevelCompleted(level) {
    const elapsedSeconds = Math.round((Date.now() - startedAt) / 1000);
    try {
      window.parent.postMessage({
        type: "LEVEL_COMPLETED",
        source: "connect-the-dots",
        levelId: level.id,
        xp: level.xp,
        elapsedSeconds,
        completedLevelIds,
      }, "*");
    } catch (e) {}

    if (!isStaff && range) {
      const isLastInRange = levelIndex >= range.endIdx || levelIndex >= levels.length - 1;
      if (isLastInRange) {
        const totalXp = levels
          .slice(range.startIdx, Math.min(range.endIdx, levels.length - 1) + 1)
          .filter((lv) => completedLevelIds.includes(lv.id))
          .reduce((sum, lv) => sum + (lv.xp || 0), 0);
        try {
          window.parent.postMessage({
            type: "ASSIGNMENT_RANGE_COMPLETED",
            source: "connect-the-dots",
            xp: totalXp,
            elapsedSeconds,
            completedLevelIds,
          }, "*");
        } catch (e) {}
        assignmentLocked = true;
      }
    }

    emitGameUpdate();
  }

  function updateHeader() {
    const level = currentLevel();
    levelNoEl.textContent = level ? levelIndex + 1 : "-";
    totalNoEl.textContent = String(levels.length);
    doneNoEl.textContent = String(completedLevelIds.length);
  }

  function loadLevel(index) {
    if (assignmentLocked && !isStaff) return;
    if (index < 0 || index >= levels.length) return;
    levelIndex = index;
    const level = currentLevel();
    if (!level) return;
    renderTarget(level);
    resetPlay(level);
    cmdButtons.forEach((btn) => {
      const cmd = btn.dataset.cmd;
      btn.style.display = level.allowedCommands.includes(cmd) ? "" : "none";
    });
    btnNext.disabled = levelIndex >= levels.length - 1;
    updateHeader();
    startedAt = Date.now();
  }

  cmdButtons.forEach((btn) => {
    btn.addEventListener("click", () => addCommand(btn.dataset.cmd));
  });
  btnGo.addEventListener("click", runQueue);
  btnClear.addEventListener("click", () => {
    if (running) return;
    const level = currentLevel();
    if (level) resetPlay(level);
  });
  btnNext.addEventListener("click", () => {
    if (levelIndex < levels.length - 1) loadLevel(levelIndex + 1);
  });
  btnBack.addEventListener("click", () => {
    const target = appBase + "/etkinlikler";
    try {
      if (window.top && window.top !== window) {
        window.top.location.href = target;
        return;
      }
    } catch (e) {}
    window.location.href = target;
  });

  window.addEventListener("message", (event) => {
    const data = event && event.data;
    if (!data || typeof data !== "object") return;

    if (data.type === "SET_LEVEL_RANGE") {
      const start = Math.max(1, Number(data.levelStart || 1));
      const end = Math.max(start, Number(data.levelEnd || start));
      range = { startIdx: start - 1, endIdx: end - 1 };
      assignmentLocked = false;
      if (levels.length) loadLevel(Math.min(range.startIdx, levels.length - 1));
      return;
    }
    if (data.type === "FORCE_ASSIGNMENT_LOCK") {
      assignmentLocked = !!data.locked;
      return;
    }
    if (data.type === "SET_ASSIGNMENT_PROGRESS") {
      if (Array.isArray(data.completedLevelIds)) {
        completedLevelIds = data.completedLevelIds.map(Number).filter(Number.isFinite);
        updateHeader();
      }
      return;
    }
  });

  async function checkGrant() {
    if (!needsGrantCheck) return true;
    try {
      const grantUrl = appBase + "/runner-grant/connect-the-dots-runner";
      const res = await fetch(grantUrl, { credentials: "same-origin" });
      if (!res.ok && res.status !== 403) {
        showFatalError("Yetki kontrolü başarısız oldu (HTTP " + res.status + "). Sayfayı yenileyin veya öğretmeninize bildirin.");
        return false;
      }
      const json = await res.json();
      if (!json.ok) {
        showFatalError(json.message || "Bu etkinlik size atanmadı.");
        return false;
      }
      if (!hasInitialRange) {
        range = { startIdx: Math.max(0, (json.from || 1) - 1), endIdx: Math.max(0, (json.to || 1) - 1) };
      }
      return true;
    } catch (e) {
      showFatalError("Sunucuya bağlanılamadı (yetki kontrolü). İnternet bağlantınızı kontrol edip sayfayı yenileyin.<br><small>" + String(e && e.message || e) + "</small>");
      return false;
    }
  }

  async function boot() {
    const grantOk = await checkGrant();
    if (!grantOk) return;

    try {
      const res = await fetch(appBase + "/connect-the-dots-runner/levels", { credentials: "same-origin" });
      if (!res.ok) {
        showFatalError("Bölümler yüklenemedi (HTTP " + res.status + "). Sayfayı yenileyin.");
        return;
      }
      const json = await res.json();
      levels = Array.isArray(json.levels) ? json.levels : [];
    } catch (e) {
      showFatalError("Bölümler yüklenemedi. İnternet bağlantınızı kontrol edip sayfayı yenileyin.<br><small>" + String(e && e.message || e) + "</small>");
      return;
    }

    if (!levels.length) {
      setFeedback("Henüz bölüm eklenmemiş. Admin panelinden bölüm ekleyin.", "bad");
      return;
    }

    const initialIndex = !isStaff && range ? Math.min(range.startIdx, levels.length - 1) : 0;
    loadLevel(Math.max(0, initialIndex));
  }

  boot();
})();
