(() => {
  const $ = (id) => document.getElementById(id);
  const params = new URLSearchParams(location.search);
  const role = (params.get('role') || 'student').toLowerCase();
  const isStaff = role === 'admin' || role === 'teacher';
  const assignmentId = params.get('assignmentId') || '';
  const STORAGE_KEY = 'web_lab_progress_v2';

  // 100 seviyelik öğrenme yolu levels.js içinde tanımlıdır (tools/learn-levels/web-lab.gen.mjs ile üretilir).
  const levels = window.WEB_LAB_LEVELS || [];

  const els={editor:$('editor'),lines:$('lineNumbers'),preview:$('preview'),list:$('levelList'),title:$('levelTitle'),tag:$('levelTag'),story:$('levelStory'),goal:$('levelGoal'),hints:$('hints'),result:$('result'),next:$('nextBtn'),prev:$('prevBtn'),xp:$('xpTotal'),progressText:$('progressText'),progressBar:$('progressBar')};
  const rangeStart=Math.max(0,Math.min(levels.length-1,(Number(params.get('from'))||1)-1));
  const rangeEnd=Math.max(rangeStart,Math.min(levels.length-1,(Number(params.get('to'))||levels.length)-1));
  let state=loadState(), current=rangeStart, file='html', startedAt=Date.now(), timer, initialized=false;

  function loadState(){try{const v=JSON.parse(localStorage.getItem(STORAGE_KEY)||'{}');return {completed:Array.isArray(v.completed)?v.completed:[],drafts:v.drafts||{}}}catch(_){return {completed:[],drafts:{}}}}
  function saveState(){try{localStorage.setItem(STORAGE_KEY,JSON.stringify(state))}catch(_){}}
  function filesFor(index){return state.drafts[index]||{...levels[index].files}}
  function saveEditor(){const files=filesFor(current);files[file]=els.editor.value;state.drafts[current]=files;saveState()}
  function canOpen(index){if(isStaff)return true;if(index<rangeStart||index>rangeEnd)return false;if(index===rangeStart)return true;return state.completed.includes(index-1)||state.completed.includes(index)}
  function renderList(){els.list.innerHTML='';levels.forEach((l,i)=>{const b=document.createElement('button');b.type='button';b.className='level-item'+(i===current?' active':'')+(state.completed.includes(i)?' done':'');b.disabled=!canOpen(i);b.innerHTML=`<span class="level-no">${state.completed.includes(i)?'✓':i+1}</span><span class="level-copy"><strong>${l.title}</strong><small>${l.track} · ${l.xp} XP</small></span>`;b.onclick=()=>openLevel(i);els.list.appendChild(b)});const act=els.list.querySelector&&els.list.querySelector('.level-item.active');if(act&&act.scrollIntoView)act.scrollIntoView({block:'nearest'})}
  function updateProgress(){const inRange=state.completed.filter(i=>i>=rangeStart&&i<=rangeEnd).length,total=rangeEnd-rangeStart+1,xp=state.completed.reduce((sum,i)=>sum+(levels[i]?.xp||0),0);els.progressText.textContent=`${inRange} / ${total} tamamlandı`;els.progressBar.style.width=(inRange/total*100)+'%';els.xp.textContent=xp}
  function openLevel(index){if(!canOpen(index))return;if(initialized)saveEditor();initialized=true;current=index;file='html';startedAt=Date.now();const l=levels[index];els.title.textContent=l.title;els.tag.textContent=`${l.track} · Seviye ${index+1}`;els.story.textContent=l.story;els.goal.textContent=l.goal;els.hints.innerHTML=l.hints.map(h=>`<span class="hint">${escapeHtml(h)}</span>`).join('');document.querySelectorAll('.tab').forEach(t=>t.classList.toggle('active',t.dataset.file===file));els.result.className='result';els.result.textContent='';els.prev.disabled=index<=rangeStart;els.next.disabled=!state.completed.includes(index)||index>=rangeEnd;els.next.textContent=index>=rangeEnd?'Öğrenme yolu tamamlandı':'Sonraki seviye →';loadEditor();renderList();runCode()}
  function loadEditor(){els.editor.value=filesFor(current)[file]||'';updateLines()}
  function updateLines(){els.lines.textContent=Array.from({length:Math.max(1,els.editor.value.split('\n').length)},(_,i)=>i+1).join('\n')}
  function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
  function sourceDoc(){const f=filesFor(current);let html=f.html||'';const styles=`<style>${f.css||''}</style>`;const script=`<script>window.onerror=function(m){document.body.insertAdjacentHTML('beforeend','<pre style="color:#b91c1c;padding:10px">JS Hatası: '+m+'</pre>')};<\/script><script>${f.js||''}<\/script>`;if(/<\/head>/i.test(html))html=html.replace(/<\/head>/i,styles+'</head>');else html=styles+html;if(/<\/body>/i.test(html))html=html.replace(/<\/body>/i,script+'</body>');else html+=script;return html}
  function runCode(){saveEditor();els.preview.srcdoc=sourceDoc()}
  function checkLevel(){runCode();const f=filesFor(current),doc=new DOMParser().parseFromString(f.html||'','text/html');const error=levels[current].check({doc,html:f.html||'',css:f.css||'',js:f.js||''});els.result.className='result show '+(error?'fail':'ok');els.result.textContent=error?'Henüz olmadı: '+error:'✓ '+levels[current].success;if(error)return;if(!state.completed.includes(current)){state.completed.push(current);state.completed.sort((a,b)=>a-b);saveState();postCompletion()}renderList();updateProgress();els.next.disabled=current>=rangeEnd}
  function postCompletion(){const done=state.completed.filter(i=>i>=rangeStart&&i<=rangeEnd),total=rangeEnd-rangeStart+1,xpEarned=done.reduce((s,i)=>s+levels[i].xp,0),elapsedSeconds=Math.max(0,Math.round((Date.now()-startedAt)/1000));const message={type:'LEVEL_COMPLETED',source:'web-lab-runner',levelId:current+1,currentLevelIndex:current,xp:levels[current].xp,duration:Date.now()-startedAt,elapsedSeconds,completedLevelIds:done.map(i=>i+1)};window.parent?.postMessage(message,'*');window.parent?.postMessage({type:'GAME_UPDATE',source:'web-lab-runner',levels:levels.map((l,i)=>({id:i+1,name:l.title,xp:l.xp})),currentLevelIndex:current,completedLevelIds:done.map(i=>i+1),xpEarned,progressPercent:Math.round(done.length/total*100)},'*');if(current>=rangeEnd&&done.length>=total)window.parent?.postMessage({type:'ASSIGNMENT_RANGE_COMPLETED',source:'web-lab-runner',currentLevelIndex:current,xp:xpEarned,elapsedSeconds,completedLevelIds:done.map(i=>i+1),assignmentId},'*')}

  document.querySelectorAll('.tab').forEach(t=>t.onclick=()=>{saveEditor();file=t.dataset.file;document.querySelectorAll('.tab').forEach(x=>x.classList.toggle('active',x===t));loadEditor()});
  els.editor.addEventListener('input',()=>{updateLines();saveEditor();clearTimeout(timer);timer=setTimeout(runCode,500)});
  els.editor.addEventListener('keydown',e=>{if(e.key==='Tab'){e.preventDefault();const s=els.editor.selectionStart;els.editor.setRangeText('  ',s,els.editor.selectionEnd,'end');els.editor.dispatchEvent(new Event('input'))}});
  $('runBtn').onclick=runCode;$('refreshBtn').onclick=runCode;$('checkBtn').onclick=checkLevel;
  $('resetBtn').onclick=()=>{if(confirm('Bu seviyedeki kodların başlangıç haline dönsün mü?')){delete state.drafts[current];saveState();loadEditor();runCode()}};
  els.prev.onclick=()=>openLevel(current-1);els.next.onclick=()=>{if(current<rangeEnd&&state.completed.includes(current))openLevel(current+1)};
  $('levelsToggle').onclick=()=>$('sidebar').classList.toggle('open');
  $('startBtn').onclick=()=>{$('welcome').classList.add('hidden');try{localStorage.setItem('web_lab_welcome_seen','1')}catch(_){}};
  if(localStorage.getItem('web_lab_welcome_seen')==='1')$('welcome').classList.add('hidden');
  updateProgress();openLevel(rangeStart);
})();
