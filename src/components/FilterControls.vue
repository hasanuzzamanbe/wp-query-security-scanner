<template>
  <div class="wpqss-results-header">
    <div class="wpqss-results-title">
      <h3>Scan Results</h3>
      <span class="wpqss-results-count">{{ resultsCountText }}</span>
    </div>
    
    <div class="wpqss-results-controls">
      <div class="wpqss-filter-group">
        <label>Filter by Severity:</label>
        <div class="wpqss-severity-filters">
          <button 
            v-for="(count, severity) in severityCounts" 
            :key="severity"
            @click="$emit('apply-filter', severity)"
            :class="[
              'wpqss-filter-btn',
              { 'active': currentFilter === severity },
              severity !== 'all' ? 'wpqss-filter-' + severity : ''
            ]"
          >
            {{ formatSeverityName(severity) }}
            <span class="wpqss-filter-count">{{ count }}</span>
          </button>
        </div>
      </div>
      
      <div class="wpqss-export-group">
        <label>Export:</label>
        <div class="wpqss-export-controls">
          <select 
            :value="exportFormat"
            @change="$emit('update-export-format', $event.target.value)"
            class="wpqss-select-compact"
          >
            <option value="json">JSON</option>
            <option value="csv">CSV</option>
            <option value="html">HTML</option>
            <option value="xml">XML</option>
          </select>
          <button 
            @click="$emit('export-all')"
            :disabled="!hasResults"
            class="wpqss-btn wpqss-btn-outline"
          >
            <span class="dashicons dashicons-download"></span>
            All Results
          </button>
          <button 
            @click="$emit('export-filtered')"
            :disabled="!hasResults"
            class="wpqss-btn wpqss-btn-outline"
          >
            <span class="dashicons dashicons-filter"></span>
            Filtered Results
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FilterControls',
  props: {
    currentFilter: {
      type: String,
      default: 'all'
    },
    severityCounts: {
      type: Object,
      default: () => ({})
    },
    resultsCountText: {
      type: String,
      default: ''
    },
    exportFormat: {
      type: String,
      default: 'json'
    },
    hasResults: {
      type: Boolean,
      default: false
    }
  },
  emits: [
    'apply-filter',
    'update-export-format',
    'export-all',
    'export-filtered'
  ],
  methods: {
    formatSeverityName(severity) {
      return severity.charAt(0).toUpperCase() + severity.slice(1);
    }
  }
};
</script>

<style scoped>
.wpqss-results-header {
  background: #f8f9fa;
  padding: 20px 24px;
  border-bottom: 1px solid #e9ecef;
}

.wpqss-results-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.wpqss-results-title h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 18px;
  font-weight: 600;
}

.wpqss-results-count {
  background: #667eea;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.wpqss-results-controls {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
  flex-wrap: wrap;
}

.wpqss-filter-group,
.wpqss-export-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.wpqss-filter-group label,
.wpqss-export-group label {
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.wpqss-severity-filters {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.wpqss-filter-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border: 1px solid #dee2e6;
  border-radius: 20px;
  background: white;
  color: #6c757d;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.wpqss-filter-btn:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-1px);
}

.wpqss-filter-btn.active {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.wpqss-filter-critical.active {
  background: #dc3545;
  border-color: #dc3545;
}

.wpqss-filter-high.active {
  background: #fd7e14;
  border-color: #fd7e14;
}

.wpqss-filter-medium.active {
  background: #ffc107;
  border-color: #ffc107;
  color: #212529;
}

.wpqss-filter-low.active {
  background: #28a745;
  border-color: #28a745;
}

.wpqss-filter-count {
  background: rgba(255, 255, 255, 0.2);
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 10px;
  font-weight: 600;
}

.wpqss-filter-btn.active .wpqss-filter-count {
  background: rgba(255, 255, 255, 0.3);
}

.wpqss-export-controls {
  display: flex;
  gap: 8px;
  align-items: center;
}

.wpqss-select-compact {
  padding: 8px 12px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  background: white;
  color: #333;
  font-size: 14px;
  min-width: 100px;
}

.wpqss-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1px solid #667eea;
  border-radius: 6px;
  background: white;
  color: #667eea;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.wpqss-btn:disabled {
  cursor: not-allowed;
}

.wpqss-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: translateY(-1px);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-results-controls {
    flex-direction: column;
    gap: 16px;
  }
  
  .wpqss-severity-filters {
    justify-content: center;
  }
  
  .wpqss-export-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .wpqss-select-compact {
    min-width: auto;
    width: 100%;
  }
}
</style>
