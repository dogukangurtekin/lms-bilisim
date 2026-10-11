<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Bakım Çalışması · {{ config('app.name', 'Eğitim Portalı') }}</title>
<style>
:root{
  --bg1:#1b1464;--bg2:#4a2fd6;--bg3:#0ea5e9;
  --card:rgba(255,255,255,.94);--ink:#1e1b4b;--soft:#5b5b8a;
  --accent:#f59e0b;--ok:#10b981;--bad:#ef4444;--violet:#5b3df5;
}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%}
body{
  font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif;color:var(--ink);
  background:linear-gradient(135deg,var(--bg1),var(--bg2) 55%,var(--bg3));background-attachment:fixed;
  overflow-x:hidden;position:relative;
}
/* Arka planda süzülen ders/kod simgeleri */
.floaters{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.floaters span{
  position:absolute;bottom:-60px;font-weight:800;color:rgba(255,255,255,.16);
  animation:rise linear infinite;user-select:none
}
@keyframes rise{to{transform:translateY(-115vh) rotate(25deg)}}
.wrap{position:relative;z-index:1;max-width:980px;margin:0 auto;padding:28px 16px 48px;display:grid;gap:18px}
.topbar{display:flex;justify-content:flex-start}
.home-btn{display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--violet);font-weight:800;text-decoration:none;padding:10px 18px;border-radius:999px;box-shadow:0 8px 20px rgba(15,10,70,.3);transition:.15s}
.home-btn:hover{transform:translateY(-2px);background:#fef3c7;color:#92400e}
.hero{text-align:center;color:#fff;padding-top:8px}
.badge{
  display:inline-block;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);
  padding:6px 14px;border-radius:999px;font-size:13px;font-weight:700;letter-spacing:.4px
}
h1{font-size:clamp(26px,5vw,44px);margin:12px 0 8px;line-height:1.15}
.hero p{margin:0 auto;max-width:620px;font-size:clamp(15px,2.4vw,18px);opacity:.95;line-height:1.55}
.note{
  margin:14px auto 0;max-width:620px;background:rgba(255,255,255,.16);border:1px dashed rgba(255,255,255,.5);
  padding:10px 14px;border-radius:14px;font-weight:600
}

/* Robot */
.stage{display:flex;justify-content:center;margin:6px 0 0}
.robot{width:min(300px,78vw);height:auto;overflow:visible}
.bob{animation:bob 2.6s ease-in-out infinite;transform-origin:50% 50%}
@keyframes bob{50%{transform:translateY(-9px)}}
.gear{transform-box:fill-box;transform-origin:center;animation:spin 6s linear infinite}
.gear.rev{animation-direction:reverse;animation-duration:4.5s}
@keyframes spin{to{transform:rotate(360deg)}}
.arm{transform-box:fill-box;transform-origin:10% 20%;animation:wave 1.1s ease-in-out infinite alternate}
@keyframes wave{from{transform:rotate(-14deg)}to{transform:rotate(16deg)}}
.blink{animation:blink 4s infinite;transform-box:fill-box;transform-origin:center}
@keyframes blink{0%,92%,100%{transform:scaleY(1)}95%{transform:scaleY(.1)}}
.spark{animation:spark 1.6s ease-in-out infinite;transform-box:fill-box;transform-origin:center}
.spark:nth-of-type(2){animation-delay:.5s}.spark:nth-of-type(3){animation-delay:1s}
@keyframes spark{0%,100%{opacity:.2;transform:scale(.6)}50%{opacity:1;transform:scale(1.15)}}

.card{background:var(--card);border-radius:22px;padding:20px;box-shadow:0 18px 50px rgba(15,10,70,.35)}
.card h2{margin:0 0 4px;font-size:20px}
.card .sub{margin:0 0 14px;color:var(--soft);font-size:14px}

/* İlerleme */
.bar{height:16px;background:#e4e1fb;border-radius:999px;overflow:hidden;position:relative}
.bar i{
  display:block;height:100%;width:8%;border-radius:999px;
  background:linear-gradient(90deg,#f59e0b,#f43f5e,#8b5cf6,#0ea5e9);background-size:200% 100%;
  animation:flow 2.5s linear infinite;transition:width .9s ease
}
@keyframes flow{to{background-position:-200% 0}}
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:14px}
.step{text-align:center;font-size:12.5px;color:var(--soft);font-weight:600;padding:8px 4px;border-radius:12px;background:#f2f0ff;transition:.4s}
.step b{display:block;font-size:22px;margin-bottom:2px}
.step.on{background:var(--violet);color:#fff;transform:translateY(-3px);box-shadow:0 8px 18px rgba(91,61,245,.4)}
.step.done{background:#d1fae5;color:#065f46}

.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}

/* Bilgi kartı */
.fact{min-height:120px;display:flex;gap:14px;align-items:center}
.fact .emo{font-size:44px;line-height:1}
.fact p{margin:0;font-size:16px;line-height:1.5;font-weight:600}
.fact-ctrl{display:flex;gap:6px;margin-top:14px}
.dot{width:9px;height:9px;border-radius:50%;background:#cfcaf7;transition:.3s}
.dot.on{background:var(--violet);transform:scale(1.35)}

/* Mini quiz */
.q-text{font-size:17px;font-weight:700;margin:0 0 12px;line-height:1.4}
.opts{display:grid;gap:8px}
.opt{
  font:inherit;text-align:left;padding:11px 14px;border-radius:14px;border:2px solid #ddd9fb;background:#fff;
  color:var(--ink);cursor:pointer;font-weight:600;transition:.15s
}
.opt:hover:not(:disabled){border-color:var(--violet);transform:translateX(3px)}
.opt.right{background:#d1fae5;border-color:var(--ok)}
.opt.wrong{background:#fee2e2;border-color:var(--bad);animation:shake .35s}
@keyframes shake{25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.q-foot{display:flex;justify-content:space-between;align-items:center;margin-top:12px;font-size:14px;color:var(--soft);font-weight:700;min-height:36px}
.next{font:inherit;border:0;background:var(--violet);color:#fff;font-weight:700;padding:8px 16px;border-radius:12px;cursor:pointer}
.next[hidden]{display:none}
.score{background:#fef3c7;color:#92400e;padding:4px 12px;border-radius:999px}

.foot{text-align:center;color:rgba(255,255,255,.85);font-size:13.5px;line-height:1.7}
.foot a{color:#fff;font-weight:700}
.foot .admin{opacity:.55;font-size:12px}
.pulse{display:inline-block;width:9px;height:9px;border-radius:50%;background:#34d399;margin-right:6px;animation:pulse 1.4s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(52,211,153,.7)}100%{box-shadow:0 0 0 10px rgba(52,211,153,0)}}

@media (max-width:760px){
  .grid{grid-template-columns:1fr}
  .steps{grid-template-columns:repeat(2,1fr)}
}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<div class="floaters" id="floaters" aria-hidden="true"></div>

<main class="wrap">
  <nav class="topbar"><a class="home-btn" href="{{ url('/') }}">🏠 Anasayfa</a></nav>
  <section class="hero">
    <span class="badge">🛠️ BAKIM ÇALIŞMASI</span>
    <h1>Sistem bakım çalışmasındadır</h1>
    <p>Daha iyi bir öğrenme deneyimi için sistemimizi geliştiriyoruz. <strong>Yakın zamanda açılacaktır!</strong> Bu sayfayı açık bırakabilirsin, sistem açılınca seni kendiliğinden içeri alırız.</p>
    @if(trim($note) !== '')
      <div class="note">📢 {{ $note }}</div>
    @endif

    <div class="stage" aria-hidden="true">
      <svg class="robot" viewBox="0 0 300 280" xmlns="http://www.w3.org/2000/svg">
        <!-- dişliler -->
        <g class="gear" style="transform-origin:58px 205px">
          <circle cx="58" cy="205" r="30" fill="#fbbf24"/>
          <circle cx="58" cy="205" r="12" fill="#1b1464"/>
          <g fill="#fbbf24">
            <rect x="52" y="168" width="12" height="14" rx="3"/><rect x="52" y="228" width="12" height="14" rx="3"/>
            <rect x="21" y="199" width="14" height="12" rx="3"/><rect x="81" y="199" width="14" height="12" rx="3"/>
            <rect x="29" y="176" width="12" height="14" rx="3" transform="rotate(-45 35 183)"/><rect x="75" y="222" width="12" height="14" rx="3" transform="rotate(-45 81 229)"/>
            <rect x="75" y="176" width="12" height="14" rx="3" transform="rotate(45 81 183)"/><rect x="29" y="222" width="12" height="14" rx="3" transform="rotate(45 35 229)"/>
          </g>
        </g>
        <g class="gear rev" style="transform-origin:246px 222px">
          <circle cx="246" cy="222" r="22" fill="#34d399"/>
          <circle cx="246" cy="222" r="9" fill="#1b1464"/>
          <g fill="#34d399">
            <rect x="241" y="195" width="10" height="11" rx="3"/><rect x="241" y="238" width="10" height="11" rx="3"/>
            <rect x="219" y="217" width="11" height="10" rx="3"/><rect x="262" y="217" width="11" height="10" rx="3"/>
          </g>
        </g>
        <!-- robot -->
        <g class="bob">
          <line x1="150" y1="22" x2="150" y2="48" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
          <circle cx="150" cy="18" r="9" fill="#f43f5e"/>
          <rect x="95" y="46" width="110" height="86" rx="26" fill="#fff"/>
          <rect x="106" y="58" width="88" height="56" rx="18" fill="#312e81"/>
          <g class="blink"><circle cx="131" cy="86" r="10" fill="#67e8f9"/></g>
          <g class="blink"><circle cx="169" cy="86" r="10" fill="#67e8f9"/></g>
          <path d="M136 103 Q150 113 164 103" stroke="#67e8f9" stroke-width="4" fill="none" stroke-linecap="round"/>
          <rect x="108" y="138" width="84" height="76" rx="22" fill="#ede9fe"/>
          <rect x="126" y="154" width="48" height="30" rx="8" fill="#5b3df5"/>
          <text x="150" y="176" text-anchor="middle" font-family="monospace" font-size="17" font-weight="800" fill="#fff">&lt;/&gt;</text>
          <rect x="124" y="214" width="20" height="26" rx="8" fill="#c4b5fd"/><rect x="156" y="214" width="20" height="26" rx="8" fill="#c4b5fd"/>
          <rect x="82" y="146" width="26" height="14" rx="7" fill="#c4b5fd"/>
          <g class="arm"><rect x="190" y="136" width="46" height="14" rx="7" fill="#c4b5fd"/><circle cx="238" cy="130" r="11" fill="#fbbf24"/></g>
          <g>
            <path class="spark" d="M236 60l5 11 11 5-11 5-5 11-5-11-11-5 11-5z" fill="#fde68a"/>
            <path class="spark" d="M62 70l4 8 8 4-8 4-4 8-4-8-8-4 8-4z" fill="#a5f3fc"/>
            <path class="spark" d="M258 150l3 6 6 3-6 3-3 6-3-6-6-3 6-3z" fill="#fbcfe8"/>
          </g>
        </g>
      </svg>
    </div>
  </section>

  <section class="card">
    <h2>⚙️ Neler yapılıyor?</h2>
    <p class="sub">Bakım ekibimiz (robotumuz de dahil!) şu adımlarda çalışıyor:</p>
    <div class="bar" role="progressbar" aria-label="Bakım ilerlemesi"><i id="bar"></i></div>
    <div class="steps" id="steps">
      <div class="step"><b>📚</b>Dersler kontrol ediliyor</div>
      <div class="step"><b>🎮</b>Oyunlar parlatılıyor</div>
      <div class="step"><b>⭐</b>XP'ler sayılıyor</div>
      <div class="step"><b>🚀</b>Kalkışa hazırlanıyor</div>
    </div>
  </section>

  <div class="grid">
    <section class="card">
      <h2>💡 Biliyor muydun?</h2>
      <p class="sub">Beklerken küçük bir mola ver, yeni bir şey öğren.</p>
      <div class="fact"><div class="emo" id="factEmo">🧠</div><p id="factText"></p></div>
      <div class="fact-ctrl" id="factDots"></div>
    </section>

    <section class="card">
      <h2>🎯 Mini Bakım Quizi</h2>
      <p class="sub">Bakım bitene kadar kendini dene!</p>
      <p class="q-text" id="qText"></p>
      <div class="opts" id="qOpts"></div>
      <div class="q-foot">
        <span class="score">Puan: <span id="score">0</span> ⭐</span>
        <button class="next" id="qNext" hidden>Sıradaki soru →</button>
      </div>
    </section>
  </div>

  <footer class="foot">
    <div><span class="pulse"></span>Sistem açılınca bu sayfa otomatik yenilenecek.</div>
    <div>Anlayışın için teşekkürler — çok yakında daha güçlü bir sistemle buradayız! 💜</div>
    <div class="admin"><a href="{{ url('/login?yonetici=1') }}">Yönetici girişi</a></div>
  </footer>
</main>

<script>
(function () {
  /* Süzülen simgeler */
  var syms = ['</>', 'π', '{ }', '+', 'Σ', '1010', '=', 'if', '∞', '?', '%', '#', '√', 'x²', ';'];
  var box = document.getElementById('floaters');
  for (var i = 0; i < 22; i++) {
    var s = document.createElement('span');
    s.textContent = syms[i % syms.length];
    s.style.left = (Math.random() * 100) + '%';
    s.style.fontSize = (16 + Math.random() * 30) + 'px';
    s.style.animationDuration = (14 + Math.random() * 16) + 's';
    s.style.animationDelay = (-Math.random() * 28) + 's';
    box.appendChild(s);
  }

  /* Sahte ama sevimli ilerleme adımları */
  var bar = document.getElementById('bar');
  var steps = [].slice.call(document.querySelectorAll('#steps .step'));
  var cur = 0;
  function paintSteps() {
    steps.forEach(function (el, i) {
      el.className = 'step' + (i < cur ? ' done' : (i === cur ? ' on' : ''));
    });
    bar.style.width = (12 + cur * 24) + '%';
  }
  paintSteps();
  setInterval(function () { cur = (cur + 1) % steps.length; paintSteps(); }, 3800);

  /* Bilgi kartları */
  var facts = [
    ['🧠', 'İlk bilgisayar programcısı Ada Lovelace’tir; 1843’te bir makine için algoritma yazmıştır.'],
    ['🐛', '“Bug” (böcek) terimi, bir bilgisayara gerçekten bir güvenin takılıp kalmasından gelir!'],
    ['🔢', 'Bilgisayarlar her şeyi 0 ve 1’lerle anlatır. 8 tane 0-1’e “1 bayt” denir.'],
    ['🌍', 'Dünyada 700’den fazla programlama dili var. Python adı bir yılan değil, komedi grubundan gelir.'],
    ['⚡', 'Bir algoritma, bir sorunu çözmek için izlenen adım adım tariftir; tıpkı kek yapmak gibi!'],
    ['🧩', 'Bir problemi küçük parçalara bölmek (ayrıştırma), programcıların en güçlü süper gücüdür.'],
    ['🔁', 'Döngüler sayesinde bilgisayar aynı işi 1 milyon kez yorulmadan tekrar edebilir.']
  ];
  var fi = Math.floor(Math.random() * facts.length);
  var dots = document.getElementById('factDots');
  facts.forEach(function () { var d = document.createElement('div'); d.className = 'dot'; dots.appendChild(d); });
  function showFact() {
    document.getElementById('factEmo').textContent = facts[fi][0];
    document.getElementById('factText').textContent = facts[fi][1];
    [].slice.call(dots.children).forEach(function (d, i) { d.className = 'dot' + (i === fi ? ' on' : ''); });
  }
  showFact();
  setInterval(function () { fi = (fi + 1) % facts.length; showFact(); }, 7000);

  /* Mini quiz */
  var bank = [
    { q: '3 × (2 + 4) işleminin sonucu kaçtır?', o: ['12', '18', '10'], a: 1 },
    { q: 'Bilgisayarın “beyni” hangisidir?', o: ['İşlemci (CPU)', 'Fare', 'Hoparlör'], a: 0 },
    { q: 'Bir işi tekrar tekrar yapmak için hangi yapı kullanılır?', o: ['Değişken', 'Döngü', 'Yorum satırı'], a: 1 },
    { q: 'İkilik (binary) sistemde hangi rakamlar kullanılır?', o: ['0 ve 1', '1 ve 2', '0 ile 9 arası'], a: 0 },
    { q: 'Algoritma nedir?', o: ['Bir bilgisayar türü', 'Sorunu çözen adım adım tarif', 'Bir oyun karakteri'], a: 1 },
    { q: 'Kare şeklinin kaç kenarı vardır?', o: ['3', '5', '4'], a: 2 },
    { q: 'if (koşul) yapısı ne işe yarar?', o: ['Karar vermeye', 'Ses çıkarmaya', 'Dosya silmeye'], a: 0 },
    { q: '2, 4, 8, 16, … dizisinde sıradaki sayı kaçtır?', o: ['24', '32', '20'], a: 1 }
  ].sort(function () { return Math.random() - .5; });
  var qi = 0, score = 0, locked = false;
  var qText = document.getElementById('qText'), qOpts = document.getElementById('qOpts'),
      qNext = document.getElementById('qNext'), scoreEl = document.getElementById('score');
  function showQ() {
    var item = bank[qi % bank.length];
    locked = false; qNext.hidden = true; qOpts.innerHTML = '';
    qText.textContent = item.q;
    item.o.forEach(function (txt, idx) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'opt'; b.textContent = txt;
      b.addEventListener('click', function () {
        if (locked) return; locked = true;
        var kids = [].slice.call(qOpts.children);
        kids.forEach(function (k) { k.disabled = true; });
        kids[item.a].classList.add('right');
        if (idx === item.a) { score += 10; scoreEl.textContent = score; }
        else { b.classList.add('wrong'); }
        qNext.hidden = false;
      });
      qOpts.appendChild(b);
    });
  }
  qNext.addEventListener('click', function () { qi++; showQ(); });
  showQ();

  /* Bakım bitince otomatik yenile */
  function poll() {
    fetch('/bakim-durumu', { cache: 'no-store', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d && d.maintenance === false) location.href = '/'; })
      .catch(function () {});
  }
  setInterval(poll, 20000);
})();
</script>
</body>
</html>
