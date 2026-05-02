(function () {
  const params = new URLSearchParams(window.location.search);
  const roleParam = (params.get("role") || "student").toLowerCase();
  const role = (roleParam === "teacher" || roleParam === "admin") ? roleParam : "student";
  const uid = params.get("uid") || "";
  const initialRangeStart = Math.max(1, Number(params.get("levelStart") || params.get("from") || 0));
  const initialRangeEndRaw = Math.max(initialRangeStart, Number(params.get("levelEnd") || params.get("to") || initialRangeStart));
  const hasInitialRange = Number.isFinite(initialRangeStart) && initialRangeStart > 0;
  const enforceGrant = params.get("grant") === "1" || params.get("enforceGrant") === "1";
  const isStaff = role === "teacher" || role === "admin";
  const needsGrantCheck = role === "student" && (hasInitialRange || enforceGrant);

  const boardEl = document.getElementById("board");
  const cmdListEl = document.getElementById("cmd-list");
  const levelNoEl = document.getElementById("level-no");
  const doneNoEl = document.getElementById("done-no");
  const totalNoEl = document.getElementById("total-no");
  const tipEl = document.getElementById("level-tip");
  const varsBoxEl = document.getElementById("vars-box");
  const varAEl = document.getElementById("var-a");
  const btnReset = document.getElementById("btn-reset");
  const btnNext = document.getElementById("btn-next");
  const countAEl = document.getElementById("count-a");
  const countBEl = document.getElementById("count-b");
  const countCEl = document.getElementById("count-c");
  const countXpEl = document.getElementById("count-xp");

  const designerModal = document.getElementById("designer-modal");
  const designerTitle = document.getElementById("designer-title");
  const lvName = document.getElementById("lv-name");
  const lvSize = document.getElementById("lv-size");
  const lvStart = document.getElementById("lv-start");
  const lvGoal = document.getElementById("lv-goal");
  const lvWalls = document.getElementById("lv-walls");
  const lvXp = document.getElementById("lv-xp");
  const btnSaveLevel = document.getElementById("btn-save-level");
  const btnCancelLevel = document.getElementById("btn-cancel-level");

  const deleteModal = document.getElementById("delete-modal");
  const deleteText = document.getElementById("delete-text");
  const btnConfirmDel = document.getElementById("btn-confirm-del");
  const btnCancelDel = document.getElementById("btn-cancel-del");

  const defaultLevels = [
    {
      id: 1,
      name: "Temel 1",
      size: 3,
      start: [0, 2],
      goal: [2, 2],
      walls: [],
      xp: 10,
      aValue: 1,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, left: { a: -1 }, up: { b: 1 }, down: { b: -1 } },
      codeLines: ["sag()", "sag()"],
      expectedMoves: ["right", "right"],
      stepToLine: [0, 1],
      targetCounters: { a: 2, b: 0, c: 0 },
      tip: "Ilk bolum: iki kez saga git."
    },
    {
      id: 2,
      name: "Temel 2",
      size: 4,
      start: [0, 0],
      goal: [3, 1],
      walls: [],
      xp: 12,
      aValue: 2,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, left: { a: -1 }, up: { b: -1 }, down: { b: 1 } },
      codeLines: ["sag()", "sag()", "sag()", "asagi()"],
      expectedMoves: ["right", "right", "right", "down"],
      stepToLine: [0, 1, 2, 3],
      targetCounters: { a: 3, b: 1, c: 0 },
      tip: "Uc sag, bir asagi."
    },
    {
      id: 3,
      name: "Temel 3",
      size: 4,
      start: [3, 3],
      goal: [1, 2],
      walls: [],
      xp: 13,
      aValue: 3,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["sol()", "sol()", "yukari()"],
      expectedMoves: ["left", "left", "up"],
      stepToLine: [0, 1, 2],
      targetCounters: { a: 2, b: 1, c: 0 },
      tip: "Iki sol, bir yukari."
    },
    {
      id: 4,
      name: "Temel 4",
      size: 5,
      start: [4, 0],
      goal: [1, 1],
      walls: [],
      xp: 14,
      aValue: 4,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, down: { b: 1 }, right: { a: -1 }, up: { b: -1 } },
      codeLines: ["sol()", "sol()", "sol()", "asagi()"],
      expectedMoves: ["left", "left", "left", "down"],
      stepToLine: [0, 1, 2, 3],
      targetCounters: { a: 3, b: 1, c: 0 },
      tip: "Uc sol, bir asagi."
    },
    {
      id: 5,
      name: "Temel 5",
      size: 5,
      start: [0, 4],
      goal: [2, 2],
      walls: [],
      xp: 15,
      aValue: 5,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { up: { b: 1 }, right: { a: 1 }, down: { b: -1 }, left: { a: -1 } },
      codeLines: ["yukari()", "yukari()", "sag()", "sag()"],
      expectedMoves: ["up", "up", "right", "right"],
      stepToLine: [0, 1, 2, 3],
      targetCounters: { a: 2, b: 2, c: 0 },
      tip: "Iki yukari, iki sag."
    },
    {
      id: 6,
      name: "Ic Ice Tekrarla 1",
      size: 7,
      start: [1, 2],
      goal: [0, 3],
      walls: [],
      xp: 20,
      aValue: 2,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, left: { a: -1 }, down: { b: 1 }, up: { b: -1 } },
      codeLines: ["tekrarla (3) {", "  tekrarla (2) {", "    sag()", "  }", "}", "asagi()"],
      expectedMoves: ["right", "right", "right", "right", "right", "right", "down"],
      stepToLine: [2, 2, 2, 2, 2, 2, 5],
      targetCounters: { a: 6, b: 1, c: 0 },
      tip: "Ic ice tekrarla yapisi."
    },
    {
      id: 7,
      name: "Ic Ice Tekrarla 2",
      size: 7,
      start: [4, 4],
      goal: [0, 0],
      walls: [],
      xp: 22,
      aValue: 3,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (4) {", "  yukari()", "  sol()", "}"],
      expectedMoves: ["up", "left", "up", "left", "up", "left", "up", "left"],
      stepToLine: [1, 2, 1, 2, 1, 2, 1, 2],
      targetCounters: { a: 4, b: 4, c: 0 },
      tip: "Sira bozulmadan ilerle."
    },
    {
      id: 8,
      name: "Kosul 1",
      size: 5,
      start: [2, 2],
      goal: [2, 1],
      walls: [],
      xp: 24,
      aValue: 5,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, right: { a: -1 }, up: { b: 1 }, down: { b: -1 } },
      codeLines: ["sol()", "eger (A >= 4) {", "  yukari()", "}", "sag()"],
      expectedMovesTrue: ["left", "up", "right"],
      stepToLineTrue: [0, 2, 4],
      expectedMovesFalse: ["left", "right"],
      stepToLineFalse: [0, 4],
      condition: { var: "A", op: ">=", value: 4 },
      goalTrue: [2, 1],
      goalFalse: [2, 2],
      targetCounters: { a: 0, b: 1, c: 0 },
      tip: "A degeri ekranda. Kosul saglanirsa ic satir calisir."
    },
    {
      id: 9,
      name: "Kosul 2",
      size: 6,
      start: [5, 5],
      goal: [1, 5],
      walls: [],
      xp: 26,
      aValue: 2,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, right: { a: -1 }, up: { b: 1 }, down: { b: -1 } },
      codeLines: ["tekrarla (3) {", "  sol()", "}", "eger (A >= 4) {", "  yukari()", "}", "sol()"],
      expectedMovesTrue: ["left", "left", "left", "up", "left"],
      stepToLineTrue: [1, 1, 1, 4, 6],
      expectedMovesFalse: ["left", "left", "left", "left"],
      stepToLineFalse: [1, 1, 1, 6],
      condition: { var: "A", op: ">=", value: 4 },
      goalTrue: [1, 4],
      goalFalse: [1, 5],
      targetCounters: { a: 4, b: 0, c: 0 },
      tip: "A kosulu tutmazsa eger blogu atlanir."
    },
    {
      id: 10,
      name: "Ic Ice Tekrarla 3",
      size: 7,
      start: [0, 6],
      goal: [6, 0],
      walls: [],
      xp: 30,
      aValue: 6,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, up: { b: 1 }, left: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (2) {", "  tekrarla (3) {", "    sag()", "  }", "  yukari()", "}", "tekrarla (4) {", "  yukari()", "}"],
      expectedMoves: ["right", "right", "right", "up", "right", "right", "right", "up", "up", "up", "up", "up"],
      stepToLine: [2, 2, 2, 4, 2, 2, 2, 4, 7, 7, 7, 7],
      targetCounters: { a: 6, b: 6, c: 0 },
      tip: "Uzun program: ic ice tekrarla + ek tekrar."
    },
    {
      id: 11,
      name: "Orta 1",
      size: 6,
      start: [0, 5],
      goal: [4, 3],
      walls: [],
      xp: 32,
      aValue: 3,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, up: { b: 1 }, left: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (4) {", "  sag()", "}", "yukari()", "yukari()"],
      expectedMoves: ["right", "right", "right", "right", "up", "up"],
      stepToLine: [1, 1, 1, 1, 3, 4],
      targetCounters: { a: 4, b: 2, c: 0 },
      tip: "Orta seviye tekrar."
    },
    {
      id: 12,
      name: "Orta 2",
      size: 6,
      start: [5, 5],
      goal: [1, 4],
      walls: [],
      xp: 34,
      aValue: 5,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (3) {", "  sol()", "}", "eger (A >= 4) {", "  yukari()", "}", "sol()"],
      expectedMovesTrue: ["left", "left", "left", "up", "left"],
      stepToLineTrue: [1, 1, 1, 4, 6],
      expectedMovesFalse: ["left", "left", "left", "left"],
      stepToLineFalse: [1, 1, 1, 6],
      condition: { var: "A", op: ">=", value: 4 },
      goalTrue: [1, 4],
      goalFalse: [1, 5],
      targetCounters: { a: 4, b: 1, c: 0 },
      tip: "Kosul dogruysa bir adim fark eder."
    },
    {
      id: 13,
      name: "Orta 3",
      size: 7,
      start: [6, 6],
      goal: [2, 2],
      walls: [],
      xp: 36,
      aValue: 2,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (4) {", "  sol()", "  yukari()", "}"],
      expectedMoves: ["left", "up", "left", "up", "left", "up", "left", "up"],
      stepToLine: [1, 2, 1, 2, 1, 2, 1, 2],
      targetCounters: { a: 4, b: 4, c: 0 },
      tip: "Capraz ilerleme."
    },
    {
      id: 14,
      name: "Orta 4",
      size: 7,
      start: [1, 1],
      goal: [6, 4],
      walls: [],
      xp: 38,
      aValue: 7,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, down: { b: 1 }, left: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (5) {", "  sag()", "}", "tekrarla (3) {", "  asagi()", "}"],
      expectedMoves: ["right", "right", "right", "right", "right", "down", "down", "down"],
      stepToLine: [1, 1, 1, 1, 1, 4, 4, 4],
      targetCounters: { a: 5, b: 3, c: 0 },
      tip: "Iki ayri dongu."
    },
    {
      id: 15,
      name: "Orta 5",
      size: 6,
      start: [2, 5],
      goal: [4, 2],
      walls: [],
      xp: 40,
      aValue: 4,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { up: { b: 1 }, right: { a: 1 }, down: { b: -1 }, left: { a: -1 } },
      codeLines: ["tekrarla (3) {", "  yukari()", "}", "eger (A >= 4) {", "  sag()", "  sag()", "}"],
      expectedMovesTrue: ["up", "up", "up", "right", "right"],
      stepToLineTrue: [1, 1, 1, 4, 5],
      expectedMovesFalse: ["up", "up", "up"],
      stepToLineFalse: [1, 1, 1],
      condition: { var: "A", op: ">=", value: 4 },
      goalTrue: [4, 2],
      goalFalse: [2, 2],
      targetCounters: { a: 2, b: 3, c: 0 },
      tip: "Kosul ile yatay hareket acilir."
    },
    {
      id: 16,
      name: "Orta 6",
      size: 8,
      start: [6, 1],
      goal: [2, 5],
      walls: [],
      xp: 42,
      aValue: 8,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, down: { b: 1 }, right: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (2) {", "  tekrarla (2) {", "    sol()", "  }", "}", "tekrarla (4) {", "  asagi()", "}"],
      expectedMoves: ["left", "left", "left", "left", "down", "down", "down", "down"],
      stepToLine: [2, 2, 2, 2, 6, 6, 6, 6],
      targetCounters: { a: 4, b: 4, c: 0 },
      tip: "Buyuk gridde denge."
    },
    {
      id: 17,
      name: "Orta 7",
      size: 7,
      start: [0, 0],
      goal: [0, 2],
      walls: [],
      xp: 44,
      aValue: 1,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, down: { b: 1 }, left: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (2) {", "  tekrarla (2) {", "    sag()", "  }", "  asagi()", "}", "sag()", "sag()", "sag()"],
      expectedMoves: ["right", "right", "down", "right", "right", "down", "right", "right", "right"],
      stepToLine: [2, 2, 4, 2, 2, 4, 6, 7, 8],
      targetCounters: { a: 7, b: 2, c: 0 },
      tip: "Ic ice + serbest adimlar."
    },
    {
      id: 18,
      name: "Orta 8",
      size: 7,
      start: [6, 3],
      goal: [1, 1],
      walls: [],
      xp: 46,
      aValue: 6,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (5) {", "  sol()", "}", "eger (A >= 5) {", "  yukari()", "  yukari()", "}"],
      expectedMovesTrue: ["left", "left", "left", "left", "left", "up", "up"],
      stepToLineTrue: [1, 1, 1, 1, 1, 4, 5],
      expectedMovesFalse: ["left", "left", "left", "left", "left"],
      stepToLineFalse: [1, 1, 1, 1, 1],
      condition: { var: "A", op: ">=", value: 5 },
      goalTrue: [1, 1],
      goalFalse: [1, 3],
      targetCounters: { a: 5, b: 2, c: 0 },
      tip: "Kosul burada belirleyici."
    },
    {
      id: 19,
      name: "Orta 9",
      size: 8,
      start: [2, 7],
      goal: [6, 2],
      walls: [],
      xp: 48,
      aValue: 9,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { up: { b: 1 }, right: { a: 1 }, down: { b: -1 }, left: { a: -1 } },
      codeLines: ["tekrarla (5) {", "  yukari()", "}", "tekrarla (4) {", "  sag()", "}"],
      expectedMoves: ["up", "up", "up", "up", "up", "right", "right", "right", "right"],
      stepToLine: [1, 1, 1, 1, 1, 4, 4, 4, 4],
      targetCounters: { a: 4, b: 5, c: 0 },
      tip: "Dikey sonra yatay."
    },
    {
      id: 20,
      name: "Orta 10",
      size: 8,
      start: [7, 7],
      goal: [1, 1],
      walls: [],
      xp: 50,
      aValue: 10,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (2) {", "  tekrarla (3) {", "    sol()", "  }", "  yukari()", "}", "tekrarla (4) {", "  yukari()", "}"],
      expectedMoves: ["left", "left", "left", "up", "left", "left", "left", "up", "up", "up", "up", "up"],
      stepToLine: [2, 2, 2, 4, 2, 2, 2, 4, 7, 7, 7, 7],
      targetCounters: { a: 6, b: 6, c: 0 },
      tip: "Orta seviyenin final bolumu."
    },
    {
      id: 21,
      name: "Orta 11",
      size: 8,
      start: [0, 7],
      goal: [5, 3],
      walls: [],
      xp: 52,
      aValue: 4,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, up: { b: 1 }, left: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (5) {", "  sag()", "}", "tekrarla (4) {", "  yukari()", "}"],
      expectedMoves: ["right", "right", "right", "right", "right", "up", "up", "up", "up"],
      stepToLine: [1, 1, 1, 1, 1, 4, 4, 4, 4],
      targetCounters: { a: 5, b: 4, c: 0 },
      tip: "Uzun yatay ve dikey rota."
    },
    {
      id: 22,
      name: "Orta 12",
      size: 8,
      start: [7, 0],
      goal: [2, 4],
      walls: [],
      xp: 54,
      aValue: 5,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, down: { b: 1 }, right: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (5) {", "  sol()", "}", "tekrarla (4) {", "  asagi()", "}"],
      expectedMoves: ["left", "left", "left", "left", "left", "down", "down", "down", "down"],
      stepToLine: [1, 1, 1, 1, 1, 4, 4, 4, 4],
      targetCounters: { a: 5, b: 4, c: 0 },
      tip: "Ayni mantigin ters yone uygulanisi."
    },
    {
      id: 23,
      name: "Orta 13",
      size: 8,
      start: [1, 1],
      goal: [5, 3],
      walls: [],
      xp: 56,
      aValue: 6,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, down: { b: 1 }, left: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (3) {", "  sag()", "}", "eger (A >= 5) {", "  asagi()", "  asagi()", "  sag()", "}"],
      expectedMovesTrue: ["right", "right", "right", "down", "down", "right"],
      stepToLineTrue: [1, 1, 1, 4, 5, 6],
      expectedMovesFalse: ["right", "right", "right"],
      stepToLineFalse: [1, 1, 1],
      condition: { var: "A", op: ">=", value: 5 },
      goalTrue: [5, 3],
      goalFalse: [4, 1],
      targetCounters: { a: 4, b: 2, c: 0 },
      tip: "Kosul dogru dali ile rota tamamlanir."
    },
    {
      id: 24,
      name: "Orta 14",
      size: 8,
      start: [6, 6],
      goal: [6, 3],
      walls: [],
      xp: 58,
      aValue: 2,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { up: { b: 1 }, down: { b: -1 }, left: { a: 1 }, right: { a: -1 } },
      codeLines: ["tekrarla (4) {", "  yukari()", "}", "eger (A >= 4) {", "  sol()", "  sol()", "}", "asagi()"],
      expectedMovesTrue: ["up", "up", "up", "up", "left", "left", "down"],
      stepToLineTrue: [1, 1, 1, 1, 4, 5, 7],
      expectedMovesFalse: ["up", "up", "up", "up", "down"],
      stepToLineFalse: [1, 1, 1, 1, 7],
      condition: { var: "A", op: ">=", value: 4 },
      goalTrue: [4, 3],
      goalFalse: [6, 3],
      targetCounters: { a: 0, b: 3, c: 0 },
      tip: "Kosul yanlis dalinda hedefe ulasilir."
    },
    {
      id: 25,
      name: "Orta 15",
      size: 8,
      start: [0, 0],
      goal: [7, 5],
      walls: [],
      xp: 60,
      aValue: 7,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, down: { b: 1 }, left: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (7) {", "  sag()", "}", "tekrarla (5) {", "  asagi()", "}"],
      expectedMoves: ["right", "right", "right", "right", "right", "right", "right", "down", "down", "down", "down", "down"],
      stepToLine: [1, 1, 1, 1, 1, 1, 1, 4, 4, 4, 4, 4],
      targetCounters: { a: 7, b: 5, c: 0 },
      tip: "Uzun ama net bir rota."
    },
    {
      id: 26,
      name: "Orta 16",
      size: 8,
      start: [4, 7],
      goal: [1, 2],
      walls: [],
      xp: 62,
      aValue: 8,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (3) {", "  sol()", "}", "tekrarla (5) {", "  yukari()", "}"],
      expectedMoves: ["left", "left", "left", "up", "up", "up", "up", "up"],
      stepToLine: [1, 1, 1, 4, 4, 4, 4, 4],
      targetCounters: { a: 3, b: 5, c: 0 },
      tip: "Yukari hareket sayisini dikkatli takip et."
    },
    {
      id: 27,
      name: "Orta 17",
      size: 8,
      start: [2, 2],
      goal: [5, 3],
      walls: [],
      xp: 64,
      aValue: 9,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, down: { b: 1 }, left: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (2) {", "  asagi()", "}", "tekrarla (3) {", "  sag()", "}", "eger (A >= 8) {", "  yukari()", "}"],
      expectedMovesTrue: ["down", "down", "right", "right", "right", "up"],
      stepToLineTrue: [1, 1, 4, 4, 4, 7],
      expectedMovesFalse: ["down", "down", "right", "right", "right"],
      stepToLineFalse: [1, 1, 4, 4, 4],
      condition: { var: "A", op: ">=", value: 8 },
      goalTrue: [5, 3],
      goalFalse: [5, 4],
      targetCounters: { a: 3, b: 1, c: 0 },
      tip: "Kosul satiri son hamleyi belirler."
    },
    {
      id: 28,
      name: "Orta 18",
      size: 8,
      start: [7, 7],
      goal: [3, 1],
      walls: [],
      xp: 66,
      aValue: 6,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { left: { a: 1 }, up: { b: 1 }, right: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (4) {", "  sol()", "}", "tekrarla (6) {", "  yukari()", "}"],
      expectedMoves: ["left", "left", "left", "left", "up", "up", "up", "up", "up", "up"],
      stepToLine: [1, 1, 1, 1, 4, 4, 4, 4, 4, 4],
      targetCounters: { a: 4, b: 6, c: 0 },
      tip: "Uzun dikey cikis."
    },
    {
      id: 29,
      name: "Orta 19",
      size: 8,
      start: [3, 0],
      goal: [1, 3],
      walls: [],
      xp: 68,
      aValue: 1,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { down: { b: 1 }, left: { a: 1 }, right: { a: -1 }, up: { b: -1 } },
      codeLines: ["tekrarla (3) {", "  asagi()", "}", "eger (A >= 3) {", "  sag()", "  sag()", "}", "tekrarla (2) {", "  sol()", "}"],
      expectedMovesTrue: ["down", "down", "down", "right", "right", "left", "left"],
      stepToLineTrue: [1, 1, 1, 4, 5, 8, 8],
      expectedMovesFalse: ["down", "down", "down", "left", "left"],
      stepToLineFalse: [1, 1, 1, 8, 8],
      condition: { var: "A", op: ">=", value: 3 },
      goalTrue: [3, 3],
      goalFalse: [1, 3],
      targetCounters: { a: 2, b: 3, c: 0 },
      tip: "Kosul yanlisken sola gecilir."
    },
    {
      id: 30,
      name: "Orta 20",
      size: 8,
      start: [0, 6],
      goal: [6, 0],
      walls: [],
      xp: 70,
      aValue: 10,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, up: { b: 1 }, left: { a: -1 }, down: { b: -1 } },
      codeLines: ["tekrarla (6) {", "  sag()", "}", "tekrarla (6) {", "  yukari()", "}"],
      expectedMoves: ["right", "right", "right", "right", "right", "right", "up", "up", "up", "up", "up", "up"],
      stepToLine: [1, 1, 1, 1, 1, 1, 4, 4, 4, 4, 4, 4],
      targetCounters: { a: 6, b: 6, c: 0 },
      tip: "Orta zorluk serisinin son bolumu."
    }
  ];

  function buildComputePath(start, rightSteps, upSteps) {
    const moves = [];
    const path = new Set([`${start[0]},${start[1]}`]);
    let x = start[0];
    let y = start[1];
    for (let i = 0; i < rightSteps; i++) {
      x += 1;
      path.add(`${x},${y}`);
      moves.push("right");
    }
    for (let i = 0; i < upSteps; i++) {
      y -= 1;
      path.add(`${x},${y}`);
      moves.push("up");
    }
    return { moves, path, goal: [x, y] };
  }

  function buildComputeWalls(size, pathSet, start, goal, seed, wallTarget) {
    const walls = [];
    const reserved = new Set([
      `${start[0]},${start[1]}`,
      `${goal[0]},${goal[1]}`
    ]);
    pathSet.forEach((k) => reserved.add(k));
    for (let y = 1; y < size - 1; y++) {
      for (let x = 1; x < size - 1; x++) {
        const key = `${x},${y}`;
        if (reserved.has(key)) continue;
        const scoreA = (x + y + seed) % 3 === 0;
        const scoreB = ((x * 2) + y + seed) % 5 === 0;
        if (scoreA || scoreB) {
          walls.push([x, y]);
          if (walls.length >= wallTarget) return walls;
        }
      }
    }
    return walls;
  }

  function buildExtraComputeLevels() {
    const extra = [];
    for (let id = 31; id <= 60; id++) {
      const rel = id - 31;
      const band = rel < 10 ? "kolay" : rel < 20 ? "orta" : "zor";
      const size = band === "zor" ? 9 : 8;
      const rightSteps = band === "kolay" ? 3 + (rel % 2) : band === "orta" ? 4 + (rel % 2) : 5 + (rel % 2);
      const upSteps = band === "kolay" ? 2 + (rel % 2) : band === "orta" ? 3 + (rel % 2) : 4 + (rel % 2);
      const start = [1, size - 2];
      const built = buildComputePath(start, rightSteps, upSteps);
      const wallCount = band === "kolay" ? 10 : band === "orta" ? 14 : 18;
      const walls = buildComputeWalls(size, built.path, start, built.goal, id, wallCount);
      const stepToLine = [
        ...Array.from({ length: rightSteps }, () => 1),
        ...Array.from({ length: upSteps }, () => 4)
      ];
      extra.push({
        id,
        name: `Yeni ${band === "kolay" ? "Kolay" : band === "orta" ? "Orta" : "Zor"} ${rel + 1}`,
        size,
        start,
        goal: built.goal,
        walls,
        xp: band === "kolay" ? 72 + rel : band === "orta" ? 88 + rel : 108 + rel,
        aValue: 5 + (rel % 6),
        countersStart: { a: 0, b: 0, c: 0 },
        counterRules: { right: { a: 1 }, up: { b: 1 }, left: { a: -1 }, down: { b: -1 } },
        codeLines: [
          `tekrarla (${rightSteps}) {`,
          "  sag()",
          "}",
          `tekrarla (${upSteps}) {`,
          "  yukari()",
          "}"
        ],
        expectedMoves: built.moves,
        stepToLine,
        targetCounters: { a: rightSteps, b: upSteps, c: 0 },
        tip: `Yeni ${band} bolumu: engeller arasindan dogru rotayi takip et.`
      });
    }
    return extra;
  }

  defaultLevels.push(...buildExtraComputeLevels());

  let levels = defaultLevels.map((l) => ({ ...l }));
  let levelIndex = 0;
  let pos = [0, 0];
  let isDone = false;
  let startMs = Date.now();
  let completed = new Set();
  let counters = { a: 0, b: 0, c: 0 };
  let stepIndex = 0;
  let editMode = "add";
  let editIndex = -1;
  let activeProgram = { moves: [], stepToLine: [], goal: [0, 0], conditionResult: null };
  let levelRange = (!isStaff) ? { startIdx: 0, endIdx: 1 } : null; // {startIdx,endIdx}
  let assignmentRangeLocked = false;
  let showDoneTick = false;
  let trailByCell = new Map();
  let grantReady = isStaff;
  let grantDenied = false;
  function enforceAssignmentSlice() {
    if (isStaff || !hasInitialRange) return;
    const startIdx = Math.max(0, initialRangeStart - 1);
    const endIdx = Math.max(startIdx, initialRangeEndRaw - 1);
    levels = levels.slice(startIdx, endIdx + 1);
    levelRange = { startIdx: 0, endIdx: Math.max(0, levels.length - 1) };
  }
  enforceAssignmentSlice();

  function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }
  function toInt(v, d = 0) { const n = Number(v); return Number.isFinite(n) ? Math.floor(n) : d; }
  function parseXY(s, fallback) {
    const p = String(s || "").split(",").map((v) => Number(v.trim()));
    if (p.length < 2 || !Number.isFinite(p[0]) || !Number.isFinite(p[1])) return fallback;
    return [Math.floor(p[0]), Math.floor(p[1])];
  }
  function parseWalls(s) {
    return String(s || "").split(";").map((t) => t.trim()).filter(Boolean).map((xy) => parseXY(xy, null)).filter(Boolean);
  }
  function cloneCounters(obj) { return { a: toInt(obj?.a), b: toInt(obj?.b), c: toInt(obj?.c) }; }
  function wallSet(level) { return new Set((level.walls || []).map(([x, y]) => `${x},${y}`)); }
  function keyXY(x, y) { return `${x},${y}`; }
  function getComputeLevelXPByLevelNo(levelNo) {
    const n = Math.max(1, toInt(levelNo, 1));
    if (n <= 10) return 5;
    if (n <= 21) return 10;
    if (n <= 35) return 17;
    if (n <= 42) return 22;
    return 30;
  }
  function getLevelNo(level, fallbackIndex) {
    const n = Number(level?.id);
    if (Number.isFinite(n) && n > 0) return Math.floor(n);
    return Math.max(1, Math.floor(Number(fallbackIndex || 0) + 1));
  }
  function getRangeIndexes() {
    if (!isStaff && levelRange) {
      const startIdx = Math.max(0, Number(levelRange.startIdx || 0));
      const endIdx = Math.min(levels.length - 1, Math.max(startIdx, Number(levelRange.endIdx || (levels.length - 1))));
      return { startIdx, endIdx };
    }
    return { startIdx: 0, endIdx: Math.max(0, levels.length - 1) };
  }
  function getRangeCompletedLevelIds() {
    const { startIdx, endIdx } = getRangeIndexes();
    const inRangeIds = new Set(
      levels
        .slice(startIdx, endIdx + 1)
        .map((l, idx) => getLevelNo(l, startIdx + idx))
    );
    return Array.from(completed)
      .map((v) => Number(v))
      .filter((v) => Number.isFinite(v) && inRangeIds.has(v))
      .sort((a, b) => a - b);
  }
  function getRangeTotalXP() {
    return getRangeCompletedLevelIds().reduce((sum, levelNo) => sum + getComputeLevelXPByLevelNo(levelNo), 0);
  }

  function emitGameUpdate() {
    try {
      window.parent.postMessage({ type: "GAME_UPDATE", source: "compute-it", levels, currentLevelIndex: levelIndex }, "*");
    } catch (e) {}
  }

  function emitLevelCompleted(level) {
    if (role !== "student") return;
    try {
      const levelNo = getLevelNo(level, levelIndex);
      window.parent.postMessage({
        type: "LEVEL_COMPLETED",
        source: "compute-it",
        userId: uid || null,
        levelId: levelNo,
        xp: getComputeLevelXPByLevelNo(levelNo),
        duration: Math.max(0, Date.now() - startMs),
        levels,
        currentLevelIndex: levelIndex
      }, "*");
    } catch (e) {}
  }

  function renderCounters() {
    if (countAEl) countAEl.textContent = String(counters.a);
    if (countBEl) countBEl.textContent = String(counters.b);
    if (countCEl) countCEl.textContent = String(counters.c);
    if (countXpEl) countXpEl.textContent = String(getRangeTotalXP());
  }

  function evalCondition(level) {
    if (!level.condition) return null;
    const aVal = toInt(level.aValue, toInt(level.countersStart?.a, 0));
    const right = toInt(level.condition.value, 0);
    const op = String(level.condition.op || "==");
    if (op === ">=") return aVal >= right;
    if (op === "<=") return aVal <= right;
    if (op === ">") return aVal > right;
    if (op === "<") return aVal < right;
    return aVal === right;
  }

  function buildProgram(level) {
    const conditionResult = evalCondition(level);
    if (conditionResult === null) {
      return {
        moves: Array.isArray(level.expectedMoves) ? level.expectedMoves.slice() : [],
        stepToLine: Array.isArray(level.stepToLine) ? level.stepToLine.slice() : [],
        goal: Array.isArray(level.goal) ? level.goal.slice() : [0, 0],
        conditionResult: null
      };
    }
    return {
      moves: conditionResult ? (level.expectedMovesTrue || []) : (level.expectedMovesFalse || []),
      stepToLine: conditionResult ? (level.stepToLineTrue || []) : (level.stepToLineFalse || []),
      goal: conditionResult ? (level.goalTrue || level.goal || [0, 0]) : (level.goalFalse || level.goal || [0, 0]),
      conditionResult
    };
  }

  function getDisplayCode(level) {
    const base = Array.isArray(level.codeLines) ? level.codeLines.slice() : ["sag()", "asagi()", "sol()", "yukari()"];
    const wrapped = ["tekrarla (1) {", ...base.map((l) => `  ${l}`), "}"];
    if (wrapped.length < 10) {
      wrapped.push("tekrarla (1) {");
      wrapped.push("  // adim sirasini takip et");
      wrapped.push("}");
    }
    return wrapped;
  }

  function renderCode(activeStep = -1, badLine = -1) {
    const level = levels[levelIndex];
    const lines = getDisplayCode(level);
    const baseActive = activeStep >= 0 ? toInt(activeProgram.stepToLine?.[activeStep], -1) : -1;
    const activeLine = baseActive >= 0 ? baseActive + 1 : -1;
    const shiftedBadLine = badLine >= 0 ? badLine + 1 : -1;
    let indent = 0;
    cmdListEl.innerHTML = lines.map((rawLine, idx) => {
      const line = String(rawLine || "");
      const trimmed = line.trim();
      const startsClose = trimmed.startsWith("}");
      if (startsClose) indent = Math.max(0, indent - 1);
      const cls = idx === shiftedBadLine ? "bad" : (idx === activeLine ? "active" : "");
      const styledLine = line
        .replace(/\btekrarla\b/gi, '<span class="kw-red">tekrarla</span>')
        .replace(/\beger\b/gi, '<span class="kw-red">eger</span>');
      const html = `<div class="line i${Math.min(3, Math.max(0, indent))} ${cls}">${styledLine}</div>`;
      const opens = (trimmed.match(/\{/g) || []).length;
      const closes = (trimmed.match(/\}/g) || []).length;
      indent = Math.max(0, indent + opens - closes);
      return html;
    }).join("");
  }

  function renderBoard() {
    const level = levels[levelIndex];
    const sizePx = window.innerWidth <= 1024 ? 64 : 92;
    boardEl.style.gridTemplateColumns = `repeat(${level.size}, ${sizePx}px)`;
    boardEl.innerHTML = "";
    const walls = wallSet(level);
    const goal = activeProgram.goal || level.goal;
    for (let y = 0; y < level.size; y++) {
      for (let x = 0; x < level.size; x++) {
        const cell = document.createElement("div");
        cell.className = "cell";
        if (walls.has(`${x},${y}`)) cell.classList.add("wall");
        const trailKind = trailByCell.get(keyXY(x, y));
        if (trailKind === 1) cell.classList.add("trail-a");
        if (trailKind === 2) cell.classList.add("trail-b");
        if (trailKind === 3) cell.classList.add("trail-c");
        if (x === pos[0] && y === pos[1]) {
          const ball = document.createElement("div");
          ball.className = "ball";
          if (showDoneTick) ball.classList.add("done");
          cell.appendChild(ball);
        }
        boardEl.appendChild(cell);
      }
    }
  }

  function applyCounterRule(dir) {
    const level = levels[levelIndex];
    const rule = level.counterRules?.[dir] || {};
    counters.a += toInt(rule.a, 0);
    counters.b += toInt(rule.b, 0);
    counters.c += toInt(rule.c, 0);
    renderCounters();
  }

  function isCounterTargetMet() {
    const t = levels[levelIndex].targetCounters || {};
    return counters.a === toInt(t.a, counters.a) && counters.b === toInt(t.b, counters.b) && counters.c === toInt(t.c, counters.c);
  }

  function buildTrail(level, program) {
    const map = new Map();
    const walls = wallSet(level);
    let x = toInt(level.start?.[0], 0);
    let y = toInt(level.start?.[1], 0);
    const moves = Array.isArray(program?.moves) ? program.moves : [];
    for (let i = 0; i < moves.length; i++) {
      let dx = 0, dy = 0;
      const dir = moves[i];
      if (dir === "right") dx = 1;
      else if (dir === "left") dx = -1;
      else if (dir === "up") dy = -1;
      else if (dir === "down") dy = 1;
      const [nx, ny] = wrapMove(x + dx, y + dy, toInt(level.size, 1));
      if (walls.has(keyXY(nx, ny))) continue;
      x = nx;
      y = ny;
      if (!map.has(keyXY(x, y))) map.set(keyXY(x, y), (i % 3) + 1);
    }
    map.delete(keyXY(level.start?.[0], level.start?.[1]));
    return map;
  }

  function resetLevel(showHint = false) {
    const level = levels[levelIndex];
    pos = [...level.start];
    counters = cloneCounters(level.countersStart || { a: 0, b: 0, c: 0 });
    stepIndex = 0;
    isDone = false;
    showDoneTick = false;
    startMs = Date.now();
    activeProgram = buildProgram(level);
    trailByCell = buildTrail(level, activeProgram);
    btnNext.disabled = true;
    renderCounters();
    renderCode(stepIndex, -1);
    renderBoard();
    const hasCondition = !!level.condition;
    if (varsBoxEl) varsBoxEl.style.display = hasCondition ? "inline-flex" : "none";
    if (hasCondition && varAEl) varAEl.textContent = String(toInt(level.aValue, 0));
    if (tipEl) tipEl.textContent = "";
  }

  function updateTop() {
    const level = levels[levelIndex];
    let total = levels.length;
    let done = completed.size;
    if (role !== "teacher" && levelRange) {
      const startIdx = Math.max(0, Number(levelRange.startIdx || 0));
      const endIdx = Math.min(levels.length - 1, Math.max(startIdx, Number(levelRange.endIdx || (levels.length - 1))));
      total = Math.max(0, endIdx - startIdx + 1);
      const inRangeIds = new Set(levels.slice(startIdx, endIdx + 1).map((l) => Number(l?.id)).filter((v) => Number.isFinite(v)));
      done = Array.from(completed).filter((id) => inRangeIds.has(Number(id))).length;
    }
    totalNoEl.textContent = String(total);
    doneNoEl.textContent = String(done);
    levelNoEl.textContent = `${level.id} - ${level.name || "Seviye"}`;
    if (tipEl) tipEl.textContent = "";
  }

  function loadLevel(index) {
    if (!isStaff && assignmentRangeLocked) {
      try { window.parent.postMessage({ type: "LOCKED_LEVEL_WARNING", message: "Ödev aralýðý tamamlandý. Uygulama kapanýyor." }, "*"); } catch (e) {}
      return;
    }
    let minIdx = 0;
    let maxIdx = Math.max(0, levels.length - 1);
    if (!isStaff && levelRange) {
      minIdx = Math.max(0, Number(levelRange.startIdx || 0));
      maxIdx = Math.min(maxIdx, Math.max(minIdx, Number(levelRange.endIdx || maxIdx)));
    }
    levelIndex = clamp(index, minIdx, maxIdx);
    activeProgram = buildProgram(levels[levelIndex]);
    updateTop();
    resetLevel(false);
  }

  function wrapMove(x, y, size) {
    let nx = x;
    let ny = y;
    if (nx < 0) nx = size - 1;
    if (nx >= size) nx = 0;
    if (ny < 0) ny = size - 1;
    if (ny >= size) ny = 0;
    return [nx, ny];
  }

  function simulateLevel(level) {
    const prog = buildProgram(level);
    const walls = wallSet(level);
    let x = toInt(level.start?.[0], 0);
    let y = toInt(level.start?.[1], 0);
    let c = cloneCounters(level.countersStart || { a: 0, b: 0, c: 0 });
    const moves = Array.isArray(prog.moves) ? prog.moves : [];
    for (let i = 0; i < moves.length; i++) {
      const dir = moves[i];
      let dx = 0, dy = 0;
      if (dir === "right") dx = 1;
      else if (dir === "left") dx = -1;
      else if (dir === "up") dy = -1;
      else if (dir === "down") dy = 1;
      const [nx, ny] = wrapMove(x + dx, y + dy, toInt(level.size, 1));
      if (walls.has(keyXY(nx, ny))) return false;
      x = nx;
      y = ny;
      const r = level.counterRules?.[dir] || {};
      c.a += toInt(r.a, 0);
      c.b += toInt(r.b, 0);
      c.c += toInt(r.c, 0);
    }
    const goal = Array.isArray(prog.goal) ? prog.goal : [0, 0];
    const tc = level.targetCounters || {};
    const goalOk = x === toInt(goal[0], x) && y === toInt(goal[1], y);
    const counterOk =
      c.a === toInt(tc.a, c.a) &&
      c.b === toInt(tc.b, c.b) &&
      c.c === toInt(tc.c, c.c);
    return goalOk && counterOk;
  }

  function animateNextLevel() {
    boardEl.classList.add("advance");
    setTimeout(() => {
      boardEl.classList.remove("advance");
      const endIdx = !isStaff && levelRange
        ? Math.min(levels.length - 1, Math.max(0, Number(levelRange.endIdx || (levels.length - 1))))
        : (levels.length - 1);
      if (levelIndex < endIdx) loadLevel(levelIndex + 1);
    }, 520);
  }

  function animateSuccessThenNext() {
    showDoneTick = true;
    renderBoard();
    setTimeout(() => {
      showDoneTick = false;
      const endIdx = !isStaff && levelRange
        ? Math.min(levels.length - 1, Math.max(0, Number(levelRange.endIdx || (levels.length - 1))))
        : (levels.length - 1);
      if (levelIndex < endIdx) animateNextLevel();
    }, 520);
  }

  function animateResetLevel() {
    boardEl.classList.add("reset-flash");
    setTimeout(() => {
      boardEl.classList.remove("reset-flash");
      resetLevel(true);
    }, 420);
  }

  function move(dir) {
    if (!grantReady || grantDenied) return;
    if (isDone) return;
    const level = levels[levelIndex];
    const expected = activeProgram.moves?.[stepIndex];
    const wrongLine = toInt(activeProgram.stepToLine?.[stepIndex], -1);
    if (expected && dir !== expected) {
      renderCode(stepIndex, wrongLine);
      animateResetLevel();
      return;
    }

    let dx = 0, dy = 0;
    if (dir === "right") dx = 1;
    else if (dir === "left") dx = -1;
    else if (dir === "up") dy = -1;
    else if (dir === "down") dy = 1;

    const wrapped = wrapMove(pos[0] + dx, pos[1] + dy, level.size);
    const nx = wrapped[0];
    const ny = wrapped[1];

    if (wallSet(level).has(`${nx},${ny}`)) {
      return;
    }

    pos = [nx, ny];
    stepIndex += 1;
    applyCounterRule(dir);
    renderCode(stepIndex, -1);
    renderBoard();

    const programDone = stepIndex >= (activeProgram.moves?.length || 0);
    const goal = activeProgram.goal || level.goal;
    const reachedGoal = nx === goal[0] && ny === goal[1];
    if (programDone && reachedGoal && isCounterTargetMet()) {
      isDone = true;
      const endIdx = !isStaff && levelRange
        ? Math.min(levels.length - 1, Math.max(0, Number(levelRange.endIdx || (levels.length - 1))))
        : (levels.length - 1);
      btnNext.disabled = levelIndex >= endIdx;
      if (!completed.has(level.id)) {
        completed.add(level.id);
        doneNoEl.textContent = String(completed.size);
      }
      // Assignment progress in parent must be updated even if this level
      // had been completed in a previous run/session.
      emitLevelCompleted(level);
      if (levelIndex < endIdx) {
        animateSuccessThenNext();
      } else {
        showDoneTick = true;
        renderBoard();
        if (!isStaff && levelRange) {
          try {
            window.parent.postMessage({
              type: "ASSIGNMENT_RANGE_COMPLETED",
              source: "compute-it",
              currentLevelIndex: levelIndex,
              levels,
              xp: getRangeTotalXP(),
              completedLevelIds: getRangeCompletedLevelIds()
            }, "*");
          } catch (e) {}
        }
      }
    }
  }

  function openDesigner(mode) {
    if (!isStaff) return;
    editMode = mode;
    editIndex = levelIndex;
    if (mode === "edit") {
      const lv = levels[levelIndex];
      designerTitle.textContent = "Seviye Duzenle";
      lvName.value = lv.name || "";
      lvSize.value = String(lv.size || 3);
      lvStart.value = `${lv.start[0]},${lv.start[1]}`;
      lvGoal.value = `${(lv.goal || [0,0])[0]},${(lv.goal || [0,0])[1]}`;
      lvWalls.value = (lv.walls || []).map((w) => `${w[0]},${w[1]}`).join(";");
      lvXp.value = String(lv.xp || 10);
    } else {
      designerTitle.textContent = "Seviye Ekle";
      lvName.value = "";
      lvSize.value = "3";
      lvStart.value = "0,0";
      lvGoal.value = "2,2";
      lvWalls.value = "";
      lvXp.value = "10";
    }
    designerModal.classList.remove("hidden");
  }

  function closeDesigner() { designerModal.classList.add("hidden"); }

  function saveDesigner() {
    const size = clamp(toInt(lvSize.value, 3), 2, 8);
    const start = parseXY(lvStart.value, [0, 0]).map((v) => clamp(v, 0, size - 1));
    const goal = parseXY(lvGoal.value, [size - 1, size - 1]).map((v) => clamp(v, 0, size - 1));
    const walls = parseWalls(lvWalls.value).map(([x, y]) => [clamp(x, 0, size - 1), clamp(y, 0, size - 1)]);
    const base = {
      id: editMode === "edit" ? levels[editIndex].id : (Math.max(0, ...levels.map((l) => toInt(l.id))) + 1),
      name: (lvName.value || "").trim() || `Compute ${levels.length + 1}`,
      size,
      start,
      goal,
      walls,
      xp: clamp(toInt(lvXp.value, 10), 1, 200),
      aValue: 1,
      countersStart: { a: 0, b: 0, c: 0 },
      counterRules: { right: { a: 1 }, down: { b: 1 }, left: { a: -1 }, up: { b: -1 } },
      codeLines: ["sag()", "sag()", "asagi()"],
      expectedMoves: ["right", "right", "down"],
      stepToLine: [0, 1, 2],
      targetCounters: { a: 2, b: 1, c: 0 },
      tip: "Ogretmen seviyesi"
    };
    if (editMode === "edit" && levels[editIndex]) levels[editIndex] = { ...levels[editIndex], ...base };
    else levels.push(base);
    emitGameUpdate();
    closeDesigner();
    loadLevel(editMode === "edit" ? editIndex : levels.length - 1);
  }

  function openDelete() {
    if (!isStaff) return;
    const lv = levels[levelIndex];
    deleteText.textContent = `"${lv.name || "Seviye"}" silinsin mi?`;
    deleteModal.classList.remove("hidden");
  }
  function closeDelete() { deleteModal.classList.add("hidden"); }
  function doDelete() {
    if (levels.length <= 1) return closeDelete();
    const removed = levels[levelIndex];
    levels.splice(levelIndex, 1);
    completed.delete(removed.id);
    emitGameUpdate();
    closeDelete();
    loadLevel(Math.max(0, levelIndex - 1));
  }

  window.addEventListener("message", (e) => {
    const data = e && e.data;
    if (!data || typeof data !== "object") return;
    if (data.type === "LOAD_STATE" && Array.isArray(data.levels) && data.levels.length) {
      const incoming = data.levels.map((l) => ({ ...l }));
      const defaultsById = new Map(defaultLevels.map((d) => [Number(d.id), { ...d }]));
      const byId = new Map();
      incoming.forEach((l) => {
        const id = Number(l.id);
        if (!Number.isFinite(id)) return;
        const fallback = defaultsById.get(id) || null;
        const candidate = { ...l };
        byId.set(id, simulateLevel(candidate) ? candidate : (fallback || candidate));
      });
      defaultLevels.forEach((d) => {
        const id = Number(d.id);
        if (!byId.has(id)) byId.set(id, { ...d });
      });
      levels = Array.from(byId.values()).sort((a, b) => Number(a.id || 0) - Number(b.id || 0));
      enforceAssignmentSlice();
      completed = new Set(levels.filter((l) => !!l.completed).map((l) => Number(l.id)));
      loadLevel(toInt(data.currentLevelIndex, 0));
      return;
    }
    if (data.type === "SET_LEVEL_RANGE") {
      assignmentRangeLocked = false;
      const start = Math.max(1, Number(data.levelStart || 1));
      const end = Math.max(start, Number(data.levelEnd || start));
      levelRange = { startIdx: start - 1, endIdx: end - 1 };
      if (!isStaff) loadLevel(start - 1);
      return;
    }
    if (data.type === "FORCE_ASSIGNMENT_LOCK") {
      assignmentRangeLocked = true;
      return;
    }
    if (data.type === "SET_ASSIGNMENT_PROGRESS") {
      const ids = Array.isArray(data.completedLevelIds)
        ? data.completedLevelIds.map((v) => Number(v)).filter((v) => Number.isFinite(v))
        : [];
      completed = new Set(ids);
      renderCounters();
      return;
    }
    if (!isStaff) return;
    if (data.type === "OPEN_DESIGNER") openDesigner("add");
    if (data.type === "OPEN_EDIT_LEVEL") openDesigner("edit");
    if (data.type === "OPEN_DELETE_LEVEL") openDelete();
  });

  function triggerMoveFromInput(dir) {
    if (!grantReady || grantDenied) return;
    if (dir === "right" || dir === "left" || dir === "up" || dir === "down") move(dir);
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") triggerMoveFromInput("right");
    else if (e.key === "ArrowLeft") triggerMoveFromInput("left");
    else if (e.key === "ArrowUp") triggerMoveFromInput("up");
    else if (e.key === "ArrowDown") triggerMoveFromInput("down");
  });

  let swipeStartX = 0;
  let swipeStartY = 0;
  let hasSwipeStart = false;
  const SWIPE_MIN_DISTANCE = 24;

  if (boardEl) {
    boardEl.style.touchAction = "none";
    boardEl.addEventListener("touchstart", (e) => {
      const t = e.touches && e.touches[0];
      if (!t) return;
      swipeStartX = t.clientX;
      swipeStartY = t.clientY;
      hasSwipeStart = true;
    }, { passive: true });

    boardEl.addEventListener("touchcancel", () => {
      hasSwipeStart = false;
    }, { passive: true });

    boardEl.addEventListener("touchend", (e) => {
      if (!hasSwipeStart) return;
      hasSwipeStart = false;
      const t = e.changedTouches && e.changedTouches[0];
      if (!t) return;
      const dx = t.clientX - swipeStartX;
      const dy = t.clientY - swipeStartY;
      const absX = Math.abs(dx);
      const absY = Math.abs(dy);
      if (Math.max(absX, absY) < SWIPE_MIN_DISTANCE) return;
      if (e.cancelable) e.preventDefault();
      if (absX >= absY) triggerMoveFromInput(dx >= 0 ? "right" : "left");
      else triggerMoveFromInput(dy >= 0 ? "down" : "up");
    }, { passive: false });
  }

  btnReset.addEventListener("click", () => {
    if (!grantReady || grantDenied) return;
    loadLevel(levelIndex);
  });
  if (btnNext) {
    btnNext.style.display = "none";
    btnNext.disabled = true;
  }
  btnSaveLevel.addEventListener("click", saveDesigner);
  btnCancelLevel.addEventListener("click", closeDesigner);
  btnConfirmDel.addEventListener("click", doDelete);
  btnCancelDel.addEventListener("click", closeDelete);
  designerModal.addEventListener("click", (e) => { if (e.target === designerModal || e.target.classList.contains("modal-backdrop")) closeDesigner(); });
  deleteModal.addEventListener("click", (e) => { if (e.target === deleteModal || e.target.classList.contains("modal-backdrop")) closeDelete(); });

  function denyRunnerAccess(message) {
    grantDenied = true;
    grantReady = false;
    if (tipEl) {
      tipEl.style.display = "block";
      tipEl.textContent = message || "Bu icerige erisim izniniz yok.";
    }
    if (btnReset) btnReset.disabled = true;
    if (btnNext) btnNext.disabled = true;
    if (boardEl) boardEl.style.opacity = "0.45";
  }

  async function applySessionGrant() {
    if (!needsGrantCheck || isStaff) {
      grantReady = true;
      return;
    }
    try {
      const grantUrl = new URL("../runner-grant/compute-it-runner", window.location.href).toString();
      const res = await fetch(grantUrl, {
        method: "GET",
        credentials: "same-origin",
        headers: { "Accept": "application/json" }
      });
      if (!res.ok) {
        denyRunnerAccess("Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.");
        return;
      }
      const data = await res.json();
      const start = Math.max(1, Number(data?.from || initialRangeStart || 1));
      const end = Math.max(start, Number(data?.to || initialRangeEndRaw || start));
      levelRange = { startIdx: start - 1, endIdx: end - 1 };
      enforceAssignmentSlice();
      grantReady = true;
    } catch (e) {
      denyRunnerAccess("Erisim dogrulanamadi. Sayfayi yenileyip tekrar deneyin.");
    }
  }

  (async () => {
    await applySessionGrant();
    if (!grantReady || grantDenied) return;
    const initialIndex = !isStaff && levelRange
      ? Math.max(0, Number(levelRange.startIdx || 0))
      : 0;
    loadLevel(initialIndex);
  })();
})();


