<template>
  <div class="wpqss-file">
    <div class="wpqss-file-header" @click="openFileInEditor" :title="'Click to open in default editor: ' + file.file">
      <div class="wpqss-file-info">
        <span class="wpqss-file-icon">📄</span>
        <span class="wpqss-file-path">{{ file.file }}</span>
        <span class="wpqss-file-count">{{ file.vulnerabilities.length }} issues</span>
      </div>
      <button class="wpqss-open-btn" :title="'Open ' + file.file + ' in default editor'">
        <span class="wpqss-open-icon">🔗</span>
      </button>
    </div>
    <div class="wpqss-vulnerabilities">
      <VulnerabilityItem
        v-for="(vulnerability, index) in file.vulnerabilities"
        :key="index"
        :vulnerability="vulnerability"
      />
    </div>
  </div>
</template>

<script>
import VulnerabilityItem from './VulnerabilityItem.vue';

export default {
  name: 'FileItem',
  components: {
    VulnerabilityItem
  },
  props: {
    file: {
      type: Object,
      required: true
    }
  },
  methods: {
    openFileInEditor() {
      // Try to open file in default editor using various protocols
      const filePath = this.file.file;

      // For other editors, we'll use a custom protocol or fallback
      const editorUrls = [
        `vscode://file/${filePath}`,
        `subl://open?url=file://${filePath}`,
        `atom://core/open/file?filename=${filePath}`,
        `phpstorm://open?file=${filePath}`
      ];

      // Try to open with the first available protocol
      let opened = false;

      for (const url of editorUrls) {
        try {
          window.open(url, '_blank');
          opened = true;
          break;
        } catch (error) {
          console.log(`Failed to open with ${url}:`, error);
        }
      }

      if (!opened) {
        // Fallback: copy file path to clipboard
        this.copyToClipboard(filePath);
        this.showNotification('File path copied to clipboard: ' + filePath);
      }
    },

    copyToClipboard(text) {
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
      } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
          document.execCommand('copy');
        } catch (err) {
          console.error('Failed to copy text: ', err);
        }
        document.body.removeChild(textArea);
      }
    },

    showNotification(message) {
      // Simple notification - you can enhance this
      const notification = document.createElement('div');
      notification.textContent = message;
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4ade80;
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        z-index: 10000;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      `;

      document.body.appendChild(notification);

      setTimeout(() => {
        notification.remove();
      }, 3000);
    }
  }
};
</script>

<style scoped>
.wpqss-file {
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 8px;
}

.wpqss-file:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.wpqss-file-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 12px 16px;
  border-bottom: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px 8px 0 0;
}

.wpqss-file-header:hover {
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.wpqss-file-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.wpqss-file-icon {
  font-size: 16px;
  opacity: 0.7;
}

.wpqss-file-path {
  font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
  font-size: 13px;
  color: #475569;
  font-weight: 500;
  flex: 1;
}

.wpqss-file-count {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.wpqss-open-btn {
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.6;
}

.wpqss-open-btn:hover {
  background: rgba(102, 126, 234, 0.1);
  opacity: 1;
  transform: scale(1.1);
}

.wpqss-open-icon {
  font-size: 14px;
}

.wpqss-vulnerabilities {
  background: #fff;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-file-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .wpqss-file-info {
    width: 100%;
  }

  .wpqss-file-path {
    font-size: 12px;
    word-break: break-all;
  }
}
</style>
