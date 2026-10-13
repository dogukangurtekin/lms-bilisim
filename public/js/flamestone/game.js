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

  draw(ts = performance.now()) {
    const s = this.state;
    const ctx = this.ctx;
    if (!s) return;
    const w = this.canvas.width;
    const h = this.canvas.height;
    const cell = Math.min(w / s.width, h / s.height);
    const ox = (w - (cell * s.width)) / 2;
    const oy = (h - (cell * s.height)) / 2;
    const pulse = 0.5 + 0.5 * Math.sin(ts / 380);

    const shaking = ts < this.shakeUntil;
    const sx = shaking ? (Math.random() - 0.5) * 8 : 0;
    const sy = shaking ? (Math.random() - 0.5) * 8 : 0;

    ctx.clearRect(0, 0, w, h);
    const bg = ctx.createRadialGradient(w / 2, h * 0.35, 20, w / 2, h / 2, Math.max(w, h) * 0.75);
    bg.addColorStop(0, '#1b3a82');
    bg.addColorStop(1, '#070f22');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, w, h);

    ctx.save();
    ctx.translate(sx, sy);

    // zemin
    for (let y = 0; y < s.height; y += 1) {
      for (let x = 0; x < s.width; x += 1) {
        const px = ox + x * cell;
        const py = oy + y * cell;
        ctx.fillStyle = (x + y) % 2 === 0 ? '#16295a' : '#12234d';
        ctx.fillRect(px, py, cell, cell);
        ctx.strokeStyle = 'rgba(147,197,253,.08)';
        ctx.lineWidth = 1;
        ctx.strokeRect(px + 0.5, py + 0.5, cell - 1, cell - 1);
      }
    }

    // nesneler
    for (let y = 0; y < s.height; y += 1) {
      for (let x = 0; x < s.width; x += 1) {
        const t = s.grid[y][x];
        const px = ox + x * cell;
        const py = oy + y * cell;
        const cx = px + cell / 2;
        const cy = py + cell / 2;

        if (t === '#') {
          this.slab(ctx, px + cell * 0.03, py + cell * 0.03, cell * 0.94, cell * 0.86, cell * 0.14, '#8b9bb8', '#4b5a78', '#2b3650');
          ctx.strokeStyle = 'rgba(15,23,42,.35)';
          ctx.lineWidth = Math.max(1, cell * 0.025);
          ctx.beginPath();
          ctx.moveTo(px + cell * 0.1, cy - cell * 0.02);
          ctx.lineTo(px + cell * 0.9, cy - cell * 0.02);
          ctx.moveTo(cx, py + cell * 0.1);
          ctx.lineTo(cx, cy - cell * 0.02);
          ctx.moveTo(px + cell * 0.3, cy - cell * 0.02);
          ctx.lineTo(px + cell * 0.3, py + cell * 0.8);
          ctx.moveTo(px + cell * 0.7, cy - cell * 0.02);
          ctx.lineTo(px + cell * 0.7, py + cell * 0.8);
          ctx.stroke();
        } else if (t === 'X') {
          ctx.save();
          ctx.setLineDash([cell * 0.14, cell * 0.1]);
          ctx.lineWidth = Math.max(2, cell * 0.06);
          ctx.strokeStyle = `rgba(167,139,250,${0.65 + 0.35 * pulse})`;
          this.rr(ctx, px + cell * 0.14, py + cell * 0.14, cell * 0.72, cell * 0.72, cell * 0.14);
          ctx.stroke();
          ctx.restore();
          ctx.fillStyle = `rgba(167,139,250,${0.12 + 0.12 * pulse})`;
          this.rr(ctx, px + cell * 0.14, py + cell * 0.14, cell * 0.72, cell * 0.72, cell * 0.14);
          ctx.fill();
        } else if (t === 'B' || t === 'b') {
          const done = t === 'b';
          if (done) {
            ctx.fillStyle = `rgba(74,222,128,${0.18 + 0.14 * pulse})`;
            this.rr(ctx, px + cell * 0.08, py + cell * 0.08, cell * 0.84, cell * 0.84, cell * 0.18);
            ctx.fill();
          }
          this.slab(ctx, px + cell * 0.12, py + cell * 0.1, cell * 0.76, cell * 0.72, cell * 0.14,
            done ? '#86efac' : '#f0a85b', done ? '#16a34a' : '#c2610f', done ? '#0f5c2b' : '#7c3a0a');
          ctx.strokeStyle = done ? 'rgba(6,78,59,.55)' : 'rgba(92,45,8,.55)';
          ctx.lineWidth = Math.max(1.5, cell * 0.035);
          ctx.beginPath();
          ctx.moveTo(px + cell * 0.2, py + cell * 0.18);
          ctx.lineTo(px + cell * 0.8, py + cell * 0.74);
          ctx.moveTo(px + cell * 0.8, py + cell * 0.18);
          ctx.lineTo(px + cell * 0.2, py + cell * 0.74);
          ctx.stroke();
          if (done) this.drawTileGlyph(ctx, px, py - cell * 0.02, cell, '✔', '#ffffff');
        } else if (t === 'T') {
          const g = ctx.createRadialGradient(cx, cy, 2, cx, cy, cell * 0.5);
          g.addColorStop(0, `rgba(248,113,113,${0.55 + 0.3 * pulse})`);
          g.addColorStop(1, 'rgba(220,38,38,0)');
          ctx.fillStyle = g;
          ctx.fillRect(px, py, cell, cell);
          ctx.fillStyle = '#7f1d1d';
          ctx.beginPath();
          ctx.arc(cx, cy, cell * 0.3, 0, Math.PI * 2);
          ctx.fill();
          ctx.fillStyle = '#ef4444';
          for (let i = 0; i < 6; i += 1) {
            const a = (Math.PI * 2 * i) / 6 + Math.PI / 6;
            ctx.beginPath();
            ctx.moveTo(cx + Math.cos(a - 0.28) * cell * 0.26, cy + Math.sin(a - 0.28) * cell * 0.26);
            ctx.lineTo(cx + Math.cos(a) * cell * 0.42, cy + Math.sin(a) * cell * 0.42);
            ctx.lineTo(cx + Math.cos(a + 0.28) * cell * 0.26, cy + Math.sin(a + 0.28) * cell * 0.26);
            ctx.closePath();
            ctx.fill();
          }
          this.drawTileGlyph(ctx, px, py, cell, '☠', '#fee2e2');
        } else if (t === 'K') {
          const bob = Math.sin(ts / 260) * cell * 0.03;
          const g = ctx.createRadialGradient(cx, cy, 2, cx, cy, cell * 0.5);
          g.addColorStop(0, `rgba(253,224,71,${0.35 + 0.25 * pulse})`);
          g.addColorStop(1, 'rgba(253,224,71,0)');
          ctx.fillStyle = g;
          ctx.fillRect(px, py, cell, cell);
          ctx.fillStyle = '#fbbf24';
          ctx.strokeStyle = '#b45309';
          ctx.lineWidth = Math.max(1.5, cell * 0.04);
          ctx.beginPath();
          ctx.arc(cx - cell * 0.12, cy + bob, cell * 0.15, 0, Math.PI * 2);
          ctx.fill();
          ctx.stroke();
          ctx.fillRect(cx - cell * 0.02, cy - cell * 0.04 + bob, cell * 0.32, cell * 0.08);
          ctx.fillRect(cx + cell * 0.18, cy + bob, cell * 0.07, cell * 0.16);
          ctx.fillStyle = '#78350f';
          ctx.beginPath();
          ctx.arc(cx - cell * 0.12, cy + bob, cell * 0.05, 0, Math.PI * 2);
          ctx.fill();
        } else if (t === 'D') {
          this.slab(ctx, px + cell * 0.16, py + cell * 0.06, cell * 0.68, cell * 0.86, cell * 0.2, '#7dd3fc', '#0369a1', '#0c4a6e');
          ctx.fillStyle = '#fde68a';
          ctx.beginPath();
          ctx.arc(cx + cell * 0.14, cy, cell * 0.05, 0, Math.PI * 2);
          ctx.fill();
          ctx.strokeStyle = 'rgba(12,74,110,.5)';
          ctx.lineWidth = Math.max(1, cell * 0.03);
          ctx.beginPath();
          ctx.moveTo(cx - cell * 0.06, py + cell * 0.16);
          ctx.lineTo(cx - cell * 0.06, py + cell * 0.8);
          ctx.stroke();
        } else if (t === 'G') {
          const g = ctx.createRadialGradient(cx, cy, 2, cx, cy, cell * (0.5 + 0.05 * pulse));
          g.addColorStop(0, 'rgba(134,239,172,.85)');
          g.addColorStop(1, 'rgba(34,197,94,0)');
          ctx.fillStyle = g;
          ctx.fillRect(px - cell * 0.1, py - cell * 0.1, cell * 1.2, cell * 1.2);
          this.slab(ctx, px + cell * 0.14, py + cell * 0.14, cell * 0.72, cell * 0.66, cell * 0.2, '#86efac', '#16a34a', '#0f5c2b');
          this.drawTileGlyph(ctx, px, py - cell * 0.02, cell, '🏁', '#052e16');
        }
      }
    }

    // oyuncu
    const p = this.getPlayerDrawPos(ts);
    const pcx = ox + p.x * cell;
    const pcy = oy + p.y * cell;
    const r = cell * 0.32;
    ctx.fillStyle = 'rgba(0,0,0,.35)';
    ctx.beginPath();
    ctx.ellipse(pcx, pcy + r * 0.95, r * 0.85, r * 0.3, 0, 0, Math.PI * 2);
    ctx.fill();
    if (s.hasKey) {
      ctx.fillStyle = `rgba(253,224,71,${0.25 + 0.2 * pulse})`;
      ctx.beginPath();
      ctx.arc(pcx, pcy, r * 1.35, 0, Math.PI * 2);
      ctx.fill();
    }
    const pg = ctx.createRadialGradient(pcx - r * 0.35, pcy - r * 0.4, r * 0.1, pcx, pcy, r);
    pg.addColorStop(0, s.hasKey ? '#ddd6fe' : '#bfdbfe');
    pg.addColorStop(1, s.hasKey ? '#7c3aed' : '#2563eb');
    ctx.fillStyle = pg;
    ctx.beginPath();
    ctx.arc(pcx, pcy, r, 0, Math.PI * 2);
    ctx.fill();
    ctx.strokeStyle = 'rgba(255,255,255,.55)';
    ctx.lineWidth = Math.max(1.5, cell * 0.03);
    ctx.stroke();
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.arc(pcx - r * 0.34, pcy - r * 0.12, r * 0.2, 0, Math.PI * 2);
    ctx.arc(pcx + r * 0.34, pcy - r * 0.12, r * 0.2, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#0b1b44';
    ctx.beginPath();
    ctx.arc(pcx - r * 0.3, pcy - r * 0.1, r * 0.1, 0, Math.PI * 2);
    ctx.arc(pcx + r * 0.38, pcy - r * 0.1, r * 0.1, 0, Math.PI * 2);
    ctx.fill();
    ctx.strokeStyle = '#0b1b44';
    ctx.lineWidth = Math.max(1.5, cell * 0.03);
    ctx.beginPath();
    ctx.arc(pcx, pcy + r * 0.12, r * 0.32, 0.15 * Math.PI, 0.85 * Math.PI);
    ctx.stroke();

    const now = performance.now();
    this.particles = this.particles.filter((p0) => now - p0.born < p0.life);
    for (const p0 of this.particles) {
      const age = now - p0.born;
      const tt = age / p0.life;
      const qx = ox + (p0.x + p0.vx * age) * cell;
      const qy = oy + (p0.y + p0.vy * age) * cell;
      ctx.fillStyle = `rgba(250,204,21,${1 - tt})`;
      ctx.beginPath();
      ctx.arc(qx, qy, 2.5 * (1 - tt) + 1, 0, Math.PI * 2);
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
