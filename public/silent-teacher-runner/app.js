const CURRICULUM = {
  easy: [
    {
      id: "easy_1",
      title: "Degiskenler",
      lesson: "Degiskene yeni deger atarsan eski deger silinir.",
      questions: [
        { code: "a = 2\na = 3\na + 1", prompt: "Son satirin sonucu nedir?", options: ["5", "1", "6", "4", "3", "2", "?!?"], answer: "4" },
        { code: "x = 9\ny = 4\nx - y", prompt: "x - y sonucu nedir?", options: ["6", "5", "13", "4", "0", "9", "?!?"], answer: "5" },
        { code: "n = 10\nn = n + 5\nn", prompt: "n'nin son degeri nedir?", options: ["15", "10", "5", "20", "0", "11", "?!?"], answer: "15" }
      ]
    },
    {
      id: "easy_2",
      title: "Dizi ve Indeks",
      lesson: "Dizilerde indeks 0'dan baslar. a[0] ilk elemandir.",
      questions: [
        { code: "let a = ['p', 'g', 'w', 't', 'x']\na[0]", prompt: "a[0] sonucu nedir?", options: ["'g'", "'x'", "'p'", "5", "4", "'w'", "?!?"], answer: "'p'" },
        { code: "let b = [8, 3, 1]\nb[2]", prompt: "b[2] sonucu nedir?", options: ["8", "3", "1", "2", "0", "9", "?!?"], answer: "1" },
        { code: "let c = ['q', 'y']\nc.length", prompt: "Uzunluk nedir?", options: ["'q'", "'y'", "1", "9", "6", "2", "?!?"], answer: "2" }
      ]
    },
    {
      id: "easy_3",
      title: "Karsilastirma",
      lesson: "== esitligi kontrol eder, sonuc True veya False olur.",
      questions: [
        { code: "14 == 14", prompt: "Ifadenin sonucu nedir?", options: ["28", "0", "True", "False", "'=='", "14", "?!?"], answer: "True" },
        { code: "8 != 8", prompt: "Sonuc nedir?", options: ["True", "False", "0", "1", "16", "8", "?!?"], answer: "False" },
        { code: "3 < 5", prompt: "3 < 5 sonucu nedir?", options: ["True", "False", "8", "2", "10", "0", "?!?"], answer: "True" }
      ]
    }
  ],
  medium: [
    {
      id: "mid_1",
      title: "if / else",
      lesson: "if dogruysa ilk blok, degilse else blogu calisir.",
      questions: [
        { code: "a = 0\nif 3 < 6:\n    a = 4\na + 1", prompt: "Kodun sonucu nedir?", options: ["5", "1", "0", "6", "3", "4", "?!?"], answer: "5" },
        { code: "b = 2\nif b > 5:\n    b = 9\nelse:\n    b = 7\nb", prompt: "b'nin son degeri nedir?", options: ["9", "2", "7", "5", "0", "1", "?!?"], answer: "7" },
        { code: "k = 1\nif k == 1:\n    k = k + 2\nk", prompt: "k'nin son degeri nedir?", options: ["1", "2", "3", "4", "0", "5", "?!?"], answer: "3" }
      ]
    },
    {
      id: "mid_2",
      title: "else if Zinciri",
      lesson: "Ilk dogru kosul calisir, diger dallar atlanir.",
      questions: [
        { code: "let a = 0\nif (1 < 7) {\n  a = 2\n} else if (6 < 8) {\n  a = 4\n} else {\n  a = 5\n}\na + 4", prompt: "Sonuc nedir?", options: ["4", "5", "2", "6", "9", "8", "?!?"], answer: "6" },
        { code: "let n = 3\nif (n > 5) {\n  n = 1\n} else if (n == 3) {\n  n = 9\n}\nn", prompt: "n'nin son degeri nedir?", options: ["3", "1", "9", "5", "0", "7", "?!?"], answer: "9" },
        { code: "let x = 10\nif (x < 0) {\n  x = 4\n} else {\n  x = x - 3\n}\nx", prompt: "Son deger nedir?", options: ["10", "7", "4", "3", "0", "13", "?!?"], answer: "7" }
      ]
    },
    {
      id: "mid_3",
      title: "Fonksiyon Giris",
      lesson: "Fonksiyon parametre alir ve return ile sonuc dondurur.",
      questions: [
        { code: "function hello(a, b) {\n  return a + b\n}\n\nhello(4, 5)", prompt: "Sonuc nedir?", options: ["10", "8", "4", "6", "5", "1", "9", "2", "7", "3", "?!?"], answer: "9" },
        { code: "function hi(a, b) {\n  return a * b\n}\n\nhi(2, 3)", prompt: "Sonuc nedir?", options: ["3", "4", "8", "6", "7", "1", "2", "5", "?!?"], answer: "6" },
        { code: "def topla(a, b):\n    return a + b\n\ntopla(7, 1)", prompt: "Fonksiyon sonucu nedir?", options: ["6", "7", "8", "9", "1", "0", "?!?"], answer: "8" }
      ]
    }
  ],
  hard: [
    {
      id: "hard_1",
      title: "Ic Ice Kosul",
      lesson: "Bir if blogunun icinde baska bir if daha olabilir.",
      questions: [
        { code: "x = 4\ny = 2\nif x > 1:\n    if y < 3:\n        x = x + y\nx", prompt: "x'in son degeri nedir?", options: ["4", "5", "6", "2", "3", "7", "?!?"], answer: "6" },
        { code: "a = 5\nif a > 2:\n    if a < 5:\n        a = 1\n    else:\n        a = 9\na", prompt: "Son deger nedir?", options: ["1", "5", "9", "2", "3", "0", "?!?"], answer: "9" },
        { code: "n = 3\nif n == 3:\n    n = n * 2\n    if n == 6:\n        n = n + 1\nn", prompt: "Son deger nedir?", options: ["6", "7", "3", "2", "5", "1", "?!?"], answer: "7" }
      ]
    },
    {
      id: "hard_2",
      title: "Dizi ve Hesaplama",
      lesson: "Indeksleme + uzunluk bilgisi birlikte kullanilabilir.",
      questions: [
        { code: "arr = [2, 4, 6, 8]\narr[1] + arr[3]", prompt: "Sonuc nedir?", options: ["10", "12", "14", "16", "8", "6", "?!?"], answer: "12" },
        { code: "s = 'qwert'\nlen(s) - 1", prompt: "Sonuc nedir?", options: ["3", "4", "5", "2", "1", "0", "?!?"], answer: "4" },
        { code: "a = ['k', 'm', 'z']\na[0] + a[2]", prompt: "Sonuc nedir?", options: ["'km'", "'kz'", "'mz'", "'z'", "'k'", "3", "?!?"], answer: "'kz'" }
      ]
    },
    {
      id: "hard_3",
      title: "Fonksiyon + Kosul",
      lesson: "Fonksiyon icinde kosul kullanarak farkli deger dondurebilirsin.",
      questions: [
        { code: "def puan(x):\n    if x >= 50:\n        return 1\n    return 0\n\npuan(70)", prompt: "Sonuc nedir?", options: ["0", "1", "50", "70", "2", "True", "?!?"], answer: "1" },
        { code: "def sec(a, b):\n    if a > b:\n        return a\n    return b\n\nsec(3, 9)", prompt: "Sonuc nedir?", options: ["3", "9", "12", "0", "6", "1", "?!?"], answer: "9" },
        { code: "function f(a, b) {\n  if (a == b) {\n    return a * 2\n  }\n  return a + b\n}\n\nf(4, 4)", prompt: "Sonuc nedir?", options: ["4", "8", "16", "2", "0", "12", "?!?"], answer: "8" }
      ]
    }
  ]
};

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
  });
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
  const grantUrl = new URL("../runner-grant/silent-teacher-runner", window.location.href).toString();
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
