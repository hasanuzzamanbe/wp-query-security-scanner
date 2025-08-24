/**
 * WP Query Security Scanner - Vue.js Application
 */

const { createApp, ref, reactive, computed, onMounted, watch } = Vue;

const WPQSSApp = {
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
            exportFormat: 'json',
            progress: {
                visible: false,
                percentage: 0,
                message: 'Initializing scan...'
            }
        });

        // Configuration
        const config = {
            progressUpdateInterval: 1000,
            maxProgressChecks: 300
        };

        // Computed properties
        const summary = computed(() => {
            if (!state.currentScanResults) return null;
            return calculateSummary(state.currentScanResults);
        });

        const filteredSummary = computed(() => {
            if (!state.filteredResults) return null;
            return calculateSummary(state.filteredResults);
        });

        const severityCounts = computed(() => {
            if (!summary.value) return { all: 0, critical: 0, high: 0, medium: 0, low: 0 };
            return {
                all: summary.value.totalVulnerabilities,
                critical: summary.value.severityCounts.critical,
                high: summary.value.severityCounts.high,
                medium: summary.value.severityCounts.medium,
                low: summary.value.severityCounts.low
            };
        });

        const resultsCount = computed(() => {
            if (!filteredSummary.value) return 0;
            return filteredSummary.value.totalVulnerabilities;
        });

        const resultsCountText = computed(() => {
            const count = resultsCount.value;
            if (state.currentFilter === 'all') {
                return `${count} vulnerabilities found`;
            }
            return `${count} ${state.currentFilter} severity vulnerabilities`;
        });

        const canScanSpecific = computed(() => {
            return state.selectedComponent !== '';
        });

        const hasResults = computed(() => {
            return state.currentScanResults && state.currentScanResults.length > 0;
        });

        // Methods
        const showNotice = (message, type = 'success') => {
            const notice = document.createElement('div');
            notice.className = `notice notice-${type} is-dismissible`;
            notice.innerHTML = `
                <p>${escapeHtml(message)}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            `;
            
            document.querySelector('.wpqss-admin').prepend(notice);
            
            setTimeout(() => {
                notice.style.opacity = '0';
                setTimeout(() => notice.remove(), 300);
            }, 5000);
        };

        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        const calculateSummary = (results) => {
            const summary = {
                totalComponents: results.length,
                totalVulnerabilities: 0,
                severityCounts: {
                    critical: 0,
                    high: 0,
                    medium: 0,
                    low: 0
                }
            };

            results.forEach(component => {
                summary.totalVulnerabilities += component.total_vulnerabilities;
                Object.keys(summary.severityCounts).forEach(severity => {
                    summary.severityCounts[severity] += component.severity_counts[severity] || 0;
                });
            });

            return summary;
        };

        const makeAjaxRequest = async (action, data = {}) => {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', wpqss_ajax.nonce);
            
            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });

            try {
                const response = await fetch(wpqss_ajax.url, {
                    method: 'POST',
                    body: formData
                });
                return await response.json();
            } catch (error) {
                console.error('AJAX request failed:', error);
                throw error;
            }
        };

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
                    showNotice(wpqss_ajax.strings.scan_complete);
                } else {
                    showNotice(response.data || wpqss_ajax.strings.scan_error, 'error');
                }
            } catch (error) {
                showNotice(wpqss_ajax.strings.scan_error + ': ' + error.message, 'error');
            } finally {
                handleScanComplete();
            }
        };

        const startSpecificScan = async (type, component) => {
            if (state.isScanning) return;

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
                    showNotice(wpqss_ajax.strings.scan_complete);
                } else {
                    showNotice(response.data || wpqss_ajax.strings.scan_error, 'error');
                }
            } catch (error) {
                showNotice(wpqss_ajax.strings.scan_error + ': ' + error.message, 'error');
            } finally {
                handleScanComplete();
            }
        };

        const handleScanSuccess = (results) => {
            state.currentScanResults = results;
            state.filteredResults = results;
            state.currentFilter = 'all';
        };

        const handleScanComplete = () => {
            state.isScanning = false;
            stopProgressMonitoring();
            hideProgress();
        };

        const showProgress = () => {
            state.progress.visible = true;
            state.progress.percentage = 0;
            state.progress.message = wpqss_ajax.strings.scanning + '...';
        };

        const hideProgress = () => {
            state.progress.visible = false;
        };

        const clearResults = () => {
            state.currentScanResults = null;
            state.filteredResults = null;
            state.currentFilter = 'all';
        };

        const startProgressMonitoring = () => {
            state.progressTimer = setInterval(async () => {
                await checkProgress();
            }, config.progressUpdateInterval);
        };

        const stopProgressMonitoring = () => {
            if (state.progressTimer) {
                clearInterval(state.progressTimer);
                state.progressTimer = null;
            }
        };

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

        const updateProgress = (progressData) => {
            state.progress.percentage = progressData.progress || 0;
            state.progress.message = progressData.message || wpqss_ajax.strings.scanning;

            if (progressData.status === 'complete') {
                stopProgressMonitoring();
            }
        };

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
                }
            } catch (error) {
                console.error('Failed to load components:', error);
                state.availableComponents = {};
            }
        };

        const applyFilter = (severity) => {
            state.currentFilter = severity;

            if (!state.currentScanResults) {
                return;
            }

            if (severity === 'all') {
                state.filteredResults = state.currentScanResults;
                return;
            }

            state.filteredResults = state.currentScanResults.map(component => {
                const filteredComponent = { ...component };
                filteredComponent.files = component.files.map(file => {
                    const filteredFile = { ...file };
                    filteredFile.vulnerabilities = file.vulnerabilities.filter(vuln => 
                        vuln.severity === severity
                    );
                    return filteredFile;
                }).filter(file => file.vulnerabilities.length > 0);

                // Recalculate counts
                filteredComponent.total_vulnerabilities = 0;
                filteredComponent.severity_counts = {
                    critical: 0, high: 0, medium: 0, low: 0
                };

                filteredComponent.files.forEach(file => {
                    file.vulnerabilities.forEach(vuln => {
                        filteredComponent.total_vulnerabilities++;
                        filteredComponent.severity_counts[vuln.severity]++;
                    });
                });

                return filteredComponent;
            }).filter(component => component.total_vulnerabilities > 0);
        };

        const exportResults = async (results, filterType) => {
            if (!results || results.length === 0) {
                showNotice('No results to export', 'error');
                return;
            }

            const filename_suffix = filterType === 'all' ? '' : `_${filterType}_severity`;

            try {
                const response = await makeAjaxRequest('wpqss_export_report', {
                    format: state.exportFormat,
                    scan_results: JSON.stringify(results),
                    filter_type: filterType,
                    filename_suffix: filename_suffix
                });

                if (response.success && response.data.download_url) {
                    window.location.href = response.data.download_url;
                    const message = filterType === 'all' 
                        ? wpqss_ajax.strings.export_success 
                        : `${filterType} severity results exported successfully`;
                    showNotice(message);
                } else {
                    showNotice(response.data || wpqss_ajax.strings.export_error, 'error');
                }
            } catch (error) {
                showNotice(wpqss_ajax.strings.export_error, 'error');
            }
        };

        // Watchers
        watch(() => state.selectedComponentType, () => {
            loadAvailableComponents();
        });

        // Lifecycle
        onMounted(() => {
            console.log('Vue app mounted, loading components...');
            loadAvailableComponents();
        });

        // Return reactive state and methods for template
        return {
            state,
            summary,
            filteredSummary,
            severityCounts,
            resultsCount,
            resultsCountText,
            canScanSpecific,
            hasResults,
            startScan,
            startSpecificScan,
            applyFilter,
            exportResults,
            loadAvailableComponents,
            escapeHtml
        };
    }
};

// Initialize Vue app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing Vue app...');
    console.log('Vue available:', typeof Vue !== 'undefined');

    if (typeof Vue === 'undefined') {
        console.error('Vue.js is not loaded!');
        return;
    }

    try {
        const app = createApp(WPQSSApp);

        // Register components if they're available
        if (typeof ScanControls !== 'undefined') {
            app.component('ScanControls', ScanControls);
            console.log('ScanControls component registered');
        }
        if (typeof ProgressIndicator !== 'undefined') {
            app.component('ProgressIndicator', ProgressIndicator);
            console.log('ProgressIndicator component registered');
        }
        if (typeof FilterControls !== 'undefined') {
            app.component('FilterControls', FilterControls);
            console.log('FilterControls component registered');
        }
        if (typeof ResultsDisplay !== 'undefined') {
            app.component('ResultsDisplay', ResultsDisplay);
            console.log('ResultsDisplay component registered');
        }
        if (typeof ComponentItem !== 'undefined') {
            app.component('ComponentItem', ComponentItem);
            console.log('ComponentItem component registered');
        }
        if (typeof FileItem !== 'undefined') {
            app.component('FileItem', FileItem);
            console.log('FileItem component registered');
        }
        if (typeof VulnerabilityItem !== 'undefined') {
            app.component('VulnerabilityItem', VulnerabilityItem);
            console.log('VulnerabilityItem component registered');
        }

        app.mount('#wpqss-vue-app');
        console.log('Vue app mounted successfully');
    } catch (error) {
        console.error('Failed to mount Vue app:', error);
    }
});
