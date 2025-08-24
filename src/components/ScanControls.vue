<template>
  <div class="wpqss-scan-controls-compact">
    <div class="wpqss-scan-header">
      <div class="wpqss-scan-title">
        <h2>Security Scanner</h2>
        <p>Detect vulnerabilities in plugins and themes</p>
      </div>
      <div class="wpqss-scan-actions">
        <button 
          @click="$emit('scan-plugins')"
          :disabled="isScanning"
          class="wpqss-btn wpqss-btn-primary"
        >
          <span class="dashicons dashicons-admin-plugins"></span>
          Scan All Plugins
        </button>
        <button 
          @click="$emit('scan-themes')"
          :disabled="isScanning"
          class="wpqss-btn wpqss-btn-secondary"
        >
          <span class="dashicons dashicons-admin-appearance"></span>
          Scan All Themes
        </button>
      </div>
    </div>
    
    <div class="wpqss-scan-options">
      <div class="wpqss-option-group">
        <label>Specific Component:</label>
        <div class="wpqss-inline-controls">
          <select 
            :value="selectedComponentType"
            @change="$emit('update-component-type', $event.target.value)"
            class="wpqss-select-compact"
          >
            <option value="plugins">Plugin</option>
            <option value="themes">Theme</option>
          </select>
          <select 
            :value="selectedComponent"
            @change="$emit('update-component', $event.target.value)"
            :disabled="Object.keys(availableComponents).length === 0"
            class="wpqss-select-compact"
          >
            <option value="">Select component...</option>
            <option 
              v-for="(component, key) in availableComponents" 
              :key="key" 
              :value="key"
            >
              {{ component.name }} (v{{ component.version }})
            </option>
          </select>
          <button 
            @click="handleSpecificScan"
            :disabled="!canScanSpecific || isScanning"
            class="wpqss-btn wpqss-btn-primary"
          >
            <span class="dashicons dashicons-search"></span>
            Scan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ScanControls',
  props: {
    isScanning: {
      type: Boolean,
      default: false
    },
    selectedComponentType: {
      type: String,
      default: 'plugins'
    },
    selectedComponent: {
      type: String,
      default: ''
    },
    availableComponents: {
      type: Object,
      default: () => ({})
    },
    canScanSpecific: {
      type: Boolean,
      default: false
    }
  },
  emits: [
    'scan-plugins',
    'scan-themes', 
    'scan-specific',
    'update-component-type',
    'update-component'
  ],
  methods: {
    handleSpecificScan() {
      this.$emit('scan-specific', this.selectedComponentType, this.selectedComponent);
    }
  }
};
</script>

<style scoped>
.wpqss-scan-controls-compact {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  color: white;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.wpqss-scan-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.wpqss-scan-title h2 {
  margin: 0;
  color: white;
  font-size: 24px;
  font-weight: 600;
}

.wpqss-scan-title p {
  margin: 4px 0 0 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
}

.wpqss-scan-actions {
  display: flex;
  gap: 12px;
}

.wpqss-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.wpqss-btn:disabled {
  cursor: not-allowed;
}

.wpqss-btn-primary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.wpqss-btn-primary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.wpqss-btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.wpqss-btn-secondary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
}

.wpqss-btn-outline {
  background: transparent;
  color: #667eea;
  border: 1px solid #667eea;
}

.wpqss-btn-outline:hover:not(:disabled) {
  background: #667eea;
  color: white;
}

.wpqss-scan-options {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 16px;
  backdrop-filter: blur(10px);
}

.wpqss-option-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: white;
  font-size: 14px;
}

.wpqss-inline-controls {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.wpqss-select-compact {
  padding: 8px 12px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.9);
  color: #333;
  font-size: 14px;
  min-width: 150px;
}

.wpqss-select-compact:disabled {
  background: rgba(255, 255, 255, 0.5);
  color: #666;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-scan-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .wpqss-scan-actions {
    justify-content: stretch;
  }
  
  .wpqss-btn {
    flex: 1;
    justify-content: center;
  }
  
  .wpqss-inline-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .wpqss-select-compact {
    min-width: auto;
    width: 100%;
  }
}
</style>
