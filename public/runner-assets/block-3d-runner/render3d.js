/* 3D Blok Kodlama — izometrik (3 boyutlu görünümlü) çizim motoru.
 * Kalın zemin blokları, yükseklikli engeller (ağaç, kaya, kristal, volkan...), dönen yıldız ve ışık huzmesi,
 * havada zıplayarak yürüyen robot, dalgalı su / yıldızlı uzay arka planı, parçacık efektleri.
 * Arayüz render.js (2B) ile aynıdır: setLevel, resetRobot, resize, turn, move, crash, celebrate, cancelTweens, THEMES.
 */
(function (root) {
  'use strict';

  var TAU = Math.PI * 2;

  var THEMES = {
    meadow:  { name: 'Çayır',         top: ['#86efac', '#6ee7b7'], side: ['#15803d', '#166534'], sky: ['#e0f2fe', '#7dd3fc'], water: ['#38bdf8', '#0ea5e9'], kinds: ['tree', 'tree', 'bush', 'rock'], clouds: true },
    beach:   { name: 'Kumsal',        top: ['#fde68a', '#fcd34d'], side: ['#b45309', '#92400e'], sky: ['#fef9c3', '#fdba74'], water: ['#22d3ee', '#06b6d4'], kinds: ['palm', 'rock', 'crate', 'palm'], clouds: true },
    snow:    { name: 'Kar',           top: ['#f8fafc', '#e0f2fe'], side: ['#60a5fa', '#3b82f6'], sky: ['#e0f2fe', '#bfdbfe'], water: ['#93c5fd', '#60a5fa'], kinds: ['pine', 'iceblock', 'snowpile', 'pine'], clouds: true },
    dusk:    { name: 'Alacakaranlık', top: ['#c4b5fd', '#a78bfa'], side: ['#6d28d9', '#5b21b6'], sky: ['#4c1d95', '#f472b6'], water: ['#7c3aed', '#6d28d9'], kinds: ['crystal', 'rock', 'pillar', 'crystal'], stars: true },
    volcano: { name: 'Yanardağ',      top: ['#a8723a', '#92612f'], side: ['#431407', '#7c2d12'], sky: ['#7f1d1d', '#fb923c'], water: ['#f97316', '#dc2626'], kinds: ['volcano', 'darkrock', 'darkrock', 'pillar'], lava: true },
    cosmic:  { name: 'Kozmik',        top: ['#4f46e5', '#4338ca'], side: ['#1e1b4b', '#0f0a2e'], sky: ['#0b0720', '#2e1065'], water: ['#1e1b4b', '#0f0a2e'], kinds: ['crystal', 'asteroid', 'orb', 'pillar'], stars: true },
  };

  function hash(a, b, c) {
    var h = (a * 374761393 + b * 668265263 + c * 2147483647) | 0;
    h = (h ^ (h >>> 13)) * 1274126177;
    return ((h ^ (h >>> 16)) >>> 0) / 4294967296;
  }
  function lerp(a, b, t) { return a + (b - a) * t; }
  function easeInOut(t) { return t < .5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; }
  function shortAngle(from, to) { var d = (to - from) % TAU; if (d > Math.PI) d -= TAU; if (d < -Math.PI) d += TAU; return d; }

  function create(canvas) {
    var ctx = canvas.getContext('2d');
    var level = null, theme = THEMES.meadow, dpr = 1;
    var a = 40, b = 20, thick = 22, ox = 0, oy = 0;       // yarım karo genişliği/yüksekliği, kalınlık, başlangıç
    var robot = { x: 0, y: 0, angle: 0, hop: 0, squash: 0, mood: 'idle' };
    var tweens = [], particles = [], clouds = [], stars = [];
    var time = 0, last = performance.now(), running = true, goalReached = false, shakeT = 0;
    var kinds = {};

    function resize() {
      var rect = canvas.getBoundingClientRect();
      var w = Math.max(240, Math.round(rect.width || 520)), h = Math.max(240, Math.round(rect.height || rect.width || 520));
      dpr = Math.min(2.5, window.devicePixelRatio || 1);
      canvas.width = Math.round(w * dpr); canvas.height = Math.round(h * dpr);
      layout();
    }
    function layout() {
      if (!level) return;
      var n = level.w + level.h, cw = canvas.width, ch = canvas.height;
      a = Math.min(cw * 0.95 / n, ch * 0.95 / (n / 2 + 1.95));
      b = a / 2; thick = a * 0.55;
      var objTop = a * 1.5;
      ox = cw / 2 + ((level.h - level.w) * a) / 2;
      oy = (ch - (n * b + thick + objTop)) / 2 + objTop;
    }

    // ---- izometrik izdüşüm: karo (x,y) üst köşesi
    function sx(x, y) { return ox + (x - y) * a; }
    function sy(x, y) { return oy + (x + y) * b; }
    function cx(x, y) { return sx(x, y); }
    function cy(x, y) { return sy(x, y) + b; }          // karo merkezi

    function setLevel(lv, themeName) {
      level = lv; theme = THEMES[themeName || lv.theme] || THEMES.meadow;
      kinds = {};
      (lv.obstacles || []).forEach(function (o) {
        var h = hash(o.x, o.y, lv.id || 1);
        kinds[o.x + ',' + o.y] = { kind: theme.kinds[Math.floor(h * theme.kinds.length)], r: hash(o.y, o.x, lv.id || 2) };
      });
      clouds = []; stars = [];
      for (var i = 0; i < 5; i++) clouds.push({ x: hash(i, 1, 5), y: .06 + hash(i, 2, 5) * .3, s: .6 + hash(i, 3, 5) * .9, v: .004 + hash(i, 4, 5) * .008 });
      for (var j = 0; j < 90; j++) stars.push({ x: hash(j, 1, 9), y: hash(j, 2, 9), s: .4 + hash(j, 3, 9) * 1.5, p: hash(j, 4, 9) * TAU });
      layout(); resetRobot(); particles = []; tweens = []; goalReached = false;
    }
    function resetRobot() {
      if (!level) return;
      robot.x = level.start.x; robot.y = level.start.y; robot.angle = level.start.dir * Math.PI / 2;
      robot.hop = 0; robot.squash = 0; robot.mood = 'idle'; goalReached = false;
    }

    // ---- animasyon
    function tween(ms, fn) {
      return new Promise(function (resolve) {
        if (!ms || ms <= 0) { fn(1); resolve(); return; }
        tweens.push({ start: performance.now(), ms: ms, fn: fn, done: resolve });
      });
    }
    function turn(dir, ms) {
      var from = robot.angle, to = from + shortAngle(from, dir * Math.PI / 2);
      return tween(ms, function (t) { robot.angle = lerp(from, to, easeInOut(t)); robot.squash = Math.sin(t * Math.PI) * .12; });
    }
    function move(to, ms) {
      var fx = robot.x, fy = robot.y; puff(fx, fy, 5);
      return tween(ms, function (t) {
        var e = easeInOut(t); robot.x = lerp(fx, to.x, e); robot.y = lerp(fy, to.y, e); robot.hop = Math.sin(t * Math.PI) * .22;
      }).then(function () { robot.x = to.x; robot.y = to.y; robot.hop = 0; puff(to.x, to.y, 3); });
    }
    function crash(target, ms) {
      var fx = robot.x, fy = robot.y, dx = (target.x - fx) * .38, dy = (target.y - fy) * .38;
      robot.mood = 'crash';
      return tween(ms, function (t) {
        var k = t < .5 ? t * 2 : (1 - t) * 2; robot.x = fx + dx * k; robot.y = fy + dy * k;
        if (t > .45 && t < .55) sparks(fx + dx * 1.3, fy + dy * 1.3, 12);
      }).then(function () { robot.x = fx; robot.y = fy; shakeT = 1; });
    }
    function celebrate() {
      goalReached = true; robot.mood = 'win';
      var s = level.star;
      for (var i = 0; i < 40; i++) {
        var ang = hash(i, 5, 9) * TAU, sp = .6 + hash(i, 6, 9) * 2.6;
        particles.push({ x: s.x, y: s.y, z: .5, vx: Math.cos(ang) * sp * .5, vy: Math.sin(ang) * sp * .5, vz: 1.5 + hash(i, 7, 9) * 2, life: 1, decay: .55 + hash(i, 8, 9) * .4, kind: 'star', color: ['#fde047', '#fb923c', '#f472b6', '#60a5fa', '#34d399'][i % 5], size: .1 + hash(i, 9, 9) * .12 });
      }
    }
    function puff(px, py, count) {
      for (var i = 0; i < count; i++) particles.push({ x: px + (Math.random() - .5) * .4, y: py + (Math.random() - .5) * .4, z: 0, vx: (Math.random() - .5) * .5, vy: (Math.random() - .5) * .5, vz: .6 + Math.random() * .6, life: 1, decay: 2, kind: 'dust', color: 'rgba(255,255,255,.7)', size: .07 + Math.random() * .06 });
    }
    function sparks(px, py, count) {
      for (var i = 0; i < count; i++) {
        var ang = Math.random() * TAU, sp = 1 + Math.random() * 2;
        particles.push({ x: px, y: py, z: .3, vx: Math.cos(ang) * sp * .4, vy: Math.sin(ang) * sp * .4, vz: 1 + Math.random() * 1.5, life: 1, decay: 2.2, kind: 'dust', color: ['#fca5a5', '#fde68a', '#fff'][i % 3], size: .05 + Math.random() * .06 });
      }
    }

    // ---- çizim yardımcıları
    function poly(pts, fill, stroke) {
      ctx.beginPath(); ctx.moveTo(pts[0][0], pts[0][1]);
      for (var i = 1; i < pts.length; i++) ctx.lineTo(pts[i][0], pts[i][1]);
      ctx.closePath(); if (fill) { ctx.fillStyle = fill; ctx.fill(); } if (stroke) { ctx.strokeStyle = stroke; ctx.lineWidth = 1 * dpr; ctx.stroke(); }
    }
    // izometrik kutu: taban merkezi (px,py), yarım genişlik hw, yarım derinlik hh, yükseklik h
    function box(px, py, hw, hh, h, cTop, cL, cR) {
      poly([[px - hw, py - h], [px, py - h + hh], [px, py + hh], [px - hw, py]], cL);
      poly([[px + hw, py - h], [px, py - h + hh], [px, py + hh], [px + hw, py]], cR);
      poly([[px, py - h - hh], [px + hw, py - h], [px, py - h + hh], [px - hw, py - h]], cTop);
    }
    function ellipse(px, py, rx, ry, fill) { ctx.fillStyle = fill; ctx.beginPath(); ctx.ellipse(px, py, rx, ry, 0, 0, TAU); ctx.fill(); }
    function shadowAt(px, py, rx) { ellipse(px, py, rx, rx * .5, 'rgba(0,0,0,.28)'); }
    function starPath(px, py, outer, inner) {
      ctx.beginPath();
      for (var i = 0; i < 10; i++) { var r = i % 2 ? inner : outer, an = -Math.PI / 2 + i * Math.PI / 5; ctx.lineTo(px + Math.cos(an) * r, py + Math.sin(an) * r); }
      ctx.closePath();
    }

    // ---- arka plan
    function drawBackground() {
      var cw = canvas.width, ch = canvas.height;
      var g = ctx.createLinearGradient(0, 0, 0, ch);
      g.addColorStop(0, theme.sky[0]); g.addColorStop(.55, theme.sky[1]); g.addColorStop(.56, theme.water[0]); g.addColorStop(1, theme.water[1]);
      ctx.fillStyle = g; ctx.fillRect(0, 0, cw, ch);
      if (theme.stars) {
        stars.forEach(function (s) {
          var tw = .5 + .5 * Math.sin(time * 2 + s.p);
          ctx.fillStyle = 'rgba(255,255,255,' + (.2 + .6 * tw) + ')';
          ctx.beginPath(); ctx.arc(s.x * cw, s.y * ch * .6, s.s * dpr, 0, TAU); ctx.fill();
        });
      }
      if (theme.clouds) {
        clouds.forEach(function (c) {
          var px = ((c.x + time * c.v) % 1.3 - .15) * cw, py = c.y * ch, w = 90 * c.s * dpr;
          ctx.fillStyle = 'rgba(255,255,255,.75)';
          [[0, 0, 1], [.5, .1, .75], [-.5, .12, .7], [.15, -.18, .6]].forEach(function (p) { ctx.beginPath(); ctx.ellipse(px + p[0] * w, py + p[1] * w * .5, w * .45 * p[2], w * .26 * p[2], 0, 0, TAU); ctx.fill(); });
        });
      }
      if (theme.lava) {
        var lg = ctx.createRadialGradient(cw / 2, ch * .5, 0, cw / 2, ch * .5, cw * .7);
        lg.addColorStop(0, 'rgba(251,146,60,' + (.18 + .08 * Math.sin(time * 2)) + ')'); lg.addColorStop(1, 'rgba(251,146,60,0)');
        ctx.fillStyle = lg; ctx.fillRect(0, 0, cw, ch);
      }
      // dalgalar
      ctx.strokeStyle = 'rgba(255,255,255,.22)'; ctx.lineWidth = 2 * dpr;
      for (var i = 0; i < 14; i++) {
        var wy = ch * (.58 + .4 * hash(i, 1, 4)), wx = hash(i, 2, 4) * cw, ww = (30 + 50 * hash(i, 3, 4)) * dpr;
        var off = Math.sin(time * 1.4 + i) * 8 * dpr;
        ctx.beginPath(); ctx.moveTo(wx + off, wy); ctx.quadraticCurveTo(wx + ww / 2 + off, wy - 5 * dpr, wx + ww + off, wy); ctx.stroke();
      }
    }

    function drawBoard() {
      var W = level.w, H = level.h;
      // zemin gölgesi
      var gx = sx((W - 1) / 2, (H - 1) / 2), gy = sy((W - 1) / 2, (H - 1) / 2) + b + thick + a * .35;
      ellipse(gx, gy, (W + H) * a * .52, (W + H) * b * .62, 'rgba(0,0,0,.18)');
      for (var d = 0; d <= W + H - 2; d++) {
        for (var x = Math.max(0, d - H + 1); x <= Math.min(W - 1, d); x++) {
          var y = d - x, px = sx(x, y), py = sy(x, y);
          poly([[px - a, py + b], [px, py + 2 * b], [px, py + 2 * b + thick], [px - a, py + b + thick]], theme.side[0]);
          poly([[px + a, py + b], [px, py + 2 * b], [px, py + 2 * b + thick], [px + a, py + b + thick]], theme.side[1]);
          poly([[px, py], [px + a, py + b], [px, py + 2 * b], [px - a, py + b]], (x + y) % 2 ? theme.top[0] : theme.top[1], 'rgba(255,255,255,.16)');
        }
      }
    }

    function drawObstacle(o) {
      var info = kinds[o.x + ',' + o.y] || { kind: 'rock', r: .5 };
      var px = cx(o.x, o.y), py = cy(o.x, o.y), u = a;
      ctx.save();
      switch (info.kind) {
        case 'tree':
          shadowAt(px, py, u * .5);
          box(px, py, u * .13, b * .13, u * .5, '#a16207', '#78350f', '#92400e');
          ellipse(px, py - u * .85, u * .52, u * .5, '#15803d'); ellipse(px - u * .1, py - u * 1.05, u * .38, u * .36, '#16a34a'); ellipse(px - u * .2, py - u * 1.15, u * .12, u * .1, 'rgba(255,255,255,.25)');
          break;
        case 'bush':
          shadowAt(px, py, u * .5);
          [[-.28, -.22, .3], [.28, -.2, .3], [0, -.42, .34]].forEach(function (c) { ellipse(px + c[0] * u, py + c[1] * u, c[2] * u, c[2] * u * .9, '#22c55e'); });
          ellipse(px + u * .15, py - u * .45, u * .06, u * .06, '#ef4444'); ellipse(px - u * .2, py - u * .3, u * .06, u * .06, '#ef4444');
          break;
        case 'rock': case 'darkrock': {
          var dk = info.kind === 'darkrock';
          shadowAt(px, py, u * .55);
          poly([[px - u * .55, py], [px - u * .4, py - u * .45], [px - u * .05, py - u * .7], [px + u * .4, py - u * .5], [px + u * .55, py]], dk ? '#292524' : '#94a3b8');
          poly([[px - u * .4, py - u * .45], [px - u * .05, py - u * .7], [px + u * .05, py - u * .25], [px - u * .3, py - u * .15]], dk ? 'rgba(251,146,60,.4)' : 'rgba(255,255,255,.4)');
          break;
        }
        case 'palm':
          shadowAt(px, py, u * .45);
          ctx.strokeStyle = '#92400e'; ctx.lineWidth = u * .16; ctx.lineCap = 'round';
          ctx.beginPath(); ctx.moveTo(px, py); ctx.quadraticCurveTo(px - u * .15, py - u * .6, px + u * .05, py - u * 1.1); ctx.stroke();
          ctx.fillStyle = '#16a34a';
          [-1.1, -.55, 0, .55, 1.1].forEach(function (an) { ctx.save(); ctx.translate(px + u * .05, py - u * 1.1); ctx.rotate(an - Math.PI / 2 + .1); ctx.beginPath(); ctx.ellipse(0, -u * .32, u * .12, u * .38, 0, 0, TAU); ctx.fill(); ctx.restore(); });
          break;
        case 'crate':
          shadowAt(px, py, u * .6);
          box(px, py, u * .5, b * .5, u * .7, '#fbbf24', '#b45309', '#d97706');
          ctx.strokeStyle = 'rgba(0,0,0,.25)'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.moveTo(px - u * .5, py - u * .35); ctx.lineTo(px, py - u * .35 + b * .5); ctx.lineTo(px + u * .5, py - u * .35); ctx.stroke();
          break;
        case 'pine':
          shadowAt(px, py, u * .45);
          box(px, py, u * .1, b * .1, u * .3, '#a16207', '#78350f', '#92400e');
          [[.55, .15], [.42, .5], [.3, .82]].forEach(function (t, i) {
            ctx.fillStyle = i % 2 ? '#15803d' : '#166534';
            ctx.beginPath(); ctx.moveTo(px, py - u * (t[1] + .5)); ctx.lineTo(px + t[0] * u, py - u * t[1] + u * .12); ctx.lineTo(px - t[0] * u, py - u * t[1] + u * .12); ctx.closePath(); ctx.fill();
          });
          ctx.fillStyle = 'rgba(255,255,255,.9)'; ctx.beginPath(); ctx.moveTo(px, py - u * 1.32); ctx.lineTo(px + u * .17, py - u * 1.0); ctx.lineTo(px - u * .17, py - u * 1.0); ctx.closePath(); ctx.fill();
          break;
        case 'iceblock':
          shadowAt(px, py, u * .6);
          box(px, py, u * .5, b * .5, u * .75, 'rgba(224,242,254,.97)', 'rgba(125,211,252,.95)', 'rgba(56,189,248,.95)');
          ctx.fillStyle = 'rgba(255,255,255,.6)'; ctx.fillRect(px - u * .35, py - u * .62, u * .22, u * .08);
          break;
        case 'snowpile':
          shadowAt(px, py, u * .55);
          ellipse(px, py - u * .15, u * .55, u * .38, '#fff'); ellipse(px + u * .15, py - u * .12, u * .3, u * .2, '#e0f2fe');
          break;
        case 'pillar':
          shadowAt(px, py, u * .45);
          box(px, py, u * .32, b * .32, u * 1.1, theme === THEMES.volcano ? '#a8a29e' : '#c4b5fd', theme === THEMES.volcano ? '#57534e' : '#6d28d9', theme === THEMES.volcano ? '#78716c' : '#7c3aed');
          break;
        case 'crystal': {
          shadowAt(px, py, u * .45);
          var pulse = .7 + .3 * Math.sin(time * 2.5 + info.r * 6);
          var cg = ctx.createLinearGradient(px - u * .4, py - u * 1.1, px + u * .4, py);
          cg.addColorStop(0, '#a5f3fc'); cg.addColorStop(1, '#a855f7');
          ctx.globalAlpha = pulse; ctx.fillStyle = cg;
          ctx.beginPath(); ctx.moveTo(px, py - u * 1.15); ctx.lineTo(px + u * .4, py - u * .45); ctx.lineTo(px + u * .25, py); ctx.lineTo(px - u * .25, py); ctx.lineTo(px - u * .4, py - u * .45); ctx.closePath(); ctx.fill();
          ctx.globalAlpha = 1; ctx.fillStyle = 'rgba(255,255,255,.45)';
          ctx.beginPath(); ctx.moveTo(px, py - u * 1.15); ctx.lineTo(px - u * .1, py - u * .1); ctx.lineTo(px - u * .4, py - u * .45); ctx.closePath(); ctx.fill();
          break;
        }
        case 'asteroid': {
          var fl = Math.sin(time * 1.6 + info.r * 6) * u * .06;
          shadowAt(px, py, u * .4);
          ctx.fillStyle = '#6b7280'; ctx.beginPath();
          for (var i = 0; i < 9; i++) { var an = i / 9 * TAU, rr = u * (.42 + .1 * hash(o.x, o.y, i)); ctx.lineTo(px + Math.cos(an) * rr, py - u * .6 + fl + Math.sin(an) * rr); }
          ctx.closePath(); ctx.fill();
          ctx.fillStyle = 'rgba(0,0,0,.25)'; ctx.beginPath(); ctx.arc(px - u * .12, py - u * .65 + fl, u * .1, 0, TAU); ctx.fill();
          break;
        }
        case 'orb': {
          var fo = Math.sin(time * 2 + info.r * 6) * u * .08;
          shadowAt(px, py, u * .35);
          var og = ctx.createRadialGradient(px, py - u * .65 + fo, 0, px, py - u * .65 + fo, u * .7);
          og.addColorStop(0, 'rgba(244,114,182,.95)'); og.addColorStop(1, 'rgba(168,85,247,0)');
          ctx.fillStyle = og; ctx.beginPath(); ctx.arc(px, py - u * .65 + fo, u * .7, 0, TAU); ctx.fill();
          ctx.fillStyle = '#f0abfc'; ctx.beginPath(); ctx.arc(px, py - u * .65 + fo, u * .28, 0, TAU); ctx.fill();
          break;
        }
        case 'volcano':
          shadowAt(px, py, u * .65);
          poly([[px - u * .62, py + b * .1], [px - u * .2, py - u * .8], [px + u * .2, py - u * .8], [px + u * .62, py + b * .1]], '#44403c');
          poly([[px - u * .2, py - u * .8], [px + u * .2, py - u * .8], [px + u * .62, py + b * .1], [px + u * .1, py + b * .15]], 'rgba(0,0,0,.25)');
          ellipse(px, py - u * .8, u * .22, u * .1, '#f97316');
          ctx.fillStyle = 'rgba(251,146,60,' + (.5 + .3 * Math.sin(time * 4)) + ')'; ctx.beginPath(); ctx.arc(px, py - u * 1.0, u * .12, 0, TAU); ctx.fill();
          break;
        default:
          box(px, py, u * .5, b * .5, u * .6, '#cbd5e1', '#64748b', '#94a3b8');
      }
      ctx.restore();
    }

    function drawGoal() {
      var s = level.star, px = cx(s.x, s.y), py = cy(s.x, s.y), u = a;
      var pulse = 1 + .08 * Math.sin(time * 4);
      // zemindeki halka
      ctx.strokeStyle = 'rgba(253,224,71,' + (.7 + .2 * Math.sin(time * 4)) + ')'; ctx.lineWidth = 3 * dpr;
      ctx.beginPath(); ctx.ellipse(px, py, u * .62 * pulse, b * .62 * pulse, 0, 0, TAU); ctx.stroke();
      ctx.fillStyle = 'rgba(253,224,71,.25)'; ctx.beginPath(); ctx.ellipse(px, py, u * .5, b * .5, 0, 0, TAU); ctx.fill();
      // ışık huzmesi
      var bg = ctx.createLinearGradient(0, py - u * 2.4, 0, py);
      bg.addColorStop(0, 'rgba(253,224,71,0)'); bg.addColorStop(1, 'rgba(253,224,71,' + (goalReached ? .6 : .35) + ')');
      ctx.fillStyle = bg; ctx.beginPath(); ctx.moveTo(px - u * .14, py - u * 2.4); ctx.lineTo(px + u * .14, py - u * 2.4); ctx.lineTo(px + u * .42, py); ctx.lineTo(px - u * .42, py); ctx.closePath(); ctx.fill();
      // dönen yıldız (3B etkisi: yatay ölçek)
      var hy = py - u * (1.0 + .08 * Math.sin(time * 3));
      var spin = Math.cos(time * 2.2);
      ctx.save(); ctx.translate(px, hy); ctx.scale(Math.max(.18, Math.abs(spin)) * pulse, pulse);
      var grd = ctx.createLinearGradient(0, -u * .5, 0, u * .5); grd.addColorStop(0, '#fef08a'); grd.addColorStop(1, '#f59e0b');
      ctx.fillStyle = grd; ctx.strokeStyle = '#b45309'; ctx.lineWidth = 2 * dpr;
      starPath(0, 0, u * .5, u * .22); ctx.fill(); ctx.stroke(); ctx.restore();
    }

    function drawStartMark() {
      var s = level.start, px = cx(s.x, s.y), py = cy(s.x, s.y);
      ctx.strokeStyle = 'rgba(255,255,255,.7)'; ctx.setLineDash([a * .14, a * .1]); ctx.lineWidth = 2.5 * dpr;
      ctx.beginPath(); ctx.ellipse(px, py, a * .6, b * .6, 0, 0, TAU); ctx.stroke(); ctx.setLineDash([]);
    }

    function drawRobot() {
      var px = cx(robot.x, robot.y), baseY = cy(robot.x, robot.y), u = a;
      var gx = Math.cos(robot.angle), gy = Math.sin(robot.angle);
      var idle = robot.mood === 'idle' ? Math.sin(time * 3) * .025 : 0;
      var win = robot.mood === 'win' ? Math.abs(Math.sin(time * 6)) * .35 : 0;
      var lift = (robot.hop + idle + win) * u;
      shadowAt(px, baseY, u * (.4 - (robot.hop + win) * .3));
      // zemindeki yön oku
      var vx = (gx - gy) * a, vy = (gx + gy) * b, len = Math.hypot(vx, vy) || 1, nx = vx / len, ny = vy / len;
      ctx.fillStyle = 'rgba(255,255,255,.85)'; ctx.strokeStyle = 'rgba(15,23,42,.35)'; ctx.lineWidth = 1.5 * dpr;
      // iz düşümünde yer düzlemi dikeyde yarıya sıkışır
      var tipX = px + nx * u * .74, tipY = baseY + ny * u * .37;
      var bx = px + nx * u * .44, by = baseY + ny * u * .22, sxo = -ny * u * .22, syo = nx * u * .11;
      ctx.beginPath(); ctx.moveTo(tipX, tipY); ctx.lineTo(bx + sxo, by + syo); ctx.lineTo(bx - sxo, by - syo); ctx.closePath(); ctx.fill(); ctx.stroke();
      var sq = 1 + robot.squash;
      var py = baseY - lift;
      ctx.save(); ctx.translate(px, py); ctx.scale(sq, 1 / sq); ctx.translate(-px, -py);
      // tekerlek/ayak
      box(px, py, u * .3, b * .3, u * .1, '#334155', '#0f172a', '#1e293b');
      // gövde
      box(px, py - u * .1, u * .36, b * .36, u * .5, '#67e8f9', '#0891b2', '#0e7490');
      // baş
      var headBase = py - u * .62;
      box(px, headBase, u * .31, b * .31, u * .34, '#e0f2fe', '#7dd3fc', '#38bdf8');
      // yüz: yalnızca görünen yüzlere (sağ = +x, sol = +y) göz çiz
      var showRight = gx > .35, showLeft = gy > .35;
      if (showRight || showLeft) {
        var hw = u * .31, hh = b * .31, headH = u * .34;
        ctx.save();
        if (showRight) ctx.transform(1, -hh / hw, 0, 1, px, headBase + hh);
        else ctx.transform(-1, -hh / hw, 0, 1, px, headBase + hh);
        ctx.fillStyle = '#0f172a';
        var blink = (Math.floor(time * 2.2) % 9 === 0) ? .3 : 1;
        [[.33, .55], [.7, .55]].forEach(function (e) { ctx.beginPath(); ctx.ellipse(hw * e[0], -headH * e[1], hw * .09, headH * .17 * blink, 0, 0, TAU); ctx.fill(); });
        if (robot.mood === 'win') { ctx.strokeStyle = '#0f172a'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.arc(hw * .52, -headH * .3, hw * .16, .2, Math.PI - .2); ctx.stroke(); }
        ctx.restore();
      }
      // anten
      ctx.strokeStyle = '#0e7490'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.moveTo(px, headBase - u * .34 - b * .31); ctx.lineTo(px, headBase - u * .55); ctx.stroke();
      ctx.fillStyle = robot.mood === 'crash' ? '#ef4444' : '#fbbf24'; ctx.beginPath(); ctx.arc(px, headBase - u * .57, u * .07 + Math.sin(time * 6) * u * .012, 0, TAU); ctx.fill();
      ctx.restore();
    }

    function drawParticles(dt) {
      for (var i = particles.length - 1; i >= 0; i--) {
        var p = particles[i]; p.life -= p.decay * dt;
        if (p.life <= 0) { particles.splice(i, 1); continue; }
        p.x += p.vx * dt; p.y += p.vy * dt; p.z += p.vz * dt; p.vz -= 3 * dt; if (p.z < 0) { p.z = 0; p.vz *= -.3; }
        var px = cx(p.x, p.y), py = cy(p.x, p.y) - p.z * a;
        ctx.globalAlpha = Math.max(0, Math.min(1, p.life)); ctx.fillStyle = p.color;
        if (p.kind === 'star') { ctx.save(); ctx.translate(px, py); ctx.rotate(p.life * 6); starPath(0, 0, p.size * a * 1.4, p.size * a * .6); ctx.fill(); ctx.restore(); }
        else { ctx.beginPath(); ctx.arc(px, py, p.size * a, 0, TAU); ctx.fill(); }
      }
      ctx.globalAlpha = 1;
    }

    function frame(now) {
      var dt = Math.min(.05, (now - last) / 1000); last = now; time += dt;
      for (var i = tweens.length - 1; i >= 0; i--) {
        var tw = tweens[i], t = Math.min(1, (now - tw.start) / tw.ms); tw.fn(t);
        if (t >= 1) { tweens.splice(i, 1); tw.done(); }
      }
      if (shakeT > 0) shakeT = Math.max(0, shakeT - dt * 2.5);
      if (level) {
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawBackground();
        drawBoard();
        drawStartMark();
        // derinlik sırası: (x+y) küçükten büyüğe; robot kesirli konumuyla araya girer
        var items = [];
        (level.obstacles || []).forEach(function (o) { items.push({ d: o.x + o.y, f: function () { drawObstacle(o); } }); });
        items.push({ d: level.star.x + level.star.y + .01, f: drawGoal });
        items.push({ d: robot.x + robot.y + .02, f: drawRobot });
        items.sort(function (p, q) { return p.d - q.d; });
        items.forEach(function (it) { it.f(); });
        drawParticles(dt);
      }
      if (running) requestAnimationFrame(frame);
    }

    function start() { if (!running) { running = true; last = performance.now(); requestAnimationFrame(frame); } }
    function stop() { running = false; }
    document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
    window.addEventListener('resize', resize);
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(function () { resize(); }).observe(canvas);
    resize(); requestAnimationFrame(frame);

    return {
      setLevel: setLevel, resetRobot: resetRobot, resize: resize, turn: turn, move: move, crash: crash, celebrate: celebrate,
      robot: robot, THEMES: THEMES, themeName: function () { return theme.name; },
      cancelTweens: function () { var t = tweens.splice(0); t.forEach(function (x) { x.done(); }); },
    };
  }

  root.Block3DRender = { create: create, THEMES: THEMES };
})(typeof self !== 'undefined' ? self : this);
