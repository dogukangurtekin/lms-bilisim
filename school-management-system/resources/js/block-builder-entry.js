import { createApp, h, ref } from 'vue';
import BlockBuilder from './components/BlockBuilder.vue';

function mountBlockBuilder() {
  const root = document.getElementById('block-builder-root');
  if (!root) return;

  const stateVisible = ref(false);

  const app = createApp({
    setup() {
      const open = () => {
        stateVisible.value = true;
      };
      const close = () => {
        stateVisible.value = false;
      };

      window.addEventListener('open-block-builder', open);
      window.addEventListener('close-block-builder', close);

      return () => h(BlockBuilder, {
        visible: stateVisible.value,
        onClose: close,
      });
    },
  });

  app.mount(root);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountBlockBuilder);
} else {
  mountBlockBuilder();
}
