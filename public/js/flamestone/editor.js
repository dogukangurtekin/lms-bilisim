export class LevelEditor {
  constructor(opts) {
    this.root = opts.root;
    this.gridEl = opts.gridEl;
    this.previewEl = opts.previewEl;
    this.nameEl = opts.nameEl;
    this.onSave = opts.onSave;
    this.tool = 'wall';
    this.size = 12;
    this.grid = Array.from({ length: this.size }, () => Array.from({ length: this.size }, () => '.'));
    this.grid[1][1] = 'S';
    this.grid[this.size - 2][this.size - 2] = 'G';
    this.render();
  }

  setTool(tool) {
    this.tool = tool;
  }

  reset() {
    this.grid = Array.from({ length: this.size }, () => Array.from({ length: this.size }, () => '.'));
    this.grid[1][1] = 'S';
    this.grid[this.size - 2][this.size - 2] = 'G';
    this.render();
  }

  applyTool(x, y) {
    const t = this.tool;
    const current = this.grid[y][x];
    if (t === 'erase') {
      this.grid[y][x] = '.';
      this.render();
      return;
    }
    if (t === 'start') {
      this.replaceAll('S', '.');
      this.grid[y][x] = 'S';
      this.render();
      return;
    }
    if (t === 'goal') {
      this.replaceAll('G', '.');
      this.grid[y][x] = 'G';
      this.render();
      return;
    }
    const map = {
      wall: '#',
      trap: 'T',
      key: 'K',
      door: 'D',
      block: 'B',
    };
    const next = map[t] || '.';
    this.grid[y][x] = current === next ? '.' : next;
    this.render();
  }

  replaceAll(token, value) {
    for (let y = 0; y < this.size; y += 1) {
      for (let x = 0; x < this.size; x += 1) {
        if (this.grid[y][x] === token) this.grid[y][x] = value;
      }
    }
  }

  validate() {
    let starts = 0;
    let goals = 0;
    for (let y = 0; y < this.size; y += 1) {
      for (let x = 0; x < this.size; x += 1) {
        if (this.grid[y][x] === 'S') starts += 1;
        if (this.grid[y][x] === 'G') goals += 1;
      }
    }
    if (starts !== 1) return 'Tam bir adet baslangic (S) olmali.';
    if (goals !== 1) return 'Tam bir adet hedef (G) olmali.';
    return '';
  }

  getPayload() {
    return {
      name: String(this.nameEl.value || '').trim() || 'Ozel Flamestone Level',
      data: {
        grid: this.grid.map((row) => row.join('')),
      },
    };
  }

  render() {
    this.gridEl.innerHTML = '';
    for (let y = 0; y < this.size; y += 1) {
      for (let x = 0; x < this.size; x += 1) {
        const val = this.grid[y][x];
        const b = document.createElement('button');
        b.type = 'button';
        b.style.height = '28px';
        b.style.border = '1px solid #cbd5e1';
        b.style.borderRadius = '4px';
        b.style.fontWeight = '700';
        b.style.fontSize = '11px';
        b.style.cursor = 'pointer';
        b.style.background = '#fff';
        b.textContent = val === '.' ? '' : val;
        if (val === '#') b.style.background = '#334155';
        if (val === 'B') b.style.background = '#f59e0b';
        if (val === 'T') b.style.background = '#ef4444';
        if (val === 'K') b.style.background = '#facc15';
        if (val === 'D') b.style.background = '#38bdf8';
        if (val === 'G') b.style.background = '#22c55e';
        if (val === 'S') b.style.background = '#60a5fa';
        b.addEventListener('click', () => this.applyTool(x, y));
        this.gridEl.appendChild(b);
      }
    }
    this.previewEl.textContent = JSON.stringify(this.getPayload(), null, 2);
  }
}
