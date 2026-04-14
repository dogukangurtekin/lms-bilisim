<template>
  <div class="studio-root" v-show="standalone || visible">
    <div class="studio-bg"></div>
    <div v-if="!standalone" class="studio-overlay" @click="emitClose"></div>

    <div class="studio-frame">
      <div ref="sceneHost" class="scene-host"></div>

      <div class="top-dock">
        <div class="dock-title">3D Grid Tasarım Stüdyosu</div>

        <div class="dock-row">
          <input v-model="designName" type="text" class="field" placeholder="Tasarım adı" />
          <label class="label">Renk</label>
          <input v-model="activeColor" type="color" class="color" @input="applyMaterial" />
          <label class="label">Texture</label>
          <select v-model="activeTexture" class="field select" @change="applyMaterial">
            <optgroup v-for="group in textureGroups" :key="group.label" :label="group.label">
              <option v-for="item in group.items" :key="item.value" :value="item.value">{{ item.label }}</option>
            </optgroup>
          </select>

          <button class="btn btn-soft" :class="mode === 'add' ? 'is-active add' : ''" @click="setMode('add')">Ekle</button>
          <button class="btn btn-soft" :class="mode === 'erase' ? 'is-active erase' : ''" @click="setMode('erase')">Sil</button>
          <button class="btn btn-soft" @click="toggleTheme">{{ theme === 'light' ? 'Koyu Grid' : 'Açık Grid' }}</button>
          <button class="btn btn-soft" @click="clearAll">Temizle</button>
          <button class="btn btn-primary" @click="resetCamera">Kamera</button>
          <button class="btn btn-success" :disabled="loading" @click="saveDesign">Kaydet</button>
          <button class="btn btn-primary" :disabled="loading" @click="loadLatest">Yükle</button>
          <button class="btn btn-soft" @click="emitClose">{{ standalone ? 'Panele Dön' : 'Kapat' }}</button>
        </div>
      </div>

      <div class="hud">Hücre: {{ hoverLabel }} | Blok: {{ blocks.length }} | {{ statusText }}</div>
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
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
}

.studio-bg {
  position: absolute;
  inset: -20%;
  background:
    radial-gradient(circle at 12% 14%, rgba(186, 230, 253, 0.7), transparent 30%),
    radial-gradient(circle at 84% 18%, rgba(224, 231, 255, 0.85), transparent 32%),
    radial-gradient(circle at 76% 78%, rgba(254, 226, 226, 0.65), transparent 30%),
    linear-gradient(180deg, #f8fbff 0%, #f1f5f9 45%, #eef2ff 100%);
  animation: drift 18s ease-in-out infinite alternate;
}

@keyframes drift {
  0% { transform: translate3d(-1.2%, -0.8%, 0) scale(1); }
  100% { transform: translate3d(1.2%, 0.8%, 0) scale(1.03); }
}

.studio-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(2px);
}

.studio-frame {
  position: absolute;
  inset: 0;
}

.scene-host {
  position: absolute;
  inset: 0;
  width: 100vw;
  height: 100vh;
}

.top-dock {
  position: absolute;
  top: 14px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 20;
  width: min(1240px, 96vw);
  padding: 10px 12px;
  border-radius: 18px;
  border: 1px solid rgba(203, 213, 225, 0.9);
  background: rgba(255, 255, 255, 0.86);
  backdrop-filter: blur(12px);
  box-shadow: 0 15px 45px rgba(148, 163, 184, 0.33);
}

.dock-title {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 8px;
  text-align: center;
  letter-spacing: 0.2px;
}

.dock-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
}

.label {
  font-size: 12px;
  color: #475569;
  font-weight: 600;
}

.field {
  height: 38px;
  padding: 0 12px;
  border: 1px solid #dbe4ef;
  border-radius: 12px;
  background: #fff;
  color: #0f172a;
  min-width: 150px;
}

.select { min-width: 110px; }

.color {
  height: 38px;
  width: 44px;
  border: 1px solid #dbe4ef;
  border-radius: 12px;
  background: #fff;
  padding: 2px;
}

.btn {
  height: 38px;
  padding: 0 14px;
  border: 1px solid #dbe4ef;
  border-radius: 999px;
  font-weight: 600;
  cursor: pointer;
  transition: transform .18s ease, box-shadow .2s ease, border-color .2s ease;
}

.btn:hover { transform: translateY(-1px); }

.btn-soft {
  background: #fff;
  color: #0f172a;
}

.btn-soft:hover {
  border-color: #93c5fd;
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.16);
}

.btn-primary {
  color: #fff;
  border-color: transparent;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  box-shadow: 0 10px 24px rgba(99, 102, 241, 0.25);
}

.btn-success {
  color: #fff;
  border-color: transparent;
  background: linear-gradient(135deg, #10b981, #14b8a6);
  box-shadow: 0 10px 24px rgba(16, 185, 129, 0.25);
}

.btn.is-active.add {
  color: #fff;
  border-color: transparent;
  background: linear-gradient(135deg, #0ea5e9, #22d3ee);
}

.btn.is-active.erase {
  color: #fff;
  border-color: transparent;
  background: linear-gradient(135deg, #f43f5e, #fb7185);
}

.hud {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 128px;
  z-index: 20;
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid rgba(203, 213, 225, 0.9);
  background: rgba(255, 255, 255, 0.9);
  color: #334155;
  font-size: 12px;
  font-weight: 600;
  box-shadow: 0 10px 24px rgba(148, 163, 184, 0.25);
}
</style>
