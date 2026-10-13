// CURRICULUM (100 bölüm) curriculum.js dosyasından gelir (tools/learn-levels/silent-teacher.gen.mjs ile üretilir).

function flattenCurriculum(curriculum) {
  const order = ["easy", "medium", "hard"];
  const out = [];
  order.forEach((difficulty) => {
    (curriculum[difficulty] || []).forEach((s, idx) => {
      out.push({
        ...s,
        difficulty,
        difficultyLabel: difficulty === "easy" ? "Kolay" : difficulty === "medium" ? "Orta" : "Zor",
        orderInDifficulty: idx + 1
      });
    });
  });
  return out;
}

const sections = flattenCurriculum(CURRICULUM);
const SECTION_XP = 30;
const sessionStartedAt = Date.now();
let rangeCompleteFired = false;

const params = new URLSearchParams(window.location.search);
const roleParam = (params.get("role") || "").toLowerCase();
const runnerRole = (roleParam === "teacher" || roleParam === "admin") ? roleParam : "student";
const assignmentId = String(params.get("assignmentId") || "").trim();
const assignmentTitle = String(params.get("assignmentTitle") || "Python Quiz Lab Odevi").trim();
const queryRangeStart = Math.max(1, Number(params.get("levelStart") || params.get("from") || 0));
const queryRangeEnd = Math.max(queryRangeStart, Number(params.get("levelEnd") || params.get("to") || queryRangeStart));
const hasQueryRange = Number.isFinite(queryRangeStart) && queryRangeStart > 0;
const enforceGrant = params.get("grant") === "1" || params.get("enforceGrant") === "1";
let levelStart = hasQueryRange ? queryRangeStart : 1;
let levelEndRaw = hasQueryRange ? queryRangeEnd : levelStart;
let levelEnd = Math.min(sections.length, levelEndRaw);
let isAssignmentMode = !!assignmentId || hasQueryRange;
if (runnerRole === "student" && !isAssignmentMode) {
  isAssignmentMode = true;
  levelStart = 1;
  levelEnd = Math.min(sections.length, 2);
}
const needsGrantCheck = runnerRole === "student" && (isAssignmentMode || enforceGrant);
let grantDenied = false;

let allowedStartIdx = isAssignmentMode ? (levelStart - 1) : 0;
let allowedEndIdx = isAssignmentMode ? (levelEnd - 1) : (sections.length - 1);

if (runnerRole === "teacher" || runnerRole === "admin") {
  isAssignmentMode = false;
  levelStart = 1;
  levelEnd = sections.length;
  allowedStartIdx = 0;
  allowedEndIdx = sections.length - 1;
}

let unlockedSectionIndex = allowedStartIdx;
let sectionIndex = allowedStartIdx;
let questionIndex = 0;
let answered = false;
let totalCorrect = 0;
let pendingSectionIndex = null;
const completedSectionIds = new Set();

const sectionListEl = document.getElementById("section-list");
const sectionTitleEl = document.getElementById("section-title");
const sectionProgressEl = document.getElementById("section-progress");
const lessonEl = document.getElementById("lesson");
const codeEl = document.getElementById("code");
const questionEl = document.getElementById("question");
const optionsEl = document.getElementById("options");
const feedbackEl = document.getElementById("feedback");
const nextBtn = document.getElementById("btn-next");
const footerEl = document.getElementById("footer");

function elapsedSeconds() {
  return Math.max(0, Math.round((Date.now() - sessionStartedAt) / 1000));
}

function inAllowedRange(idx) {
  return idx >= allowedStartIdx && idx <= allowedEndIdx;
}

function isSectionCompletedByState(idx) {
  return completedSectionIds.has(idx + 1);
}

function computeRangeProgressPercent() {
  let solved = 0;
  let total = 0;
  sections.forEach((sec, idx) => {
    if (!inAllowedRange(idx)) return;
    const qCount = Array.isArray(sec.questions) ? sec.questions.length : 0;
    total += qCount;
    if (idx < sectionIndex) solved += qCount;
    else if (idx === sectionIndex) solved += Math.max(0, questionIndex + (answered ? 1 : 0));
  });
  if (total <= 0) return 0;
  return Math.min(100, Math.round((solved / total) * 100));
}

function escapeHtml(v) {
  return String(v || "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;");
}

function highlightCode(text) {
  const escaped = escapeHtml(text);
  return escaped
    .replace(/\b(if|and|or|not|True|False|else|def|return|function|let)\b/g, '<span class="kw">$1</span>')
    .replace(/\b(\d+)\b/g, '<span class="num">$1</span>');
}

function buildSectionButtons() {
  let active = null;
  sectionListEl.innerHTML = "";
  sections.forEach((s, idx) => {
    const btn = document.createElement("button");
    const outOfRange = !inAllowedRange(idx);
    const locked = grantDenied || outOfRange || idx > unlockedSectionIndex;
    btn.className = "section-btn";
    if (idx === sectionIndex) btn.classList.add("active");
    if (locked) btn.classList.add("locked");
    const suffix = outOfRange ? " (Odev disi)" : "";
    btn.textContent = `${idx + 1}. [${s.difficultyLabel}] ${s.title}${suffix}`;
    btn.disabled = locked;
    btn.addEventListener("click", () => {
      if (locked) return;
      sectionIndex = idx;
      questionIndex = 0;
      answered = false;
      render();
      emitProgress();
    });
    sectionListEl.appendChild(btn);
    if (idx === sectionIndex) active = btn;
  });
  if (active && active.scrollIntoView) active.scrollIntoView({ block: "nearest" });
}

function getCurrentQuestion() {
  return sections[sectionIndex].questions[questionIndex];
}

function setFeedback(text, ok = null) {
  feedbackEl.textContent = text || "";
  feedbackEl.style.color = ok === true ? "#166534" : ok === false ? "#b91c1c" : "#334155";
}

function renderOptions() {
  const q = getCurrentQuestion();
  optionsEl.innerHTML = "";
  q.options.forEach((opt) => {
    const btn = document.createElement("button");
    btn.className = "opt";
    btn.textContent = opt;
    if (opt === "?!?") btn.classList.add("trap");
    btn.disabled = answered || grantDenied;
    btn.addEventListener("click", () => onAnswer(opt, btn));
    optionsEl.appendChild(btn);
  });
}

function renderFooter() {
  const totalQuestions = sections.reduce((sum, s, idx) => {
    if (!inAllowedRange(idx)) return sum;
    return sum + s.questions.length;
  }, 0);
  const prefix = isAssignmentMode ? "Odev " : "";
  footerEl.textContent = `${prefix}Dogru: ${totalCorrect} / ${totalQuestions}`;
}

function render() {
  const sec = sections[sectionIndex];
  const q = getCurrentQuestion();
  sectionTitleEl.textContent = `${sec.difficultyLabel} - ${sec.title}`;
  sectionProgressEl.textContent = `Soru ${questionIndex + 1}/${sec.questions.length}`;
  lessonEl.textContent = sec.lesson;
  codeEl.innerHTML = highlightCode(q.code);
  questionEl.textContent = q.prompt;
  nextBtn.style.display = "none";
  nextBtn.disabled = true;
  nextBtn.textContent = "Sonraki Bolume Gec";
  setFeedback("");
  renderOptions();
  buildSectionButtons();
  renderFooter();
}

function animateWrong(btn) {
  if (!btn) return;
  btn.classList.add("wrong", "wrong-anim");
  setTimeout(() => btn.classList.remove("wrong"), 320);
  setTimeout(() => btn.classList.remove("wrong-anim"), 420);
}

function emitProgress() {
  const percent = computeRangeProgressPercent();
  try {
    window.parent?.postMessage({
      type: "GAME_UPDATE",
      source: "silent-teacher",
      assignmentId: assignmentId || null,
      assignmentTitle,
      levelStart,
      levelEnd,
      completedSectionIds: Array.from(completedSectionIds).sort((a, b) => a - b),
      elapsedSeconds: elapsedSeconds(),
      levels: sections.map((s, idx) => ({
        id: s.id,
        name: `[${s.difficultyLabel}] ${s.title}`,
        completed: isSectionCompletedByState(idx)
      })),
      currentLevelIndex: Number(sectionIndex || 0),
      progressPercent: percent,
      score: totalCorrect
    }, "*");
  } catch (e) {}
}

function emitSectionCompleted() {
  const levelNo = sectionIndex + 1;
  const doneInRange = Array.from(completedSectionIds).filter((id) => id >= levelStart && id <= levelEnd).length;
  const totalRange = Math.max(1, levelEnd - levelStart + 1);
  const percent = Math.min(100, Math.round((doneInRange / totalRange) * 100));
  try {
    window.parent?.postMessage({
      type: "LEVEL_COMPLETED",
      source: "silent-teacher",
      assignmentId: assignmentId || null,
      assignmentTitle,
      levelId: sections[sectionIndex]?.id || `section_${levelNo}`,
      levelNo,
      levelStart,
      levelEnd,
      completedSectionIds: Array.from(completedSectionIds).sort((a, b) => a - b),
      currentLevelIndex: Number(sectionIndex || 0),
      percent,
      progressPercent: computeRangeProgressPercent(),
      xp: SECTION_XP,
      elapsedSeconds: elapsedSeconds(),
      score: totalCorrect,
      levels: sections.map((s, idx) => ({
        id: s.id,
        name: `[${s.difficultyLabel}] ${s.title}`,
        completed: isSectionCompletedByState(idx)
      }))
    }, "*");
  } catch (e) {}
}

function emitRangeCompletedIfNeeded() {
  if (!isAssignmentMode || rangeCompleteFired) return;
  const doneInRange = Array.from(completedSectionIds).filter((id) => id >= levelStart && id <= levelEnd).length;
  const totalRange = Math.max(1, levelEnd - levelStart + 1);
  if (doneInRange < totalRange) return;
  rangeCompleteFired = true;
  try {
    window.parent?.postMessage({
      type: "ASSIGNMENT_RANGE_COMPLETED",
      source: "silent-teacher",
      assignmentId,
      assignmentTitle,
      levelStart,
      levelEnd,
      completedSectionIds: Array.from(completedSectionIds).sort((a, b) => a - b),
      xp: doneInRange * SECTION_XP,
      elapsedSeconds: elapsedSeconds(),
      currentLevelIndex: Number(sectionIndex || 0)
    }, "*");
  } catch (e) {}
}

function onAnswer(selected, btnEl) {
  if (answered || grantDenied) return;
  const q = getCurrentQuestion();
  const ok = String(selected) === String(q.answer);
  if (!ok) {
    animateWrong(btnEl);
    setFeedback("Yanlis. Tekrar dene.", false);
    return;
  }
  answered = true;
  totalCorrect += 1;
  if (btnEl) btnEl.classList.add("correct");
  setFeedback("Dogru!", true);
  emitProgress();
  setTimeout(() => {
    const sec = sections[sectionIndex];
    if (questionIndex < sec.questions.length - 1) {
      questionIndex += 1;
      answered = false;
      render();
      emitProgress();
      return;
    }
    completeSection();
  }, 500);
}

function completeSection() {
  completedSectionIds.add(sectionIndex + 1);
  emitSectionCompleted();

  if (sectionIndex < allowedEndIdx) {
    pendingSectionIndex = sectionIndex + 1;
    unlockedSectionIndex = Math.max(unlockedSectionIndex, pendingSectionIndex);
    setFeedback("Bolum tamamlandi.", true);
    nextBtn.style.display = "inline-block";
    nextBtn.disabled = false;
    nextBtn.textContent = "Sonraki Bolume Gec";
  } else {
    setFeedback(isAssignmentMode ? "Odev bolumleri tamamlandi." : "Tebrikler! Tum bolumleri tamamladin.", true);
    nextBtn.style.display = "inline-block";
    nextBtn.disabled = true;
    nextBtn.textContent = "Tamamlandi";
  }
  emitProgress();
  emitRangeCompletedIfNeeded();
}

function onNext() {
  if (grantDenied) return;
  if (!Number.isInteger(pendingSectionIndex)) return;
  sectionIndex = pendingSectionIndex;
  pendingSectionIndex = null;
  questionIndex = 0;
  answered = false;
  render();
  emitProgress();
}

document.getElementById("btn-close")?.addEventListener("click", () => {
  try {
    window.parent?.postMessage({ type: "REQUEST_CLOSE_ACTIVITY", source: "silent-teacher" }, "*");
  } catch (e) {}
});

nextBtn.addEventListener("click", onNext);
function denyRunnerAccess(message) {
  grantDenied = true;
  pendingSectionIndex = null;
  nextBtn.style.display = "inline-block";
  nextBtn.disabled = true;
  nextBtn.textContent = "Erisim Engellendi";
  setFeedback(message || "Bu odev araligina erisim izniniz yok.", false);
}

async function resolveAssignmentRangeFromGrant() {
  const grantUrl = (window.RUNNER_APP_BASE || "").replace(/\/$/, "") + "/runner-grant/silent-teacher-runner" + window.location.search;
  if (runnerRole === "teacher" || runnerRole === "admin") return;
  if (!needsGrantCheck) {
    try {
      const probe = await fetch(grantUrl, {
        credentials: "same-origin",
        headers: { Accept: "application/json" }
      });
      if (probe.ok) {
        const probeGrant = await probe.json();
        if (probeGrant && probeGrant.ok && probeGrant.role === "staff") {
          isAssignmentMode = false;
          levelStart = 1;
          levelEnd = sections.length;
          allowedStartIdx = 0;
          allowedEndIdx = sections.length - 1;
          unlockedSectionIndex = Math.max(unlockedSectionIndex, allowedEndIdx);
        }
      }
    } catch (e) {}
    return;
  }
  try {
    const res = await fetch(grantUrl, {
      credentials: "same-origin",
      headers: { Accept: "application/json" }
    });
    if (!res.ok) {
      if (hasQueryRange) {
        isAssignmentMode = true;
        levelStart = queryRangeStart;
        levelEnd = Math.min(sections.length, queryRangeEnd);
        allowedStartIdx = Math.max(0, levelStart - 1);
        allowedEndIdx = Math.max(allowedStartIdx, levelEnd - 1);
        unlockedSectionIndex = allowedStartIdx;
        sectionIndex = allowedStartIdx;
        questionIndex = 0;
        answered = false;
        return;
      }
      denyRunnerAccess("Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.");
      return;
    }
    const grant = await res.json();
    if (grant && grant.ok && grant.role === "staff") {
      isAssignmentMode = false;
      levelStart = 1;
      levelEnd = sections.length;
      allowedStartIdx = 0;
      allowedEndIdx = sections.length - 1;
      unlockedSectionIndex = Math.max(unlockedSectionIndex, allowedEndIdx);
      sectionIndex = 0;
      questionIndex = 0;
      answered = false;
      return;
    }
    if (!grant || !grant.ok || grant.role !== "student") {
      denyRunnerAccess("Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.");
      return;
    }
    levelStart = Math.max(1, Number(grant.from || 1));
    levelEnd = Math.min(sections.length, Math.max(levelStart, Number(grant.to || levelStart)));
    isAssignmentMode = true;
    allowedStartIdx = levelStart - 1;
    allowedEndIdx = levelEnd - 1;
    unlockedSectionIndex = Math.max(allowedStartIdx, Math.min(unlockedSectionIndex, allowedEndIdx));
    sectionIndex = allowedStartIdx;
    questionIndex = 0;
    answered = false;
  } catch (e) {
    denyRunnerAccess("Erisim dogrulanamadi. Sayfayi yenileyip tekrar deneyin.");
  }
}

resolveAssignmentRangeFromGrant().finally(() => {
  render();
  emitProgress();
});
