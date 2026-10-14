/* Blok Kodlama — tuval çizimi (v2): temalı zemin, engeller, parlayan yıldız, animasyonlu robot, parçacıklar. */
(function (root) {
  'use strict';

  var TAU = Math.PI * 2;

  var THEMES = {
    forest: { name: 'Orman',  a: '#86efac', b: '#6ee7b7', edge: '#14532d', glow: 'rgba(255,255,255,.18)', kinds: ['tree', 'tree', 'bush', 'rock'] },
    desert: { name: 'Çöl',    a: '#fde68a', b: '#fcd34d', edge: '#92400e', glow: 'rgba(255,255,255,.22)', kinds: ['cactus', 'rock', 'cactus', 'dune'] },
    ice:    { name: 'Buz',    a: '#e0f2fe', b: '#bae6fd', edge: '#075985', glow: 'rgba(255,255,255,.35)', kinds: ['iceblock', 'pine', 'snowpile', 'iceblock'] },
    space:  { name: 'Uzay',   a: '#312e81', b: '#3730a3', edge: '#1e1b4b', glow: 'rgba(165,180,252,.18)', kinds: ['asteroid', 'crystal', 'asteroid', 'orb'], stars: true },
    lava:   { name: 'Lav',    a: '#7f1d1d', b: '#991b1b', edge: '#450a0a', glow: 'rgba(251,146,60,.20)',  kinds: ['volcano', 'darkrock', 'darkrock', 'volcano'], cracks: true },
    cosmic: { name: 'Kozmik', a: '#1e1b4b', b: '#2e1065', edge: '#0f0a2e', glow: 'rgba(217,70,239,.20)',  kinds: ['crystal', 'orb', 'asteroid', 'crystal'], stars: true },
  };

  function hash(a, b, c) {
    var h = (a * 374761393 + b * 668265263 + c * 2147483647) | 0;
    h = (h ^ (h >>> 13)) * 1274126177;
    return ((h ^ (h >>> 16)) >>> 0) / 4294967296;
  }

  function lerp(a, b, t) { return a + (b - a) * t; }
  function easeInOut(t) { return t < .5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; }
  function shortAngle(from, to) {
    var d = (to - from) % TAU;
    if (d > Math.PI) d -= TAU;
    if (d < -Math.PI) d += TAU;
    return d;
  }

  function create(canvas) {
    var ctx = canvas.getContext('2d');
    var level = null, theme = THEMES.forest;
    var cssSize = 480, dpr = 1, cell = 40, ox = 0, oy = 0;
    var robot = { x: 0, y: 0, angle: 0, bob: 0, squash: 0, mood: 'idle' };
    var tweens = [];
    var particles = [];
    var time = 0, last = performance.now(), running = true, goalReached = false, shake = 0;
    var starsBg = [];
    var obstacleKinds = {};

    function resize() {
      var rect = canvas.getBoundingClientRect();
      var size = Math.max(200, Math.round(rect.width || 480));
      var hgt = Math.max(160, Math.round(rect.height || size));   // geniş ekranda yükseklik genişlikten küçük olabilir
      dpr = Math.min(2.5, window.devicePixelRatio || 1);
      cssSize = size;
      canvas.width = Math.round(size * dpr);
      canvas.height = Math.round(hgt * dpr);
      layout();
    }

    function layout() {
      if (!level) return;
      var n = Math.max(level.w, level.h);
      cell = Math.min(canvas.width / level.w, canvas.height / level.h);
      ox = (canvas.width - cell * level.w) / 2;
      oy = (canvas.height - cell * level.h) / 2;
    }

    function setLevel(lv, themeName) {
      level = lv;
      theme = THEMES[themeName || lv.theme] || THEMES.forest;
      obstacleKinds = {};
      (lv.obstacles || []).forEach(function (o) {
        var h = hash(o.x, o.y, lv.id || 1);
        obstacleKinds[o.x + ',' + o.y] = { kind: theme.kinds[Math.floor(h * theme.kinds.length)], r: hash(o.y, o.x, lv.id || 2) };
      });
      starsBg = [];
      if (theme.stars) for (var i = 0; i < 60; i++) starsBg.push({ x: hash(i, 1, 7), y: hash(i, 2, 7), s: .4 + hash(i, 3, 7) * 1.4, p: hash(i, 4, 7) * TAU });
      layout();
      resetRobot();
      particles = []; tweens = []; goalReached = false;
    }

    function resetRobot() {
      if (!level) return;
      robot.x = level.start.x; robot.y = level.start.y;
      robot.angle = level.start.dir * Math.PI / 2;
      robot.bob = 0; robot.squash = 0; robot.mood = 'idle';
      goalReached = false;
    }

    // ----- animasyon yardımcıları -----
    function tween(ms, onUpdate) {
      return new Promise(function (resolve) {
        if (!ms || ms <= 0) { onUpdate(1); resolve(); return; }
        tweens.push({ start: performance.now(), ms: ms, fn: onUpdate, done: resolve });
      });
    }

    function turn(dir, ms) {
      var from = robot.angle;
      var to = from + shortAngle(from, dir * Math.PI / 2);
      return tween(ms, function (t) { robot.angle = lerp(from, to, easeInOut(t)); robot.squash = Math.sin(t * Math.PI) * .08; });
    }

    function move(to, ms) {
      var fx = robot.x, fy = robot.y;
      puff(fx, fy, 4);
      return tween(ms, function (t) {
        var e = easeInOut(t);
        robot.x = lerp(fx, to.x, e); robot.y = lerp(fy, to.y, e);
        robot.bob = Math.sin(t * Math.PI) * .08;
      }).then(function () { robot.x = to.x; robot.y = to.y; robot.bob = 0; });
    }

    function crash(target, ms) {
      // hedefe doğru atılıp geri sekme
      var fx = robot.x, fy = robot.y;
      var dx = (target.x - fx) * .38, dy = (target.y - fy) * .38;
      robot.mood = 'crash';
      return tween(ms, function (t) {
        var k = t < .5 ? t * 2 : (1 - t) * 2;
        robot.x = fx + dx * k; robot.y = fy + dy * k;
        if (t > .45 && t < .55) sparks(fx + dx * 1.3 + .5, fy + dy * 1.3 + .5, 10);
      }).then(function () { robot.x = fx; robot.y = fy; shake = 1; });
    }

    function celebrate() {
      goalReached = true; robot.mood = 'win';
      var s = level.star;
      for (var i = 0; i < 36; i++) {
        var a = hash(i, 5, 9) * TAU, sp = .6 + hash(i, 6, 9) * 2.4;
        particles.push({ x: s.x + .5, y: s.y + .5, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp - 1, life: 1, decay: .6 + hash(i, 7, 9) * .5, kind: 'star', color: ['#fde047', '#fb923c', '#f472b6', '#60a5fa', '#34d399'][i % 5], size: .12 + hash(i, 8, 9) * .18 });
      }
    }

    function puff(cx, cy, count) {
      for (var i = 0; i < count; i++) {
        particles.push({ x: cx + .5 + (Math.random() - .5) * .4, y: cy + .5 + .25, vx: (Math.random() - .5) * .8, vy: -Math.random() * .6, life: 1, decay: 2.2, kind: 'dust', color: 'rgba(255,255,255,.55)', size: .08 + Math.random() * .08 });
      }
    }
    function sparks(cx, cy, count) {
      for (var i = 0; i < count; i++) {
        var a = Math.random() * TAU, sp = 1 + Math.random() * 2.5;
        particles.push({ x: cx, y: cy, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp, life: 1, decay: 2.4, kind: 'dust', color: ['#fca5a5', '#fde68a', '#fff'][i % 3], size: .06 + Math.random() * .08 });
      }
    }

    // ----- çizim -----
    function px(v) { return v * cell; }

    // Geniş ekranda tahtanın yanlarını temaya uygun arka planla doldurur; tahtaya çerçeve ve gölge verir.
    function drawBackdrop() {
      var cw = canvas.width, ch = canvas.height;
      var g = ctx.createLinearGradient(0, 0, 0, ch);
      g.addColorStop(0, theme.b); g.addColorStop(1, theme.edge);
      ctx.fillStyle = g; ctx.fillRect(0, 0, cw, ch);
      ctx.fillStyle = theme.glow;
      for (var i = 0; i < 46; i++) {
        var sxp = hash(i, 21, 5) * cw, syp = hash(i, 22, 5) * ch, sr = (1 + hash(i, 23, 5) * 2.4) * dpr;
        ctx.beginPath(); ctx.arc(sxp, syp, sr, 0, TAU); ctx.fill();
      }
      var vg = ctx.createRadialGradient(cw / 2, ch / 2, Math.min(cw, ch) * .35, cw / 2, ch / 2, Math.max(cw, ch) * .75);
      vg.addColorStop(0, 'rgba(0,0,0,0)'); vg.addColorStop(1, 'rgba(0,0,0,.32)');
      ctx.fillStyle = vg; ctx.fillRect(0, 0, cw, ch);
      if (level) {
        ctx.save();
        ctx.shadowColor = 'rgba(0,0,0,.45)'; ctx.shadowBlur = 18 * dpr; ctx.shadowOffsetY = 4 * dpr;
        ctx.fillStyle = theme.edge;
        ctx.fillRect(ox - 4 * dpr, oy - 4 * dpr, cell * level.w + 8 * dpr, cell * level.h + 8 * dpr);
        ctx.restore();
      }
    }

    function drawBoard() {
      var w = level.w, h = level.h;
      ctx.save();
      ctx.translate(ox, oy);
      for (var y = 0; y < h; y++) for (var x = 0; x < w; x++) {
        ctx.fillStyle = (x + y) % 2 ? theme.a : theme.b;
        ctx.fillRect(px(x), px(y), cell + .5, cell + .5);
      }
      if (theme.stars) {
        starsBg.forEach(function (s) {
          var tw = .5 + .5 * Math.sin(time * 2 + s.p);
          ctx.fillStyle = 'rgba(255,255,255,' + (.25 + .55 * tw) + ')';
          ctx.beginPath(); ctx.arc(s.x * px(w), s.y * px(h), s.s * dpr, 0, TAU); ctx.fill();
        });
      }
      if (theme.cracks) {
        ctx.strokeStyle = 'rgba(251,146,60,' + (.35 + .2 * Math.sin(time * 2)) + ')';
        ctx.lineWidth = 2 * dpr;
        for (var i = 0; i < w * h * .35; i++) {
          var cx = hash(i, 11, 3) * px(w), cy = hash(i, 12, 3) * px(h);
          ctx.beginPath(); ctx.moveTo(cx, cy);
          ctx.lineTo(cx + (hash(i, 13, 3) - .5) * cell, cy + (hash(i, 14, 3) - .5) * cell);
          ctx.lineTo(cx + (hash(i, 15, 3) - .5) * cell * 1.3, cy + (hash(i, 16, 3) - .5) * cell * 1.3);
          ctx.stroke();
        }
      }
      // ince ızgara çizgileri
      ctx.strokeStyle = 'rgba(0,0,0,.07)';
      ctx.lineWidth = 1;
      ctx.beginPath();
      for (var gx = 0; gx <= w; gx++) { ctx.moveTo(px(gx), 0); ctx.lineTo(px(gx), px(h)); }
      for (var gy = 0; gy <= h; gy++) { ctx.moveTo(0, px(gy)); ctx.lineTo(px(w), px(gy)); }
      ctx.stroke();
      // kenar
      ctx.strokeStyle = theme.edge; ctx.lineWidth = 4 * dpr;
      ctx.strokeRect(0, 0, px(w), px(h));
      ctx.restore();
    }

    function shadow(cx, cy, rx, ry, a) {
      ctx.fillStyle = 'rgba(0,0,0,' + (a || .22) + ')';
      ctx.beginPath(); ctx.ellipse(cx, cy, rx, ry, 0, 0, TAU); ctx.fill();
    }

    function drawObstacle(o) {
      var info = obstacleKinds[o.x + ',' + o.y] || { kind: 'rock', r: .5 };
      var cx = ox + px(o.x) + cell / 2, cy = oy + px(o.y) + cell / 2, u = cell / 2;
      var k = info.kind;
      ctx.save();
      switch (k) {
        case 'tree':
          shadow(cx, cy + u * .72, u * .62, u * .22);
          ctx.fillStyle = '#7c4a21'; ctx.fillRect(cx - u * .13, cy + u * .1, u * .26, u * .6);
          ctx.fillStyle = '#15803d'; ctx.beginPath(); ctx.arc(cx, cy - u * .1, u * .62, 0, TAU); ctx.fill();
          ctx.fillStyle = '#16a34a'; ctx.beginPath(); ctx.arc(cx - u * .15, cy - u * .28, u * .42, 0, TAU); ctx.fill();
          ctx.fillStyle = 'rgba(255,255,255,.18)'; ctx.beginPath(); ctx.arc(cx - u * .25, cy - u * .4, u * .16, 0, TAU); ctx.fill();
          break;
        case 'bush':
          shadow(cx, cy + u * .6, u * .7, u * .2);
          ctx.fillStyle = '#22c55e';
          [[-.35, .1, .4], [.3, .1, .42], [0, -.15, .46]].forEach(function (c) { ctx.beginPath(); ctx.arc(cx + c[0] * u, cy + c[1] * u, c[2] * u, 0, TAU); ctx.fill(); });
          ctx.fillStyle = '#ef4444'; [[-.2, -.05], [.25, -.2], [.1, .2]].forEach(function (d) { ctx.beginPath(); ctx.arc(cx + d[0] * u, cy + d[1] * u, u * .07, 0, TAU); ctx.fill(); });
          break;
        case 'rock': case 'darkrock': {
          var dark = k === 'darkrock';
          shadow(cx, cy + u * .68, u * .72, u * .22, .3);
          ctx.fillStyle = dark ? '#1f1a1a' : '#94a3b8';
          ctx.beginPath();
          ctx.moveTo(cx - u * .7, cy + u * .55); ctx.lineTo(cx - u * .5, cy - u * .35); ctx.lineTo(cx - u * .05, cy - u * .72);
          ctx.lineTo(cx + u * .5, cy - u * .4); ctx.lineTo(cx + u * .72, cy + u * .55); ctx.closePath(); ctx.fill();
          ctx.fillStyle = dark ? 'rgba(251,146,60,.35)' : 'rgba(255,255,255,.35)';
          ctx.beginPath(); ctx.moveTo(cx - u * .5, cy - u * .35); ctx.lineTo(cx - u * .05, cy - u * .72); ctx.lineTo(cx + u * .1, cy - u * .1); ctx.lineTo(cx - u * .35, cy + u * .05); ctx.closePath(); ctx.fill();
          break;
        }
        case 'cactus':
          shadow(cx, cy + u * .72, u * .55, u * .2);
          ctx.fillStyle = '#16a34a';
          roundRect(cx - u * .17, cy - u * .65, u * .34, u * 1.3, u * .17); ctx.fill();
          roundRect(cx - u * .6, cy - u * .2, u * .28, u * .2, u * .1); ctx.fill();
          roundRect(cx - u * .6, cy - u * .5, u * .18, u * .42, u * .09); ctx.fill();
          roundRect(cx + u * .32, cy - u * .05, u * .28, u * .2, u * .1); ctx.fill();
          roundRect(cx + u * .42, cy - u * .4, u * .18, u * .42, u * .09); ctx.fill();
          ctx.fillStyle = 'rgba(255,255,255,.25)'; ctx.fillRect(cx - u * .08, cy - u * .55, u * .06, u * 1.1);
          break;
        case 'dune':
          shadow(cx, cy + u * .55, u * .8, u * .18, .15);
          ctx.fillStyle = '#d97706'; ctx.beginPath(); ctx.ellipse(cx, cy + u * .25, u * .85, u * .5, 0, Math.PI, TAU); ctx.fill();
          ctx.fillStyle = '#f59e0b'; ctx.beginPath(); ctx.ellipse(cx - u * .15, cy + u * .25, u * .6, u * .34, 0, Math.PI, TAU); ctx.fill();
          break;
        case 'iceblock':
          shadow(cx, cy + u * .72, u * .7, u * .2, .2);
          ctx.fillStyle = 'rgba(125,211,252,.95)'; roundRect(cx - u * .7, cy - u * .65, u * 1.4, u * 1.3, u * .2); ctx.fill();
          ctx.fillStyle = 'rgba(255,255,255,.6)'; roundRect(cx - u * .55, cy - u * .5, u * .5, u * .22, u * .1); ctx.fill();
          ctx.strokeStyle = 'rgba(14,116,144,.5)'; ctx.lineWidth = 2 * dpr; roundRect(cx - u * .7, cy - u * .65, u * 1.4, u * 1.3, u * .2); ctx.stroke();
          break;
        case 'snowpile':
          shadow(cx, cy + u * .55, u * .8, u * .18, .15);
          ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.ellipse(cx, cy + u * .3, u * .8, u * .55, 0, Math.PI, TAU); ctx.fill();
          ctx.fillStyle = '#e0f2fe'; ctx.beginPath(); ctx.ellipse(cx + u * .2, cy + u * .3, u * .5, u * .32, 0, Math.PI, TAU); ctx.fill();
          break;
        case 'pine':
          shadow(cx, cy + u * .75, u * .5, u * .18);
          ctx.fillStyle = '#6b4423'; ctx.fillRect(cx - u * .1, cy + u * .35, u * .2, u * .4);
          [[.62, .1], [.5, -.18], [.36, -.44]].forEach(function (t, i) {
            ctx.fillStyle = i % 2 ? '#15803d' : '#166534';
            ctx.beginPath(); ctx.moveTo(cx, cy + t[1] * u - u * .5); ctx.lineTo(cx + t[0] * u, cy + t[1] * u + u * .3); ctx.lineTo(cx - t[0] * u, cy + t[1] * u + u * .3); ctx.closePath(); ctx.fill();
          });
          ctx.fillStyle = 'rgba(255,255,255,.85)'; ctx.beginPath(); ctx.moveTo(cx, cy - u * .95); ctx.lineTo(cx + u * .22, cy - u * .55); ctx.lineTo(cx - u * .22, cy - u * .55); ctx.closePath(); ctx.fill();
          break;
        case 'asteroid':
          shadow(cx, cy + u * .7, u * .7, u * .2, .3);
          ctx.fillStyle = '#6b7280';
          ctx.beginPath();
          for (var i = 0; i < 9; i++) { var a = i / 9 * TAU, r = u * (.62 + .16 * hash(o.x, o.y, i)); ctx.lineTo(cx + Math.cos(a) * r, cy + Math.sin(a) * r); }
          ctx.closePath(); ctx.fill();
          ctx.fillStyle = 'rgba(0,0,0,.25)'; [[-.2, -.15, .16], [.25, .1, .12], [-.05, .3, .1]].forEach(function (c) { ctx.beginPath(); ctx.arc(cx + c[0] * u, cy + c[1] * u, c[2] * u, 0, TAU); ctx.fill(); });
          break;
        case 'crystal': {
          shadow(cx, cy + u * .7, u * .6, u * .18, .3);
          var pulse = .7 + .3 * Math.sin(time * 2.5 + info.r * 6);
          var grd = ctx.createLinearGradient(cx - u * .5, cy - u, cx + u * .5, cy + u);
          grd.addColorStop(0, '#a5f3fc'); grd.addColorStop(1, '#a855f7');
          ctx.fillStyle = grd; ctx.globalAlpha = pulse;
          ctx.beginPath(); ctx.moveTo(cx, cy - u * .85); ctx.lineTo(cx + u * .5, cy - u * .1); ctx.lineTo(cx + u * .3, cy + u * .65); ctx.lineTo(cx - u * .3, cy + u * .65); ctx.lineTo(cx - u * .5, cy - u * .1); ctx.closePath(); ctx.fill();
          ctx.globalAlpha = 1; ctx.fillStyle = 'rgba(255,255,255,.45)';
          ctx.beginPath(); ctx.moveTo(cx, cy - u * .85); ctx.lineTo(cx - u * .1, cy + u * .1); ctx.lineTo(cx - u * .5, cy - u * .1); ctx.closePath(); ctx.fill();
          break;
        }
        case 'orb': {
          var p2 = .6 + .4 * Math.sin(time * 3 + info.r * 8);
          var g2 = ctx.createRadialGradient(cx, cy, 0, cx, cy, u * .75);
          g2.addColorStop(0, 'rgba(244,114,182,' + p2 + ')'); g2.addColorStop(1, 'rgba(168,85,247,0)');
          ctx.fillStyle = g2; ctx.beginPath(); ctx.arc(cx, cy, u * .8, 0, TAU); ctx.fill();
          ctx.fillStyle = '#f0abfc'; ctx.beginPath(); ctx.arc(cx, cy, u * .32, 0, TAU); ctx.fill();
          break;
        }
        case 'volcano':
          shadow(cx, cy + u * .72, u * .75, u * .2, .35);
          ctx.fillStyle = '#292524'; ctx.beginPath(); ctx.moveTo(cx - u * .8, cy + u * .65); ctx.lineTo(cx - u * .25, cy - u * .55); ctx.lineTo(cx + u * .25, cy - u * .55); ctx.lineTo(cx + u * .8, cy + u * .65); ctx.closePath(); ctx.fill();
          ctx.fillStyle = '#f97316'; ctx.beginPath(); ctx.ellipse(cx, cy - u * .55, u * .27, u * .12, 0, 0, TAU); ctx.fill();
          ctx.fillStyle = 'rgba(251,146,60,' + (.5 + .3 * Math.sin(time * 4)) + ')'; ctx.beginPath(); ctx.arc(cx, cy - u * .78, u * .14, 0, TAU); ctx.fill();
          break;
        default:
          ctx.fillStyle = '#94a3b8'; roundRect(cx - u * .7, cy - u * .7, u * 1.4, u * 1.4, u * .2); ctx.fill();
      }
      ctx.restore();
    }

    function roundRect(x, y, w, h, r) {
      ctx.beginPath();
      ctx.moveTo(x + r, y); ctx.arcTo(x + w, y, x + w, y + h, r); ctx.arcTo(x + w, y + h, x, y + h, r);
      ctx.arcTo(x, y + h, x, y, r); ctx.arcTo(x, y, x + w, y, r); ctx.closePath();
    }

    function starPath(cx, cy, outer, inner) {
      ctx.beginPath();
      for (var i = 0; i < 10; i++) {
        var r = i % 2 ? inner : outer, a = -Math.PI / 2 + i * Math.PI / 5;
        ctx.lineTo(cx + Math.cos(a) * r, cy + Math.sin(a) * r);
      }
      ctx.closePath();
    }

    function drawStar() {
      var s = level.star;
      var cx = ox + px(s.x) + cell / 2, cy = oy + px(s.y) + cell / 2, u = cell / 2;
      var pulse = 1 + .08 * Math.sin(time * 4);
      // hale
      var g = ctx.createRadialGradient(cx, cy, 0, cx, cy, u * 1.5);
      g.addColorStop(0, 'rgba(253,224,71,' + (goalReached ? .9 : .55) + ')'); g.addColorStop(1, 'rgba(253,224,71,0)');
      ctx.fillStyle = g; ctx.beginPath(); ctx.arc(cx, cy, u * 1.5, 0, TAU); ctx.fill();
      shadow(cx, cy + u * .62, u * .5, u * .14, .25);
      ctx.save();
      ctx.translate(cx, cy + Math.sin(time * 3) * u * .06);
      ctx.rotate(Math.sin(time * 1.5) * .12);
      ctx.scale(pulse, pulse);
      var grd = ctx.createLinearGradient(0, -u * .7, 0, u * .7);
      grd.addColorStop(0, '#fef08a'); grd.addColorStop(1, '#f59e0b');
      ctx.fillStyle = grd; ctx.strokeStyle = '#b45309'; ctx.lineWidth = 2 * dpr;
      starPath(0, 0, u * .72, u * .32); ctx.fill(); ctx.stroke();
      ctx.fillStyle = 'rgba(255,255,255,.55)'; ctx.beginPath(); ctx.arc(-u * .18, -u * .15, u * .09, 0, TAU); ctx.fill();
      ctx.restore();
      // parıltılar
      for (var i = 0; i < 3; i++) {
        var a = time * 1.3 + i * TAU / 3, rr = u * (.95 + .1 * Math.sin(time * 3 + i));
        ctx.fillStyle = 'rgba(255,255,255,' + (.5 + .4 * Math.sin(time * 5 + i)) + ')';
        ctx.beginPath(); ctx.arc(cx + Math.cos(a) * rr, cy + Math.sin(a) * rr, u * .06, 0, TAU); ctx.fill();
      }
    }

    function drawStart() {
      // başlangıç noktası: yumuşak bir işaret
      var s = level.start;
      var cx = ox + px(s.x) + cell / 2, cy = oy + px(s.y) + cell / 2;
      ctx.strokeStyle = 'rgba(255,255,255,.55)'; ctx.setLineDash([cell * .12, cell * .1]); ctx.lineWidth = 2.5 * dpr;
      ctx.beginPath(); ctx.arc(cx, cy, cell * .42, 0, TAU); ctx.stroke(); ctx.setLineDash([]);
    }

    function drawRobot() {
      var cx = ox + px(robot.x) + cell / 2, cy = oy + px(robot.y) + cell / 2, u = cell / 2;
      var idle = robot.mood === 'idle' ? Math.sin(time * 3) * .025 : 0;
      var win = robot.mood === 'win' ? Math.abs(Math.sin(time * 6)) * .22 : 0;
      shadow(cx, cy + u * .7, u * (.55 - win * .4), u * .16, .28);
      ctx.save();
      ctx.translate(cx, cy - (robot.bob + idle + win) * u * 1.4);
      ctx.rotate(robot.angle);
      var sq = 1 + robot.squash;
      ctx.scale(sq, 1 / sq);
      // tekerlekler
      ctx.fillStyle = '#1e293b';
      roundRect(-u * .5, -u * .62, u * .62, u * .22, u * .08); ctx.fill();
      roundRect(-u * .5, u * .4, u * .62, u * .22, u * .08); ctx.fill();
      // gövde
      var body = ctx.createLinearGradient(-u * .6, -u * .6, u * .6, u * .6);
      body.addColorStop(0, '#818cf8'); body.addColorStop(1, '#4f46e5');
      ctx.fillStyle = body; ctx.strokeStyle = '#312e81'; ctx.lineWidth = 2 * dpr;
      roundRect(-u * .58, -u * .5, u * 1.16, u * 1.0, u * .26); ctx.fill(); ctx.stroke();
      // ön yüz (visor) — robotun baktığı yön sağ
      ctx.fillStyle = '#c7d2fe'; roundRect(u * .02, -u * .34, u * .5, u * .68, u * .16); ctx.fill();
      ctx.fillStyle = robot.mood === 'crash' ? '#ef4444' : '#0f172a';
      var blink = (Math.floor(time * 2.2) % 9 === 0) ? .25 : 1;
      ctx.beginPath(); ctx.ellipse(u * .26, -u * .15, u * .075, u * .11 * blink, 0, 0, TAU); ctx.fill();
      ctx.beginPath(); ctx.ellipse(u * .26, u * .15, u * .075, u * .11 * blink, 0, 0, TAU); ctx.fill();
      if (robot.mood === 'win') { ctx.strokeStyle = '#0f172a'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.arc(u * .36, 0, u * .1, -1.2, 1.2); ctx.stroke(); }
      // anten
      ctx.strokeStyle = '#312e81'; ctx.lineWidth = 2 * dpr; ctx.beginPath(); ctx.moveTo(-u * .25, 0); ctx.lineTo(-u * .5, 0); ctx.stroke();
      ctx.fillStyle = '#fbbf24'; ctx.beginPath(); ctx.arc(-u * .52, 0, u * .09 + Math.sin(time * 6) * u * .015, 0, TAU); ctx.fill();
      // parlama
      ctx.fillStyle = 'rgba(255,255,255,.35)'; roundRect(-u * .5, -u * .42, u * .42, u * .14, u * .07); ctx.fill();
      ctx.restore();
    }

    function drawParticles(dt) {
      for (var i = particles.length - 1; i >= 0; i--) {
        var p = particles[i];
        p.life -= p.decay * dt;
        if (p.life <= 0) { particles.splice(i, 1); continue; }
        p.x += p.vx * dt; p.y += p.vy * dt; p.vy += 2.2 * dt;
        var cx = ox + px(p.x), cy = oy + px(p.y);
        ctx.globalAlpha = Math.max(0, Math.min(1, p.life));
        ctx.fillStyle = p.color;
        if (p.kind === 'star') { ctx.save(); ctx.translate(cx, cy); ctx.rotate(p.life * 6); starPath(0, 0, p.size * cell, p.size * cell * .45); ctx.fill(); ctx.restore(); }
        else { ctx.beginPath(); ctx.arc(cx, cy, p.size * cell, 0, TAU); ctx.fill(); }
      }
      ctx.globalAlpha = 1;
    }

    function frame(now) {
      var dt = Math.min(.05, (now - last) / 1000); last = now; time += dt;
      for (var i = tweens.length - 1; i >= 0; i--) {
        var tw = tweens[i], t = Math.min(1, (now - tw.start) / tw.ms);
        tw.fn(t);
        if (t >= 1) { tweens.splice(i, 1); tw.done(); }
      }
      if (shake > 0) shake = Math.max(0, shake - dt * 2.5);
      if (level) {
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawBackdrop();
        drawBoard();
        drawStart();
        var obs = level.obstacles || [];
        // arkadan öne: yüksek y sonra çizilsin
        for (var k = 0; k < obs.length; k++) drawObstacle(obs[k]);
        drawStar();
        drawRobot();
        drawParticles(dt);
      }
      if (running) requestAnimationFrame(frame);
    }

    function start() { if (!running) { running = true; last = performance.now(); requestAnimationFrame(frame); } }
    function stop() { running = false; }
    document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
    window.addEventListener('resize', resize);
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(function () { resize(); }).observe(canvas);

    resize();
    requestAnimationFrame(frame);

    return {
      setLevel: setLevel, resetRobot: resetRobot, resize: resize, turn: turn, move: move, crash: crash, celebrate: celebrate,
      robot: robot, themeName: function () { return theme.name; }, THEMES: THEMES,
      cancelTweens: function () { var t = tweens.splice(0); t.forEach(function (x) { x.done(); }); },
    };
  }

  root.BlockRender = { create: create, THEMES: THEMES };
})(typeof self !== 'undefined' ? self : this);
