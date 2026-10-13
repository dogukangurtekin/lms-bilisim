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
        b.className = 'fs-ed-cell';
        b.dataset.v = val;
        b.textContent = val === '.' ? '' : val;
        b.addEventListener('click', () => this.applyTool(x, y));
        this.gridEl.appendChild(b);
      }
    }
    this.previewEl.textContent = JSON.stringify(this.getPayload(), null, 2);
  }
}
