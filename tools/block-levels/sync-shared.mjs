// 2B ve 3B Blok Kodlama'nın ortak dosyalarını (yürütme motoru + blok editörü) eşitler.
//   node tools/block-levels/sync-shared.mjs
// Kaynak: public/runner-assets/block-grid-runner/{engine.js,editor.js}  ->  public/runner-assets/block-3d-runner/
// (Her runner kendi klasöründen sürümlenerek sunulduğu için dosyalar kopyalanır; ortak mantığı sadece 2B klasöründe düzenleyin.)
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
const here = path.dirname(fileURLToPath(import.meta.url));
const from = path.resolve(here, '../../public/runner-assets/block-grid-runner');
const to = path.resolve(here, '../../public/runner-assets/block-3d-runner');
for (const f of ['engine.js', 'editor.js']) {
  fs.copyFileSync(path.join(from, f), path.join(to, f));
  console.log('kopyalandı:', f);
}
