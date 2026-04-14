<template>
  <section class="console">
    <div class="console-head">
      <h3>Console Output</h3>
      <button @click="$emit('clear')">Temizle</button>
    </div>
    <pre>{{ lines.length ? lines.join('\n') : 'Henüz çıktı yok.' }}</pre>
  </section>
</template>

<script setup>
defineProps({
  lines: { type: Array, default: () => [] },
});
</script>

<style scoped>
.console { border:1px solid #1e293b; border-radius:12px; background:#020617; color:#cbd5e1; padding:10px; min-height:170px; }
.console-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
.console-head h3 { margin:0; font-size:14px; color:#e2e8f0; }
.console-head button { border:1px solid #334155; background:#0f172a; color:#e2e8f0; border-radius:8px; padding:4px 8px; cursor:pointer; }
pre { margin:0; white-space:pre-wrap; font-size:12px; line-height:1.35; }
</style>

