(function () {
  'use strict';
  var $ = function (s) { return document.querySelector(s); };
  var KB = window.KBVisual, BYCH = KB.BYCH, BYCODE = KB.BYCODE, COLORS = KB.COLORS, FNAME = KB.FNAME;
  var kb = KB.mount($('#kb'), $('#hands'));
  var TOTAL = 1000;
  /* aralık modu: canlı yarışma (assignmentId=competition-N) ve ödev (hw-N / ga-N) + from=a&to=b.
     Bu modlarda oyun menüsü yoktur: öğrenci aralığın İLK bölümünden başlar, bölümler yapıldıkça sırayla açılır. */
  var qp = new URLSearchParams(location.search), asg = qp.get('assignmentId') || '';
  var comp = /^competition-\d+$/.test(asg), asgMode = /^(hw|ga)-\d+$/.test(asg), rangeMode = comp || asgMode, cFrom = Math.max(1, Math.min(TOTAL, parseInt(qp.get('from'), 10) || 1));
  var cTo = Math.max(cFrom, Math.min(TOTAL, parseInt(qp.get('to'), 10) || cFrom));
  function post(m) { try { if (window.parent && window.parent !== window) window.parent.postMessage(m, '*'); } catch (e) {} }
  if (rangeMode) document.body.classList.add('comp');

  /* ================= müfredat: sıralar (dünyalar) ================= */
  var WORLDS = [
    { id: 'home', em: '🏠', n: 'Ana Sıra', d: 'F J D K S L A Ş G H İ — parmakların evi', c: 25, kind: 'keys', keys: 'fjdkslaşghi', col: '#1dd1a1', wpm: [6, 16] },
    { id: 'top', em: '🔼', n: 'Üst Sıra', d: 'R U E I T Y W O Q P Ğ Ü', c: 25, kind: 'keys', keys: 'rueıtywoqpğü', col: '#54a0ff', wpm: [8, 18] },
    { id: 'bottom', em: '🔽', n: 'Alt Sıra', d: 'V N C M X Ö Z Ç B . ,', c: 25, kind: 'keys', keys: 'vncmxözçb.,', col: '#a29bfe', wpm: [8, 18] },
    { id: 'nums', em: '🔢', n: 'Sayı Sırası', d: '4 7 3 8 2 9 1 0 5 6 * -', c: 25, kind: 'keys', keys: '473829105 6*-'.replace(' ', ''), col: '#feca57', wpm: [6, 15] },
    { id: 'all', em: '🔤', n: 'Tüm Harfler', d: 'Bütün harfler karışık gruplar', c: 50, kind: 'letters', col: '#ff78c4', wpm: [10, 22] },
    { id: 'short', em: '🌱', n: 'Kısa Kelimeler', d: '2-4 harfli kelimeler', c: 100, kind: 'words', b: 'short', col: '#34d399', wpm: [12, 26] },
    { id: 'mid', em: '🌿', n: 'Orta Kelimeler', d: '5-7 harfli kelimeler', c: 100, kind: 'words', b: 'mid', col: '#22c55e', wpm: [18, 33] },
    { id: 'long', em: '🌳', n: 'Uzun Kelimeler', d: '8+ harfli kelimeler', c: 100, kind: 'words', b: 'long', col: '#16a34a', wpm: [22, 38] },
    { id: 'numw', em: '🔟', n: 'Sayılı Çalışma', d: 'Kelimeler ve sayılar birlikte', c: 50, kind: 'numw', col: '#f59e0b', wpm: [20, 35] },
    { id: 'sent1', em: '💬', n: 'Kısa Cümleler', d: 'Üç dört kelimelik cümleler', c: 100, kind: 'sent', long: false, col: '#38bdf8', wpm: [20, 38] },
    { id: 'sent2', em: '📜', n: 'Uzun Cümleler', d: 'Birleşik ve uzun cümleler', c: 100, kind: 'sent', long: true, col: '#0ea5e9', wpm: [25, 45] },
    { id: 'punct', em: '✒️', n: 'Noktalama', d: 'Virgül ve nokta ile cümleler', c: 50, kind: 'punct', col: '#8b5cf6', wpm: [25, 45] },
    { id: 'speed', em: '⚡', n: 'Hız Antrenmanı', d: 'Karışık uzun metinler', c: 100, kind: 'mix', len: [80, 200], col: '#f97316', wpm: [30, 50] },
    { id: 'master', em: '🏆', n: 'Usta Yarışı', d: 'Zorlu ve uzun metinler', c: 100, kind: 'mix', len: [130, 300], col: '#ef4444', wpm: [40, 65] },
    { id: 'legend', em: '👑', n: 'Efsane', d: 'Klavye ustaları için', c: 50, kind: 'mix', len: [220, 380], col: '#fbbf24', wpm: [55, 90] }
  ];
  (function () { var s = 1; WORLDS.forEach(function (w) { w.s = s; w.e = s + w.c - 1; s += w.c; }); if (s - 1 !== TOTAL) throw new Error('seviye sayısı ' + (s - 1)); })();
  function worldOf(n) { for (var i = 0; i < WORLDS.length; i++) if (n <= WORLDS[i].e) return i; return WORLDS.length - 1; }

  /* ================= metin havuzları ================= */
  var WORDLIST = ('ada ağ arı at ay ev el ben biz sen siz oku yaz çiz say gel git al ver kal dur koş uç göz kol saç diş dil baş kar gül dal yol gün kış süt çay bal tuz yağ su ateş kedi kuş kuzu anne baba dede nine abla top ip il ot un üç dört beş altı yedi sekiz dokuz on bir iki yüz bin ' +
    'masa kalem kitap okul sınıf tahta defter silgi çanta kapı pencere bahçe çiçek ağaç orman nehir deniz göl yıldız güneş bulut yağmur rüzgar fırtına elma armut üzüm kiraz limon şeker ekmek peynir yumurta çorba pilav makarna salata balık tavuk köpek aslan kaplan fil zürafa maymun tavşan kurbağa uçak gemi tren araba otobüs robot ekran klavye fare yazıcı program dosya klasör sayfa resim müzik video oyun proje kod döngü veri tablo hesap formül arkadaş öğrenci ders ödev sınav başarı sanat spor futbol yüzme satranç sevgi saygı dostluk yazılım donanım işlemci bellek merhaba kahraman mutlu hızlı yavaş güzel büyük küçük sarı kırmızı yeşil mavi turuncu mor beyaz siyah ' +
    'öğretmen bilgisayar kütüphane matematik çalışkan arkadaşlık sorumluluk dürüstlük yardımlaşma programlama teknoloji algoritma fonksiyon değişken bisiklet basketbol voleybol kaplumbağa internet kahvaltı öğretmenler bilgisayarlar uygulama tarayıcı dayanışma cumhuriyet çocukluk gökyüzü mühendis yıldızlar yaratıcı başarılı okuyucu çalışmak öğrenmek düşünmek tasarlamak geliştirmek paylaşmak').split(' ');
  var B = { short: [], mid: [], long: [] };
  WORDLIST.forEach(function (w) { (w.length <= 4 ? B.short : w.length <= 7 ? B.mid : B.long).push(w); });
  var TIMES = ['bugün', 'yarın', 'sabah', 'akşam', 'her gün', 'okulda', 'evde', 'bahçede', 'teneffüste', 'hafta sonu'];
  var SUBJ = ['ali', 'ayşe', 'kedi', 'öğretmen', 'annem', 'babam', 'kardeşim', 'arkadaşım', 'robot', 'zeynep', 'mehmet', 'öğrenciler'];
  var PAIRS = [['kitap', 'okur'], ['süt', 'içer'], ['resim', 'çizer'], ['top', 'oynar'], ['ödev', 'yapar'], ['müzik', 'dinler'], ['kod', 'yazar'], ['çiçek', 'sular'], ['film', 'izler'], ['yemek', 'yer'], ['şarkı', 'söyler'], ['proje', 'hazırlar'], ['bisiklet', 'sürer'], ['oyun', 'tasarlar'], ['mektup', 'yazar'], ['çorba', 'içer']];
  var FIXED = ['doğru parmakla yazmak çok kolay', 'klavyeyi hızlı kullanırım', 'her gün biraz daha hızlanırım', 'bilgisayar bize çok yardım eder', 'arı bahçede çiçek arar', 'robot bana el sallıyor', 'okulda yeni şeyler öğrenirim', 'gökyüzünde yıldızlar parlıyor', 'sabırla çalışan başarır', 'bugün hava çok güzel'];
  function pick(a) { return a[Math.floor(Math.random() * a.length)]; }
  function rnd(a, b) { return a + Math.floor(Math.random() * (b - a + 1)); }
  function lerp(w, i) { return w.c > 1 ? i / (w.c - 1) : 0; }
  function words(bucket, n) { var o = []; for (var i = 0; i < n; i++) o.push(pick(bucket)); return o.join(' '); }
  function sentence(long) {
    var p = pick(PAIRS), s = pick(SUBJ), t = Math.random() < .5 ? pick(TIMES) + ' ' : '';
    var base = t + s + ' ' + p[0] + ' ' + p[1];
    if (!long) return Math.random() < .3 ? pick(FIXED) : base;
    var q = pick(PAIRS), s2 = pick(SUBJ);
    return base + ' ve ' + s2 + ' ' + q[0] + ' ' + q[1];
  }
  function punct() {
    var p = pick(PAIRS), q = pick(PAIRS), t = pick(TIMES), s = pick(SUBJ), s2 = pick(SUBJ);
    var forms = [t + ', ' + s + ' ' + p[0] + ' ' + p[1] + '.', s + ' ' + p[0] + ' ' + p[1] + ', ' + s2 + ' ' + q[0] + ' ' + q[1] + '.', pick(WORDLIST) + ', ' + pick(WORDLIST) + ', ' + pick(WORDLIST) + ' ve ' + pick(WORDLIST) + '.'];
    return pick(forms);
  }
  function num() { return String(rnd(0, 1) ? rnd(1, 99) : rnd(100, 999)); }
  function mix(len) {
    var out = '';
    while (out.length < len) {
      var r = Math.random(), chunk;
      if (r < .45) chunk = sentence(Math.random() < .5); else if (r < .7) chunk = words(B.mid.concat(B.long), rnd(3, 6)); else if (r < .85) chunk = punct(); else chunk = num() + ' ' + pick(B.short) + ' ' + num() + ' ' + pick(B.mid);
      out += (out ? ' ' : '') + chunk;
    }
    return out.slice(0, len).replace(/\s+$/, '');
  }

  /* ================= bölüm üretici ================= */
  var keysBefore = [];
  (function () { var acc = ''; WORLDS.forEach(function (w) { w.before = acc; if (w.kind === 'keys') acc += w.keys; }); })();
  function level(n) {
    var wi = worldOf(n), w = WORLDS[wi], i = n - w.s, p = lerp(w, i);
    var L = { n: n, w: wi, i: i, title: w.n + ' · ' + (i + 1) + '/' + w.c, mode: 'text', newKey: null, pool: null, target: Math.round(w.wpm[0] + (w.wpm[1] - w.wpm[0]) * p), boss: (i + 1) % 10 === 0 || i === w.c - 1 };
    var t = '';
    if (w.kind === 'keys') {
      var keys = w.keys.split(''), m = keys.length, steps = Math.min(m - 1, 16);
      var a = Math.min(m, 2 + Math.floor(i * (m - 1) / Math.max(1, Math.min(w.c - 9, 16))));
      var pa = i === 0 ? 0 : Math.min(m, 2 + Math.floor((i - 1) * (m - 1) / Math.max(1, Math.min(w.c - 9, 16))));
      if (i === 0) L.newKey = keys[0] + ' ve ' + keys[1]; else if (a > pa) L.newKey = keys[a - 1];
      var pool = keys.slice(0, a), review = w.before ? w.before.split('') : [];
      L.pool = pool.concat(w.before ? review : []);
      var tile = i < 6, len = tile ? 18 + i : 30 + i * 2;
      var newest = keys[a - 1];
      var gen = function () { var r = Math.random(); if (review.length && i >= 8 && r < .18) return pick(review); if (r < .38) return newest; return pick(pool); };
      if (tile) { var last = ''; for (var k = 0; k < len; k++) { var c; var g = 0; do { c = gen(); g++; } while (c === last && g < 6); t += c; last = c; } L.mode = 'tile'; }
      else { while (t.length < len) { var gl = rnd(2, 5), grp = ''; for (var q = 0; q < gl; q++) grp += gen(); t += (t ? ' ' : '') + grp; } t = t.slice(0, len).trim(); }
    } else if (w.kind === 'letters') {
      var all = 'abcçdefgğhıijklmnoöprsştuüvyz'.split(''), len2 = 40 + Math.floor(i * 1.2);
      while (t.length < len2) { var l = rnd(3, 6), gg = ''; for (var z = 0; z < l; z++) gg += pick(all); t += (t ? ' ' : '') + gg; } t = t.slice(0, len2).trim();
    } else if (w.kind === 'words') {
      var cnt = w.b === 'long' ? 6 + Math.floor(i / 8) : 8 + Math.floor(i / (w.b === 'mid' ? 6 : 5));
      var bucket = w.b === 'long' ? B.long.concat(B.long, B.mid.slice(0, 40)) : B[w.b];
      t = words(bucket, cnt);
    } else if (w.kind === 'numw') {
      var pairs = 4 + Math.floor(i / 8), parts = []; for (var y = 0; y < pairs; y++) parts.push(num(), pick(y % 2 ? B.mid : B.short)); t = parts.join(' ');
    } else if (w.kind === 'sent') {
      var sc = (w.long ? 3 : 2) + Math.floor(i / 20); var arr = []; for (var u = 0; u < sc; u++) arr.push(sentence(w.long)); t = arr.join(' ');
    } else if (w.kind === 'punct') {
      var pc = 3 + Math.floor(i / 12); var ar2 = []; for (var v = 0; v < pc; v++) ar2.push(punct()); t = ar2.join(' ');
    } else {
      t = mix(Math.round(w.len[0] + (w.len[1] - w.len[0]) * p));
    }
    L.text = t;
    return L;
  }

  /* ================= ilerleme ================= */
  var store = { done: {}, open: false };
  var KB_KEY = 'bk_kbfinger2' + (function () { try { var a = new URLSearchParams(location.search).get('assignmentId') || ''; return /^(competition|hw|ga)-\d+$/.test(a) ? ':' + a : ''; } catch (e) { return ''; } })();
  try { var sv = JSON.parse(localStorage.getItem(KB_KEY) || 'null'); if (sv && sv.done) store = sv; } catch (e) {}
  function persist() { try { localStorage.setItem(KB_KEY, JSON.stringify(store)); } catch (e) {} }
  function maxDone() { var m = 0; Object.keys(store.done).forEach(function (k) { if (store.done[k].s >= 1 && +k > m) m = +k; }); return m; }
  function isOpen(n) { return store.open || n === 1 || !!(store.done[n - 1] && store.done[n - 1].s >= 1) || !!store.done[n]; }
  function stars(n) { return store.done[n] ? store.done[n].s : 0; }
  function nextLevel() { for (var n = 1; n <= TOTAL; n++) if (!store.done[n] || store.done[n].s < 1) return n; return TOTAL; }

  /* ================= ekranlar ================= */
  var view = 'menu', curWorld = 0, cur = null;
  function show(v) { view = v; ['menu', 'world', 'play'].forEach(function (x) { $('#' + x).hidden = x !== v; }); window.scrollTo(0, 0); }
  function renderMenu() {
    var done = 0, st = 0, best = 0; Object.keys(store.done).forEach(function (k) { var d = store.done[k]; if (d.s >= 1) done++; st += d.s; if (d.w > best) best = d.w; });
    $('#sumDone').textContent = done; $('#sumStars').textContent = st; $('#sumWpm').textContent = best;
    $('#optOpen').checked = !!store.open;
    $('#btnContinue').textContent = done >= TOTAL ? '🎉 Hepsi tamam — tekrar oyna' : '▶ Devam et (Bölüm ' + nextLevel() + ')';
    var h = '';
    WORLDS.forEach(function (w, i) {
      var d = 0, s = 0; for (var n = w.s; n <= w.e; n++) { if (stars(n) >= 1) d++; s += stars(n); }
      var locked = !isOpen(w.s);
      h += '<button class="wcard' + (locked ? ' locked' : '') + '" data-w="' + i + '"><span class="band" style="background:' + w.col + '"></span><span class="st">★ ' + s + '/' + w.c * 3 + '</span><div class="em">' + (locked ? '🔒' : w.em) + '</div><h3>' + (i + 1) + '. ' + w.n + '</h3><small>' + w.d + '</small><div class="rng">Bölüm ' + w.s + '–' + w.e + ' · ' + d + '/' + w.c + ' tamam</div><div class="pbar"><i style="width:' + (d / w.c * 100) + '%"></i></div></button>';
    });
    $('#worlds').innerHTML = h;
  }
  $('#worlds').addEventListener('click', function (e) { var b = e.target.closest('.wcard'); if (!b || b.classList.contains('locked')) return; openWorld(+b.dataset.w); });
  function openWorld(i) {
    curWorld = i; var w = WORLDS[i], nx = nextLevel(), h = '', d = 0;
    for (var n = w.s; n <= w.e; n++) {
      var s = stars(n), op = isOpen(n); if (s >= 1) d++;
      var idx = n - w.s, boss = (idx + 1) % 10 === 0 || idx === w.c - 1;
      h += '<button class="node' + (s >= 1 ? ' done' : '') + (n === nx ? ' next' : '') + (!op ? ' locked' : '') + (boss ? ' boss' : '') + '" data-n="' + n + '">' + n + '<small>' + (op ? ('★'.repeat(s) + '☆'.repeat(3 - s)) : '🔒') + '</small></button>';
    }
    $('#wName').textContent = (i + 1) + '. ' + w.em + ' ' + w.n; $('#wDesc').textContent = w.d; $('#wProg').textContent = d + ' / ' + w.c + ' tamam';
    $('#nodes').innerHTML = h; show('world');
    var nn = $('#nodes .node.next'); if (nn && nn.scrollIntoView) nn.scrollIntoView({ block: 'center' });
  }
  $('#nodes').addEventListener('click', function (e) { var b = e.target.closest('.node'); if (!b || b.classList.contains('locked')) return; startLevel(+b.dataset.n); });
  $('#btnBackMenu').addEventListener('click', function () { renderMenu(); show('menu'); });
  $('#btnBackWorld').addEventListener('click', backToWorld);
  $('#btnWorld').addEventListener('click', backToWorld);
  function backToWorld() { stopTimer(); $('#result').hidden = true; openWorld(worldOf(cur ? cur.n : 1)); }
  $('#btnContinue').addEventListener('click', function () { var n = nextLevel(); startLevel(n); });
  $('#optOpen').addEventListener('change', function () { store.open = this.checked; persist(); renderMenu(); });
  $('#btnReset').addEventListener('click', function () { if (confirm('Tüm ilerleme silinsin mi?')) { store = { done: {}, open: false }; persist(); renderMenu(); } });

  /* ================= oyun ================= */
  var text = '', pos = 0, errors = 0, typed = 0, startAt = 0, running = false, finished = false, timer = null, finalSec = 0, started = false;
  function stopTimer() { clearInterval(timer); running = false; }
  function startLevel(n) {
    cur = level(n); show('play'); $('#result').hidden = true;
    stopTimer(); text = cur.text; pos = 0; errors = 0; typed = 0; startAt = 0; finished = false; started = false; finalSec = 0;
    $('#pTitle').textContent = 'Bölüm ' + n + ' · ' + cur.title.split(' · ')[0] + (cur.boss ? ' 👑' : '');
    $('#pGoal').textContent = '★★★ için: ≥ %97 doğruluk ve ' + cur.target + ' kelime/dk · geçmek için ≥ %80';
    var kk = kb.keyEls(); Object.keys(kk).forEach(function (c) { kk[c].classList.remove('off'); });
    if (cur.pool) { Object.keys(kk).forEach(function (c) { var k = BYCODE[c]; if (/mod/.test(k.cls) || k.ch === ' ') return; if (cur.pool.indexOf(k.ch) < 0) kk[c].classList.add('off'); }); }
    $('#msg').innerHTML = cur.newKey ? 'Yeni tuş: <b>' + cur.newKey.toLocaleUpperCase('tr') + '</b>' + (BYCH[cur.newKey] ? ' — ' + FNAME[BYCH[cur.newKey].f] : '') + '<br>Başlamak için tuşa bas ya da Başla\'ya tıkla' : 'Başlamak için tuşa bas ya da Başla\'ya tıkla';
    $('#btnStart').style.display = ''; $('#handNote').textContent = 'Yanlış basınca doğru parmak burada yanar.';
    renderTarget(); hint(); updStats();
  }
  function renderTarget() {
    var el = $('#target'); el.classList.toggle('tile', cur.mode === 'tile');
    if (cur.mode === 'tile') {
      var ch = text.charAt(pos), k = BYCH[ch];
      el.innerHTML = ch && k ? '<div class="keytile" style="background:' + COLORS[k.f] + '">' + ch.toLocaleUpperCase('tr') + '</div>' : '';
    } else {
      var h = '';
      var inW = false;
      for (var i = 0; i < text.length; i++) {
        var c = text.charAt(i), cl = 'ch' + (i < pos ? ' done' : i === pos ? ' cur' : '') + (c === ' ' ? ' sp' : '');
        if (c !== ' ' && !inW) { h += '<span class="wd">'; inW = true; }
        if (c === ' ' && inW) { h += '</span>'; inW = false; }
        h += '<span class="' + cl + '">' + (c === ' ' ? '&nbsp;' : c) + '</span>';
      }
      if (inW) h += '</span>';
      el.innerHTML = h; var cu = el.querySelector('.cur'); if (cu) el.scrollTop = Math.max(0, cu.offsetTop - el.offsetTop - 60);
    }
    $('#progBar').style.width = (pos / text.length * 100) + '%';
  }
  function hint() { if (finished) { kb.hint('', false); return; } kb.hint(text.charAt(pos), $('#optHints').checked); }
  $('#optHints').addEventListener('change', hint);
  function accNow() { return typed ? Math.round((typed - errors) / typed * 100) : 0; }
  function updStats() {
    var acc = accNow(), sec = running ? (Date.now() - startAt) / 1000 : (finished ? finalSec : 0);
    $('#acc').textContent = '%' + acc; $('#errs').textContent = errors + ' hata'; $('#accBar').style.width = acc + '%';
    var c = $('#cardAcc'); c.classList.toggle('zero', !typed); c.classList.toggle('good', !!typed && acc >= 95); c.classList.toggle('mid', !!typed && acc < 95 && acc >= 80); c.classList.toggle('bad', !!typed && acc < 80);
    $('#time').textContent = Math.floor(sec / 60) + ':' + ('0' + Math.floor(sec % 60)).slice(-2);
    $('#wpm').textContent = sec > 2 ? Math.round(pos / 5 / (sec / 60)) : 0;
  }
  function begin() { if (running || finished) return; running = true; started = true; startAt = Date.now(); $('#btnStart').style.display = 'none'; $('#msg').textContent = 'Parmak ucunu ışıklı tuşa götür!'; timer = setInterval(updStats, 300); }
  $('#btnStart').addEventListener('click', begin);

  function press(code) {
    if (finished || !cur) return;
    var k = BYCODE[code]; if (!k || /^(Shift|Backspace|Enter)/.test(code)) return;
    begin(); typed++;
    var want = text.charAt(pos), wk = BYCH[want];
    kb.flash(code, 'press', 90);
    if (wk && wk.code === code) {
      pos++;
      var nk = BYCH[text.charAt(pos)];
      $('#handNote').textContent = 'Harika!' + (nk ? ' Sıradaki: ' + FNAME[nk.f] + '.' : '');
      if (pos >= text.length) return finish();
      renderTarget(); hint();
    } else {
      errors++; kb.flash(code, 'wrong', 250);
      var cu = $('#target .cur'); if (cu) { cu.classList.add('bad'); setTimeout(function () { cu.classList.remove('bad'); }, 260); }
      if (wk) { $('#handNote').textContent = 'Yanlış! Doğru tuş için ' + FNAME[wk.f] + ' kullan.'; kb.showFinger(wk.f); }
    }
    updStats();
  }
  document.addEventListener('keydown', function (e) {
    if (view !== 'play' || e.ctrlKey || e.metaKey || e.altKey) return;
    if (!$('#result').hidden) { if (e.key === 'Enter') $('#btnNext').click(); return; }
    if (e.code === 'Space' || /^(Backspace|Quote|Slash)$/.test(e.code)) e.preventDefault();
    press(e.code);
  });
  // Ekrandaki klavye: PC'de yalnızca gerçek klavye; dokunmatik cihazda ekran tuşları çalışır.
  // Telefon/tablette dokunma kabul edilir; fare (PC) yok sayılır.
  $('#kb').addEventListener('mousedown', function (e) { e.preventDefault(); });
  $('#kb').addEventListener('pointerdown', function (e) {
    if (e.pointerType !== 'touch' && e.pointerType !== 'pen') return;
    if (view !== 'play' || !$('#result').hidden) return;
    e.preventDefault();
    var el = e.target.closest ? e.target.closest('.k') : null;
    if (el && el.dataset.code) press(el.dataset.code);
  });

  function finish() {
    finished = true; stopTimer(); finalSec = Math.max(1, (Date.now() - startAt) / 1000);
    var acc = accNow(), wpm = Math.round(text.length / 5 / (finalSec / 60));
    var s = acc >= 97 && wpm >= cur.target ? 3 : acc >= 92 && wpm >= cur.target * .7 ? 2 : acc >= 80 ? 1 : 0;
    renderTarget(); hint(); updStats();
    var old = store.done[cur.n];
    if (!old || s > old.s || (s === old.s && wpm > old.w)) store.done[cur.n] = { s: s, w: wpm, a: acc };
    else if (old && wpm > old.w) old.w = wpm;
    persist();
    if (rangeMode && s > 0) {
      var doneN = cur.n - cFrom + 1, totN = cTo - cFrom + 1;
      post({ type: 'LEVEL_COMPLETED', source: 'keyboard-finger-runner', assignmentId: asg, levelId: String(cur.n), levelNo: cur.n, levelStart: cFrom, levelEnd: cTo, currentLevelIndex: cur.n - 1, progressPercent: Math.round(doneN / totN * 100), xp: s * 10, stars: s, elapsedSeconds: Math.round(finalSec) });
      if (cur.n >= cTo && rangeAllDone()) post({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'keyboard-finger-runner', assignmentId: asg, levelStart: cFrom, levelEnd: cTo, xp: 0, currentLevelIndex: cur.n - 1 });
    }
    $('#stars').textContent = '★'.repeat(s) + '☆'.repeat(3 - s);
    $('#resTitle').textContent = s === 3 ? 'Mükemmel!' : s === 2 ? 'Çok iyi!' : s === 1 ? 'Bölüm geçildi!' : 'Tekrar dene';
    $('#resText').textContent = 'Doğruluk %' + acc + ' · ' + wpm + ' kelime/dk · ' + Math.round(finalSec) + ' sn · ' + errors + ' hata';
    $('#resGoal').textContent = s === 0 ? 'Geçmek için en az %80 doğruluk gerekir.' : s < 3 ? '3 yıldız için %97 doğruluk ve ' + cur.target + ' kelime/dk gerekir.' : 'Hedefi aştın!';
    $('#btnNext').style.display = s >= 1 && cur.n < (rangeMode ? cTo : TOTAL) ? '' : 'none';
    $('#result').hidden = false;
  }
  $('#btnAgain').addEventListener('click', function () { startLevel(cur.n); });
  $('#btnNext').addEventListener('click', function () { if (cur.n < TOTAL) startLevel(cur.n + 1); });

  // Aralıktaki tüm bölümler (en az 1 yıldızla) tamamlandı mı?
  function rangeAllDone() { for (var n = cFrom; n <= cTo; n++) if (!store.done[n] || store.done[n].s < 1) return false; return true; }
  // Aralık modunda her zaman aralığın ilk bitmemiş bölümünden başlar (yenilenirse kaldığı yerden devam eder).
  function rangeStartLevel() { for (var n = cFrom; n <= cTo; n++) if (!store.done[n] || store.done[n].s < 1) return n; return cTo; }
  if (rangeMode) startLevel(rangeStartLevel()); else { renderMenu(); show('menu'); }
  window.KBF = { level: level, WORLDS: WORLDS };
})();
