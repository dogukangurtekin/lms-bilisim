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
    var tweens = [], particles = [], clouds = [], stars = [], ambient = [], tallScale = 1;
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
      var big = Math.max(lv.w, lv.h); tallScale = big >= 12 ? .62 : big >= 10 ? .72 : big >= 8 ? .86 : 1;
      kinds = {};
      (lv.obstacles || []).forEach(function (o) {
        var h = hash(o.x, o.y, lv.id || 1);
        kinds[o.x + ',' + o.y] = { kind: theme.kinds[Math.floor(h * theme.kinds.length)], r: hash(o.y, o.x, lv.id || 2) };
      });
      clouds = []; stars = [];
      for (var i = 0; i < 5; i++) clouds.push({ x: hash(i, 1, 5), y: .06 + hash(i, 2, 5) * .3, s: .6 + hash(i, 3, 5) * .9, v: .004 + hash(i, 4, 5) * .008 });
      for (var j = 0; j < 90; j++) stars.push({ x: hash(j, 1, 9), y: hash(j, 2, 9), s: .4 + hash(j, 3, 9) * 1.5, p: hash(j, 4, 9) * TAU });
      ambient = []; for (var k = 0; k < 26; k++) ambient.push({ x: hash(k, 1, 77), ph: hash(k, 2, 77), s: .8 + hash(k, 3, 77) * 1.6, v: .03 + hash(k, 4, 77) * .05 });
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
    function shadowAt(px, py, rx) { var g = ctx.createRadialGradient(px, py, 0, px, py, rx); g.addColorStop(0, 'rgba(0,0,0,.42)'); g.addColorStop(1, 'rgba(0,0,0,0)'); ctx.save(); ctx.translate(px, py); ctx.scale(1, .5); ctx.translate(-px, -py); ctx.fillStyle = g; ctx.beginPath(); ctx.arc(px, py, rx, 0, TAU); ctx.fill(); ctx.restore(); }
    function starPath(px, py, outer, inner) {
      ctx.beginPath();
      for (var i = 0; i < 10; i++) { var r = i % 2 ? inner : outer, an = -Math.PI / 2 + i * Math.PI / 5; ctx.lineTo(px + Math.cos(an) * r, py + Math.sin(an) * r); }
      ctx.closePath();
    }

    // ---- renk yardımcıları (yüzlere ışık/gölge vermek için)
    function hexRgb(h) {
      if (h.charAt(0) !== '#') return null;
      if (h.length === 4) h = '#' + h[1] + h[1] + h[2] + h[2] + h[3] + h[3];
      return [parseInt(h.substr(1, 2), 16), parseInt(h.substr(3, 2), 16), parseInt(h.substr(5, 2), 16)];
    }
    function shade(h, k) { // k: -1 (siyaha) .. +1 (beyaza)
      var c = hexRgb(h); if (!c) return h;
      var t = k < 0 ? 0 : 255, p = Math.abs(k);
      return 'rgb(' + Math.round(c[0] + (t - c[0]) * p) + ',' + Math.round(c[1] + (t - c[1]) * p) + ',' + Math.round(c[2] + (t - c[2]) * p) + ')';
    }
    function vgrad(y0, y1, c0, c1) { var g = ctx.createLinearGradient(0, y0, 0, y1); g.addColorStop(0, c0); g.addColorStop(1, c1); return g; }

    // ---- arka plan
    function drawBackground() {
      var cw = canvas.width, ch = canvas.height, hz = ch * .56;
      var g = ctx.createLinearGradient(0, 0, 0, hz);
      g.addColorStop(0, theme.sky[0]); g.addColorStop(1, theme.sky[1]);
      ctx.fillStyle = g; ctx.fillRect(0, 0, cw, hz + 1);
      // güneş / ay parıltısı
      var sunX = cw * .8, sunY = ch * .16, sr = cw * .2;
      var sg = ctx.createRadialGradient(sunX, sunY, 0, sunX, sunY, sr);
      sg.addColorStop(0, theme.stars ? 'rgba(226,232,255,.55)' : 'rgba(255,248,200,.85)'); sg.addColorStop(.25, theme.stars ? 'rgba(199,210,254,.22)' : 'rgba(255,240,170,.3)'); sg.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.fillStyle = sg; ctx.fillRect(0, 0, cw, hz);
      ctx.fillStyle = theme.stars ? 'rgba(241,245,255,.95)' : 'rgba(255,252,225,.95)';
      ctx.beginPath(); ctx.arc(sunX, sunY, cw * .028, 0, TAU); ctx.fill();
      if (theme.stars) {
        stars.forEach(function (s) {
          var tw = .5 + .5 * Math.sin(time * 2 + s.p);
          ctx.fillStyle = 'rgba(255,255,255,' + (.2 + .6 * tw) + ')';
          ctx.beginPath(); ctx.arc(s.x * cw, s.y * hz, s.s * dpr, 0, TAU); ctx.fill();
        });
      }
      // uzak tepeler (siluet)
      var hillCol = theme.stars ? 'rgba(15,10,46,.55)' : theme.lava ? 'rgba(60,10,10,.5)' : 'rgba(30,64,110,.22)';
      ctx.fillStyle = hillCol; ctx.beginPath(); ctx.moveTo(0, hz);
      for (var hx = 0; hx <= 12; hx++) ctx.lineTo(cw * hx / 12, hz - (8 + 26 * hash(hx, 5, 11)) * dpr * (hx % 3 === 0 ? 1.4 : 1));
      ctx.lineTo(cw, hz); ctx.closePath(); ctx.fill();
      if (theme.clouds) {
        clouds.forEach(function (c) {
          var px = ((c.x + time * c.v) % 1.3 - .15) * cw, py = c.y * ch, w = 90 * c.s * dpr;
          ctx.fillStyle = 'rgba(255,255,255,.82)';
          [[0, 0, 1], [.5, .1, .75], [-.5, .12, .7], [.15, -.18, .6]].forEach(function (p) { ctx.beginPath(); ctx.ellipse(px + p[0] * w, py + p[1] * w * .5, w * .45 * p[2], w * .26 * p[2], 0, 0, TAU); ctx.fill(); });
        });
      }
      // su / zemin
      var wg = ctx.createLinearGradient(0, hz, 0, ch);
      wg.addColorStop(0, theme.water[0]); wg.addColorStop(1, theme.water[1]);
      ctx.fillStyle = wg; ctx.fillRect(0, hz, cw, ch - hz);
      if (theme.lava) {
        var lg = ctx.createRadialGradient(cw / 2, ch * .55, 0, cw / 2, ch * .55, cw * .75);
        lg.addColorStop(0, 'rgba(251,146,60,' + (.28 + .1 * Math.sin(time * 2)) + ')'); lg.addColorStop(1, 'rgba(251,146,60,0)');
        ctx.fillStyle = lg; ctx.fillRect(0, 0, cw, ch);
      }
      // güneş yansıması
      var gl = ctx.createLinearGradient(0, hz, 0, ch);
      gl.addColorStop(0, theme.stars ? 'rgba(199,210,254,.35)' : 'rgba(255,250,210,.5)'); gl.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.fillStyle = gl; ctx.beginPath(); ctx.moveTo(sunX - cw * .03, hz); ctx.lineTo(sunX + cw * .03, hz); ctx.lineTo(sunX + cw * .09, ch); ctx.lineTo(sunX - cw * .09, ch); ctx.closePath(); ctx.fill();
      // dalgalar
      ctx.strokeStyle = 'rgba(255,255,255,.26)'; ctx.lineWidth = 2 * dpr; ctx.lineCap = 'round';
      for (var i = 0; i < 20; i++) {
        var wy = hz + (ch - hz) * (.06 + .94 * hash(i, 1, 4)), wx = hash(i, 2, 4) * cw, ww = (24 + 56 * hash(i, 3, 4)) * dpr;
        var off = Math.sin(time * 1.4 + i) * 9 * dpr;
        ctx.beginPath(); ctx.moveTo(wx + off, wy); ctx.quadraticCurveTo(wx + ww / 2 + off, wy - 5 * dpr, wx + ww + off, wy); ctx.stroke();
      }
    }

    function drawBoard() {
      var W = level.w, H = level.h;
      // sahne zemin gölgesi + ışıma
      var gx = sx((W - 1) / 2, (H - 1) / 2), gy = sy((W - 1) / 2, (H - 1) / 2) + b + thick + a * .35;
      ellipse(gx, gy, (W + H) * a * .56, (W + H) * b * .7, 'rgba(0,0,0,.2)');
      // platform çerçevesi (karoların altındaki koyu kalın taban)
      var m = a * .26, fy = thick * 1.45;
      var T = [sx(0, 0), sy(0, 0) - m * .5], R = [sx(W, 0) + m, sy(W, 0)], B = [sx(W, H), sy(W, H) + m * .5], Lf = [sx(0, H) - m, sy(0, H)];
      poly([Lf, B, [B[0], B[1] + fy], [Lf[0], Lf[1] + fy]], shade(theme.side[0], -.45));
      poly([R, B, [B[0], B[1] + fy], [R[0], R[1] + fy]], shade(theme.side[1], -.55));
      poly([T, R, B, Lf], shade(theme.side[0], -.25));
      for (var d = 0; d <= W + H - 2; d++) {
        for (var x = Math.max(0, d - H + 1); x <= Math.min(W - 1, d); x++) {
          var y = d - x, px = sx(x, y), py = sy(x, y);
          var s0 = theme.side[0], s1 = theme.side[1];
          poly([[px - a, py + b], [px, py + 2 * b], [px, py + 2 * b + thick], [px - a, py + b + thick]], vgrad(py + b, py + b + thick, shade(s0, .12), shade(s0, -.22)));
          poly([[px + a, py + b], [px, py + 2 * b], [px, py + 2 * b + thick], [px + a, py + b + thick]], vgrad(py + b, py + b + thick, shade(s1, .04), shade(s1, -.32)));
          var base = (x + y) % 2 ? theme.top[0] : theme.top[1];
          poly([[px, py], [px + a, py + b], [px, py + 2 * b], [px - a, py + b]], vgrad(py, py + 2 * b, shade(base, .16), shade(base, -.08)));
          // iç parlama + kenar ışığı
          poly([[px, py + b * .26], [px + a * .74, py + b], [px, py + b * 1.74], [px - a * .74, py + b]], 'rgba(255,255,255,.07)');
          ctx.lineWidth = 1.3 * dpr; ctx.lineCap = 'round';
          ctx.strokeStyle = 'rgba(255,255,255,.4)'; ctx.beginPath(); ctx.moveTo(px - a, py + b); ctx.lineTo(px, py); ctx.lineTo(px + a, py + b); ctx.stroke();
          ctx.strokeStyle = 'rgba(0,0,0,.14)'; ctx.beginPath(); ctx.moveTo(px - a, py + b); ctx.lineTo(px, py + 2 * b); ctx.lineTo(px + a, py + b); ctx.stroke();
        }
      }
    }

    function drawObstacle(o) {
      var info = kinds[o.x + ',' + o.y] || { kind: 'rock', r: .5 };
      var px = cx(o.x, o.y), py = cy(o.x, o.y), u = a;
      ctx.save();
      // büyük tahtalarda engeller alçaltılır; arkalarındaki karolar görünsün/seçilebilsin
      if (tallScale < 1) { ctx.translate(px, py); ctx.scale(1, tallScale); ctx.translate(-px, -py); }
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
      // altın kaide
      box(px, py + b * .05, u * .46, b * .46, u * .1, '#fde68a', '#f59e0b', '#d97706');
      // yayılan halkalar
      for (var k = 0; k < 2; k++) {
        var ph = (time * .8 + k * .5) % 1;
        ctx.strokeStyle = 'rgba(253,224,71,' + (.7 * (1 - ph)) + ')'; ctx.lineWidth = 3 * dpr;
        ctx.beginPath(); ctx.ellipse(px, py - u * .1, u * (.4 + .7 * ph), b * (.4 + .7 * ph), 0, 0, TAU); ctx.stroke();
      }
      // ışık huzmesi
      var bg = ctx.createLinearGradient(0, py - u * 2.6, 0, py);
      bg.addColorStop(0, 'rgba(253,224,71,0)'); bg.addColorStop(1, 'rgba(253,224,71,' + (goalReached ? .65 : .4) + ')');
      ctx.fillStyle = bg; ctx.beginPath(); ctx.moveTo(px - u * .16, py - u * 2.6); ctx.lineTo(px + u * .16, py - u * 2.6); ctx.lineTo(px + u * .44, py - u * .1); ctx.lineTo(px - u * .44, py - u * .1); ctx.closePath(); ctx.fill();
      // yıldız parıltısı
      var hy = py - u * (1.05 + .08 * Math.sin(time * 3));
      var halo = ctx.createRadialGradient(px, hy, 0, px, hy, u * .9);
      halo.addColorStop(0, 'rgba(254,240,138,.85)'); halo.addColorStop(1, 'rgba(254,240,138,0)');
      ctx.fillStyle = halo; ctx.beginPath(); ctx.arc(px, hy, u * .9, 0, TAU); ctx.fill();
      // dönen yıldız (3B etkisi: yatay ölçek)
      var spin = Math.cos(time * 2.2);
      ctx.save(); ctx.translate(px, hy); ctx.scale(Math.max(.18, Math.abs(spin)) * pulse, pulse);
      var grd = ctx.createLinearGradient(0, -u * .5, 0, u * .5); grd.addColorStop(0, '#fef9c3'); grd.addColorStop(1, '#f59e0b');
      ctx.fillStyle = grd; ctx.strokeStyle = '#b45309'; ctx.lineWidth = 2 * dpr;
      starPath(0, 0, u * .5, u * .22); ctx.fill(); ctx.stroke(); ctx.restore();
      // yörüngedeki kıvılcımlar
      for (var q = 0; q < 4; q++) {
        var an = time * 1.6 + q * TAU / 4;
        ctx.fillStyle = 'rgba(255,255,255,' + (.5 + .4 * Math.sin(time * 5 + q)) + ')';
        ctx.beginPath(); ctx.arc(px + Math.cos(an) * u * .6, hy + Math.sin(an) * u * .22, u * .045, 0, TAU); ctx.fill();
      }
    }

    function drawStartMark() {
      var s = level.start, px = cx(s.x, s.y), py = cy(s.x, s.y);
      var glow = .35 + .2 * Math.sin(time * 3);
      poly([[px, py - b * .72], [px + a * .72, py], [px, py + b * .72], [px - a * .72, py]], 'rgba(103,232,249,' + glow + ')');
      ctx.strokeStyle = 'rgba(255,255,255,.85)'; ctx.setLineDash([a * .14, a * .1]); ctx.lineWidth = 2.5 * dpr;
      ctx.beginPath(); ctx.ellipse(px, py, a * .6, b * .6, 0, 0, TAU); ctx.stroke(); ctx.setLineDash([]);
    }

    function drawRobot() {
      var px = cx(robot.x, robot.y), baseY = cy(robot.x, robot.y), u = a;
      var gx = Math.cos(robot.angle), gy = Math.sin(robot.angle);
      var idle = robot.mood === 'idle' ? Math.sin(time * 3) * .025 : 0;
      var win = robot.mood === 'win' ? Math.abs(Math.sin(time * 6)) * .35 : 0;
      var lift = (robot.hop + idle + win) * u;
      shadowAt(px, baseY, u * (.42 - (robot.hop + win) * .3));
      // zemindeki yön oku
      var vx = (gx - gy) * a, vy = (gx + gy) * b, len = Math.hypot(vx, vy) || 1, nx = vx / len, ny = vy / len;
      ctx.fillStyle = 'rgba(255,255,255,.9)'; ctx.strokeStyle = 'rgba(15,23,42,.35)'; ctx.lineWidth = 1.5 * dpr;
      var tipX = px + nx * u * .74, tipY = baseY + ny * u * .37;
      var bx = px + nx * u * .44, by = baseY + ny * u * .22, sxo = -ny * u * .22, syo = nx * u * .11;
      ctx.beginPath(); ctx.moveTo(tipX, tipY); ctx.lineTo(bx + sxo, by + syo); ctx.lineTo(bx - sxo, by - syo); ctx.closePath(); ctx.fill(); ctx.stroke();
      var sq = 1 + robot.squash;
      var py = baseY - lift;
      ctx.save(); ctx.translate(px, py); ctx.scale(sq, 1 / sq); ctx.translate(-px, -py);
      // itici alev (havadayken)
      if (robot.hop + win > .03) {
        var fl = (robot.hop + win) * 1.8;
        var fg = ctx.createRadialGradient(px, py + u * .04, 0, px, py + u * .04, u * .3);
        fg.addColorStop(0, 'rgba(253,224,71,.95)'); fg.addColorStop(1, 'rgba(251,146,60,0)');
        ctx.fillStyle = fg; ctx.beginPath(); ctx.ellipse(px, py + u * .08, u * .26, u * (.14 + fl * .18), 0, 0, TAU); ctx.fill();
      }
      // paletli ayak tabanı
      box(px, py, u * .32, b * .32, u * .11, '#475569', '#0f172a', '#1e293b');
      ctx.fillStyle = '#22d3ee'; ctx.beginPath(); ctx.ellipse(px, py - u * .02, u * .16, b * .16, 0, 0, TAU); ctx.fill();
      // yan kollar
      var armL = [px - u * .38, py - u * .34], armR = [px + u * .38, py - u * .34];
      ctx.fillStyle = '#0e7490'; ctx.beginPath(); ctx.ellipse(armL[0], armL[1], u * .09, u * .12, 0, 0, TAU); ctx.ellipse(armR[0], armR[1], u * .09, u * .12, 0, 0, TAU); ctx.fill();
      // gövde (parlak mavi), göğüs paneli
      box(px, py - u * .1, u * .36, b * .36, u * .5, '#a5f3fc', '#06b6d4', '#0e7490');
      var chestX = px + (gx - gy) * u * .12, chestY = py - u * .36 + (gx + gy) * u * .05;
      ctx.fillStyle = 'rgba(255,255,255,.55)'; ctx.beginPath(); ctx.ellipse(chestX, chestY, u * .11, u * .08, 0, 0, TAU); ctx.fill();
      ctx.fillStyle = robot.mood === 'crash' ? '#ef4444' : '#22c55e'; ctx.beginPath(); ctx.arc(chestX, chestY, u * .04, 0, TAU); ctx.fill();
      // baş (küre + camsı vizör)
      var hc = [px, py - u * .98], hr = u * .36;
      var hg = ctx.createRadialGradient(hc[0] - hr * .35, hc[1] - hr * .4, hr * .1, hc[0], hc[1], hr);
      hg.addColorStop(0, '#ffffff'); hg.addColorStop(1, '#7dd3fc');
      ctx.fillStyle = hg; ctx.beginPath(); ctx.arc(hc[0], hc[1], hr, 0, TAU); ctx.fill();
      ctx.strokeStyle = 'rgba(14,116,144,.5)'; ctx.lineWidth = 1.6 * dpr; ctx.stroke();
      // vizör: yüzün baktığı yöne kayar; arkaya bakıyorsa daha küçük görünür
      var facing = ny; // >0: kameraya doğru
      var vsx = hc[0] + nx * hr * .42, vsy = hc[1] + hr * .06 + ny * hr * .2;
      var vw = hr * (.62 - .18 * Math.max(0, -facing)), vh = hr * .42;
      ctx.fillStyle = facing > -.25 ? '#0f172a' : 'rgba(15,23,42,.55)';
      ctx.beginPath(); ctx.ellipse(vsx, vsy, vw, vh, 0, 0, TAU); ctx.fill();
      if (facing > -.25) {
        var blink = (Math.floor(time * 2.2) % 9 === 0) ? .25 : 1;
        var ex = -ny * hr * .2, ey = nx * hr * .05;
        ctx.fillStyle = robot.mood === 'crash' ? '#fca5a5' : '#67e8f9';
        ctx.shadowColor = robot.mood === 'crash' ? '#ef4444' : '#22d3ee'; ctx.shadowBlur = 8 * dpr;
        ctx.beginPath(); ctx.ellipse(vsx + ex, vsy + ey, hr * .09, hr * .15 * blink, 0, 0, TAU); ctx.ellipse(vsx - ex, vsy - ey, hr * .09, hr * .15 * blink, 0, 0, TAU); ctx.fill();
        ctx.shadowBlur = 0;
        if (robot.mood === 'win') { ctx.strokeStyle = '#67e8f9'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.arc(vsx, vsy + hr * .12, hr * .16, .2, Math.PI - .2); ctx.stroke(); }
      }
      // anten + parlayan uç
      ctx.strokeStyle = '#0e7490'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.moveTo(hc[0], hc[1] - hr); ctx.lineTo(hc[0], hc[1] - hr - u * .22); ctx.stroke();
      var bulb = robot.mood === 'crash' ? '#ef4444' : '#fbbf24', ay = hc[1] - hr - u * .26;
      var ag = ctx.createRadialGradient(hc[0], ay, 0, hc[0], ay, u * .2);
      ag.addColorStop(0, bulb); ag.addColorStop(1, 'rgba(251,191,36,0)');
      ctx.fillStyle = ag; ctx.globalAlpha = .55 + .35 * Math.sin(time * 6); ctx.beginPath(); ctx.arc(hc[0], ay, u * .2, 0, TAU); ctx.fill(); ctx.globalAlpha = 1;
      ctx.fillStyle = bulb; ctx.beginPath(); ctx.arc(hc[0], ay, u * .07, 0, TAU); ctx.fill();
      ctx.restore();
    }

    // ---- ortam parçacıkları (temaya göre) ve vinyet
    function drawAmbient() {
      var cw = canvas.width, ch = canvas.height;
      ambient.forEach(function (p) {
        var t = (time * p.v + p.ph) % 1;
        var px, py, al;
        if (theme.name === 'Kar') { px = ((p.x + Math.sin(time + p.ph * 9) * .02 + t * .04) % 1) * cw; py = t * ch; al = .85; ctx.fillStyle = 'rgba(255,255,255,' + al + ')'; ctx.beginPath(); ctx.arc(px, py, p.s * dpr * 1.4, 0, TAU); ctx.fill(); }
        else if (theme.name === 'Yanardağ') { px = (p.x + Math.sin(time * 1.4 + p.ph * 9) * .03) * cw; py = ch * (1 - t); al = Math.sin(t * Math.PI); ctx.fillStyle = 'rgba(251,146,60,' + al * .9 + ')'; ctx.beginPath(); ctx.arc(px, py, p.s * dpr * 1.1, 0, TAU); ctx.fill(); }
        else if (theme.name === 'Kumsal') { px = (p.x + Math.sin(time * 1.2 + p.ph * 9) * .015) * cw; py = ch * (.98 - t * .42); al = Math.sin(t * Math.PI); ctx.strokeStyle = 'rgba(255,255,255,' + al * .7 + ')'; ctx.lineWidth = 1.5 * dpr; ctx.beginPath(); ctx.arc(px, py, p.s * dpr * 2.2, 0, TAU); ctx.stroke(); }
        else if (theme.name === 'Kozmik' || theme.name === 'Alacakaranlık') { px = (p.x + Math.sin(time * .6 + p.ph * 9) * .03) * cw; py = (.18 + .7 * ((p.ph * 7) % 1)) * ch; al = .35 + .65 * Math.abs(Math.sin(time * 2 + p.ph * 20)); var gl = ctx.createRadialGradient(px, py, 0, px, py, p.s * dpr * 5); gl.addColorStop(0, 'rgba(253,230,138,' + al + ')'); gl.addColorStop(1, 'rgba(253,230,138,0)'); ctx.fillStyle = gl; ctx.beginPath(); ctx.arc(px, py, p.s * dpr * 5, 0, TAU); ctx.fill(); }
        else { px = (p.x + Math.sin(time * .8 + p.ph * 9) * .04) * cw; py = ch * (.9 - t * .6); al = Math.sin(t * Math.PI) * .8; ctx.fillStyle = 'rgba(255,253,200,' + al + ')'; ctx.beginPath(); ctx.arc(px, py, p.s * dpr * 1.2, 0, TAU); ctx.fill(); }
      });
      var vg = ctx.createRadialGradient(cw / 2, ch * .52, Math.min(cw, ch) * .35, cw / 2, ch * .52, Math.max(cw, ch) * .78);
      vg.addColorStop(0, 'rgba(0,0,0,0)'); vg.addColorStop(1, 'rgba(8,12,32,.34)');
      ctx.fillStyle = vg; ctx.fillRect(0, 0, cw, ch);
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
        drawAmbient();
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
