<template>
  <div class="wpqss-debug-panel">
    <div class="wpqss-debug-header">
      <h4>🔧 Development Debug Panel</h4>
      <button @click="isExpanded = !isExpanded" class="wpqss-debug-toggle">
        {{ isExpanded ? 'Hide' : 'Show' }} Details
      </button>
    </div>
    
    <div v-if="isExpanded" class="wpqss-debug-content">
      <div class="wpqss-debug-section">
        <h5>Application State</h5>
        <div class="wpqss-debug-grid">
          <div class="wpqss-debug-item">
            <label>Scanning:</label>
            <span :class="state.isScanning ? 'status-active' : 'status-inactive'">
              {{ state.isScanning ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <div class="wpqss-debug-item">
            <label>Selected Type:</label>
            <span>{{ state.selectedComponentType }}</span>
          </div>
          <div class="wpqss-debug-item">
            <label>Available Components:</label>
            <span>{{ Object.keys(state.availableComponents).length }}</span>
          </div>
          <div class="wpqss-debug-item">
            <label>Selected Component:</label>
            <span>{{ state.selectedComponent || 'None' }}</span>
          </div>
          <div class="wpqss-debug-item">
            <label>Current Filter:</label>
            <span>{{ state.currentFilter }}</span>
          </div>
          <div class="wpqss-debug-item">
            <label>Export Format:</label>
            <span>{{ state.exportFormat.toUpperCase() }}</span>
          </div>
        </div>
      </div>
      
      <div class="wpqss-debug-section">
        <h5>Results Summary</h5>
        <div class="wpqss-debug-grid">
          <div class="wpqss-debug-item">
            <label>Total Results:</label>
            <span>{{ state.currentScanResults ? state.currentScanResults.length : 0 }}</span>
          </div>
          <div class="wpqss-debug-item">
            <label>Filtered Results:</label>
            <span>{{ state.filteredResults ? state.filteredResults.length : 0 }}</span>
          </div>
        </div>
      </div>
      
      <div class="wpqss-debug-section">
        <h5>Severity Counts</h5>
        <div class="wpqss-debug-grid">
          <div 
            v-for="(count, severity) in severityCounts" 
            :key="severity"
            class="wpqss-debug-item"
          >
            <label>{{ severity.charAt(0).toUpperCase() + severity.slice(1) }}:</label>
            <span :class="'severity-' + severity">{{ count }}</span>
          </div>
        </div>
      </div>
      
      <div class="wpqss-debug-section">
        <h5>Progress Status</h5>
        <div class="wpqss-debug-grid">
          <div class="wpqss-debug-item">
            <label>Visible:</label>
            <span :class="state.progress.visible ? 'status-active' : 'status-inactive'">
              {{ state.progress.visible ? 'Yes' : 'No' }}
            </span>
          </div>
          <div class="wpqss-debug-item">
            <label>Percentage:</label>
            <span>{{ state.progress.percentage }}%</span>
          </div>
          <div class="wpqss-debug-item">
            <label>Message:</label>
            <span>{{ state.progress.message }}</span>
          </div>
        </div>
      </div>
      
      <div class="wpqss-debug-actions">
        <button @click="$emit('reload-components')" class="wpqss-debug-btn">
          🔄 Reload Components
        </button>
        <button @click="logState" class="wpqss-debug-btn">
          📝 Log State to Console
        </button>
        <button @click="clearState" class="wpqss-debug-btn">
          🗑️ Clear Results
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'DebugPanel',
  props: {
    state: {
      type: Object,
      required: true
    },
    severityCounts: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['reload-components', 'clear-state'],
  setup(props, { emit }) {
    const isExpanded = ref(false);

    const logState = () => {
      console.group('🔧 WPQSS Debug State');
      console.log('Full State:', props.state);
      console.log('Severity Counts:', props.severityCounts);
      console.log('Available Components:', props.state.availableComponents);
      console.groupEnd();
    };

    const clearState = () => {
      console.log('🗑️ Requesting state clear...');
      emit('clear-state');
    };
    
    return {
      isExpanded,
      logState,
      clearState
    };
  }
};
</script>

<style scoped>
.wpqss-debug-panel {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: 2px solid #5a67d8;
  border-radius: 8px;
  margin-bottom: 20px;
  color: white;
  font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
  font-size: 12px;
}

.wpqss-debug-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.wpqss-debug-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
}

.wpqss-debug-toggle {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.wpqss-debug-toggle:hover {
  background: rgba(255, 255, 255, 0.3);
}

.wpqss-debug-content {
  padding: 16px;
}

.wpqss-debug-section {
  margin-bottom: 16px;
}

.wpqss-debug-section h5 {
  margin: 0 0 8px 0;
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding-bottom: 4px;
}

.wpqss-debug-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 8px;
}

.wpqss-debug-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 8px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.wpqss-debug-item label {
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.wpqss-debug-item span {
  color: white;
  font-weight: 500;
}

.status-active {
  color: #4ade80 !important;
  font-weight: bold;
}

.status-inactive {
  color: #f87171 !important;
}

.severity-critical {
  color: #fca5a5 !important;
  font-weight: bold;
}

.severity-high {
  color: #fdba74 !important;
  font-weight: bold;
}

.severity-medium {
  color: #fde047 !important;
  font-weight: bold;
}

.severity-low {
  color: #86efac !important;
  font-weight: bold;
}

.wpqss-debug-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.wpqss-debug-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.wpqss-debug-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-debug-grid {
    grid-template-columns: 1fr;
  }
  
  .wpqss-debug-header {
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
  }
  
  .wpqss-debug-actions {
    flex-direction: column;
  }
}
</style>
