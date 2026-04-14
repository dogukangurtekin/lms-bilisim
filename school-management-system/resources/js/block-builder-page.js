import { createApp, h } from 'vue';
import BlockBuilder from './components/BlockBuilder.vue';

function mountPage() {
  const root = document.getElementById('block-builder-page-root');
  if (!root) return;

  const app = createApp({
    setup() {
      const close = () => {
        if (window.history.length > 1) {
          window.history.back();
          return;
        }
        window.location.href = '/';
      };

      return () => h(BlockBuilder, {
        visible: true,
        standalone: true,
        onClose: close,
      });
    },
  });

  app.mount(root);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountPage);
} else {
  mountPage();
}
