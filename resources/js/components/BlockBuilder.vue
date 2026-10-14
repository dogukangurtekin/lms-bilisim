<template>
  <div class="studio-root" v-show="standalone || visible" :class="theme === 'dark' ? 'is-dark' : 'is-light'">
    <div v-if="!standalone" class="studio-overlay" @click="emitClose"></div>

    <div class="studio-frame">
      <div ref="sceneHost" class="scene-host"></div>

      <!-- üst şerit -->
      <header class="topbar">
        <div class="brand">
          <span class="brand-mark">🧱</span>
          <div class="brand-text">
            <strong>Blok Tasarım Stüdyosu</strong>
            <small>3D Grid · Bilişim Kod</small>
          </div>
        </div>

        <input v-model="designName" type="text" class="name-field" placeholder="Tasarım adı" aria-label="Tasarım adı" />

        <div class="top-actions">
          <button class="tbtn tbtn-save" :disabled="loading" @click="saveDesign">💾 Kaydet</button>
          <button class="tbtn tbtn-load" :disabled="loading" @click="loadLatest">📂 Yükle</button>
          <button class="tbtn tbtn-exit" @click="emitClose">{{ standalone ? 'Panele Dön' : 'Kapat' }}</button>
        </div>
      </header>

      <!-- sol araç paneli -->
      <aside class="tool-panel">
        <div class="seg" role="group" aria-label="Araç">
          <button class="seg-btn" :class="mode === 'add' ? 'on add' : ''" @click="setMode('add')">＋ Ekle</button>
          <button class="seg-btn" :class="mode === 'erase' ? 'on erase' : ''" @click="setMode('erase')">⌫ Sil</button>
        </div>

        <section class="group">
          <h4>Renk</h4>
          <div class="swatches">
            <button
              v-for="c in colorPresets"
              :key="c"
              class="swatch"
              :class="{ on: c.toLowerCase() === activeColor.toLowerCase() }"
              :style="{ background: c }"
              :title="c"
              @click="pickColor(c)"
            ></button>
            <label class="swatch custom" title="Özel renk">
              <input v-model="activeColor" type="color" @input="applyMaterial" />
              <span>＋</span>
            </label>
          </div>
        </section>

        <section class="group group-scroll">
          <h4>Doku</h4>
          <div v-for="group in textureGroups" :key="group.label" class="tex-group">
            <div class="tex-label">{{ group.label }}</div>
            <div class="tex-grid">
              <button
                v-for="item in group.items"
                :key="item.value"
                class="tex"
                :class="{ on: activeTexture === item.value }"
                :title="item.label"
                @click="pickTexture(item.value)"
              >
                <i class="tex-chip" :style="texChipStyle(item.value)"></i>
                <span>{{ item.label }}</span>
              </button>
            </div>
          </div>
        </section>

        <div class="tool-foot">
          <button class="mini" @click="toggleTheme">{{ theme === 'light' ? '🌙 Koyu' : '☀️ Açık' }}</button>
          <button class="mini" @click="resetCamera">🎥 Kamera</button>
          <button class="mini danger" @click="clearAll">🗑 Temizle</button>
        </div>
      </aside>

      <!-- alt bilgi çubuğu -->
      <div class="hud">
        <span class="chip">📍 {{ hoverLabel }}</span>
        <span class="chip">🧱 {{ blocks.length }} blok</span>
        <span class="chip chip-status">{{ statusText }}</span>
        <span class="chip chip-tip">Sol tık: {{ mode === 'add' ? 'ekle' : 'sil' }} · Sürükle: döndür · Sağ tık: kaydır · Tekerlek: yakınlaş</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { BlockScene } from '../three/scene';

const props = defineProps({
  visible: { type: Boolean, default: false },
  standalone: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'saved', 'loaded', 'changed']);

const sceneHost = ref(null);
const scene = ref(null);
const mode = ref('add');
const theme = ref('light');
const activeColor = ref('#38bdf8');
const activeTexture = ref('flat_matte');
const hoverCell = ref(null);
const blocks = ref([]);
const designName = ref('Tasarim');
const designId = ref(null);
const loading = ref(false);
const statusText = ref('Hazır');
const textureGroups = [
  {
    label: 'Düz Renk',
    items: [
      { value: 'flat_matte', label: 'Düz Mat' },
      { value: 'flat_soft', label: 'Düz Soft' },
      { value: 'flat_gloss', label: 'Düz Parlak' },
      { value: 'plain', label: 'Düz Klasik' },
    ],
  },
  {
    label: 'Ahşap',
    items: [
      { value: 'wood_oak', label: 'Meşe' },
      { value: 'wood_walnut', label: 'Ceviz' },
      { value: 'wood_pine', label: 'Çam' },
    ],
  },
  {
    label: 'Beton / Taş',
    items: [
      { value: 'concrete', label: 'Beton Standart' },
      { value: 'concrete_dark', label: 'Beton Koyu' },
      { value: 'concrete_light', label: 'Beton Açık' },
    ],
  },
  {
    label: 'Su / Deniz',
    items: [
      { value: 'sea_shallow', label: 'Deniz Sığ' },
      { value: 'sea_deep', label: 'Deniz Derin' },
      { value: 'sea_foam', label: 'Deniz Köpük' },
    ],
  },
  {
    label: 'Çim / Doğa',
    items: [
      { value: 'grass', label: 'Çim Standart' },
      { value: 'grass_fresh', label: 'Çim Taze' },
      { value: 'grass_dark', label: 'Çim Koyu' },
    ],
  },
];

const colorPresets = [
  '#38bdf8', '#2563eb', '#7c3aed', '#ec4899', '#ef4444', '#f97316',
  '#facc15', '#22c55e', '#14b8a6', '#ffffff', '#94a3b8', '#1e293b',
];

// Doku seçici kartlarındaki küçük önizlemeler (yaklaşık CSS karşılıkları)
const texturePreview = {
  flat_matte: 'linear-gradient(145deg, #cbd5e1, #94a3b8)',
  flat_soft: 'linear-gradient(145deg, #f1f5f9, #cbd5e1)',
  flat_gloss: 'linear-gradient(145deg, #ffffff 10%, #93c5fd 45%, #3b82f6)',
  plain: 'linear-gradient(145deg, #e2e8f0, #cbd5e1)',
  wood_oak: 'repeating-linear-gradient(90deg, #c08a4a 0 6px, #a9733a 6px 9px)',
  wood_walnut: 'repeating-linear-gradient(90deg, #6b4423 0 6px, #55341a 6px 9px)',
  wood_pine: 'repeating-linear-gradient(90deg, #e3b97a 0 6px, #cfa062 6px 9px)',
  concrete: 'radial-gradient(circle at 30% 30%, #b6bcc6 0 2px, transparent 3px) 0 0 / 9px 9px, #9ca3af',
  concrete_dark: 'radial-gradient(circle at 30% 30%, #6b7280 0 2px, transparent 3px) 0 0 / 9px 9px, #4b5563',
  concrete_light: 'radial-gradient(circle at 30% 30%, #e5e7eb 0 2px, transparent 3px) 0 0 / 9px 9px, #d1d5db',
  sea_shallow: 'repeating-linear-gradient(160deg, #67e8f9 0 5px, #22d3ee 5px 10px)',
  sea_deep: 'repeating-linear-gradient(160deg, #1d4ed8 0 5px, #1e3a8a 5px 10px)',
  sea_foam: 'repeating-linear-gradient(160deg, #e0f7ff 0 5px, #7dd3fc 5px 10px)',
  grass: 'repeating-linear-gradient(100deg, #4ade80 0 3px, #22c55e 3px 6px)',
  grass_fresh: 'repeating-linear-gradient(100deg, #a3e635 0 3px, #65a30d 3px 6px)',
  grass_dark: 'repeating-linear-gradient(100deg, #15803d 0 3px, #14532d 3px 6px)',
};

function texChipStyle(value) {
  return { background: texturePreview[value] || '#cbd5e1' };
}

function pickColor(c) {
  activeColor.value = c;
  applyMaterial();
}

function pickTexture(value) {
  activeTexture.value = value;
  applyMaterial();
}

const hoverLabel = computed(() => {
  if (!hoverCell.value) return '-';
  const h = hoverCell.value;
  return `${h.x}, ${h.y}, ${h.z}`;
});

function emitClose() {
  emit('close');
}

function getAuthHeaders() {
  const raw = localStorage.getItem('mysql_auth_user');
  const parsed = raw ? JSON.parse(raw) : null;
  const uid = String(parsed?.uid || parsed?.id || '').trim();
  const token = String(parsed?.token || '').trim();
  if (!uid || !token) {
    throw new Error('Önce giriş yapmalısınız.');
  }
  return {
    headers: {
      'Content-Type': 'application/json',
      'X-Client-Uid': uid,
      'X-Client-Token': token,
      Authorization: `Bearer ${token}`,
    },
  };
}

function getApiBase() {
  const metaBase = String(document.querySelector('meta[name="app-base-url"]')?.content || '').trim().replace(/\/+$/, '');
  if (metaBase) return `${metaBase}/api/client`;

  const hasIndexPhp = /\/index\.php(\/|$)/i.test(window.location.pathname);
  const root = hasIndexPhp ? `${window.location.origin}/index.php` : window.location.origin;
  return `${root}/api/client`;
}

function apiUrl(path) {
  return `${getApiBase().replace(/\/+$/, '')}/block-builder${path}`;
}

async function apiRequest(path, options = {}) {
  const auth = getAuthHeaders();
  const response = await fetch(apiUrl(path), {
    ...options,
    headers: {
      ...(options.headers || {}),
      ...auth.headers,
    },
  });
  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(String(data?.message || `HTTP ${response.status}`));
  }
  return data;
}

function buildScene() {
  if (!sceneHost.value || scene.value) return;
  scene.value = new BlockScene({
    container: sceneHost.value,
    onHoverCell: (cell) => {
      hoverCell.value = cell;
    },
    onBlocksChanged: (list) => {
      blocks.value = list;
      emit('changed', list);
    },
  });
  scene.value.setMode(mode.value);
  scene.value.setActiveMaterial({ color: activeColor.value, texture: activeTexture.value });
  scene.value.setTheme(theme.value);
}

function destroyScene() {
  if (!scene.value) return;
  scene.value.dispose();
  sceneHost.value?.replaceChildren();
  scene.value = null;
}

function setMode(nextMode) {
  mode.value = nextMode;
  scene.value?.setMode(nextMode);
}

function toggleTheme() {
  theme.value = theme.value === 'light' ? 'dark' : 'light';
  scene.value?.setTheme(theme.value);
}

function applyMaterial() {
  scene.value?.setActiveMaterial({ color: activeColor.value, texture: activeTexture.value });
}

function clearAll() {
  scene.value?.clearAll();
  statusText.value = 'Sahne temizlendi';
}

function resetCamera() {
  scene.value?.resetCamera();
}

async function saveDesign() {
  try {
    loading.value = true;
    statusText.value = 'Kaydediliyor...';
    const payload = {
      id: designId.value,
      name: designName.value,
      blocks: scene.value?.getBlocks() || [],
    };
    const res = await apiRequest('/designs', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    designId.value = Number(res?.design?.id || 0) || null;
    designName.value = String(res?.design?.name || designName.value);
    statusText.value = 'Kaydedildi';
    emit('saved', res?.design || null);
  } catch (error) {
    statusText.value = `Hata: ${error.message}`;
  } finally {
    loading.value = false;
  }
}

async function loadLatest() {
  try {
    loading.value = true;
    statusText.value = 'Yükleniyor...';
    const res = await apiRequest('/designs/latest', { method: 'GET' });
    if (!res?.design) {
      statusText.value = 'Kayıt bulunamadı';
      scene.value?.setBlocks([]);
      designId.value = null;
      return;
    }
    designId.value = Number(res.design.id || 0) || null;
    designName.value = String(res.design.name || 'Tasarim');
    scene.value?.setBlocks(Array.isArray(res.design.blocks) ? res.design.blocks : []);
    statusText.value = 'Yüklendi';
    emit('loaded', res.design);
  } catch (error) {
    statusText.value = `Hata: ${error.message}`;
  } finally {
    loading.value = false;
  }
}

watch(
  () => [props.visible, props.standalone],
  async ([isVisible, isStandalone]) => {
    if (isStandalone || isVisible) {
      await nextTick();
      buildScene();
      if (!blocks.value.length) {
        loadLatest().catch(() => {});
      }
      return;
    }
    statusText.value = 'Hazır';
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  destroyScene();
});
</script>

<style scoped>
.studio-root {
  --navy-1: #0a1738;
  --navy-2: #12285c;
  --ink: #0b2447;
  --soft: #5b7096;
  --line: #d3e1fb;
  --panel: rgba(255, 255, 255, .88);
  --panel-ink: #0b2447;
  --chip: rgba(255, 255, 255, .92);
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  font-family: "Nunito", "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
  color: var(--panel-ink);
  background: linear-gradient(160deg, #eef4ff, #dbe8ff);
}
.studio-root.is-dark {
  --panel: rgba(13, 26, 56, .86);
  --panel-ink: #e6eeff;
  --soft: #93a9d6;
  --line: rgba(255, 255, 255, .14);
  --chip: rgba(13, 26, 56, .86);
  background: #0a1738;
}

.studio-overlay { position: absolute; inset: 0; background: rgba(8, 18, 44, .5); backdrop-filter: blur(3px); }
.studio-frame { position: absolute; inset: 0; }
.scene-host { position: absolute; inset: 0; width: 100vw; height: 100vh; }

/* ---------- üst şerit ---------- */
.topbar {
  position: absolute; top: 10px; left: 12px; right: 12px; z-index: 20; display: flex; align-items: center; gap: 14px; padding: 8px 14px;
  border-radius: 22px; color: #fff; background: linear-gradient(165deg, var(--navy-1), var(--navy-2));
  border: 1px solid rgba(255, 255, 255, .12); box-shadow: 0 14px 30px rgba(11, 36, 71, .35), inset 0 1px 0 rgba(255, 255, 255, .14);
}
.brand { display: flex; align-items: center; gap: 11px; min-width: 0; }
.brand-mark {
  width: 42px; height: 42px; display: grid; place-items: center; font-size: 23px; border-radius: 14px; flex: 0 0 auto;
  background: linear-gradient(145deg, #4cc3ee, #1d8fd0); box-shadow: 0 6px 14px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .45);
}
.brand-text strong { display: block; font-size: 16px; font-weight: 900; letter-spacing: .2px; white-space: nowrap; }
.brand-text small { display: block; font-size: 11.5px; font-weight: 700; color: #aac2ee; }
.name-field {
  flex: 1; max-width: 340px; min-width: 120px; height: 38px; padding: 0 14px; border-radius: 13px; margin-left: auto;
  border: 2px solid rgba(255, 255, 255, .2); background: rgba(255, 255, 255, .1); color: #fff; font: inherit; font-weight: 800;
}
.name-field::placeholder { color: #93a9d6; }
.name-field:focus { outline: none; border-color: #7fb0ff; background: rgba(255, 255, 255, .16); }
.top-actions { display: flex; gap: 8px; flex: 0 0 auto; }
.tbtn {
  height: 38px; padding: 0 16px; border: 0; border-radius: 13px; color: #fff; font: inherit; font-weight: 900; font-size: 13.5px; cursor: pointer;
  transition: transform .1s, box-shadow .1s, filter .1s; box-shadow: 0 4px 0 rgba(0, 0, 0, .28);
}
.tbtn:hover:not(:disabled) { filter: brightness(1.08); }
.tbtn:active:not(:disabled) { transform: translateY(3px); box-shadow: 0 1px 0 rgba(0, 0, 0, .28); }
.tbtn:disabled { opacity: .5; cursor: not-allowed; }
.tbtn-save { background: linear-gradient(180deg, #22d398, #16a34a); }
.tbtn-load { background: linear-gradient(145deg, #4cc3ee, #1d8fd0); }
.tbtn-exit { background: rgba(255, 255, 255, .14); border: 2px solid rgba(255, 255, 255, .22); box-shadow: none; }

/* ---------- sol araç paneli ---------- */
.tool-panel {
  position: absolute; z-index: 18; left: 12px; top: 74px; bottom: 56px; width: 236px; display: flex; flex-direction: column; gap: 12px; padding: 12px;
  border-radius: 24px; background: var(--panel); border: 1px solid var(--line); backdrop-filter: blur(14px); box-shadow: 0 18px 40px rgba(11, 36, 71, .22);
}
.seg { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; padding: 4px; border-radius: 16px; background: rgba(120, 150, 210, .16); }
.seg-btn {
  height: 40px; border: 0; border-radius: 12px; background: transparent; color: var(--panel-ink); font: inherit; font-weight: 900; font-size: 14px; cursor: pointer;
  transition: background .15s, color .15s, box-shadow .15s;
}
.seg-btn:hover { background: rgba(255, 255, 255, .5); }
.seg-btn.on.add { background: linear-gradient(145deg, #22d3ee, #0ea5e9); color: #fff; box-shadow: 0 6px 12px rgba(14, 165, 233, .4); }
.seg-btn.on.erase { background: linear-gradient(145deg, #fb7185, #e11d48); color: #fff; box-shadow: 0 6px 12px rgba(225, 29, 72, .4); }

.group h4 { margin: 0 0 7px; font-size: 11.5px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: var(--soft); }
.group-scroll { flex: 1; min-height: 0; overflow-y: auto; padding-right: 4px; scrollbar-width: thin; }
.swatches { display: grid; grid-template-columns: repeat(6, 1fr); gap: 7px; }
.swatch {
  position: relative; aspect-ratio: 1; border-radius: 11px; border: 2px solid rgba(255, 255, 255, .85); cursor: pointer; padding: 0;
  box-shadow: 0 3px 6px rgba(11, 36, 71, .25); transition: transform .12s, box-shadow .12s;
}
.swatch:hover { transform: translateY(-2px) scale(1.06); }
.swatch.on { box-shadow: 0 0 0 3px #2563eb, 0 3px 8px rgba(11, 36, 71, .3); transform: scale(1.08); }
.swatch.custom { display: grid; place-items: center; background: conic-gradient(#ef4444, #facc15, #22c55e, #38bdf8, #a855f7, #ef4444); color: #fff; font-weight: 900; overflow: hidden; }
.swatch.custom input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.swatch.custom span { text-shadow: 0 1px 3px rgba(0, 0, 0, .6); pointer-events: none; }

.tex-group { margin-bottom: 10px; }
.tex-label { font-size: 12px; font-weight: 900; color: var(--soft); margin: 0 0 5px 2px; }
.tex-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.tex {
  display: flex; align-items: center; gap: 7px; padding: 5px 8px 5px 5px; border-radius: 12px; border: 2px solid transparent; background: rgba(120, 150, 210, .14);
  color: var(--panel-ink); font: inherit; font-weight: 800; font-size: 11.5px; cursor: pointer; text-align: left; transition: border-color .12s, background .12s, transform .12s;
}
.tex:hover { background: rgba(120, 150, 210, .26); transform: translateY(-1px); }
.tex.on { border-color: #2563eb; background: rgba(37, 99, 235, .18); }
.tex-chip { width: 26px; height: 26px; border-radius: 8px; flex: 0 0 auto; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .15), 0 2px 4px rgba(0, 0, 0, .2); }
.tex span { line-height: 1.15; }

.tool-foot { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.mini {
  height: 34px; border-radius: 11px; border: 2px solid var(--line); background: rgba(255, 255, 255, .6); color: var(--panel-ink); font: inherit; font-weight: 900; font-size: 12px; cursor: pointer;
  transition: transform .1s, background .12s;
}
.studio-root.is-dark .mini { background: rgba(255, 255, 255, .08); }
.mini:hover { transform: translateY(-1px); background: rgba(255, 255, 255, .85); }
.studio-root.is-dark .mini:hover { background: rgba(255, 255, 255, .16); }
.mini.danger { grid-column: 1 / -1; color: #dc2626; border-color: rgba(220, 38, 38, .3); }

/* ---------- alt bilgi çubuğu ---------- */
.hud { position: absolute; z-index: 18; left: 12px; right: 12px; bottom: 10px; display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; pointer-events: none; }
.chip {
  padding: 7px 13px; border-radius: 999px; background: var(--chip); border: 1px solid var(--line); color: var(--panel-ink); font-size: 12.5px; font-weight: 800;
  box-shadow: 0 6px 14px rgba(11, 36, 71, .14); backdrop-filter: blur(10px);
}
.chip-status { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border-color: transparent; }
.chip-tip { color: var(--soft); font-weight: 700; }

@media (max-width: 860px) {
  .brand-text small, .chip-tip { display: none; }
  .tool-panel { width: 200px; }
  .name-field { max-width: 160px; }
}
@media (max-width: 640px) {
  .topbar { flex-wrap: wrap; gap: 8px; }
  .name-field { order: 3; max-width: none; flex: 1 1 100%; margin-left: 0; }
  .tool-panel { top: 120px; width: min(220px, 62vw); }
  .tbtn { padding: 0 11px; }
}
</style>
