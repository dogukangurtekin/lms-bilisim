function setGridCell(grid, x, y, value) {
  const row = grid[y].split('');
  row[x] = value;
  grid[y] = row.join('');
}

function onPath(path, x, y) {
  return path.some(([px, py]) => px === x && py === y);
}

function generateGridForLevel(levelNo) {
  const size = levelNo <= 20 ? 10 : 12;
  const grid = Array.from({ length: size }, () => '.'.repeat(size));

  for (let y = 0; y < size; y += 1) {
    for (let x = 0; x < size; x += 1) {
      if (x === 0 || y === 0 || x === size - 1 || y === size - 1) setGridCell(grid, x, y, '#');
    }
  }

  const path = [];
  let x = 1; let y = 1;
  path.push([x, y]);
  while (x < size - 2) { x += 1; path.push([x, y]); }
  while (y < size - 2) { y += 1; path.push([x, y]); }

  let seed = 31 + (levelNo * 97);
  const blockedCount = Math.min(Math.floor((size * size) / 5), 8 + Math.floor(levelNo / 2));
  for (let i = 0; i < blockedCount; i += 1) {
    seed = (seed * 1103515245 + 12345) & 0x7fffffff;
    const rx = 1 + (seed % (size - 2));
    seed = (seed * 1103515245 + 12345) & 0x7fffffff;
    const ry = 1 + (seed % (size - 2));
    if (onPath(path, rx, ry)) continue;
    setGridCell(grid, rx, ry, '#');
  }

  setGridCell(grid, 1, 1, 'S');
  setGridCell(grid, size - 2, size - 2, 'G');

  const bx = Math.min(size - 3, 2 + (levelNo % Math.max(2, size - 4)));
  const by = Math.min(size - 3, 2 + Math.floor((levelNo * 3) % Math.max(2, size - 4)));
  if (!onPath(path, bx, by)) setGridCell(grid, bx, by, 'B');

  if (levelNo >= 6) {
    const tx = Math.min(size - 3, 2 + (levelNo % Math.max(2, size - 5)));
    const ty = Math.min(size - 3, 3 + (levelNo % Math.max(2, size - 6)));
    if (!onPath(path, tx, ty)) setGridCell(grid, tx, ty, 'T');
  }

  // Tum bolumlerde anahtar-kapi mekanigi aktif.
  const kx = 2;
  const ky = size - 3;
  const dx = size - 3;
  const dy = size - 3;
  if (grid[ky][kx] === '#') setGridCell(grid, kx, ky, '.');
  if (grid[dy][dx] === '#') setGridCell(grid, dx, dy, '.');
  setGridCell(grid, kx, ky, 'K');
  setGridCell(grid, dx, dy, 'D');

  return grid;
}

function buildBuiltinLevels() {
  const out = [];
  for (let i = 1; i <= 50; i += 1) {
    const diff = i <= 18 ? 'Kolay' : (i <= 35 ? 'Orta' : 'Zor');
    out.push({
      id: `builtin_${i}`,
      name: `${diff} ${String(i).padStart(2, '0')}`,
      data: { grid: generateGridForLevel(i) },
    });
  }
  return out;
}

export const BUILTIN_LEVELS = buildBuiltinLevels();

function cloneGrid(grid) {
  return grid.map((row) => row.split(''));
}

function findCell(grid, token) {
  for (let y = 0; y < grid.length; y += 1) {
    for (let x = 0; x < grid[y].length; x += 1) {
      if (grid[y][x] === token) return { x, y };
    }
  }
  return null;
}

function inside(grid, x, y) {
  return y >= 0 && y < grid.length && x >= 0 && x < grid[0].length;
}

export class FlamestoneGame {
  constructor(opts) {
    this.canvas = opts.canvas;
    this.ctx = this.canvas.getContext('2d');
    this.onMove = opts.onMove || (() => {});
    this.onToast = opts.onToast || (() => {});
    this.onLevelComplete = opts.onLevelComplete || (() => {});
    this.state = null;
    this.tween = null;
    this.particles = [];
    this.shakeUntil = 0;
    this.lastTs = 0;
    this.anim = this.anim.bind(this);
    requestAnimationFrame(this.anim);
  }

  loadLevel(level) {
    const rawGrid = Array.isArray(level?.data?.grid) ? level.data.grid : [];
    if (!rawGrid.length) throw new Error('Level grid eksik');
    const grid = cloneGrid(rawGrid);
    const start = findCell(grid, 'S');
    if (!start) throw new Error('Level baslangic noktasi (S) eksik');
    grid[start.y][start.x] = '.';

    this.state = {
      level,
      grid,
      width: grid[0].length,
      height: grid.length,
      player: { x: start.x, y: start.y },
      hasKey: false,
      moves: 0,
      done: false,
    };
    this.tween = null;
    this.particles = [];
    this.shakeUntil = 0;
    this.onMove(this.state.moves);
    this.draw();
  }

  tryMove(dx, dy) {
    const s = this.state;
    if (!s || s.done || this.tween) return;
    const tx = s.player.x + dx;
    const ty = s.player.y + dy;
    if (!inside(s.grid, tx, ty)) return;

    const target = s.grid[ty][tx];
    if (target === '#') return;
    if (target === 'D' && !s.hasKey) {
      this.onToast('Kapi kilitli. Once anahtari al.');
      return;
    }

    if (target === 'B' || target === 'b') {
      const bx = tx + dx;
      const by = ty + dy;
      if (!inside(s.grid, bx, by)) return;
      const next = s.grid[by][bx];
      if (next !== '.' && next !== 'T' && next !== 'G' && next !== 'X') return;
      s.grid[by][bx] = (next === 'X') ? 'b' : 'B';
      s.grid[ty][tx] = (target === 'b') ? 'X' : '.';
    }

    const prev = { x: s.player.x, y: s.player.y };
    s.player.x = tx;
    s.player.y = ty;
    s.moves += 1;
    this.onMove(s.moves);

    if (target === 'K') {
      if (!this.areAllSlotsFilled()) {
        this.onToast('Once bloklari hedef noktalarina (X) yerlestir.');
      } else {
        s.hasKey = true;
        s.grid[ty][tx] = '.';
        this.onToast('Anahtar alindi.');
      }
    }

    if (target === 'D' && s.hasKey) {
      s.grid[ty][tx] = '.';
      this.onToast('Kapi acildi.');
    }

    if (target === 'T') {
      this.shakeUntil = performance.now() + 280;
      this.onToast('Tuzaga dustun, level sifirlaniyor...');
      const current = s.level;
      setTimeout(() => this.loadLevel(current), 260);
    }

    if (target === 'G') {
      if (!this.areAllSlotsFilled()) {
        this.onToast('Once bloklari hedef noktalarina (X) yerlestir.');
      } else {
        s.done = true;
        this.spawnParticles(tx, ty);
        this.onToast('Level tamamlandi!');
        this.onLevelComplete({ level: s.level, moves: s.moves });
      }
    }

    this.tween = {
      from: prev,
      to: { x: tx, y: ty },
      start: performance.now(),
      dur: 120,
    };
  }

  spawnParticles(cx, cy) {
    for (let i = 0; i < 40; i += 1) {
      this.particles.push({
        x: cx + 0.5,
        y: cy + 0.5,
        vx: (Math.random() - 0.5) * 0.05,
        vy: (Math.random() - 0.5) * 0.05,
        life: 700 + Math.random() * 500,
        born: performance.now(),
      });
    }
  }

  anim(ts) {
    this.lastTs = ts;
    this.draw(ts);
    requestAnimationFrame(this.anim);
  }

  getPlayerDrawPos(ts) {
    const s = this.state;
    if (!s) return { x: 0, y: 0 };
    if (!this.tween) return { x: s.player.x + 0.5, y: s.player.y + 0.5 };
    const t = Math.min(1, (ts - this.tween.start) / this.tween.dur);
    if (t >= 1) {
      this.tween = null;
      return { x: s.player.x + 0.5, y: s.player.y + 0.5 };
    }
    return {
      x: this.tween.from.x + (this.tween.to.x - this.tween.from.x) * t + 0.5,
      y: this.tween.from.y + (this.tween.to.y - this.tween.from.y) * t + 0.5,
    };
  }

  rr(ctx, x, y, w, h, r) {
    const rad = Math.max(0, Math.min(r, w / 2, h / 2));
    ctx.beginPath();
    ctx.moveTo(x + rad, y);
    ctx.lineTo(x + w - rad, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + rad);
    ctx.lineTo(x + w, y + h - rad);
    ctx.quadraticCurveTo(x + w, y + h, x + w - rad, y + h);
    ctx.lineTo(x + rad, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - rad);
    ctx.lineTo(x, y + rad);
    ctx.quadraticCurveTo(x, y, x + rad, y);
    ctx.closePath();
  }

  // Yumuşak degrade dolgulu, alt kenarı koyu "kalınlık" şeritli kutu
  slab(ctx, x, y, w, h, r, top, bottom, edge) {
    this.rr(ctx, x, y + h * 0.08, w, h, r);
    ctx.fillStyle = edge;
    ctx.fill();
    const g = ctx.createLinearGradient(0, y, 0, y + h);
    g.addColorStop(0, top);
    g.addColorStop(1, bottom);
    this.rr(ctx, x, y, w, h, r);
    ctx.fillStyle = g;
    ctx.fill();
    ctx.save();
    ctx.globalAlpha = 0.28;
    this.rr(ctx, x + w * 0.08, y + h * 0.07, w * 0.84, h * 0.22, r * 0.6);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.restore();
  }

  poly(ctx, pts, fill, stroke) {
    ctx.beginPath();
    ctx.moveTo(pts[0][0], pts[0][1]);
    for (let i = 1; i < pts.length; i += 1) ctx.lineTo(pts[i][0], pts[i][1]);
    ctx.closePath();
    if (fill) { ctx.fillStyle = fill; ctx.fill(); }
    if (stroke) { ctx.strokeStyle = stroke; ctx.lineWidth = 1.2; ctx.stroke(); }
  }

  // İzometrik kutu: taban merkezi (px,py), yarım genişlik hw, yarım derinlik hh, yükseklik h
  box(ctx, px, py, hw, hh, h, cTop, cL, cR) {
    this.poly(ctx, [[px - hw, py - h], [px, py - h + hh], [px, py + hh], [px - hw, py]], cL);
    this.poly(ctx, [[px + hw, py - h], [px, py - h + hh], [px, py + hh], [px + hw, py]], cR);
    this.poly(ctx, [[px, py - h - hh], [px + hw, py - h], [px, py - h + hh], [px - hw, py - h]], cTop);
  }

  ellipseAt(ctx, px, py, rx, ry, fill) {
    ctx.fillStyle = fill;
    ctx.beginPath();
    ctx.ellipse(px, py, rx, ry, 0, 0, Math.PI * 2);
    ctx.fill();
  }

  draw(ts = performance.now()) {
    const s = this.state;
    const ctx = this.ctx;
    if (!s) return;
    const W = this.canvas.width;
    const H = this.canvas.height;
    const n = s.width + s.height;
    const a = Math.min((W * 0.95) / n, (H * 0.95) / (n / 2 + 1.9));
    const b = a / 2;
    const thick = a * 0.5;
    const ox = W / 2 + ((s.height - s.width) * a) / 2;
    const oy = (H - (n * b + thick + a * 1.4)) / 2 + a * 1.4;
    const sxp = (x, y) => ox + (x - y) * a;
    const syp = (x, y) => oy + (x + y) * b;
    const pulse = 0.5 + 0.5 * Math.sin(ts / 380);

    const shaking = ts < this.shakeUntil;
    const sx = shaking ? (Math.random() - 0.5) * 8 : 0;
    const sy = shaking ? (Math.random() - 0.5) * 8 : 0;

    ctx.clearRect(0, 0, W, H);
    const bg = ctx.createLinearGradient(0, 0, 0, H);
    bg.addColorStop(0, '#0b1d4d');
    bg.addColorStop(1, '#2a1a5e');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, W, H);
    for (let i = 0; i < 70; i += 1) {
      const hx = ((i * 7919) % 1000) / 1000;
      const hy = ((i * 104729) % 997) / 997;
      ctx.fillStyle = `rgba(255,255,255,${0.25 + 0.5 * Math.abs(Math.sin(ts / 900 + i))})`;
      ctx.fillRect(hx * W, hy * H * 0.7, 1.6, 1.6);
    }

    ctx.save();
    ctx.translate(sx, sy);

    // zemin blokları
    for (let y = 0; y < s.height; y += 1) {
      for (let x = 0; x < s.width; x += 1) {
        const t = s.grid[y][x];
        const topC = (x + y) % 2 === 0 ? '#7dd3fc' : '#60c3f5';
        const L = [sxp(x, y + 1), syp(x, y + 1)];
        const B = [sxp(x + 1, y + 1), syp(x + 1, y + 1)];
        const R = [sxp(x + 1, y), syp(x + 1, y)];
        this.poly(ctx, [L, B, [B[0], B[1] + thick], [L[0], L[1] + thick]], '#1d4ed8');
        this.poly(ctx, [R, B, [B[0], B[1] + thick], [R[0], R[1] + thick]], '#1e3a8a');
        this.poly(ctx, [[sxp(x, y), syp(x, y)], R, B, L], t === 'T' ? '#4c1d1d' : topC, 'rgba(255,255,255,.18)');
      }
    }

    // oyuncu ve nesneler derinlik sırasıyla
    const p = this.getPlayerDrawPos(ts);
    const playerDepth = (p.x - 0.5) + (p.y - 0.5);
    let playerDone = false;
    const drawPlayer = () => {
      playerDone = true;
      const px = sxp(p.x, p.y);
      const py = syp(p.x, p.y);
      const r = a * 0.34;
      const moving = !!this.tween;
      const hop = moving ? Math.abs(Math.sin(ts / 70)) * a * 0.12 : Math.sin(ts / 320) * a * 0.02;
      this.ellipseAt(ctx, px, py + b * 0.1, r * 0.95, r * 0.48, 'rgba(0,0,0,.35)');
      if (s.hasKey) this.ellipseAt(ctx, px, py - r * 1.1 - hop, r * (1.5 + 0.15 * pulse), r * (1.5 + 0.15 * pulse), 'rgba(253,224,71,.22)');
      const cy = py - r * 1.05 - hop;
      const pg = ctx.createRadialGradient(px - r * 0.35, cy - r * 0.4, r * 0.1, px, cy, r);
      pg.addColorStop(0, s.hasKey ? '#ddd6fe' : '#dbeafe');
      pg.addColorStop(1, s.hasKey ? '#7c3aed' : '#2563eb');
      ctx.fillStyle = pg;
      ctx.beginPath();
      ctx.arc(px, cy, r, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = 'rgba(255,255,255,.6)';
      ctx.lineWidth = Math.max(1.5, a * 0.03);
      ctx.stroke();
      ctx.fillStyle = '#ffffff';
      ctx.beginPath();
      ctx.arc(px - r * 0.34, cy - r * 0.1, r * 0.2, 0, Math.PI * 2);
      ctx.arc(px + r * 0.34, cy - r * 0.1, r * 0.2, 0, Math.PI * 2);
      ctx.fill();
      ctx.fillStyle = '#0b1b44';
      ctx.beginPath();
      ctx.arc(px - r * 0.3, cy - r * 0.08, r * 0.1, 0, Math.PI * 2);
      ctx.arc(px + r * 0.38, cy - r * 0.08, r * 0.1, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = '#0b1b44';
      ctx.beginPath();
      ctx.arc(px, cy + r * 0.14, r * 0.3, 0.15 * Math.PI, 0.85 * Math.PI);
      ctx.stroke();
    };

    for (let d = 0; d <= s.width + s.height - 2; d += 1) {
      for (let x = 0; x < s.width; x += 1) {
        const y = d - x;
        if (y < 0 || y >= s.height) continue;
        if (!playerDone && playerDepth < d - 0.0001) drawPlayer();
        const t = s.grid[y][x];
        const px = sxp(x + 0.5, y + 0.5);
        const py = syp(x + 0.5, y + 0.5);

        if (t === '#') {
          this.ellipseAt(ctx, px + a * 0.1, py + b * 0.15, a * 0.55, b * 0.55, 'rgba(0,0,0,.25)');
          this.box(ctx, px, py + b * 0.05, a * 0.5, b * 0.5, a * 0.95, '#cbd5e1', '#64748b', '#475569');
          ctx.fillStyle = 'rgba(15,23,42,.25)';
          ctx.fillRect(px - a * 0.36, py - a * 0.52, a * 0.2, a * 0.1);
          ctx.fillRect(px + a * 0.1, py - a * 0.3, a * 0.2, a * 0.1);
        } else if (t === 'X') {
          const k = 0.7;
          this.poly(ctx, [[px, py - b * k], [px + a * k, py], [px, py + b * k], [px - a * k, py]], `rgba(167,139,250,${0.35 + 0.3 * pulse})`, `rgba(237,233,254,${0.7 + 0.3 * pulse})`);
          const k2 = 0.35;
          this.poly(ctx, [[px, py - b * k2], [px + a * k2, py], [px, py + b * k2], [px - a * k2, py]], `rgba(196,181,253,${0.4 + 0.3 * pulse})`);
        } else if (t === 'B' || t === 'b') {
          const done = t === 'b';
          if (done) this.poly(ctx, [[px, py - b * 0.8], [px + a * 0.8, py], [px, py + b * 0.8], [px - a * 0.8, py]], `rgba(74,222,128,${0.25 + 0.2 * pulse})`);
          this.ellipseAt(ctx, px + a * 0.08, py + b * 0.1, a * 0.5, b * 0.5, 'rgba(0,0,0,.28)');
          this.box(ctx, px, py + b * 0.1, a * 0.42, b * 0.42, a * 0.62,
            done ? '#bbf7d0' : '#fcd9a8', done ? '#16a34a' : '#c2610f', done ? '#15803d' : '#92400e');
          ctx.strokeStyle = done ? 'rgba(6,78,59,.5)' : 'rgba(92,45,8,.5)';
          ctx.lineWidth = Math.max(1.2, a * 0.03);
          ctx.beginPath();
          ctx.moveTo(px - a * 0.3, py - a * 0.66);
          ctx.lineTo(px + a * 0.3, py - a * 0.66);
          ctx.moveTo(px, py - a * 0.66 - b * 0.3);
          ctx.lineTo(px, py - a * 0.66 + b * 0.3);
          ctx.stroke();
        } else if (t === 'T') {
          const g = ctx.createRadialGradient(px, py, 2, px, py, a * 0.7);
          g.addColorStop(0, `rgba(248,113,113,${0.5 + 0.3 * pulse})`);
          g.addColorStop(1, 'rgba(220,38,38,0)');
          ctx.fillStyle = g;
          ctx.fillRect(px - a, py - b, a * 2, b * 2);
          this.poly(ctx, [[px, py - b * 0.7], [px + a * 0.7, py], [px, py + b * 0.7], [px - a * 0.7, py]], '#1c0a0a');
          for (let i = 0; i < 4; i += 1) {
            const qx = px + [-0.28, 0.28, 0, 0][i] * a;
            const qy = py + [0, 0, -0.28, 0.28][i] * b * 1.2;
            this.poly(ctx, [[qx - a * 0.1, qy], [qx, qy - a * 0.38], [qx + a * 0.1, qy]], '#fca5a5', '#7f1d1d');
          }
        } else if (t === 'K') {
          const bob = Math.sin(ts / 260 + x) * a * 0.06;
          this.ellipseAt(ctx, px, py, a * 0.3, b * 0.3, 'rgba(0,0,0,.28)');
          this.ellipseAt(ctx, px, py - a * 0.5 + bob, a * 0.42, a * 0.42, `rgba(253,224,71,${0.18 + 0.15 * pulse})`);
          const ky = py - a * 0.5 + bob;
          ctx.fillStyle = '#fbbf24';
          ctx.strokeStyle = '#b45309';
          ctx.lineWidth = Math.max(1.5, a * 0.04);
          ctx.beginPath();
          ctx.arc(px - a * 0.14, ky, a * 0.15, 0, Math.PI * 2);
          ctx.fill();
          ctx.stroke();
          ctx.fillRect(px - a * 0.02, ky - a * 0.04, a * 0.34, a * 0.08);
          ctx.fillRect(px + a * 0.2, ky, a * 0.07, a * 0.16);
          ctx.fillStyle = '#78350f';
          ctx.beginPath();
          ctx.arc(px - a * 0.14, ky, a * 0.05, 0, Math.PI * 2);
          ctx.fill();
        } else if (t === 'D') {
          this.ellipseAt(ctx, px + a * 0.08, py + b * 0.1, a * 0.5, b * 0.5, 'rgba(0,0,0,.25)');
          this.box(ctx, px, py + b * 0.05, a * 0.46, b * 0.46, a * 1.0, '#bae6fd', '#0284c7', '#0369a1');
          ctx.fillStyle = '#fde68a';
          ctx.beginPath();
          ctx.arc(px + a * 0.18, py - a * 0.32, a * 0.05, 0, Math.PI * 2);
          ctx.fill();
          ctx.fillStyle = 'rgba(255,255,255,.22)';
          ctx.fillRect(px - a * 0.34, py - a * 0.72, a * 0.2, a * 0.5);
        } else if (t === 'G') {
          const k = 0.78 + 0.05 * pulse;
          this.poly(ctx, [[px, py - b * k], [px + a * k, py], [px, py + b * k], [px - a * k, py]], `rgba(74,222,128,${0.45 + 0.25 * pulse})`, '#bbf7d0');
          const k2 = 0.4;
          this.poly(ctx, [[px, py - b * k2], [px + a * k2, py], [px, py + b * k2], [px - a * k2, py]], '#16a34a');
          ctx.strokeStyle = '#e2e8f0';
          ctx.lineWidth = Math.max(2, a * 0.05);
          ctx.beginPath();
          ctx.moveTo(px, py);
          ctx.lineTo(px, py - a * 0.95);
          ctx.stroke();
          const wave = Math.sin(ts / 200) * a * 0.04;
          this.poly(ctx, [[px, py - a * 0.95], [px + a * 0.42, py - a * 0.8 + wave], [px, py - a * 0.62]], '#ef4444');
        }
      }
    }
    if (!playerDone) drawPlayer();

    const now = performance.now();
    this.particles = this.particles.filter((p0) => now - p0.born < p0.life);
    for (const p0 of this.particles) {
      const age = now - p0.born;
      const tt = age / p0.life;
      const qx = sxp(p0.x + p0.vx * age, p0.y + p0.vy * age);
      const qy = syp(p0.x + p0.vx * age, p0.y + p0.vy * age) - a * 0.5 - age * 0.02;
      ctx.fillStyle = `rgba(250,204,21,${1 - tt})`;
      ctx.beginPath();
      ctx.arc(qx, qy, 3 * (1 - tt) + 1, 0, Math.PI * 2);
      ctx.fill();
    }

    ctx.restore();
  }

  drawTileGlyph(ctx, px, py, cell, glyph, color) {
    if (!glyph) return;
    ctx.save();
    ctx.fillStyle = color || '#ffffff';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.font = `700 ${Math.max(10, Math.floor(cell * 0.42))}px "Segoe UI Emoji", "Apple Color Emoji", sans-serif`;
    ctx.fillText(glyph, px + (cell / 2), py + (cell / 2));
    ctx.restore();
  }

  areAllSlotsFilled() {
    const s = this.state;
    if (!s) return true;
    let hasSlot = false;
    for (let y = 0; y < s.height; y += 1) {
      for (let x = 0; x < s.width; x += 1) {
        if (s.grid[y][x] === 'X') {
          hasSlot = true;
        }
      }
    }
    return !hasSlot;
  }
}
