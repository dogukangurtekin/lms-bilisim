<template>
  <aside class="panel">
    <div class="panel-head">
      <h3>Blok Özellikleri</h3>
      <span v-if="node" :class="['type-badge', `type-${node.type}`]">{{ typeLabel }}</span>
    </div>

    <div v-if="!node" class="empty-hint">
      <span class="empty-icon">🧭</span>
      Düzenlemek için tuval üzerinden bir blok seçin.
    </div>
    <template v-else>
      <label>Görünen Metin</label>
      <input
        :value="node.text"
        placeholder="Bloğun üzerinde görünecek kısa başlık"
        @input="$emit('update-node', { text: $event.target.value })"
      />

      <template v-if="node.type !== 'start' && node.type !== 'end'">
        <label>Kod</label>
        <textarea
          :value="node.code"
          rows="4"
          spellcheck="false"
          :placeholder="codePlaceholder"
          @input="$emit('update-node', { code: $event.target.value })"
        />
        <p class="hint">{{ hintText }}</p>
      </template>

      <div class="actions">
        <button class="danger" type="button" @click="$emit('delete-node')">🗑 Bloğu Sil</button>
      </div>
    </template>
  </aside>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  node: { type: Object, default: null },
});

const typeLabel = computed(() => {
  const map = { start: 'Başla', end: 'Bitir', process: 'İşlem', io: 'Giriş/Çıkış', decision: 'Koşul' };
  return map[props.node?.type] || '';
});

const codePlaceholder = computed(() => {
  if (props.node?.type === 'process') return 'x = x + 1';
  if (props.node?.type === 'decision') return 'x > 10';
  if (props.node?.type === 'io') return 'input x   veya   output x';
  return '';
});

const hintText = computed(() => {
  if (props.node?.type === 'process') return 'Bir değişkene değer ata: degisken = ifade. Örn: toplam = toplam + i';
  if (props.node?.type === 'decision') return 'Doğru/yanlış sonuç veren bir karşılaştırma yaz: x > 10, x == 0, x <= n ...';
  if (props.node?.type === 'io') return '"input degisken" kullanıcıdan değer ister; "output ifade" ekrana yazdırır. Örn: output "Merhaba " + isim';
  return '';
});
</script>

<style scoped>
.panel { border:1px solid #dbe4ee; border-radius:16px; padding:14px; background:#fff; box-shadow:0 10px 24px rgba(15,23,42,.06); }
.panel-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px; }
.panel-head h3 { margin:0; font-size:14px; color:#0f172a; }
.type-badge { font-size:10.5px; font-weight:800; padding:3px 9px; border-radius:999px; text-transform:uppercase; letter-spacing:.03em; }
.type-start { background:#E4F7EF; color:#0EA57A; }
.type-end { background:#FEE2E2; color:#DC2626; }
.type-process { background:#EEEBFD; color:#5B3DF5; }
.type-io { background:#FEF3E2; color:#D97706; }
.type-decision { background:#FFEEE4; color:#EA580C; }

.empty-hint { display:flex; flex-direction:column; align-items:center; gap:8px; padding:24px 8px; color:#94a3b8; font-size:12.5px; text-align:center; }
.empty-icon { font-size:26px; }

label { font-size:12px; font-weight:700; color:#475569; display:block; margin:10px 0 4px; }
input,textarea { width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font-size:13px; box-sizing:border-box; font-family:'JetBrains Mono', ui-monospace, monospace; }
input { font-family: inherit; }
.hint { margin:6px 2px 0; font-size:11.5px; line-height:1.5; color:#64748b; background:#f8fafc; border-radius:8px; padding:8px 10px; }
.actions { margin-top:14px; display:flex; }
.danger { background:#DC2626; color:#fff; border:none; border-radius:9px; padding:9px 12px; cursor:pointer; font-weight:700; font-size:13px; transition:filter .12s; }
.danger:hover { filter:brightness(.92); }
</style>
