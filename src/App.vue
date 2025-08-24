<template>
  <div class="wpqss-app">
    <!-- Debug Panel (development only) -->
    <DebugPanel
      v-if="isDevelopment"
      :state="state"
      :severity-counts="severityCounts"
      @reload-components="loadAvailableComponents"
      @clear-state="clearState"
    />
    
    <!-- Scan Controls -->
    <ScanControls
      :is-scanning="state.isScanning"
      :selected-component-type="state.selectedComponentType"
      :selected-component="state.selectedComponent"
      :available-components="state.availableComponents"
      :can-scan-specific="canScanSpecific"
      @scan-plugins="startScan('plugins')"
      @scan-themes="startScan('themes')"
      @scan-specific="startSpecificScan"
      @update-component-type="updateComponentType"
      @update-component="updateSelectedComponent"
    />
    
    <!-- Progress Indicator -->
    <ProgressIndicator
      :visible="state.progress.visible"
      :percentage="state.progress.percentage"
      :message="state.progress.message"
    />
    
    <!-- Results Container -->
    <div v-if="hasResults" class="wpqss-results-container">
      <!-- Filter Controls -->
      <FilterControls
        :current-filter="state.currentFilter"
        :severity-counts="severityCounts"
        :results-count-text="resultsCountText"
        :export-format="state.exportFormat"
        :has-results="hasResults"
        @apply-filter="applyFilter"
        @update-export-format="updateExportFormat"
        @export-all="exportAllResults"
        @export-filtered="exportFilteredResults"
      />
      
      <!-- Results Display -->
      <ResultsDisplay
        :results="state.filteredResults || []"
        :has-results="hasFilteredResults"
      />
    </div>

    <!-- Help Section -->
    <HelpSection />
  </div>
</template>

<script>
import { reactive, computed, onMounted, watch } from 'vue';
import ScanControls from './components/ScanControls.vue';
import ProgressIndicator from './components/ProgressIndicator.vue';
import FilterControls from './components/FilterControls.vue';
import ResultsDisplay from './components/ResultsDisplay.vue';
import HelpSection from './components/HelpSection.vue';
import DebugPanel from './components/DebugPanel.vue';
import { useScanner } from './composables/useScanner';
import { useFiltering } from './composables/useFiltering';
import { useExport } from './composables/useExport';

export default {
  name: 'WPQSSApp',
  components: {
    ScanControls,
    ProgressIndicator,
    FilterControls,
    ResultsDisplay,
    HelpSection,
    DebugPanel
  },
  setup() {
    // Reactive state
    const state = reactive({
      isScanning: false,
      progressTimer: null,
      progressChecks: 0,
      currentScanResults: null,
      filteredResults: null,
      currentFilter: 'all',
      availableComponents: {},
      selectedComponentType: 'plugins',
      selectedComponent: '',
      exportFormat: 'html',
      progress: {
        visible: false,
        percentage: 0,
        message: 'Initializing scan...'
      }
    });

    // Composables
    const { 
      startScan, 
      startSpecificScan, 
      loadAvailableComponents,
      stopProgressMonitoring 
    } = useScanner(state);
    
    const { 
      applyFilter, 
      severityCounts, 
      resultsCountText 
    } = useFiltering(state);
    
    const { 
      exportResults 
    } = useExport(state);

    // Computed properties
    const canScanSpecific = computed(() => {
      return state.selectedComponent !== '';
    });

    const hasResults = computed(() => {
      return state.currentScanResults && state.currentScanResults.length > 0;
    });

    const hasFilteredResults = computed(() => {
      return state.filteredResults && state.filteredResults.length > 0;
    });

    const isDevelopment = computed(() => {
      return process.env.NODE_ENV === 'development';
    });

    // Methods
    const updateComponentType = (type) => {
      state.selectedComponentType = type;
      state.selectedComponent = '';
      loadAvailableComponents();
    };

    const updateSelectedComponent = (component) => {
      state.selectedComponent = component;
    };

    const updateExportFormat = (format) => {
      state.exportFormat = format;
    };

    const exportAllResults = () => {
      exportResults(state.currentScanResults, 'all');
    };

    const exportFilteredResults = () => {
      exportResults(state.filteredResults, state.currentFilter);
    };

    const clearState = () => {
      state.currentScanResults = null;
      state.filteredResults = null;
      state.selectedComponent = '';
      state.currentFilter = 'all';
      console.log('🗑️ State cleared');
    };

    // Watchers
    watch(() => state.selectedComponentType, () => {
      loadAvailableComponents();
    });

    // Lifecycle
    onMounted(() => {
      console.log('App mounted, loading components...');
      loadAvailableComponents();
    });

    // Cleanup on unmount
    const cleanup = () => {
      stopProgressMonitoring();
    };

    return {
      state,
      severityCounts,
      resultsCountText,
      canScanSpecific,
      hasResults,
      hasFilteredResults,
      isDevelopment,
      startScan,
      startSpecificScan,
      loadAvailableComponents,
      applyFilter,
      updateComponentType,
      updateSelectedComponent,
      updateExportFormat,
      exportAllResults,
      exportFilteredResults,
      clearState,
      cleanup
    };
  },
  beforeUnmount() {
    this.cleanup();
  }
};
</script>

<style scoped>
.wpqss-app {
  max-width: 1200px;
  margin: 0 auto;
}

.wpqss-results-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 24px;
}
</style>
