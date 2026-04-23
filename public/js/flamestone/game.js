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

  draw(ts = performance.now()) {
    const s = this.state;
    const ctx = this.ctx;
    if (!s) return;
    const w = this.canvas.width;
    const h = this.canvas.height;
    const cell = Math.min(w / s.width, h / s.height);
    const ox = (w - (cell * s.width)) / 2;
    const oy = (h - (cell * s.height)) / 2;

    const shaking = ts < this.shakeUntil;
    const sx = shaking ? (Math.random() - 0.5) * 8 : 0;
    const sy = shaking ? (Math.random() - 0.5) * 8 : 0;

    ctx.clearRect(0, 0, w, h);
    ctx.save();
    ctx.translate(sx, sy);

    for (let y = 0; y < s.height; y += 1) {
      for (let x = 0; x < s.width; x += 1) {
        const t = s.grid[y][x];
        const px = ox + x * cell;
        const py = oy + y * cell;
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(px, py, cell, cell);
        ctx.strokeStyle = '#1e293b';
        ctx.strokeRect(px, py, cell, cell);

        if (t === '#') {
          ctx.fillStyle = '#334155';
          ctx.fillRect(px + 2, py + 2, cell - 4, cell - 4);
          this.drawTileGlyph(ctx, px, py, cell, '🧱', '#e2e8f0');
        } else if (t === 'B') {
          ctx.fillStyle = '#d97706';
          ctx.fillRect(px + 4, py + 4, cell - 8, cell - 8);
          this.drawTileGlyph(ctx, px, py, cell, '⬛', '#fff7ed');
        } else if (t === 'b') {
          ctx.fillStyle = '#fb923c';
          ctx.fillRect(px + 4, py + 4, cell - 8, cell - 8);
          this.drawTileGlyph(ctx, px, py, cell, '✅', '#052e16');
        } else if (t === 'T') {
          ctx.fillStyle = '#ef4444';
          ctx.fillRect(px + 6, py + 6, cell - 12, cell - 12);
          this.drawTileGlyph(ctx, px, py, cell, '☠', '#fee2e2');
        } else if (t === 'K') {
          ctx.fillStyle = '#facc15';
          ctx.beginPath();
          ctx.arc(px + cell * 0.45, py + cell * 0.45, cell * 0.17, 0, Math.PI * 2);
          ctx.fill();
          ctx.fillRect(px + cell * 0.5, py + cell * 0.42, cell * 0.25, cell * 0.08);
          this.drawTileGlyph(ctx, px, py, cell, '🔑', '#111827');
        } else if (t === 'D') {
          ctx.fillStyle = '#38bdf8';
          ctx.fillRect(px + 6, py + 3, cell - 12, cell - 6);
          this.drawTileGlyph(ctx, px, py, cell, '🚪', '#0c4a6e');
        } else if (t === 'G') {
          ctx.fillStyle = '#22c55e';
          ctx.fillRect(px + 4, py + 4, cell - 8, cell - 8);
          this.drawTileGlyph(ctx, px, py, cell, '🏁', '#052e16');
        } else if (t === 'X') {
          ctx.fillStyle = '#a78bfa';
          ctx.fillRect(px + 5, py + 5, cell - 10, cell - 10);
          this.drawTileGlyph(ctx, px, py, cell, '🎯', '#312e81');
        }
      }
    }

    const p = this.getPlayerDrawPos(ts);
    ctx.fillStyle = s.hasKey ? '#a78bfa' : '#60a5fa';
    ctx.beginPath();
    ctx.arc(ox + p.x * cell, oy + p.y * cell, cell * 0.32, 0, Math.PI * 2);
    ctx.fill();
    this.drawTileGlyph(ctx, ox + (p.x - 0.5) * cell, oy + (p.y - 0.5) * cell, cell, '🙂', '#0f172a');

    const now = performance.now();
    this.particles = this.particles.filter((p0) => now - p0.born < p0.life);
    for (const p0 of this.particles) {
      const age = now - p0.born;
      const tt = age / p0.life;
      const px = ox + (p0.x + p0.vx * age) * cell;
      const py = oy + (p0.y + p0.vy * age) * cell;
      ctx.fillStyle = `rgba(250,204,21,${1 - tt})`;
      ctx.fillRect(px, py, 3, 3);
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
