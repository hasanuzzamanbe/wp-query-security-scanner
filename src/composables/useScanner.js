import { useNotifications } from './useNotifications';
import { makeAjaxRequest } from '../utils/api';

export function useScanner(state) {
  const { showNotice } = useNotifications();
  
  const config = {
    progressUpdateInterval: 1000,
    maxProgressChecks: 300
  };

  // Start scanning plugins or themes
  const startScan = async (type) => {
    if (state.isScanning) return;

    state.isScanning = true;
    state.progressChecks = 0;
    showProgress();
    clearResults();
    startProgressMonitoring();

    try {
      const response = await makeAjaxRequest(`wpqss_scan_${type}`, {
        scan_type: type
      });

      if (response.success && response.data) {
        handleScanSuccess(response.data);
        showNotice('Scan completed successfully', 'success');
      } else {
        showNotice(response.data || 'Scan failed', 'error');
      }
    } catch (error) {
      showNotice('Scan error: ' + error.message, 'error');
    } finally {
      handleScanComplete();
    }
  };

  // Start scanning specific component
  const startSpecificScan = async (type, component) => {
    if (state.isScanning || !component) return;

    state.isScanning = true;
    state.progressChecks = 0;
    showProgress();
    clearResults();
    startProgressMonitoring();

    const action = type === 'themes' ? 'wpqss_scan_specific_theme' : 'wpqss_scan_specific_plugin';
    const dataKey = type === 'themes' ? 'theme_folder' : 'plugin_folder';

    try {
      const response = await makeAjaxRequest(action, {
        [dataKey]: component
      });

      if (response.success && response.data) {
        handleScanSuccess(response.data);
        showNotice('Specific scan completed successfully', 'success');
      } else {
        showNotice(response.data || 'Specific scan failed', 'error');
      }
    } catch (error) {
      showNotice('Specific scan error: ' + error.message, 'error');
    } finally {
      handleScanComplete();
    }
  };

  // Load available components
  const loadAvailableComponents = async () => {
    console.log('Loading available components for type:', state.selectedComponentType);
    
    try {
      const response = await makeAjaxRequest('wpqss_get_available_components', {
        type: state.selectedComponentType
      });

      console.log('Components response:', response);
      
      if (response.success && response.data) {
        state.availableComponents = response.data;
        state.selectedComponent = '';
        console.log('Available components loaded:', state.availableComponents);
      } else {
        console.error('Failed to load components:', response);
        state.availableComponents = {};
      }
    } catch (error) {
      console.error('Failed to load components:', error);
      state.availableComponents = {};
      showNotice('Failed to load components: ' + error.message, 'error');
    }
  };

  // Handle successful scan
  const handleScanSuccess = (results) => {
    state.currentScanResults = results;
    state.filteredResults = results;
    state.currentFilter = 'all';
  };

  // Handle scan completion
  const handleScanComplete = () => {
    state.isScanning = false;
    stopProgressMonitoring();
    hideProgress();
  };

  // Show progress indicator
  const showProgress = () => {
    state.progress.visible = true;
    state.progress.percentage = 0;
    state.progress.message = 'Initializing scan...';
  };

  // Hide progress indicator
  const hideProgress = () => {
    state.progress.visible = false;
  };

  // Clear scan results
  const clearResults = () => {
    state.currentScanResults = null;
    state.filteredResults = null;
    state.currentFilter = 'all';
  };

  // Start progress monitoring
  const startProgressMonitoring = () => {
    state.progressTimer = setInterval(async () => {
      await checkProgress();
    }, config.progressUpdateInterval);
  };

  // Stop progress monitoring
  const stopProgressMonitoring = () => {
    if (state.progressTimer) {
      clearInterval(state.progressTimer);
      state.progressTimer = null;
    }
  };

  // Check scan progress
  const checkProgress = async () => {
    state.progressChecks++;

    if (state.progressChecks > config.maxProgressChecks) {
      stopProgressMonitoring();
      return;
    }

    try {
      const response = await makeAjaxRequest('wpqss_get_scan_progress');
      if (response.success && response.data) {
        updateProgress(response.data);
      }
    } catch (error) {
      console.error('Progress check failed:', error);
    }
  };

  // Update progress display
  const updateProgress = (progressData) => {
    state.progress.percentage = progressData.progress || 0;
    state.progress.message = progressData.message || 'Scanning...';

    if (progressData.status === 'complete') {
      stopProgressMonitoring();
    }
  };

  return {
    startScan,
    startSpecificScan,
    loadAvailableComponents,
    stopProgressMonitoring,
    clearResults
  };
}
