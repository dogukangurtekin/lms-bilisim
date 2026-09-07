<template>
  <section class="console">
    <div class="console-head">
      <div class="console-title"><span class="dot"></span> Konsol Çıktısı</div>
      <button type="button" @click="$emit('clear')">Temizle</button>
    </div>
    <pre v-if="lines.length">{{ lines.join('\n') }}</pre>
    <p v-else class="empty">Henüz çıktı yok — "Tam Çalıştır" veya "Adım Adım" ile akışı başlatın.</p>
  </section>
</template>

<script setup>
defineProps({
  lines: { type: Array, default: () => [] },
});
</script>

<style scoped>
.console { border:1px solid #1e293b; border-radius:14px; background:#0b0f1a; color:#cbd5e1; padding:12px; min-height:170px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.03); }
.console-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
.console-title { display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:700; color:#e2e8f0; letter-spacing:.02em; }
.dot { width:8px; height:8px; border-radius:999px; background:#22c55e; box-shadow:0 0 8px #22c55e; }
.console-head button { border:1px solid #334155; background:#151b2c; color:#e2e8f0; border-radius:8px; padding:5px 10px; cursor:pointer; font-size:11.5px; font-weight:700; transition:background .12s; }
.console-head button:hover { background:#1f2940; }
pre { margin:0; white-space:pre-wrap; word-break:break-word; font-size:12.5px; line-height:1.5; font-family:'JetBrains Mono', ui-monospace, monospace; }
.empty { margin:0; font-size:12px; color:#64748b; line-height:1.5; }
</style>
