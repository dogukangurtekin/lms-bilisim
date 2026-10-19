{{-- Canlı Yarışmalar ortak teması (Klavye Yarışması tasarımıyla aynı dil: koyu lacivert + canlı renkler) --}}
<style>
.cx{--cx-bg1:#020617;--cx-bg2:#0f172a;--cx-bg3:#1e2a44;--cx-line:rgba(148,163,184,.25);--cx-ink:#e2e8f0;--cx-mute:#94a3b8;
    --cx-c1:#ff6b6b;--cx-c2:#ff9f43;--cx-c3:#feca57;--cx-c4:#1dd1a1;--cx-c5:#48dbfb;--cx-c6:#54a0ff;--cx-c7:#a29bfe;--cx-c8:#ff78c4;
    position:relative;overflow:hidden;border-radius:22px;padding:18px;color:var(--cx-ink);
    background:linear-gradient(160deg,var(--cx-bg1) 0%,var(--cx-bg2) 52%,#111827 100%);border:1px solid rgba(56,189,248,.25)}
.cx::before{content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(circle at 16% 12%,rgba(6,182,212,.22),transparent 45%),radial-gradient(circle at 86% 6%,rgba(236,72,153,.18),transparent 38%),radial-gradient(circle at 50% 100%,rgba(59,130,246,.14),transparent 60%)}
.cx>*{position:relative;z-index:1}
.cx h1,.cx h2,.cx h3{color:#fff;margin:0}
.cx p{color:var(--cx-mute)}
.cx-hero{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:16px;align-items:center;padding:20px;border-radius:18px;color:#fff;
    background:linear-gradient(135deg,#4338ca 0%,#7c3aed 52%,#db2777 100%);box-shadow:0 18px 45px rgba(79,70,229,.25)}
.cx-hero-icon{width:68px;height:68px;border-radius:20px;display:grid;place-items:center;font-size:34px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3)}
.cx-hero h1{font-size:clamp(20px,3vw,28px);line-height:1.2}
.cx-hero p{margin:4px 0 0;color:rgba(255,255,255,.85);font-size:14px}
.cx-chips{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.cx-chip{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.18);font-size:13px;font-weight:800;color:#fff;white-space:nowrap}
.cx-chip.live{background:#22c55e;animation:cxpulse 1.6s infinite}
.cx-chip.lobby{background:#f59e0b}.cx-chip.done{background:#64748b}
@keyframes cxpulse{50%{box-shadow:0 0 0 7px rgba(34,197,94,.25)}}
.cx-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.2fr);gap:14px;margin-top:14px}
.cx-panel{margin-top:14px;padding:16px;border:1px solid var(--cx-line);border-radius:16px;background:rgba(255,255,255,.06);box-shadow:0 8px 25px rgba(2,6,23,.25)}
.cx-grid .cx-panel{margin-top:0}
.cx-panel h3{font-size:16px;margin-bottom:10px}
.cx label{display:block;margin:0 0 6px;font-size:12px;font-weight:800;letter-spacing:.02em;color:var(--cx-mute)}
.cx .form-control,.cx select,.cx input[type=text],.cx input[type=number]{width:100%;background:rgba(15,23,42,.7)!important;border:1px solid var(--cx-line)!important;border-radius:10px!important;color:#fff!important;padding:10px 12px;min-height:42px;font-size:15px}
.cx select option{color:#0f172a;background:#fff}
.cx .form-control::placeholder{color:#64748b}
.cx .btn{border-radius:12px;border:0;padding:10px 18px;font-weight:800;color:#fff;background:rgba(255,255,255,.12);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:transform .12s,filter .12s}
.cx .btn:hover{transform:translateY(-1px);filter:brightness(1.12)}
.cx .btn-primary{background:linear-gradient(135deg,#4f46e5,#7c3aed)}
.cx .btn-start{background:linear-gradient(135deg,#16a34a,#1dd1a1);font-size:18px;padding:14px 32px;box-shadow:0 10px 24px rgba(29,209,161,.3)}
.cx .btn-danger{background:linear-gradient(135deg,#dc2626,#f43f5e)}
.cx-row{margin-bottom:12px}
.cx-err{background:rgba(220,38,38,.18);border:1px solid rgba(248,113,113,.5);color:#fecaca;border-radius:10px;padding:10px 12px;margin-bottom:12px;font-weight:700;font-size:14px}
/* tablo */
.cx table{width:100%;border-collapse:collapse;color:var(--cx-ink)}
.cx th{background:rgba(255,255,255,.08);color:#cbd5e1;font-size:12px;text-align:left;padding:9px 10px;border:0}
.cx td{padding:10px;border:0;border-bottom:1px solid var(--cx-line);font-size:14px;background:transparent}
.cx .tag{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:800}
.cx .tag.lobby{background:#f59e0b33;color:#fcd34d}.cx .tag.live{background:#22c55e33;color:#86efac}.cx .tag.done{background:#64748b44;color:#cbd5e1}
.cx .code{font-family:Consolas,monospace;font-weight:900;letter-spacing:.15em;color:#feca57}
/* açık (beyaz) panel: tablo listeleri için */
.cx-panel.light{background:#fff;border-color:#dbe3ef;color:#0f172a}
.cx-panel.light h3{color:#0f172a}
.cx-panel.light table,.cx-panel.light td{color:#0f172a;background:#fff}
.cx-panel.light th{background:#eef2ff;color:#334155;border-bottom:1px solid #dbe3ef}
.cx-panel.light td{border-bottom:1px solid #e5e7eb}
.cx-panel.light tr:hover td{background:#f8fafc}
.cx-panel.light .code{color:#4f46e5}
.cx-panel.light .tag.lobby{background:#fef3c7;color:#92400e}.cx-panel.light .tag.live{background:#dcfce7;color:#166534}.cx-panel.light .tag.done{background:#e2e8f0;color:#475569}
.cx-panel.light .btn{background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe}
.cx-panel.light .btn-danger{background:linear-gradient(135deg,#dc2626,#f43f5e);color:#fff;border:0}
.cx-panel.light p{color:#64748b}
/* lobi kodu */
.cx-lobby{text-align:center;padding:26px 16px}
.cx-lobby-code,.comp-lobby-code{font-size:clamp(44px,9vw,84px);font-weight:950;letter-spacing:.2em;line-height:1;margin:10px 0;
    background:linear-gradient(90deg,#feca57,#ff78c4,#48dbfb);-webkit-background-clip:text;background-clip:text;color:transparent;text-shadow:none}
.cx-big{font-size:clamp(40px,7vw,64px);font-weight:950;color:#feca57;line-height:1}
/* zaman çubuğu */
.comp-timer-bar,.comp-play-timer-bar{height:12px;border-radius:999px;background:rgba(148,163,184,.28);overflow:hidden;margin-top:10px}
.comp-timer-fill,.comp-play-timer-fill{height:100%;background:linear-gradient(90deg,#1dd1a1,#54a0ff,#a29bfe);width:100%;transition:width .25s linear}
.comp-timer-fill.warn,.comp-play-timer-fill.warn{background:linear-gradient(90deg,#feca57,#ff6b6b)}
.comp-big-clock{font-size:36px;font-weight:900;color:#fff}
/* öğrenci kartları ve sıralama */
.comp-student-grid,.comp-joined-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px;align-content:start;margin-top:12px;text-align:left}
.comp-rank-row{display:grid;grid-template-columns:30px 40px minmax(0,1fr);grid-template-rows:auto auto;gap:4px 8px;align-items:center;padding:10px;border-radius:14px;background:rgba(255,255,255,.07);border:1px solid var(--cx-line);border-left:4px solid #54a0ff;min-width:0;will-change:transform}
.comp-rank-row.leader{border-left-color:#feca57;background:linear-gradient(135deg,rgba(254,202,87,.18),rgba(255,255,255,.06))}
.comp-rank-row .comp-rank-no{font-weight:900;color:#94a3b8}.comp-rank-row.leader .comp-rank-no{color:#feca57}
.comp-rank-avatar{grid-column:2;grid-row:1;width:40px;height:40px;border-radius:12px;object-fit:cover;background:#1e2a44}
.comp-rank-avatar.empty{display:flex;align-items:center;justify-content:center;font-size:18px;color:#94a3b8}
.comp-rank-name{grid-column:3;grid-row:1;min-width:0;font-size:13px;font-weight:700;color:#fff;overflow-wrap:anywhere;line-height:1.25}
.comp-rank-status{grid-column:2/4;grid-row:2;color:#cbd5e1;font-size:12px;overflow-wrap:anywhere}
.comp-rank-row.status-green{border-left-color:#1dd1a1;background:rgba(29,209,161,.16)}
.comp-rank-row.status-orange{border-left-color:#ff9f43;background:rgba(255,159,67,.14)}
.comp-rank-row.status-red{border-left-color:#ff6b6b;background:rgba(255,107,107,.14)}
.comp-joined-tile{display:grid;grid-template-columns:40px minmax(0,1fr);align-items:center;gap:10px;padding:10px;border-radius:14px;background:rgba(255,255,255,.08);border:1px solid var(--cx-line);animation:cxpop .3s}
.comp-joined-tile:nth-child(8n+1){border-left:4px solid var(--cx-c1)}.comp-joined-tile:nth-child(8n+2){border-left:4px solid var(--cx-c2)}.comp-joined-tile:nth-child(8n+3){border-left:4px solid var(--cx-c3)}.comp-joined-tile:nth-child(8n+4){border-left:4px solid var(--cx-c4)}
.comp-joined-tile:nth-child(8n+5){border-left:4px solid var(--cx-c5)}.comp-joined-tile:nth-child(8n+6){border-left:4px solid var(--cx-c6)}.comp-joined-tile:nth-child(8n+7){border-left:4px solid var(--cx-c7)}.comp-joined-tile:nth-child(8n+8){border-left:4px solid var(--cx-c8)}
.comp-joined-tile img,.comp-joined-tile .empty{width:40px;height:40px;border-radius:12px;object-fit:cover;background:#1e2a44;display:grid;place-items:center;color:#94a3b8;font-weight:800;border:0}
.comp-joined-tile span{font-size:13px;font-weight:700;color:#fff;overflow-wrap:anywhere;line-height:1.25}
@keyframes cxpop{from{transform:scale(.85);opacity:0}}
/* öğrenci bekleme / bitiş kutusu */
.comp-wait-stage{min-height:420px;display:grid;place-items:center}
.comp-wait-box{border:1px solid var(--cx-line);border-radius:22px;padding:28px;width:min(760px,100%);text-align:center;background:rgba(255,255,255,.07);box-shadow:0 18px 45px rgba(2,6,23,.4)}
.comp-wait-title{margin:0 0 8px;font-size:clamp(22px,4vw,32px);font-weight:900;color:#fff}
.comp-wait-count{font-size:72px;line-height:1;font-weight:950;margin:10px 0;background:linear-gradient(90deg,#feca57,#ff78c4);-webkit-background-clip:text;background-clip:text;color:transparent}
.comp-wait-box p{color:var(--cx-mute)}
.cx-dots{display:flex;gap:8px;justify-content:center;margin:10px 0}.cx-dots i{width:12px;height:12px;border-radius:50%;animation:cxbounce 1.2s infinite}
.cx-dots i:nth-child(1){background:var(--cx-c1)}.cx-dots i:nth-child(2){background:var(--cx-c3);animation-delay:.15s}.cx-dots i:nth-child(3){background:var(--cx-c4);animation-delay:.3s}.cx-dots i:nth-child(4){background:var(--cx-c6);animation-delay:.45s}.cx-dots i:nth-child(5){background:var(--cx-c8);animation-delay:.6s}
@keyframes cxbounce{40%{transform:translateY(-12px)}}
/* oyun çerçevesi */
.comp-play-frame-wrap{border:1px solid var(--cx-line);border-radius:18px;overflow:hidden;background:#000;position:relative;margin-top:14px}
.comp-play-frame-wrap iframe{width:100%;display:block;border:0}
.comp-play-badge{background:rgba(255,255,255,.18);border-radius:999px;padding:7px 13px;font-weight:800;font-size:13px;color:#fff}
.comp-play-bar{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:16px;align-items:center;padding:16px 20px;border-radius:18px;color:#fff;background:linear-gradient(135deg,#4338ca 0%,#7c3aed 52%,#db2777 100%);box-shadow:0 18px 45px rgba(79,70,229,.25);margin-bottom:12px}
.comp-play-bar h1{font-size:clamp(18px,2.6vw,24px);color:#fff}
.comp-play-badges{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.comp-celebrate-fadeout{transition:opacity .5s ease,transform .5s ease;opacity:0;transform:scale(.97)}
#compConfettiCanvas{position:fixed;inset:0;pointer-events:none;z-index:9999}
.comp-done-enter{opacity:0;transform:translateY(14px) scale(.96)}
.comp-done-enter-active{opacity:1;transform:translateY(0) scale(1);transition:opacity .5s ease,transform .5s ease}
.comp-trophy{font-size:64px;line-height:1;display:inline-block;animation:comp-trophy-bounce 1s ease 1}
@keyframes comp-trophy-bounce{0%{transform:scale(.2) rotate(-15deg);opacity:0}55%{transform:scale(1.15) rotate(8deg);opacity:1}75%{transform:scale(.95) rotate(-4deg)}100%{transform:scale(1) rotate(0)}}
/* silme modalı */
.cx .modal-card{background:#0f172a;border:1px solid var(--cx-line);color:#e2e8f0}
@media(max-width:900px){.cx-grid{grid-template-columns:1fr}.cx-hero,.comp-play-bar{grid-template-columns:auto minmax(0,1fr)}.cx-chips,.comp-play-badges{grid-column:1/-1;justify-content:flex-start}}
/* ---- hareketli giriş / bekleme ekranı (canlı quiz) ---- */
.cx-bubbles{position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:0}
.cx-bubbles i{position:absolute;bottom:-60px;border-radius:50%;opacity:.55;animation:cxrise linear infinite}
.cx-bubbles i:nth-child(1){left:6%;width:34px;height:34px;background:var(--cx-c1,#ff6b6b);animation-duration:11s}
.cx-bubbles i:nth-child(2){left:18%;width:20px;height:20px;background:var(--cx-c3,#feca57);animation-duration:8s;animation-delay:1s}
.cx-bubbles i:nth-child(3){left:31%;width:44px;height:44px;background:var(--cx-c4,#1dd1a1);animation-duration:13s;animation-delay:2s}
.cx-bubbles i:nth-child(4){left:47%;width:26px;height:26px;background:var(--cx-c6,#54a0ff);animation-duration:9s;animation-delay:.5s}
.cx-bubbles i:nth-child(5){left:60%;width:38px;height:38px;background:var(--cx-c8,#ff78c4);animation-duration:12s;animation-delay:3s}
.cx-bubbles i:nth-child(6){left:73%;width:22px;height:22px;background:var(--cx-c7,#a29bfe);animation-duration:7.5s;animation-delay:1.5s}
.cx-bubbles i:nth-child(7){left:84%;width:40px;height:40px;background:var(--cx-c2,#ff9f43);animation-duration:10.5s;animation-delay:2.5s}
.cx-bubbles i:nth-child(8){left:94%;width:24px;height:24px;background:var(--cx-c5,#48dbfb);animation-duration:9.5s;animation-delay:4s}
@keyframes cxrise{0%{transform:translateY(0) rotate(0);opacity:0}10%{opacity:.55}100%{transform:translateY(-115vh) rotate(300deg);opacity:0}}
.cx-keys{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin:14px 0}
.cx-key{width:clamp(44px,9vw,78px);height:clamp(54px,11vw,92px);border-radius:16px;display:grid;place-items:center;font-size:clamp(26px,6vw,48px);font-weight:950;color:#1b2238;
    box-shadow:0 6px 0 rgba(0,0,0,.28);animation:cxkey 2.2s ease-in-out infinite}
.cx-key:nth-child(1){background:#ff6b6b}.cx-key:nth-child(2){background:#ff9f43;animation-delay:.15s}.cx-key:nth-child(3){background:#feca57;animation-delay:.3s}
.cx-key:nth-child(4){background:#1dd1a1;animation-delay:.45s}.cx-key:nth-child(5){background:#48dbfb;animation-delay:.6s}.cx-key:nth-child(6){background:#a29bfe;animation-delay:.75s}
@keyframes cxkey{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}
.cx-title-wave span{display:inline-block;animation:cxwave 1.6s ease-in-out infinite}
@keyframes cxwave{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.cx-chipwall{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin:16px auto 6px;max-width:960px}
.lq-student-chip,.lq-participant-chip{padding:9px 16px;border-radius:14px;font-weight:800;color:#1b2238;border:0;box-shadow:0 4px 0 rgba(0,0,0,.25);animation:cxpop .35s both,cxfloat 3.4s ease-in-out infinite}
.lq-student-chip:nth-child(8n+1),.lq-participant-chip:nth-child(8n+1){background:#ff6b6b}.lq-student-chip:nth-child(8n+2),.lq-participant-chip:nth-child(8n+2){background:#ff9f43}
.lq-student-chip:nth-child(8n+3),.lq-participant-chip:nth-child(8n+3){background:#feca57}.lq-student-chip:nth-child(8n+4),.lq-participant-chip:nth-child(8n+4){background:#1dd1a1}
.lq-student-chip:nth-child(8n+5),.lq-participant-chip:nth-child(8n+5){background:#48dbfb}.lq-student-chip:nth-child(8n+6),.lq-participant-chip:nth-child(8n+6){background:#54a0ff}
.lq-student-chip:nth-child(8n+7),.lq-participant-chip:nth-child(8n+7){background:#a29bfe}.lq-student-chip:nth-child(8n+8),.lq-participant-chip:nth-child(8n+8){background:#ff78c4}
.lq-student-chip:nth-child(even),.lq-participant-chip:nth-child(even){animation-delay:0s,.6s}
@keyframes cxfloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
.lq-student-empty,.lq-participant-empty{color:#94a3b8;font-weight:700}
.cx-count{font-size:clamp(56px,12vw,96px);font-weight:950;line-height:1;background:linear-gradient(90deg,#feca57,#ff78c4,#48dbfb);-webkit-background-clip:text;background-clip:text;color:transparent;animation:cxbeat 1.4s ease-in-out infinite}
@keyframes cxbeat{50%{transform:scale(1.08)}}
.cx-hero-icon.spin{animation:cxspin 6s linear infinite}
@keyframes cxspin{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){.cx-bubbles i,.cx-key,.cx-title-wave span,.cx-count,.lq-student-chip,.lq-participant-chip,.cx-hero-icon.spin{animation:none}}
</style>
