// Simple Block Grid Runner
// - Provides Blockly palette and custom blocks
// - Translates workspace into a command list
// - Steps a character on a grid with obstacles and a star

let workspace;
const canvas = document.getElementById('gridCanvas');
const ctx = canvas.getContext('2d');
const gridSize = 8; // cells per side
const EASY_LEVEL_XP = 5;
const MEDIUM_LEVEL_XP = 12;
const HARD_LEVEL_XP = 21;
const DEFAULT_LEVEL_XP = MEDIUM_LEVEL_XP;
const MAX_LEVEL_XP = 500;

function getCurrentLevelGridWidth(){
  const lvl = levels?.[state?.levelIndex ?? 0] || {};
  return Math.max(6, Math.min(12, Number(lvl.gridWidth || gridSize)));
}

function getCurrentLevelGridHeight(){
  const lvl = levels?.[state?.levelIndex ?? 0] || {};
  return Math.max(6, Math.min(12, Number(lvl.gridHeight || gridSize)));
}

function getCellPx(){
  const w = getCurrentLevelGridWidth();
  const h = getCurrentLevelGridHeight();
  return canvas.width / Math.max(w, h);
}

let levels = [
  {
    id:0, name:'Seviye 1', group: 'Kolay', start:{x:1,y:7,dir:0}, star:{x:4,y:1}, obstacles:[{x:1,y:6},{x:2,y:5},{x:3,y:3}]
  },
  {
    id:1, name:'Seviye 2', group: 'Orta', start:{x:0,y:7,dir:0}, star:{x:5,y:1}, obstacles:[{x:1,y:6},{x:2,y:5},{x:3,y:4},{x:4,y:3}]
  }
];

function buildBlockPath(start, star){
  const path = new Set([`${start.x},${start.y}`]);
  const moves = [];
  let x = start.x;
  let y = start.y;
  const stepX = star.x >= x ? 1 : -1;
  while(x !== star.x){
    x += stepX;
    path.add(`${x},${y}`);
    moves.push({ x, y });
  }
  const stepY = star.y >= y ? 1 : -1;
  while(y !== star.y){
    y += stepY;
    path.add(`${x},${y}`);
    moves.push({ x, y });
  }
  return { path, moves };
}

function buildBlockObstacles(pathSet, start, star, seed, targetCount){
  const obstacles = [];
  const reserved = new Set([`${start.x},${start.y}`, `${star.x},${star.y}`]);
  pathSet.forEach((k) => reserved.add(k));
  for(let y = 0; y < gridSize; y++){
    for(let x = 0; x < gridSize; x++){
      const key = `${x},${y}`;
      if(reserved.has(key)) continue;
      const scoreA = ((x + y + seed) % 3) === 0;
      const scoreB = (((x * 2) + y + seed) % 5) === 0;
      if(scoreA || scoreB){
        obstacles.push({ x, y });
        if(obstacles.length >= targetCount) return obstacles;
      }
    }
  }
  return obstacles;
}

function buildExtraBlockLevels(){
  const out = [];
  let id = levels.reduce((m, lv) => Math.max(m, Number(lv?.id || 0)), 0) + 1;
  const bands = [
    { group: 'Kolay', count: 10, obstacleCount: 8, starYBase: 3 },
    { group: 'Orta', count: 10, obstacleCount: 12, starYBase: 2 },
    { group: 'Zor', count: 10, obstacleCount: 16, starYBase: 1 }
  ];
  bands.forEach((band) => {
    for(let i = 0; i < band.count; i++){
      const start = { x: 0, y: 7, dir: 0 };
      const star = { x: 4 + (i % 4), y: Math.max(0, band.starYBase - (i % 2)) };
      const pathData = buildBlockPath(start, star);
      const obstacles = buildBlockObstacles(pathData.path, start, star, id, band.obstacleCount + (i % 3));
      out.push({
        id,
        order: id + 1,
        name: `${band.group} Yeni ${i + 1}`,
        group: band.group,
        start,
        star,
        obstacles
      });
      id += 1;
    }
  });
  return out;
}

levels = levels.concat(buildExtraBlockLevels());

function getDefaultXpByGroup(group){
  const g = String(group || '').trim().toLowerCase();
  if(g === 'kolay' || g === 'easy') return EASY_LEVEL_XP;
  if(g === 'orta' || g === 'medium') return MEDIUM_LEVEL_XP;
  if(g === 'zor' || g === 'hard') return HARD_LEVEL_XP;
  return DEFAULT_LEVEL_XP;
}

function getDefaultXpByLevelNo(levelNo){
  const n = Math.max(1, Number(levelNo || 1));
  if(n <= 5) return EASY_LEVEL_XP;
  if(n <= 11) return MEDIUM_LEVEL_XP;
  return HARD_LEVEL_XP;
}

function normalizeLevelXp(value){
  const n = Number(value);
  if(!Number.isFinite(n)) return DEFAULT_LEVEL_XP;
  return Math.max(1, Math.min(MAX_LEVEL_XP, Math.floor(n)));
}

function applyXpDefaults(){
  levels = (levels || []).map((lvl, idx) => {
    const levelNo = Number.isFinite(Number(lvl?.order)) ? Number(lvl.order) : (idx + 1);
    const xp = getDefaultXpByLevelNo(levelNo);
    return { ...lvl, xp: normalizeLevelXp(xp) };
  });
}

applyXpDefaults();

// Preferred group ordering (easy -> hard)
const groupOrder = ['Kolay','Orta','Zor','Özel','Genel'];

function getGroupRank(name){
  const idx = groupOrder.indexOf(String(name || 'Genel'));
  return idx >= 0 ? idx : 999;
}

function sortLevelsInPlace(){
  levels.sort((a, b) => {
    const ga = getGroupRank(a?.group);
    const gb = getGroupRank(b?.group);
    if (ga !== gb) return ga - gb;
    const oa = Number.isFinite(Number(a?.order)) ? Number(a.order) : Number.MAX_SAFE_INTEGER;
    const ob = Number.isFinite(Number(b?.order)) ? Number(b.order) : Number.MAX_SAFE_INTEGER;
    if (oa !== ob) return oa - ob;
    return Number(a?.id || 0) - Number(b?.id || 0);
  });
}

let state = {
  levelIndex:0,
  x:0,y:0,dir:0, // 0 right, 1 down, 2 left, 3 up
  commands:[], ip:0, running:false
};
let sessionStart = null;
let levelRange = null; // {startIdx,endIdx} 0-based
let assignmentRangeLocked = false;
let grantDenied = false;
let assignmentCompletedLevelIds = null; // Set<number> for active assignment
let levelAttemptStart = null;
const runnerRole = (() => {
  try {
    const params = new URLSearchParams(window.location.search);
    const role = (params.get("role") || "").toLowerCase();
    return (role === "teacher" || role === "admin") ? role : "student";
  } catch (e) {
    return "student";
  }
})();
const hasQueryAssignmentRange = !!getQueryAssignmentRange();
const assignmentId = (() => {
  try {
    const params = new URLSearchParams(window.location.search);
    return String(params.get("assignmentId") || "").trim();
  } catch (e) {
    return "";
  }
})();
const enforceGrant = (() => {
  try {
    const params = new URLSearchParams(window.location.search);
    return params.get("grant") === "1" || params.get("enforceGrant") === "1";
  } catch (e) {
    return false;
  }
})();
const needsGrantCheck = runnerRole === "student" && (hasQueryAssignmentRange || !!assignmentId || enforceGrant);
const isStaffMode = () => runnerRole === "teacher" || runnerRole === "admin" || window.__runnerStaffMode === true;
const isAdminMode = () => runnerRole === "admin" || window.__runnerAdminMode === true;

function getQueryAssignmentRange(){
  try{
    const params = new URLSearchParams(window.location.search);
    const start = Math.max(1, Number(params.get("levelStart") || params.get("from") || 0));
    const end = Math.max(start, Number(params.get("levelEnd") || params.get("to") || start));
    if(Number.isFinite(start) && Number.isFinite(end) && start > 0){
      return { startIdx: start - 1, endIdx: end - 1 };
    }
  }catch(e){}
  return null;
}
(() => {
  try {
    const params = new URLSearchParams(window.location.search);
    const start = Math.max(1, Number(params.get("levelStart") || params.get("from") || 0));
    const end = Math.max(start, Number(params.get("levelEnd") || params.get("to") || start));
    if (!isStaffMode() && Number.isFinite(start) && start > 0) {
      levelRange = { startIdx: start - 1, endIdx: end - 1 };
      assignmentRangeLocked = false;
    }
  } catch (e) {
    // ignore malformed query
  }
})();

async function resolveAssignmentRangeFromGrant(){
  if(!needsGrantCheck) return;
  if(isStaffMode()) return;
  try{
    const base = (window.RUNNER_APP_BASE || '').replace(/\/$/, '');
    const res = await fetch(base + '/runner-grant/block-grid-runner', {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' }
    });
    if(!res.ok){
      grantDenied = true;
      setStatus('Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.');
      return;
    }
    const grant = await res.json();
    if(grant && grant.ok && grant.role === 'staff'){
      window.__runnerStaffMode = true;
      grantDenied = false;
      levelRange = null;
      assignmentRangeLocked = false;
      return;
    }
    if(!grant || !grant.ok || grant.role !== 'student'){
      grantDenied = true;
      setStatus('Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.');
      return;
    }
    const start = Math.max(1, Number(grant.from || 1));
    const end = Math.max(start, Number(grant.to || start));
    assignmentRangeLocked = false;
    levelRange = { startIdx: start - 1, endIdx: end - 1 };
    try{ populateLevels(); }catch(e){}
    try{
      const target = Math.max(levelRange.startIdx, Math.min(levelRange.endIdx, Number(state.levelIndex || 0)));
      loadLevel(target);
      setStatus('Seviye araligi: ' + start + '-' + end);
    }catch(e){}
  }catch(e){
    grantDenied = true;
    setStatus('Erisim dogrulanamadi. Sayfayi yenileyip tekrar deneyin.');
  }
}

let designerEditMode = false;
let designerDraft = null;
let designerSelectedTool = 'obstacle';

function clampDesigner(v, min, max){
  const n = Number(v);
  if(!Number.isFinite(n)) return min;
  return Math.max(min, Math.min(max, Math.floor(n)));
}

function parseDesignerSize(){
  const wEl = document.getElementById('designerGridWidth');
  const hEl = document.getElementById('designerGridHeight');
  return {
    w: clampDesigner((wEl||{}).value || 8, 6, 12),
    h: clampDesigner((hEl||{}).value || 8, 6, 12)
  };
}

function makeEmptyDesignerDraft(){
  const s = parseDesignerSize();
  return {
    w: s.w,
    h: s.h,
    start: { x: 0, y: s.h - 1, dir: 0 },
    star: { x: s.w - 1, y: 0 },
    obstacles: []
  };
}

function draftHasObstacle(x, y){
  return !!designerDraft?.obstacles?.some((o)=> Number(o.x) === Number(x) && Number(o.y) === Number(y));
}

function draftRemoveObstacle(x, y){
  if(!designerDraft) return;
  designerDraft.obstacles = (designerDraft.obstacles || []).filter((o)=> !(Number(o.x) === Number(x) && Number(o.y) === Number(y)));
}

function setDesignerTool(tool){
  designerSelectedTool = String(tool || 'obstacle');
  const items = document.querySelectorAll('#designerPalette .palette-tile');
  items.forEach((it)=> it.classList.toggle('active', it.dataset.item === designerSelectedTool));
}

function designerCellLabel(x, y){
  if(!designerDraft) return '';
  if(Number(designerDraft.start.x) === Number(x) && Number(designerDraft.start.y) === Number(y)) return 'S';
  if(Number(designerDraft.star.x) === Number(x) && Number(designerDraft.star.y) === Number(y)) return 'H';
  if(draftHasObstacle(x,y)) return 'E';
  return '';
}

function placeDesignerItem(x, y, item){
  if(!designerDraft) return;
  const cx = clampDesigner(x, 0, designerDraft.w - 1);
  const cy = clampDesigner(y, 0, designerDraft.h - 1);
  const kind = String(item || designerSelectedTool || 'obstacle');
  if(kind === 'erase'){
    draftRemoveObstacle(cx, cy);
    return;
  }
  if(kind === 'start'){
    draftRemoveObstacle(cx, cy);
    designerDraft.start.x = cx; designerDraft.start.y = cy;
    if(Number(designerDraft.star.x) === cx && Number(designerDraft.star.y) === cy){
      designerDraft.star.x = designerDraft.w - 1; designerDraft.star.y = 0;
    }
    return;
  }
  if(kind === 'star'){
    draftRemoveObstacle(cx, cy);
    designerDraft.star.x = cx; designerDraft.star.y = cy;
    if(Number(designerDraft.start.x) === cx && Number(designerDraft.start.y) === cy){
      designerDraft.start.x = 0; designerDraft.start.y = designerDraft.h - 1;
    }
    return;
  }
  if(kind === 'obstacle'){
    if((Number(designerDraft.start.x) === cx && Number(designerDraft.start.y) === cy) ||
       (Number(designerDraft.star.x) === cx && Number(designerDraft.star.y) === cy)) return;
    if(!draftHasObstacle(cx, cy)) designerDraft.obstacles.push({ x: cx, y: cy });
  }
}

function renderDesignerBoard(){
  const board = document.getElementById('designerBoard');
  if(!board || !designerDraft) return;
  board.innerHTML = '';
  board.style.gridTemplateColumns = `repeat(${designerDraft.w}, 36px)`;
  board.style.gridTemplateRows = `repeat(${designerDraft.h}, 36px)`;
  for(let y=0; y<designerDraft.h; y++){
    for(let x=0; x<designerDraft.w; x++){
      const cell = document.createElement('div');
      cell.className = 'designer-cell';
      if(Number(designerDraft.start.x) === x && Number(designerDraft.start.y) === y) cell.classList.add('start');
      else if(Number(designerDraft.star.x) === x && Number(designerDraft.star.y) === y) cell.classList.add('star');
      else if(draftHasObstacle(x, y)) cell.classList.add('obstacle');
      cell.dataset.x = String(x);
      cell.dataset.y = String(y);
      cell.textContent = designerCellLabel(x,y);
      cell.addEventListener('click', ()=>{ placeDesignerItem(x, y, designerSelectedTool); renderDesignerBoard(); });
      cell.addEventListener('dragover', (ev)=>{ ev.preventDefault(); cell.classList.add('drag-over'); });
      cell.addEventListener('dragleave', ()=> cell.classList.remove('drag-over'));
      cell.addEventListener('drop', (ev)=>{
        ev.preventDefault();
        cell.classList.remove('drag-over');
        const t = (ev.dataTransfer && ev.dataTransfer.getData('text/plain')) || designerSelectedTool;
        placeDesignerItem(x, y, t);
        renderDesignerBoard();
      });
      board.appendChild(cell);
    }
  }
}

function initBlockly(){
  if(!window.Blockly || typeof Blockly.inject !== 'function'){
    const statusEl = document.getElementById('status');
    if(statusEl) statusEl.textContent = 'Blok editoru yukleniyor...';
    return false;
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
      type: "repeat_times",
      message0: "repeat %1 times",
      args0: [
        { type: "field_number", name: "TIMES", value: 2, min: 0, max: 50, precision: 1 }
      ],
      message1: "do %1",
      args1: [
        { type: "input_statement", name: "DO" }
      ],
      previousStatement: null,
      nextStatement: null,
      colour: 95,
      tooltip: "Belirtilen sayı kadar tekrar et"
    },
    {
      type: "move_forward",
      message0: "Ileri git",
      previousStatement: null,
      nextStatement: null,
      colour: 188,
      tooltip: "Karakteri ileri götür"
    },
    {
      type: "turn_left",
      message0: "Sola dön",
      previousStatement: null,
      nextStatement: null,
      colour: 268,
      tooltip: "Karakteri sola çevir"
    },
    {
      type: "turn_right",
      message0: "Sağa dön",
      previousStatement: null,
      nextStatement: null,
      colour: 268,
      tooltip: "Karakteri sağa çevir"
    }
  ]);

  const toolbox = `
    <xml xmlns="https://developers.google.com/blockly/xml">
      <block type="program_start"></block>
      <block type="repeat_times"></block>
      <block type="move_forward"></block>
      <block type="turn_left"></block>
      <block type="turn_right"></block>
    </xml>
  `;

  workspace = Blockly.inject('blocklyDiv', {
    toolbox,
    trashcan: true,
    grid: { spacing: 20, length: 3, colour: "#cbd5e1", snap: true },
    move: { scrollbars: true, drag: true, wheel: true }
  });

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
    try {
      const startBlock = workspace.newBlock("program_start");
      startBlock.initSvg?.();
      startBlock.render?.();
      startBlock.moveBy?.(20, 20);
    } catch {}
  }

  // JavaScript generators for custom blocks so Blockly produces runnable code
  Blockly.JavaScript['program_start'] = function(block){
    const body = Blockly.JavaScript.statementToCode(block, 'DO');
    return body || '';
  };
  Blockly.JavaScript['move_forward'] = function(){
    return '  await moveForward();\n';
  };
  Blockly.JavaScript['turn_left'] = function(){
    return '  await turnLeft();\n';
  };
  Blockly.JavaScript['turn_right'] = function(){
    return '  await turnRight();\n';
  };

  // When workspace changes, save the XML to the current level so blocks persist per-level
  try{
    workspace.addChangeListener(function(){
      try{
        if(!workspace) return;
        const idx = state.levelIndex;
        if(typeof idx !== 'number' || !levels[idx]) return;
        const xml = Blockly.Xml.domToText(Blockly.Xml.workspaceToDom(workspace));
        levels[idx].solutionXml = xml;
        saveLevelsToStorage();
      }catch(e){ /* ignore */ }
    });
  }catch(e){ /* ignore */ }
  return true;
}
function generateCommands(){
  // Returns array of {cmd: 'forward'|'left'|'right', blockId}
  function collectFromBlock(block){
    const out = [];
    let b = block;
    while(b){
      const t = b.type;
      if(t === 'move_forward') out.push({cmd:'forward', blockId:b.id});
      else if(t === 'turn_left') out.push({cmd:'left', blockId:b.id});
      else if(t === 'turn_right') out.push({cmd:'right', blockId:b.id});
      else if(t === 'program_start'){
        const inner = b.getInputTargetBlock && b.getInputTargetBlock('DO');
        if(inner){
          const innerCmds = collectFromBlock(inner);
          out.push(...innerCmds);
        }
      }
      else if(t === 'repeat_times'){
        let times = parseInt(b.getFieldValue && b.getFieldValue('TIMES'), 10);
        if(!Number.isFinite(times)) times = 0;
        times = Math.max(0, times);
        const inner = b.getInputTargetBlock && b.getInputTargetBlock('DO');
        if(inner){
          const innerCmds = collectFromBlock(inner);
          for(let i=0;i<times;i++) out.push(...innerCmds.map(c=>({cmd:c.cmd, blockId:b.id})));
        }
      }
      else if(t === 'controls_repeat_ext' || t === 'controls_repeat'){
        let times = 0;
        try{
          if(typeof b.getFieldValue === 'function'){
            const fv = b.getFieldValue('TIMES');
            if(fv !== null && fv !== undefined && fv !== '') times = parseInt(fv) || 0;
          }
        }catch(e){ /* ignore */ }
        if(!times){
          const timesBlock = b.getInputTargetBlock && b.getInputTargetBlock('TIMES');
          if(timesBlock && typeof timesBlock.getFieldValue === 'function'){
            const num = timesBlock.getFieldValue('NUM');
            if(num !== null && num !== undefined && num !== '') times = parseInt(num) || 0;
          }
        }
        const inner = b.getInputTargetBlock && b.getInputTargetBlock('DO');
        if(inner){
          const innerCmds = collectFromBlock(inner);
          for(let i=0;i<times;i++) out.push(...innerCmds.map(c=>({cmd:c.cmd, blockId:b.id})));
        }
      }
      b = b.getNextBlock && b.getNextBlock();
    }
    return out;
  }

  const cmds = [];
  const top = workspace.getTopBlocks(true);
  for(const t of top){
    cmds.push(...collectFromBlock(t));
  }
  return cmds;
}

function loadLevel(idx){
  if(!isStaffMode() && grantDenied){
    warnLockedLevel('Bu oyuna sadece atanmis odev araligindan erisebilirsiniz.');
    return;
  }
  if(!isStaffMode() && assignmentRangeLocked){
    warnLockedLevel('Ödev aralığı tamamlandı. Uygulama kapanıyor.');
    return;
  }
  if(!isStaffMode() && levelRange){
    const startIdx = Math.max(0, Number(levelRange.startIdx || 0));
    const endIdx = Math.min(levels.length - 1, Math.max(startIdx, Number(levelRange.endIdx ?? (levels.length - 1))));
    if(idx < startIdx || idx > endIdx){
      warnLockedLevel('Bu seviye bu ödev aralığında değil.');
      return;
    }
    const firstIncomplete = getFirstIncompleteRangeIndex();
    if(typeof firstIncomplete === 'number' && idx > firstIncomplete){
      warnLockedLevel('İlk bölümler tamamlanmadan sonraki bölümlere geçilemez.');
      return;
    }
  }
  if(levelRange){
    const startIdx = Math.max(0, Number(levelRange.startIdx || 0));
    const endIdx = Math.min(levels.length - 1, Math.max(startIdx, Number(levelRange.endIdx ?? (levels.length - 1))));
    if(idx < startIdx) idx = startIdx;
    if(idx > endIdx) idx = endIdx;
  }
  // Before switching, persist any current workspace XML into the current level
  try{
    if(typeof state.levelIndex === 'number' && levels[state.levelIndex] && window.Blockly && workspace){
      const prevXml = Blockly.Xml.domToText(Blockly.Xml.workspaceToDom(workspace));
      levels[state.levelIndex].solutionXml = prevXml;
      saveLevelsToStorage();
    }
  }catch(e){ /* ignore */ }

  state.levelIndex = idx;
  const lvl = levels[idx];
  levelAttemptStart = Date.now();
  state.x = lvl.start.x; state.y = lvl.start.y; state.dir = lvl.start.dir;
  state.commands = []; state.ip = 0; state.running = false;
  draw();
  // Restore saved workspace blocks for this level if available
  try{
    if(window.Blockly && workspace){
      workspace.clear();
      if(lvl && lvl.solutionXml){
        try{
          const xmlDom = Blockly.Xml.textToDom(lvl.solutionXml);
          Blockly.Xml.domToWorkspace(xmlDom, workspace);
        }catch(e){ /* invalid xml? ignore */ }
      }
      if(workspace.getAllBlocks(false).length === 0){
        const starter = workspace.newBlock('program_start');
        starter.initSvg();
        starter.render();
        starter.moveBy(40, 28);
      }
    }
  }catch(e){ /* ignore */ }
  // persist currently selected level index for resume
  try{ saveLevelsToStorage(); }catch(e){ /* ignore */ }
}

function draw(){
  ctx.clearRect(0,0,canvas.width,canvas.height);
  const gw = getCurrentLevelGridWidth();
  const gh = getCurrentLevelGridHeight();
  const cellPx = getCellPx();
  // grid
  ctx.strokeStyle='#e2e8f0';
  for(let i=0;i<=gw;i++){
    ctx.beginPath(); ctx.moveTo(i*cellPx,0); ctx.lineTo(i*cellPx,gh*cellPx); ctx.stroke();
  }
  for(let j=0;j<=gh;j++){
    ctx.beginPath(); ctx.moveTo(0,j*cellPx); ctx.lineTo(gw*cellPx,j*cellPx); ctx.stroke();
  }
  const lvl = levels[state.levelIndex];
  // obstacles
  ctx.fillStyle='#93a3b8';
  for(const o of lvl.obstacles){
    ctx.fillRect(o.x*cellPx+4, o.y*cellPx+4, cellPx-8, cellPx-8);
  }
  // star
  ctx.fillStyle='#ffcf3d';
  const s = lvl.star;
  ctx.beginPath();
  ctx.arc(s.x*cellPx + cellPx/2, s.y*cellPx + cellPx/2, cellPx/4, 0, Math.PI*2);
  ctx.fill();
  // character
  const cx = state.x*cellPx + cellPx/2;
  const cy = state.y*cellPx + cellPx/2;
  ctx.save();
  ctx.translate(cx,cy);
  ctx.rotate(state.dir * Math.PI/2);
  ctx.fillStyle = '#1e88e5';
  ctx.beginPath(); ctx.moveTo(-12, -10); ctx.lineTo(14,0); ctx.lineTo(-12,10); ctx.closePath(); ctx.fill();
  ctx.restore();

  document.getElementById('posX').textContent = state.x;
  document.getElementById('posY').textContent = state.y;
}

function stepOnce(){
  if(state.ip >= state.commands.length) return false;
  const c = state.commands[state.ip++];
  // highlight block
  try{ workspace.highlightBlock(c.blockId); }catch(e){}
  if(c.cmd === 'left'){
    state.dir = (state.dir + 3) % 4;
    draw();
    setTimeout(()=>workspace.highlightBlock(null), 150);
    checkStar();
    return true;
  }
  else if(c.cmd === 'right'){
    state.dir = (state.dir + 1) % 4;
    draw();
    setTimeout(()=>workspace.highlightBlock(null), 150);
    checkStar();
    return true;
  }
  else if(c.cmd === 'forward'){
    const gw = getCurrentLevelGridWidth();
    const gh = getCurrentLevelGridHeight();
    let nx = state.x, ny = state.y;
    if(state.dir===0) nx++;
    if(state.dir===1) ny++;
    if(state.dir===2) nx--;
    if(state.dir===3) ny--;
    // bounds
    if(nx<0||nx>=gw||ny<0||ny>=gh) { state.running=false; setStatus('Sınır!'); flashCanvas('#ff6b6b'); workspace.highlightBlock(null); return false; }
    // collision
    const lvl = levels[state.levelIndex];
    for(const o of lvl.obstacles) if(o.x===nx && o.y===ny){ state.running=false; setStatus('Engel!'); flashCanvas('#ff6b6b'); workspace.highlightBlock(null); return false; }

    // animate move
    animateMove({x:state.x,y:state.y}, {x:nx,y:ny}, 250, ()=>{
      state.x = nx; state.y = ny; draw(); workspace.highlightBlock(null); checkStar();
    });
    return true;
  }
}

function animateMove(from, to, ms, cb){
  const start = performance.now();
  function frame(now){
    const t = Math.min(1, (now-start)/ms);
    const gw = getCurrentLevelGridWidth();
    const gh = getCurrentLevelGridHeight();
    const cellPx = getCellPx();
    // clear and draw grid and obstacles and star
    ctx.clearRect(0,0,canvas.width,canvas.height);
    // grid
    ctx.strokeStyle='#e2e8f0';
    for(let i=0;i<=gw;i++){
      ctx.beginPath(); ctx.moveTo(i*cellPx,0); ctx.lineTo(i*cellPx,gh*cellPx); ctx.stroke();
    }
    for(let j=0;j<=gh;j++){
      ctx.beginPath(); ctx.moveTo(0,j*cellPx); ctx.lineTo(gw*cellPx,j*cellPx); ctx.stroke();
    }
    const lvl = levels[state.levelIndex];
    // obstacles
    ctx.fillStyle='#93a3b8';
    for(const o of lvl.obstacles){ ctx.fillRect(o.x*cellPx+4, o.y*cellPx+4, cellPx-8, cellPx-8); }
    // star
    ctx.fillStyle='#ffcf3d';
    const s = lvl.star; ctx.beginPath(); ctx.arc(s.x*cellPx + cellPx/2, s.y*cellPx + cellPx/2, cellPx/4, 0, Math.PI*2); ctx.fill();

    // draw character interpolated
    const cx = (from.x + (to.x-from.x)*t) * cellPx + cellPx/2;
    const cy = (from.y + (to.y-from.y)*t) * cellPx + cellPx/2;
    ctx.save(); ctx.translate(cx,cy); ctx.rotate(state.dir * Math.PI/2);
    ctx.fillStyle = '#1e88e5'; ctx.beginPath(); ctx.moveTo(-12, -10); ctx.lineTo(14,0); ctx.lineTo(-12,10); ctx.closePath(); ctx.fill(); ctx.restore();
    document.getElementById('posX').textContent = Math.round(from.x + (to.x-from.x)*t);
    document.getElementById('posY').textContent = Math.round(from.y + (to.y-from.y)*t);

    if(t<1) requestAnimationFrame(frame);
    else if(cb) cb();
  }
  requestAnimationFrame(frame);
}

function flashCanvas(color){
  const orig = canvas.style.boxShadow;
  canvas.style.boxShadow = `0 0 0 4px ${color}66`;
  setTimeout(()=> canvas.style.boxShadow = orig, 300);
}

function checkStar(){
  const lvl = levels[state.levelIndex];
  if(state.x === lvl.star.x && state.y === lvl.star.y){
    state.running=false; setStatus('Yıldız bulundu! Bölüm tamamlandı.');
    try{
      if(assignmentCompletedLevelIds instanceof Set){
        assignmentCompletedLevelIds.add(Number(lvl.id));
      }
    }catch(e){/* ignore */}
    // mark this level completed and persist
    try{ 
      levels[state.levelIndex].completed = true; 
      saveLevelsToStorage(); 
      // compute simple XP and completion percent and notify parent
      try{
        // determine stars and XP (1 star per level here)
        const stars = 1;
        const xpEarned = normalizeLevelXp(lvl?.xp);
        const completedCount = levels.filter(l=>l.completed).length;
        const percent = Math.round((completedCount / Math.max(1, levels.length)) * 100);
        const durationMs = levelAttemptStart ? (Date.now() - levelAttemptStart) : null;
        // mark stars and completion time on the level object
        levels[state.levelIndex].stars = stars;
        levels[state.levelIndex].completedAt = Date.now();
        if(window.parent && window.parent !== window){
          window.parent.postMessage({
            type: 'LEVEL_COMPLETED',
            levelId: lvl.id,
            xp: xpEarned,
            percent: percent,
            levels: levels,
            stars: stars,
            duration: durationMs,
            currentLevelIndex: state.levelIndex,
            starCollected: true
          }, '*');
        }
      }catch(err){/* ignore */}
    }catch(e){}
    populateLevels();
    unlockNext();
    const isAssignmentRangeEnd = (() => {
      if (isStaffMode()) return false;
      const activeRange = levelRange || getQueryAssignmentRange();
      if(!activeRange) return false;
      const endIdx = Math.min(levels.length - 1, Math.max(0, Number(activeRange.endIdx || (levels.length - 1))));
      return state.levelIndex >= endIdx;
    })();
    if (isAssignmentRangeEnd) {
      assignmentRangeLocked = true;
      postAssignmentCompleted();
      return;
    }
    if (!isAdminMode()) {
      if (levelRange || getQueryAssignmentRange()) {
        goToNextLevel();
        return;
      }
    }
    // only teacher flow keeps level-complete modal
    state.commands = state.commands.length ? state.commands : generateCommands();
    showLevelModal();
  }
}

function postAssignmentCompleted(){
  try {
    if (window.parent && window.parent !== window) {
      window.parent.postMessage({
        type: "ASSIGNMENT_RANGE_COMPLETED",
        source: "block-runner",
        currentLevelIndex: state.levelIndex,
        levels,
        xp: xpTotalEarned(),
        elapsedSeconds: levelAttemptStart ? Math.max(0, Math.round((Date.now() - levelAttemptStart) / 1000)) : 0,
        completedLevelIds: assignmentCompletedLevelIds instanceof Set ? Array.from(assignmentCompletedLevelIds) : []
      }, "*");
    }
  } catch (e) {}
}

function showLevelModal(){
  try{ workspace.highlightBlock(null); }catch(e){}
  const modal = document.getElementById('levelModal');
  const pre = document.getElementById('modalCodePre');
  const title = document.getElementById('modalTitle');
  const sub = document.getElementById('modalSubtitle');
  const code = generateJSFromCommands(state.commands);
  // Friendly feedback
  const cmdCount = state.commands? state.commands.length : 0;
  const activeRange = levelRange || getQueryAssignmentRange();
  const rangeStartIdx = activeRange ? Math.max(0, Number(activeRange.startIdx || 0)) : 0;
  const rangeEndIdx = activeRange ? Math.min(levels.length - 1, Math.max(0, Number(activeRange.endIdx || (levels.length - 1)))) : (levels.length - 1);
  const isRangeEnd = (!!activeRange && state.levelIndex >= rangeEndIdx) || (!isStaffMode() && assignmentRangeLocked);
  const currentLevelXp = normalizeLevelXp(levels[state.levelIndex]?.xp);
  const xpTotal = levels
    .slice(rangeStartIdx, rangeEndIdx + 1)
    .reduce((sum, lv) => sum + (lv?.completed ? normalizeLevelXp(lv?.xp) : 0), 0);
  title.textContent = 'Harika! Seviye Tamamlandı ??';
  sub.textContent = isRangeEnd
    ? `Toplam adım: ${cmdCount}. Toplam +${xpTotal} XP ile ödevdeki tüm seviyeler tamamlandı.`
    : `Toplam adım: ${cmdCount}. Bu seviyede +${currentLevelXp} XP kazandın, bir sonraki seviyeye hazırsın.`;
  pre.textContent = generateExecutableJS();
  const nextBtn = document.getElementById('modalNextBtn');
  if (nextBtn) {
    nextBtn.disabled = isRangeEnd;
    nextBtn.style.opacity = isRangeEnd ? "0.5" : "1";
    nextBtn.style.pointerEvents = isRangeEnd ? "none" : "auto";
  }
  // trigger confetti
  try{ startConfetti(); }catch(e){}
  modal.classList.remove('hidden');
}

function xpTotalEarned(){
  const activeRange = levelRange || getQueryAssignmentRange();
  const rangeStartIdx = activeRange ? Math.max(0, Number(activeRange.startIdx || 0)) : 0;
  const rangeEndIdx = activeRange ? Math.min(levels.length - 1, Math.max(0, Number(activeRange.endIdx || (levels.length - 1)))) : (levels.length - 1);
  return levels
    .slice(rangeStartIdx, rangeEndIdx + 1)
    .reduce((sum, lv) => sum + (lv?.completed ? normalizeLevelXp(lv?.xp) : 0), 0);
}

// Confetti animation (simple canvas based)
let _confettiRAF = null;
function startConfetti(){
  const c = document.getElementById('confettiCanvas');
  if(!c) return;
  const ctx = c.getContext('2d');
  const W = c.width = c.clientWidth || 800;
  const H = c.height = 140;
  const pieces = [];
  const colors = ['#ff595e','#ffca3a','#8ac926','#1982c4','#6a4c93'];
  for(let i=0;i<80;i++){
    pieces.push({x:Math.random()*W, y:Math.random()*-H, r:Math.random()*6+4, vx:(Math.random()-0.5)*2, vy:Math.random()*3+2, color:colors[i%colors.length], rot:Math.random()*360});
  }
  const start = performance.now();
  function frame(t){
    const dt = (t-start)/1000;
    ctx.clearRect(0,0,W,H);
    for(const p of pieces){
      p.x += p.vx; p.y += p.vy; p.rot += 6;
      ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.rot*Math.PI/180);
      ctx.fillStyle = p.color; ctx.fillRect(-p.r/2,-p.r/2,p.r,p.r*0.6);
      ctx.restore();
      if(p.y>H+20){ p.y = -10; p.x = Math.random()*W; }
    }
    if(t-start < 1600) _confettiRAF = requestAnimationFrame(frame);
    else { ctx.clearRect(0,0,W,H); cancelAnimationFrame(_confettiRAF); _confettiRAF = null; }
  }
  if(_confettiRAF) cancelAnimationFrame(_confettiRAF);
  _confettiRAF = requestAnimationFrame(frame);
}

function hideLevelModal(){
  const modal = document.getElementById('levelModal');
  modal.classList.add('hidden');
}

function getNormalizedUnlocked(){
  let raw = [];
  try{
    const parsed = JSON.parse(localStorage.getItem('unlocked') || '[]');
    raw = Array.isArray(parsed) ? parsed : [];
  }catch(e){
    raw = [];
  }
  const set = new Set();
  for(const v of raw){
    const n = Number(v);
    if(!Number.isFinite(n)) continue;
    if(n >= 0 && n < levels.length){
      set.add(Math.floor(n));
      continue;
    }
    const idxById = levels.findIndex(l => Number(l?.id) === Math.floor(n));
    if(idxById >= 0) set.add(idxById);
  }
  if(levels.length > 0) set.add(0);
  const normalized = Array.from(set).sort((a,b)=>a-b);
  localStorage.setItem('unlocked', JSON.stringify(normalized));
  return normalized;
}

function unlockNext(){
  if (levelRange && !isStaffMode()) {
    const endIdx = Math.min(levels.length - 1, Math.max(0, Number(levelRange.endIdx || (levels.length - 1))));
    if (state.levelIndex >= endIdx) return;
  }
  const unlocked = getNormalizedUnlocked();
  if(!unlocked.includes(state.levelIndex+1) && levels[state.levelIndex+1]){
    unlocked.push(state.levelIndex+1);
    localStorage.setItem('unlocked', JSON.stringify(Array.from(new Set(unlocked)).sort((a,b)=>a-b)));
    populateLevels();
  }
}

function isCurrentLevelUnlocked(){
  if(isStaffMode()) return true;
  if(levelRange){
    const i = Number(state.levelIndex || 0);
    const s = Number(levelRange.startIdx || 0);
    const e = Number(levelRange.endIdx || 0);
    if(i >= s && i <= e) return true;
  }
  const unlocked = getNormalizedUnlocked();
  return unlocked.includes(Number(state.levelIndex));
}

function isLevelCompletedForAssignment(level){
  if(!level) return false;
  if(assignmentCompletedLevelIds instanceof Set){
    return assignmentCompletedLevelIds.has(Number(level.id));
  }
  return !!level.completed;
}

function getFirstIncompleteRangeIndex(){
  if(!levelRange) return null;
  const startIdx = Math.max(0, Number(levelRange.startIdx || 0));
  const endIdx = Math.min(levels.length - 1, Math.max(startIdx, Number(levelRange.endIdx ?? (levels.length - 1))));
  for(let i=startIdx; i<=endIdx; i++){
    if(!isLevelCompletedForAssignment(levels[i])) return i;
  }
  return endIdx;
}

function warnLockedLevel(customMessage){
  const msg = customMessage || 'Bu seviye hen?z kilitli.';
  setStatus(msg);
  try{
    if(window.parent && window.parent !== window){
      window.parent.postMessage({ type: 'LOCKED_LEVEL_WARNING', message: msg }, '*');
    }
  }catch(e){/* ignore */}
}

function normalizeGroupOrders(groupName){
  const same = levels
    .map((l, idx) => ({ l, idx }))
    .filter(x => String(x.l?.group || 'Genel') === String(groupName || 'Genel'))
    .sort((a, b) => Number(a.l?.order || 0) - Number(b.l?.order || 0));
  same.forEach((x, i) => { x.l.order = i + 1; });
}

function moveCurrentLevel(direction){
  const current = levels[state.levelIndex];
  if(!current) return;
  const groupName = current.group || 'Genel';
  const same = levels
    .map((l, idx) => ({ l, idx }))
    .filter(x => String(x.l?.group || 'Genel') === String(groupName))
    .sort((a, b) => Number(a.l?.order || 0) - Number(b.l?.order || 0));
  const pos = same.findIndex(x => x.idx === state.levelIndex);
  if(pos < 0) return;
  const targetPos = direction === 'up' ? pos - 1 : pos + 1;
  if(targetPos < 0 || targetPos >= same.length){
    setStatus(direction === 'up' ? 'Daha yukarı taşınamaz' : 'Daha aşağı taşınamaz');
    return;
  }
  const a = same[pos].l;
  const b = same[targetPos].l;
  const tmp = Number(a.order || pos + 1);
  a.order = Number(b.order || targetPos + 1);
  b.order = tmp;
  normalizeGroupOrders(groupName);
  sortLevelsInPlace();
  const keepId = current.id;
  saveLevelsToStorage();
  populateLevels();
  const nextIndex = levels.findIndex(l => Number(l?.id) === Number(keepId));
  loadLevel(nextIndex >= 0 ? nextIndex : 0);
  setStatus(direction === 'up' ? 'Seviye yukarı taşındı' : 'Seviye aşağı taşındı');
}

function moveCurrentLevelUp(){ moveCurrentLevel('up'); }
function moveCurrentLevelDown(){ moveCurrentLevel('down'); }


function goToNextLevel(){
  const activeRange = levelRange || getQueryAssignmentRange();
  if (!isStaffMode() && assignmentRangeLocked) {
    postAssignmentCompleted();
    setStatus('Ödev aralığı tamamlandı');
    return;
  }
  if (activeRange) {
    const endIdx = Math.min(levels.length - 1, Math.max(0, Number(activeRange.endIdx || (levels.length - 1))));
    if (state.levelIndex >= endIdx) {
      assignmentRangeLocked = true;
      postAssignmentCompleted();
      setStatus('Ödev aralığı tamamlandı');
      return;
    }
  }
  const next = state.levelIndex + 1;
  if(!levels[next]){ setStatus('Tüm seviyeler tamamlandı'); return; }
  setStatus('Seviye tamamlandı! Sonraki seviyeye geçiliyor...');
  setTimeout(()=>{
    try{ if(window.workspace) workspace.clear(); else workspace.clear(); }catch(e){}
    loadLevel(next);
    populateLevels();
    const cp_next = document.getElementById('commandsPre'); if(cp_next) cp_next.textContent = '[]';
    setStatus('Seviye ' + (next+1) + ' yüklendi');
  }, 700);
}

function setStatus(t){ document.getElementById('status').textContent = t; }

function runProgram(){
  if(state.running) return;
  if(!levelAttemptStart) levelAttemptStart = Date.now();
  if(!isCurrentLevelUnlocked()){
    warnLockedLevel();
    return;
  }
  // reset character to level start before running
  const lvl = levels[state.levelIndex];
  state.x = lvl.start.x; state.y = lvl.start.y; state.dir = lvl.start.dir;
  draw();
  try{ workspace.highlightBlock(null); }catch(e){}
  state.commands = generateCommands();
  // show executable JS generated from Blockly if available
  const cp_run = document.getElementById('commandsPre'); if(cp_run) cp_run.textContent = generateExecutableJS();
  state.ip = 0; state.running = true; setStatus('Çalışıyor...');
  function tick(){
    if(!state.running) return;
    const ok = stepOnce();
    if(!ok){ state.running=false; return; }
    if(state.ip >= state.commands.length){ state.running=false; setStatus('Bitti'); return; }
    setTimeout(tick, 400);
  }
  setTimeout(tick, 300);
}

function stepProgram(){
  if(state.running) return;
  if(!isCurrentLevelUnlocked()){
    warnLockedLevel();
    return;
  }
  if(state.commands.length===0) state.commands = generateCommands();
  const cp_step = document.getElementById('commandsPre'); if(cp_step) cp_step.textContent = generateJSFromCommands(state.commands);
  stepOnce();
}

function generateJSFromCommands(cmds){
  if(!cmds || cmds.length===0) return '// Program boş';
  // Fallback: produce readable pseudo-JS from commands
  const lines = [];
  lines.push('async function program(){');
  for(const c of cmds){
    if(c.cmd==='forward') lines.push('  await moveForward();');
    else if(c.cmd==='left') lines.push('  await turnLeft();');
    else if(c.cmd==='right') lines.push('  await turnRight();');
  }
  lines.push('}');
  lines.push('');
  lines.push('// Örnek hareket fonksiyonları:');
  lines.push('async function moveForward(){ /* karakteri bir hücre öne taşı */ }');
  lines.push('async function turnLeft(){ /* 90° sola dön */ }');
  lines.push('async function turnRight(){ /* 90° sağa dön */ }');
  return lines.join('\n');
}

function generateExecutableJS(){
  // If Blockly is available, produce real JS from workspace
  try{
    if(window.Blockly && workspace){
      const body = Blockly.JavaScript.workspaceToCode(workspace);
      const wrapper = [];
      wrapper.push('async function program(){');
      // ensure indentation of body
      const bodyLines = body.split('\n').map(l=> l.length? '  '+l : '');
      wrapper.push(...bodyLines);
      wrapper.push('}');
      wrapper.push('');
      wrapper.push('// Yardımcı hareket fonksiyonları (örnek):');
      wrapper.push('async function moveForward(){ /* karakter bir hücre ilerlesin */ }');
      wrapper.push('async function turnLeft(){ /* sola dön */ }');
      wrapper.push('async function turnRight(){ /* sağa dön */ }');
      return wrapper.join('\n');
    }
  }catch(e){/* ignore */}
  return generateJSFromCommands(state.commands);
}

function populateLevels(){
  const sel = document.getElementById('levelSelect'); sel.innerHTML='';
  const unlocked = getNormalizedUnlocked();
  // group levels by their group property
  const groups = {};
  for(const l of levels){ const g = l.group || 'Genel'; if(!groups[g]) groups[g] = []; groups[g].push(l); }
  // order groups using groupOrder; fallback to alphabetical
  const orderedGroupNames = Object.keys(groups).sort((a,b)=>{
    const ia = groupOrder.indexOf(a); const ib = groupOrder.indexOf(b);
    if(ia >=0 && ib >=0) return ia-ib;
    if(ia >=0) return -1; if(ib >=0) return 1;
    return a.localeCompare(b);
  });
  // build optgroups
  for(const gName of orderedGroupNames){
    const optg = document.createElement('optgroup'); optg.label = gName;
    for(const l of groups[gName]){
      const opt = document.createElement('option');
      // use index within flat levels array as value
      const idx = levels.indexOf(l);
      const inRange = !levelRange || (idx >= Number(levelRange.startIdx || 0) && idx <= Number(levelRange.endIdx || 0));
      if (!isStaffMode() && levelRange && !inRange) continue;
      const firstIncomplete = (!isStaffMode() && levelRange) ? getFirstIncompleteRangeIndex() : null;
      const lockedBySequence = (!isStaffMode()) && levelRange && typeof firstIncomplete === 'number' ? idx > firstIncomplete : false;
      opt.value = idx;
      if(isLevelCompletedForAssignment(l)){ opt.textContent = '[Tamam] ' + l.name; opt.style.color = '#16a34a'; }
      else opt.textContent = (!isStaffMode() && (lockedBySequence || (!unlocked.includes(idx) && !levelRange)) ? '[Kilitli] ' : '') + l.name;
      if(!isStaffMode()){
        if(!inRange) opt.disabled = true;
        else if(!unlocked.includes(idx) && !levelRange) opt.disabled = true;
      }
      optg.appendChild(opt);
    }
    if (optg.children.length > 0) sel.appendChild(optg);
  }
  sel.value = state.levelIndex;
}

function initUI(){
  document.getElementById('runBtn').addEventListener('click', runProgram);
  document.getElementById('stepBtn').addEventListener('click', stepProgram);
  document.getElementById('resetBtn').addEventListener('click', ()=> loadLevel(state.levelIndex));
  document.getElementById('levelSelect').addEventListener('change', (e)=>{ loadLevel(parseInt(e.target.value)); });
  const levelMoveUpBtn = document.getElementById('levelMoveUpBtn');
  const levelMoveDownBtn = document.getElementById('levelMoveDownBtn');
  if(levelMoveUpBtn) levelMoveUpBtn.addEventListener('click', ()=>{ if(isAdminMode()) moveCurrentLevelUp(); });
  if(levelMoveDownBtn) levelMoveDownBtn.addEventListener('click', ()=>{ if(isAdminMode()) moveCurrentLevelDown(); });

  // Side menu toggle and Add Level
  const menuToggle = document.getElementById('menuToggle');
  const sideMenu = document.getElementById('sideMenu');
  const sideClose = document.getElementById('sideClose');
  const addLevelBtn = document.getElementById('addLevelBtn');
  const deleteLevelBtn = document.getElementById('deleteLevelBtn');
  // Side drawer is deprecated. Keep closed and hidden.
  if(menuToggle) menuToggle.style.display = isAdminMode() ? "inline-flex" : "none";
  if(sideMenu){ sideMenu.classList.add('hidden'); sideMenu.setAttribute('aria-hidden','true'); }
  if(menuToggle){ menuToggle.addEventListener('click', ()=>{ if(!isAdminMode()) return; if(!sideMenu) return; const hidden = sideMenu.classList.contains('hidden'); if(hidden){ sideMenu.classList.remove('hidden'); sideMenu.setAttribute('aria-hidden','false'); sideMenu.style.display = 'flex'; sideMenu.style.visibility = 'visible'; sideMenu.style.transform = 'translateX(0)'; sideMenu.style.pointerEvents = 'auto'; sideMenu.style.zIndex = '9999'; } else { sideMenu.classList.add('hidden'); sideMenu.setAttribute('aria-hidden','true'); sideMenu.style.transform = 'translateX(-110%)'; } }); }
  if(sideClose){ sideClose.addEventListener('click', ()=>{ sideMenu.classList.add('hidden'); sideMenu.setAttribute('aria-hidden','true'); sideMenu.style.transform = 'translateX(-110%)'; }); }
  if(addLevelBtn){ addLevelBtn.addEventListener('click', ()=>{ openLevelDesigner(); const sm = document.getElementById('sideMenu'); if(sm) { sm.classList.add('hidden'); sm.setAttribute('aria-hidden','true'); } }); }
  if(deleteLevelBtn){ deleteLevelBtn.addEventListener('click', ()=>{ openDeleteLevelModal(); const sm = document.getElementById('sideMenu'); if(sm) { sm.classList.add('hidden'); sm.setAttribute('aria-hidden','true'); } }); }
  // Close side menu when any menu button is clicked
  try{
    sideMenu.addEventListener('click', (e)=>{
      const btn = e.target.closest && e.target.closest('.menu-btn');
      if(btn){ sideMenu.classList.add('hidden'); sideMenu.setAttribute('aria-hidden','true'); }
    });
  }catch(e){/* ignore if sideMenu missing */}

  // Student mode cannot access "Seviye Ekle" menu actions.
  if (!isAdminMode()) {
    try {
      if (menuToggle) menuToggle.style.display = "none";
      if (sideMenu) {
        sideMenu.classList.add("hidden");
        sideMenu.setAttribute("aria-hidden", "true");
      }
      if (addLevelBtn) addLevelBtn.style.display = "none";
      if (deleteLevelBtn) deleteLevelBtn.style.display = "none";
      if (levelMoveUpBtn) levelMoveUpBtn.style.display = "none";
      if (levelMoveDownBtn) levelMoveDownBtn.style.display = "none";
    } catch (e) {
      /* ignore */
    }
  }

  // modal buttons
  const nextBtn = document.getElementById('modalNextBtn');
  const retryBtn = document.getElementById('modalRetryBtn');
  const closeBtn = document.getElementById('modalCloseBtn');
  if(nextBtn) nextBtn.addEventListener('click', ()=>{ hideLevelModal(); goToNextLevel(); });
  if(retryBtn) retryBtn.addEventListener('click', ()=>{ hideLevelModal(); loadLevel(state.levelIndex); setStatus('Tekrar deneyin'); });
  if(closeBtn) closeBtn.addEventListener('click', ()=>{ hideLevelModal(); setStatus(''); });
  // also allow clicking backdrop to close
  const modal = document.getElementById('levelModal');
  if(modal){
    modal.addEventListener('click',(e)=>{ if(e.target === modal) hideLevelModal(); });
  }
  const deleteModal = document.getElementById('levelDeleteModal');
  if(deleteModal){
    deleteModal.addEventListener('click', (e)=>{ if(e.target === deleteModal) closeDeleteLevelModal(); });
  }

  // Level designer modal controls
  const designerModal = document.getElementById('levelDesignerModal');
  const designerSaveBtn = document.getElementById('designerSaveBtn');
  const designerResetBtn = document.getElementById('designerResetBtn');
  const designerCancelBtn = document.getElementById('designerCancelBtn');
  const confirmDeleteLevelBtn = document.getElementById('confirmDeleteLevelBtn');
  const cancelDeleteLevelBtn = document.getElementById('cancelDeleteLevelBtn');
  if(designerSaveBtn) designerSaveBtn.addEventListener('click', ()=>{ saveDesignerLevel(); });
  if(designerResetBtn) designerResetBtn.addEventListener('click', ()=>{ resetDesignerModal(); });
  if(designerCancelBtn) designerCancelBtn.addEventListener('click', ()=>{ closeLevelDesigner(); });
  if(confirmDeleteLevelBtn) confirmDeleteLevelBtn.addEventListener('click', ()=>{ deleteCurrentLevel(); });
  if(cancelDeleteLevelBtn) cancelDeleteLevelBtn.addEventListener('click', ()=>{ closeDeleteLevelModal(); });

  // Designer bindings (drag-drop grid editor)
  try{
    const paletteTiles = document.querySelectorAll('#designerPalette .palette-tile');
    paletteTiles.forEach((tile)=>{
      tile.addEventListener('click', ()=> setDesignerTool(tile.dataset.item));
      tile.addEventListener('dragstart', (ev)=>{
        const item = tile.dataset.item || 'obstacle';
        setDesignerTool(item);
        if(ev.dataTransfer){
          ev.dataTransfer.setData('text/plain', item);
          ev.dataTransfer.effectAllowed = 'copy';
        }
      });
    });
    const wEl = document.getElementById('designerGridWidth');
    const hEl = document.getElementById('designerGridHeight');
    const onSizeChange = ()=>{
      designerDraft = makeEmptyDesignerDraft();
      renderDesignerBoard();
    };
    if(wEl) wEl.addEventListener('change', onSizeChange);
    if(hEl) hEl.addEventListener('change', onSizeChange);
  }catch(e){/* ignore */}

  // Dark mode toggle
  try{
    const darkToggle = document.getElementById('darkToggle');
    function applyTheme(t){ if(t==='dark') document.body.classList.add('dark'); else document.body.classList.remove('dark'); }
    const stored = localStorage.getItem('theme'); if(stored) applyTheme(stored);
    if(darkToggle){ darkToggle.addEventListener('click', ()=>{ const isDark = document.body.classList.toggle('dark'); localStorage.setItem('theme', isDark? 'dark':'light'); darkToggle.textContent = isDark? '??' : '??'; });
      // set initial icon
      darkToggle.textContent = document.body.classList.contains('dark')? '??' : '??';
    }
  }catch(e){/* ignore */}
}

function openLevelDesigner(editMode = false){
  const modal = document.getElementById('levelDesignerModal');
  if(!modal) return;
  const lvl = levels[state.levelIndex] || {};
  designerEditMode = !!editMode;

  const header = modal.querySelector('.modal-header h3');
  if(header) header.textContent = designerEditMode ? 'Seviye Düzenle' : 'Seviye Tasarla';

  const saveBtn = document.getElementById('designerSaveBtn');
  if(saveBtn) saveBtn.textContent = designerEditMode ? 'Güncelle' : 'Kaydet';

  document.getElementById('designerLevelName').value = designerEditMode
    ? (lvl.name || 'Özel Seviye')
    : (lvl.name ? lvl.name + ' - kopya' : 'Özel Seviye');
  const groupInput = document.getElementById('designerGroup');
  if(groupInput) groupInput.value = lvl.group || 'Özel';
  const orderInput = document.getElementById('designerLevelOrder');
  if(orderInput) {
    const fallbackOrder = state.levelIndex + 1;
    orderInput.value = Number.isFinite(Number(lvl.order)) ? Number(lvl.order) : fallbackOrder;
  }
  const xpInput = document.getElementById('designerLevelXp');
  if(xpInput){
    xpInput.value = String(normalizeLevelXp(lvl?.xp));
  }

  const wEl = document.getElementById('designerGridWidth');
  const hEl = document.getElementById('designerGridHeight');
  if(designerEditMode){
    const w = clampDesigner((lvl.gridWidth ?? gridSize), 6, 12);
    const h = clampDesigner((lvl.gridHeight ?? gridSize), 6, 12);
    if(wEl) wEl.value = String(w);
    if(hEl) hEl.value = String(h);
    designerDraft = {
      w,
      h,
      start: { x: clampDesigner((lvl.start||{}).x, 0, w-1), y: clampDesigner((lvl.start||{}).y, 0, h-1), dir: clampDesigner((lvl.start||{}).dir, 0, 3) },
      star: { x: clampDesigner((lvl.star||{}).x, 0, w-1), y: clampDesigner((lvl.star||{}).y, 0, h-1) },
      obstacles: Array.isArray(lvl.obstacles)
        ? lvl.obstacles
            .map((o)=>({x: clampDesigner(o.x,0,w-1), y: clampDesigner(o.y,0,h-1)}))
            .filter((o)=> !(o.x===lvl.start?.x && o.y===lvl.start?.y) && !(o.x===lvl.star?.x && o.y===lvl.star?.y))
        : []
    };
  } else {
    if(wEl && !wEl.value) wEl.value = '8';
    if(hEl && !hEl.value) hEl.value = '8';
    designerDraft = makeEmptyDesignerDraft();
  }

  modal.classList.remove('hidden');
  modal.setAttribute('aria-hidden','false');
  setDesignerTool('obstacle');
  renderDesignerBoard();
}

function closeLevelDesigner(){
  const modal = document.getElementById('levelDesignerModal');
  if(!modal) return;
  modal.classList.add('hidden');
  modal.setAttribute('aria-hidden','true');
  designerEditMode = false;
  designerDraft = null;
  const saveBtn = document.getElementById('designerSaveBtn');
  if(saveBtn) saveBtn.textContent = 'Kaydet';
}

function openDeleteLevelModal(){
  const modal = document.getElementById('levelDeleteModal');
  const msg = document.getElementById('deleteLevelMessage');
  if(!modal) return;
  const lvl = levels[state.levelIndex];
  if(msg){
    msg.textContent = `"${lvl?.name || 'Seviye'}" seviyesini silmek istiyor musunuz?`;
  }
  modal.classList.remove('hidden');
  modal.setAttribute('aria-hidden','false');
}

function closeDeleteLevelModal(){
  const modal = document.getElementById('levelDeleteModal');
  if(!modal) return;
  modal.classList.add('hidden');
  modal.setAttribute('aria-hidden','true');
}

function deleteCurrentLevel(){
  if(!isAdminMode()) return;
  if(!Array.isArray(levels) || levels.length <= 1){
    setStatus('Son seviye silinemez');
    closeDeleteLevelModal();
    return;
  }
  const deleteIndex = state.levelIndex;
  levels.splice(deleteIndex, 1);
  try{
    const unlocked = JSON.parse(localStorage.getItem('unlocked') || '[0]');
    const fixed = unlocked
      .map((v)=> Number(v))
      .filter((v)=> Number.isFinite(v))
      .map((v)=> v > deleteIndex ? v - 1 : v)
      .filter((v)=> v >= 0 && v < levels.length);
    if(!fixed.includes(0)) fixed.push(0);
    localStorage.setItem('unlocked', JSON.stringify(Array.from(new Set(fixed)).sort((a,b)=>a-b)));
  }catch(e){/* ignore */}
  saveLevelsToStorage();
  populateLevels();
  const nextIndex = Math.max(0, Math.min(levels.length - 1, deleteIndex));
  loadLevel(nextIndex);
  setStatus('Seviye silindi');
  closeDeleteLevelModal();
}

function resetDesignerModal(){
  const wEl = document.getElementById('designerGridWidth');
  const hEl = document.getElementById('designerGridHeight');
  if(wEl) wEl.value = '8';
  if(hEl) hEl.value = '8';
  const xpEl = document.getElementById('designerLevelXp');
  if(xpEl) xpEl.value = String(DEFAULT_LEVEL_XP);
  designerDraft = makeEmptyDesignerDraft();
  setDesignerTool('obstacle');
  renderDesignerBoard();
  setStatus('Tasarım alanı temizlendi');
}

function saveDesignerLevel(){
  try{
    const name = (document.getElementById('designerLevelName')||{}).value || ('Özel Seviye');
    const groupName = ((document.getElementById('designerGroup')||{}).value || 'Özel').trim() || 'Özel';
    const levelOrder = Math.max(1, parseInt((document.getElementById('designerLevelOrder')||{}).value) || 1);
    const levelXp = normalizeLevelXp((document.getElementById('designerLevelXp')||{}).value);
    if(!designerDraft) designerDraft = makeEmptyDesignerDraft();
    const gw = clampDesigner(designerDraft.w, 6, 12);
    const gh = clampDesigner(designerDraft.h, 6, 12);
    const sx = clampDesigner(designerDraft.start?.x, 0, gw-1);
    const sy = clampDesigner(designerDraft.start?.y, 0, gh-1);
    const sdir = clampDesigner(designerDraft.start?.dir ?? 0, 0, 3);
    const starX = clampDesigner(designerDraft.star?.x, 0, gw-1);
    const starY = clampDesigner(designerDraft.star?.y, 0, gh-1);
    const obs = (designerDraft.obstacles || [])
      .map((o)=>({ x: clampDesigner(o.x, 0, gw-1), y: clampDesigner(o.y, 0, gh-1) }))
      .filter((o)=> !(o.x===sx && o.y===sy) && !(o.x===starX && o.y===starY));

    const base = levels[state.levelIndex] || { obstacles: [] , star:{x:0,y:0}, start:{x:0,y:0,dir:0} };
    const newLevel = JSON.parse(JSON.stringify(base));

    if (designerEditMode) {
      newLevel.id = base.id;
    } else {
      const nextId = (levels.length ? Math.max(...levels.map(l => Number(l.id || 0))) + 1 : 0);
      newLevel.id = nextId;
    }

    newLevel.name = name;
    newLevel.group = groupName;
    newLevel.order = levelOrder;
    newLevel.xp = levelXp;
    newLevel.gridWidth = gw;
    newLevel.gridHeight = gh;
    newLevel.start = { x: sx, y: sy, dir: sdir };
    newLevel.star = { x: starX, y: starY };
    newLevel.obstacles = obs.length ? obs : [];

    const xmlText = (window.Blockly && workspace) ? Blockly.Xml.domToText(Blockly.Xml.workspaceToDom(workspace)) : '';
    newLevel.solutionXml = xmlText;
    newLevel.solutionCode = generateExecutableJS();

    if (designerEditMode) {
      const keepId = newLevel.id;
      levels[state.levelIndex] = newLevel;
      sortLevelsInPlace();
      saveLevelsToStorage();
      populateLevels();
      const editedIndex = levels.findIndex(l => Number(l?.id) === Number(keepId));
      loadLevel(editedIndex >= 0 ? editedIndex : state.levelIndex);
      setStatus('Seviye guncellendi: ' + name);
    } else {
      levels.push(newLevel);
      sortLevelsInPlace();
      saveLevelsToStorage();
      populateLevels();
      const newIndex = levels.findIndex(l => Number(l?.id) === Number(newLevel.id));
      loadLevel(newIndex >= 0 ? newIndex : 0);
      setStatus('Yeni seviye tasarlandı ve yüklendi: ' + name);
    }

    try{ if(window.Blockly && workspace) workspace.clear(); }catch(e){}
    closeLevelDesigner();
  }catch(e){
    console.warn('saveDesignerLevel', e);
    setStatus('Seviye kaydedilemedi');
  }
}

function addCurrentAsLevel(){
  // Create a copy of current level layout and attach Blockly XML & code as solution
  try{
    const base = levels[state.levelIndex];
    const nextId = Math.max(...levels.map(l=>l.id))+1;
    const nameInput = document.getElementById('newLevelName');
    const name = (nameInput && nameInput.value.trim()) ? nameInput.value.trim() : ('Özel Seviye ' + nextId);
    // if designerGroup input exists in the UI, use it; otherwise default to 'Özel'
    const groupInput = document.getElementById('designerGroup');
    const groupName = (groupInput && groupInput.value.trim()) ? groupInput.value.trim() : 'Özel';
    const xmlText = (window.Blockly && workspace) ? Blockly.Xml.domToText(Blockly.Xml.workspaceToDom(workspace)) : '';
    const code = generateExecutableJS();
    const newLevel = JSON.parse(JSON.stringify(base));
    newLevel.id = nextId;
    newLevel.name = name;
    // Use current character position as start for the new level
    newLevel.start = { x: state.x, y: state.y, dir: state.dir };
    // If user provided star position, use it
    try{
      const sx = parseInt((document.getElementById('starX')||{}).value);
      const sy = parseInt((document.getElementById('starY')||{}).value);
      if(!Number.isNaN(sx) && !Number.isNaN(sy)) newLevel.star = { x: Math.max(0, Math.min(gridSize-1, sx)), y: Math.max(0, Math.min(gridSize-1, sy)) };
    }catch(e){ /* ignore */ }
    // Parse obstacles from textarea (format: "x,y; x2,y2")
    try{
      const obsTxt = (document.getElementById('obstaclesInput')||{}).value || '';
      const obs = [];
      obsTxt.split(';').map(s=>s.trim()).filter(Boolean).forEach(part=>{
        const p = part.split(',').map(s=>s.trim());
        if(p.length>=2){ const ox = parseInt(p[0]); const oy = parseInt(p[1]); if(!Number.isNaN(ox)&&!Number.isNaN(oy)) obs.push({x: Math.max(0, Math.min(gridSize-1, ox)), y: Math.max(0, Math.min(gridSize-1, oy))}); }
      });
      if(obs.length) newLevel.obstacles = obs;
    }catch(e){ /* ignore */ }
    // Attach solution metadata
    newLevel.solutionXml = xmlText;
    newLevel.solutionCode = code;
    newLevel.group = groupName;
    levels.push(newLevel);
    saveLevelsToStorage();
    populateLevels();
    // select the new level
    const newIndex = levels.length-1;
    loadLevel(newIndex);
    // Clear Blockly workspace so the user can start solving the new level from empty
    try{ if(window.Blockly && workspace) workspace.clear(); }catch(e){ /* ignore */ }
    // reset commands view
    const cmdsPre = document.getElementById('commandsPre'); if(cmdsPre) cmdsPre.textContent = '// Buraya bloklar ile çözüm yazılacak';
    setStatus('Yeni seviye eklendi ve workspace temizlendi: ' + name + ' - şimdi çözebilirsin.');
    // close menu
    const sideMenu = document.getElementById('sideMenu'); if(sideMenu) { sideMenu.classList.add('hidden'); sideMenu.setAttribute('aria-hidden','true'); }
  }catch(e){ console.warn('addCurrentAsLevel', e); setStatus('Seviye eklenirken hata oluştu'); }
}

function exportBlocklyXml(){
  try{
    if(!window.Blockly || !workspace){ setStatus('Blockly yüklü değil'); return; }
    const xml = Blockly.Xml.domToPrettyText(Blockly.Xml.workspaceToDom(workspace));
    // download as file
    const blob = new Blob([xml], {type:'text/xml'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'workspace.xml'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    setStatus('Blockly XML dışa aktarıldı');
  }catch(e){ console.warn('exportBlocklyXml', e); setStatus('Dışa aktarım başarısız'); }
}

// --- Level editor and persistence ---
function loadStoredLevels(){
  try{
    const raw = localStorage.getItem('levels');
    if(raw){
      const parsed = JSON.parse(raw);
      if(Array.isArray(parsed) && parsed.length>0){
        const byId = new Map();
        // keep stored levels first (teacher customizations), then fill missing bundled levels
        parsed.forEach((p, idx)=>{
          if(!p.group) p.group = 'Genel';
          if(!Number.isFinite(Number(p.order))) p.order = idx + 1;
          const id = Number(p?.id);
          if(Number.isFinite(id)) byId.set(id, p);
        });
        levels.forEach((p, idx) => {
          const id = Number(p?.id);
          if(!Number.isFinite(id) || byId.has(id)) return;
          const clone = { ...p };
          if(!clone.group) clone.group = 'Genel';
          if(!Number.isFinite(Number(clone.order))) clone.order = (idx + 1);
          byId.set(id, clone);
        });
        levels = Array.from(byId.values());
        applyXpDefaults();
        sortLevelsInPlace();
      }
    }
  }catch(e){ console.warn('loadStoredLevels', e); }
}

function saveLevelsToStorage(){
  try{
    sortLevelsInPlace();
    localStorage.setItem('levels', JSON.stringify(levels));
    // notify parent window (if embedded) about the game state update
    try{
      if(window.parent && window.parent !== window){
        window.parent.postMessage({
          type: 'GAME_UPDATE',
          levels: levels,
          currentLevelIndex: state.levelIndex
        }, '*');
      }
    }catch(e){}
  }
  catch(e){ console.warn('saveLevelsToStorage', e); }
}

// Listen for parent -> iframe messages for state injection
(function(){
  try{
    window.addEventListener('message', function(e){
      const data = e && e.data;
      if(!data || typeof data !== 'object') return;
      if(data.type === 'LOAD_STATE'){
        try{
          if(Array.isArray(data.levels)){
            levels = data.levels;
            levels.forEach((p, idx)=>{
              if(!p.group) p.group = 'Genel';
              if(!Number.isFinite(Number(p.order))) p.order = idx + 1;
            });
            applyXpDefaults();
            sortLevelsInPlace();
            // persist locally so the iframe can continue offline
            localStorage.setItem('levels', JSON.stringify(levels));
            populateLevels();
            const idx = Math.max(0, Math.min(levels.length - 1, Number(data.currentLevelIndex || 0)));
            loadLevel(idx);
          }
        }catch(err){ console.warn('LOAD_STATE error', err); }
      }
      else if(data.type === 'SET_CURRENT_LEVEL'){
        try{
          const idx = Math.max(0, Math.min(levels.length - 1, Number(data.currentLevelIndex || 0)));
          loadLevel(idx);
        }catch(err){/* ignore */}
      }
      else if(data.type === 'SET_LEVEL_RANGE'){
        try{
          assignmentRangeLocked = false;
          const start = Math.max(1, Number(data.levelStart || 1));
          const end = Math.max(start, Number(data.levelEnd || start));
          levelRange = {
            startIdx: start - 1,
            endIdx: end - 1
          };
          populateLevels();
          if (state.levelIndex < levelRange.startIdx || state.levelIndex > levelRange.endIdx) {
            loadLevel(levelRange.startIdx);
          } else {
            loadLevel(state.levelIndex);
          }
          setStatus(`Seviye aralığı: ${start}-${end}`);
        }catch(err){/* ignore */}
      }
      else if(data.type === 'FORCE_ASSIGNMENT_LOCK'){
        try{
          assignmentRangeLocked = true;
          setStatus('Ödev aralığı tamamlandı');
        }catch(err){/* ignore */}
      }
      else if(data.type === 'SET_ASSIGNMENT_PROGRESS'){
        try{
          const ids = Array.isArray(data.completedLevelIds) ? data.completedLevelIds : [];
          assignmentCompletedLevelIds = new Set(
            ids.map((v)=> Number(v)).filter((v)=> Number.isFinite(v))
          );
          populateLevels();
        }catch(err){/* ignore */}
      }
      else if(data.type === 'RESET_LOCAL'){
        try{ localStorage.removeItem('levels'); loadStoredLevels(); populateLevels(); loadLevel(0); }catch(err){}
      }
      else if(data.type === 'REQUEST_SAVE'){
        try{ saveLevelsToStorage(); }catch(err){/* ignore */}
      }
      else if(data.type === 'RUN'){
        try{ runProgram(); }catch(e){}
      }
      else if(data.type === 'STEP'){
        try{ stepProgram(); }catch(e){}
      }
      else if(data.type === 'RESET'){
        try{ loadLevel(state.levelIndex); }catch(e){}
      }
      else if(data.type === 'DISABLE_MENU'){
        try{ const mt = document.getElementById('menuToggle'); if(mt) mt.style.display = 'none'; const sm = document.getElementById('sideMenu'); if(sm) { sm.classList.add('hidden'); sm.setAttribute('aria-hidden','true'); } }catch(e){}
      }
      else if(data.type === 'ENABLE_MENU'){
        try{ const mt = document.getElementById('menuToggle'); if(mt) mt.style.display = isAdminMode() ? 'inline-flex' : 'none'; }catch(e){}
      }
      else if(data.type === 'OPEN_DESIGNER'){
        try{ if(isAdminMode()) openLevelDesigner(false); }catch(e){}
      }
      else if(data.type === 'OPEN_EDIT_LEVEL'){
        try{ if(isAdminMode()) openLevelDesigner(true); }catch(e){}
      }
      else if(data.type === 'OPEN_DELETE_LEVEL'){
        try{ if(isAdminMode()) openDeleteLevelModal(); }catch(e){}
      }
      else if(data.type === 'MOVE_LEVEL_UP'){
        try{ if(isAdminMode()) moveCurrentLevelUp(); }catch(e){}
      }
      else if(data.type === 'MOVE_LEVEL_DOWN'){
        try{ if(isAdminMode()) moveCurrentLevelDown(); }catch(e){}
      }
    }, false);
  }catch(e){/* ignore */}
})();

// Level editor removed: editor UI and canvas editing handlers were deleted
// boot
// (removed START_SESSION handler - sessionStart set when user interacts inside iframe)

// boot
loadStoredLevels();
initUI();
if(!localStorage.getItem('unlocked')) localStorage.setItem('unlocked', JSON.stringify([0]));
getNormalizedUnlocked();
populateLevels();

function bootBlocklyWithRetry(attempt = 0){
  const ok = initBlockly();
  if(ok){
    loadLevel(0);
    return;
  }
  if(attempt >= 30){
    const statusEl = document.getElementById('status');
    if(statusEl) statusEl.textContent = 'Blok paneli yüklenemedi. Sayfayı yenileyin.';
    return;
  }
  setTimeout(() => bootBlocklyWithRetry(attempt + 1), 200);
}

bootBlocklyWithRetry(0);
resolveAssignmentRangeFromGrant();










