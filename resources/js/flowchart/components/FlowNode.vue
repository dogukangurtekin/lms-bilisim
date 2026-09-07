<template>
  <div :class="['node-shell', `node-${data.type}`, { active: data.active, executing: data.executing, waiting: data.waiting }]">
    <Handle type="target" :position="Position.Top" class="flow-handle flow-handle-target" :connectable="true" />
    <Handle
      v-if="data.type === 'decision'"
      id="yes"
      type="source"
      :position="Position.Right"
      :connectable="true"
      class="flow-handle flow-handle-yes"
    />
    <Handle
      v-if="data.type === 'decision'"
      id="no"
      type="source"
      :position="Position.Bottom"
      :connectable="true"
      class="flow-handle flow-handle-no"
    />
    <Handle
      v-if="data.type !== 'end' && data.type !== 'decision'"
      type="source"
      :position="Position.Bottom"
      :connectable="true"
      class="flow-handle"
    />

    <div class="node-shape">
      <span class="node-icon">{{ icon }}</span>
      <div class="node-body">
        <div class="node-title">{{ title }}</div>
        <div class="node-text">{{ data.text || placeholder }}</div>
      </div>
    </div>
    <span v-if="data.waiting" class="node-waiting-badge" title="Girdi bekleniyor">⏳ Girdi bekleniyor</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Handle, Position } from '@vue-flow/core';

const props = defineProps({
  data: { type: Object, required: true },
});

const title = computed(() => {
  const map = {
    start: 'Başla',
    end: 'Bitir',
    process: 'İşlem',
    io: 'Giriş / Çıkış',
    decision: 'Koşul',
  };
  return map[props.data.type] || 'Blok';
});

const icon = computed(() => {
  const map = { start: '▶', end: '■', process: '⚙', io: '⇄', decision: '◆' };
  return map[props.data.type] || '•';
});

const placeholder = computed(() => {
  if (props.data.type === 'process') return 'x = x + 1';
  if (props.data.type === 'decision') return 'x > 10';
  if (props.data.type === 'io') return 'input x  /  output x';
  return '...';
});
</script>

<style scoped>
/* Genel kabuk: Vue Flow'un handle'ları bu kapsayıcıya göre konumlanır. */
.node-shell {
  position: relative;
  min-width: 176px;
  max-width: 240px;
}

/* Gerçek şekil: her blok tipi kendi rengini ve gerçek flowchart formunu
   (oval / dikdörtgen / paralelkenar / eşkenar dörtgen) taşır. Eski sürümde
   "decision" bloğu 45° döndürülmüş bir kare ile taklit ediliyordu; bu hem
   görsel olarak bozuk duruyordu hem de metin okunabilirliğini düşürüyordu. */
.node-shape {
  display: flex;
  align-items: center;
  gap: 10px;
  min-height: 64px;
  padding: 10px 16px;
  border: 2.5px solid var(--nc-border, #64748b);
  background: var(--nc-bg, #fff);
  box-shadow: 0 10px 22px rgba(15, 23, 42, .10), inset 0 0 0 1px rgba(255,255,255,.5);
  transition: box-shadow .18s, transform .12s, border-color .18s;
}
.node-icon {
  flex: 0 0 auto;
  width: 26px; height: 26px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: 999px;
  background: var(--nc-border, #64748b);
  color: #fff;
  font-size: 13px;
}
.node-body { min-width: 0; }
.node-title { font-weight: 800; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--nc-border, #475569); margin-bottom: 2px; }
.node-text { font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.3; word-break: break-word; font-family: 'JetBrains Mono', ui-monospace, monospace; }

/* Start / End: oval (hap) form, koyu yeşil / kırmızı */
.node-start .node-shape, .node-end .node-shape { border-radius: 999px; justify-content: center; }
.node-start { --nc-border: #0EA57A; --nc-bg: #E4F7EF; }
.node-end { --nc-border: #DC2626; --nc-bg: #FEE2E2; }

/* Process: dikdörtgen, mor */
.node-process { --nc-border: #5B3DF5; --nc-bg: #EEEBFD; }
.node-process .node-shape { border-radius: 12px; }

/* I/O: paralelkenar (skew ile), amber */
.node-io { --nc-border: #D97706; --nc-bg: #FEF3E2; }
.node-io .node-shape {
  border-radius: 4px;
  clip-path: polygon(12% 0%, 100% 0%, 88% 100%, 0% 100%);
  padding-left: 26px;
  padding-right: 26px;
}
.node-io .node-icon { transform: none; }

/* Decision: gerçek eşkenar dörtgen (rotasyon hilesi yok), turuncu */
.node-decision { --nc-border: #EA580C; --nc-bg: #FFEEE4; min-width: 190px; }
.node-decision .node-shape {
  min-height: 108px;
  border-radius: 6px;
  clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
  padding: 18px 40px;
  justify-content: center;
  text-align: center;
}
.node-decision .node-body { text-align: center; }
.node-decision .node-icon { display: none; }

/* Seçili (özellik panelinde düzenleniyor) */
.active .node-shape { box-shadow: 0 0 0 4px rgba(91,61,245,.25), 0 14px 30px rgba(15,23,42,.16); }

/* Şu an çalışan blok: nabız gibi atan belirgin halka */
.executing .node-shape {
  animation: node-pulse 1.1s ease-in-out infinite;
  border-color: #14b8a6 !important;
}
@keyframes node-pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(20,184,166,.45), 0 10px 22px rgba(15,23,42,.10); }
  50% { box-shadow: 0 0 0 8px rgba(20,184,166,0), 0 10px 22px rgba(15,23,42,.10); }
}

.node-waiting-badge {
  position: absolute;
  left: 50%;
  bottom: -26px;
  transform: translateX(-50%);
  white-space: nowrap;
  background: #1e1b3a;
  color: #fbbf24;
  font-size: 10.5px;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 999px;
  box-shadow: 0 8px 18px rgba(15,23,42,.28);
}

.flow-handle { width: 14px; height: 14px; border: 2px solid #0f172a; background: #fff; }
.flow-handle-yes { background: #86efac; border-color: #15803d; }
.flow-handle-no { background: #fecaca; border-color: #b91c1c; }
</style>
