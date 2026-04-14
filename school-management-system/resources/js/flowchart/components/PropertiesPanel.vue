<template>
  <aside class="panel">
    <h3>Blok Özellikleri</h3>
    <div v-if="!node">Bir blok seçin.</div>
    <template v-else>
      <label>Tip</label>
      <input :value="node.type" disabled />

      <label>Metin</label>
      <textarea :value="node.text" rows="3" @input="$emit('update-node', { text: $event.target.value })" />

      <label>Kod</label>
      <textarea :value="node.code" rows="6" @input="$emit('update-node', { code: $event.target.value })" />

      <div class="actions">
        <button class="danger" @click="$emit('delete-node')">Bloğu Sil</button>
      </div>
    </template>
  </aside>
</template>

<script setup>
defineProps({
  node: { type: Object, default: null },
});
</script>

<style scoped>
.panel { border:1px solid #d1d5db; border-radius:14px; padding:12px; background:#fff; box-shadow:0 8px 18px rgba(15,23,42,.08); }
label { font-size:12px; font-weight:700; color:#475569; display:block; margin:8px 0 4px; }
input,textarea { width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font-size:13px; }
.actions { margin-top:10px; display:flex; }
.danger { background:#b91c1c; color:#fff; border:none; border-radius:8px; padding:8px 10px; cursor:pointer; font-weight:700; }
</style>

