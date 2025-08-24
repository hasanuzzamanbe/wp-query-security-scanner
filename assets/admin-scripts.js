/**
 * WP Query Security Scanner - Admin Scripts
 */

(function($) {
    'use strict';

    const WPQSS = {
        
        // Configuration
        config: {
            progressUpdateInterval: 1000,
            maxProgressChecks: 300 // 5 minutes max
        },
        
        // State
        state: {
            isScanning: false,
            progressTimer: null,
            progressChecks: 0,
            currentScanResults: null
        },
        
        // Initialize
        init: function() {
            this.bindEvents();
            this.initializeTooltips();
        },
        
        // Bind event handlers
        bindEvents: function() {
            $('#wpqss-scan-plugins').on('click', this.scanPlugins.bind(this));
            $('#wpqss-scan-themes').on('click', this.scanThemes.bind(this));
            $('#wpqss-scan-specific').on('click', this.scanSpecific.bind(this));
            $('#wpqss-export-report').on('click', this.exportReport.bind(this));

            // Component type change
            $('#wpqss-component-type').on('change', this.loadAvailableComponents.bind(this));

            // Component selection change
            $('#wpqss-component-select').on('change', this.updateScanSpecificButton.bind(this));

            // Component toggle functionality
            $(document).on('click', '.wpqss-component-header', this.toggleComponent.bind(this));

            // Export format change
            $('#wpqss-export-format').on('change', this.updateExportButton.bind(this));

            // Load initial components
            this.loadAvailableComponents();
        },
        
        // Initialize tooltips
        initializeTooltips: function() {
            if (typeof $.fn.tooltip === 'function') {
                $('[data-tooltip]').tooltip();
            }
        },
        
        // Scan plugins
        scanPlugins: function(e) {
            e.preventDefault();
            this.startScan('plugins');
        },
        
        // Scan themes
        scanThemes: function(e) {
            e.preventDefault();
            this.startScan('themes');
        },

        // Scan specific component
        scanSpecific: function(e) {
            e.preventDefault();

            const type = $('#wpqss-component-type').val();
            const component = $('#wpqss-component-select').val();

            if (!component) {
                this.showErrorMessage('Please select a component to scan');
                return;
            }

            this.startSpecificScan(type, component);
        },
        
        // Start scanning process
        startScan: function(type) {
            if (this.state.isScanning) {
                return;
            }
            
            this.state.isScanning = true;
            this.state.progressChecks = 0;
            
            // Update UI
            this.showProgress();
            this.disableScanButtons();
            this.clearResults();
            
            // Start progress monitoring
            this.startProgressMonitoring();
            
            // Make AJAX request
            $.ajax({
                url: wpqss_ajax.url,
                type: 'POST',
                data: {
                    action: 'wpqss_scan_' + type,
                    nonce: wpqss_ajax.nonce,
                    scan_type: type
                },
                success: this.handleScanSuccess.bind(this),
                error: this.handleScanError.bind(this),
                complete: this.handleScanComplete.bind(this)
            });
        },

        // Start specific component scan
        startSpecificScan: function(type, component) {
            if (this.state.isScanning) {
                return;
            }

            this.state.isScanning = true;
            this.state.progressChecks = 0;

            // Update UI
            this.showProgress();
            this.disableScanButtons();
            this.clearResults();

            // Start progress monitoring
            this.startProgressMonitoring();

            const action = type === 'themes' ? 'wpqss_scan_specific_theme' : 'wpqss_scan_specific_plugin';
            const dataKey = type === 'themes' ? 'theme_folder' : 'plugin_folder';

            // Make AJAX request
            $.ajax({
                url: wpqss_ajax.url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: wpqss_ajax.nonce,
                    [dataKey]: component
                },
                success: this.handleScanSuccess.bind(this),
                error: this.handleScanError.bind(this),
                complete: this.handleScanComplete.bind(this)
            });
        },

        // Load available components
        loadAvailableComponents: function() {
            const type = $('#wpqss-component-type').val();
            const $select = $('#wpqss-component-select');

            $select.prop('disabled', true).html('<option value="">Loading...</option>');
            $('#wpqss-scan-specific').prop('disabled', true);

            $.ajax({
                url: wpqss_ajax.url,
                type: 'POST',
                data: {
                    action: 'wpqss_get_available_components',
                    nonce: wpqss_ajax.nonce,
                    type: type
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.populateComponentSelect(response.data);
                    } else {
                        $select.html('<option value="">Error loading components</option>');
                    }
                },
                error: () => {
                    $select.html('<option value="">Error loading components</option>');
                }
            });
        },

        // Populate component select dropdown
        populateComponentSelect: function(components) {
            const $select = $('#wpqss-component-select');

            $select.empty().append('<option value="">Select a component...</option>');

            Object.keys(components).forEach(key => {
                const component = components[key];
                $select.append(`<option value="${key}">${component.name} (v${component.version})</option>`);
            });

            $select.prop('disabled', false);
        },

        // Update scan specific button state
        updateScanSpecificButton: function() {
            const hasSelection = $('#wpqss-component-select').val() !== '';
            $('#wpqss-scan-specific').prop('disabled', !hasSelection);
        },
        
        // Handle successful scan
        handleScanSuccess: function(response) {
            if (response.success && response.data) {
                this.state.currentScanResults = response.data;
                this.displayResults(response.data);
                this.showSuccessMessage(wpqss_ajax.strings.scan_complete);
            } else {
                this.showErrorMessage(response.data || wpqss_ajax.strings.scan_error);
            }
        },
        
        // Handle scan error
        handleScanError: function(xhr, status, error) {
            console.error('Scan error:', error);
            this.showErrorMessage(wpqss_ajax.strings.scan_error + ': ' + error);
        },
        
        // Handle scan completion
        handleScanComplete: function() {
            this.state.isScanning = false;
            this.stopProgressMonitoring();
            this.hideProgress();
            this.enableScanButtons();
        },
        
        // Start progress monitoring
        startProgressMonitoring: function() {
            this.state.progressTimer = setInterval(() => {
                this.checkProgress();
            }, this.config.progressUpdateInterval);
        },
        
        // Stop progress monitoring
        stopProgressMonitoring: function() {
            if (this.state.progressTimer) {
                clearInterval(this.state.progressTimer);
                this.state.progressTimer = null;
            }
        },
        
        // Check scan progress
        checkProgress: function() {
            this.state.progressChecks++;
            
            if (this.state.progressChecks > this.config.maxProgressChecks) {
                this.stopProgressMonitoring();
                return;
            }
            
            $.ajax({
                url: wpqss_ajax.url,
                type: 'POST',
                data: {
                    action: 'wpqss_get_scan_progress',
                    nonce: wpqss_ajax.nonce
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.updateProgress(response.data);
                    }
                }
            });
        },
        
        // Update progress display
        updateProgress: function(progressData) {
            const progress = progressData.progress || 0;
            const message = progressData.message || wpqss_ajax.strings.scanning;
            
            $('.wpqss-progress-fill').css('width', progress + '%');
            $('.wpqss-progress-text').text(message + ' (' + progress + '%)');
            
            if (progressData.status === 'complete') {
                this.stopProgressMonitoring();
            }
        },
        
        // Display scan results
        displayResults: function(results) {
            const $container = $('#wpqss-results');
            
            if (!results || results.length === 0) {
                this.showEmptyState($container);
                return;
            }
            
            const summary = this.calculateSummary(results);
            const html = this.buildResultsHTML(results, summary);
            
            $container.html(html);
            this.enableExportButton();
        },
        
        // Calculate summary statistics
        calculateSummary: function(results) {
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
        },
        
        // Build results HTML
        buildResultsHTML: function(results, summary) {
            let html = '<div class="wpqss-results-header">';
            html += '<h2>Scan Results</h2>';
            html += '<div class="wpqss-export-controls">';
            html += '<select id="wpqss-export-format">';
            html += '<option value="json">JSON</option>';
            html += '<option value="csv">CSV</option>';
            html += '<option value="html">HTML</option>';
            html += '<option value="xml">XML</option>';
            html += '</select>';
            html += '<button id="wpqss-export-report" class="button">Export Report</button>';
            html += '</div>';
            html += '</div>';
            
            // Summary cards
            html += '<div class="wpqss-summary">';
            html += this.buildSummaryCard('Components', summary.totalComponents, 'total');
            html += this.buildSummaryCard('Vulnerabilities', summary.totalVulnerabilities, 'total');
            html += this.buildSummaryCard('Critical', summary.severityCounts.critical, 'critical');
            html += this.buildSummaryCard('High', summary.severityCounts.high, 'high');
            html += this.buildSummaryCard('Medium', summary.severityCounts.medium, 'medium');
            html += this.buildSummaryCard('Low', summary.severityCounts.low, 'low');
            html += '</div>';
            
            // Components
            results.forEach(component => {
                html += this.buildComponentHTML(component);
            });
            
            return html;
        },
        
        // Build summary card HTML
        buildSummaryCard: function(title, count, type) {
            return `
                <div class="wpqss-summary-card ${type}">
                    <h3>${title}</h3>
                    <p class="count">${count}</p>
                </div>
            `;
        },
        
        // Build component HTML
        buildComponentHTML: function(component) {
            let html = `<div class="wpqss-component collapsed" data-component="${component.name}">`;
            
            // Component header
            html += '<div class="wpqss-component-header">';
            html += `<h3>${this.escapeHtml(component.name)}</h3>`;
            html += '<div class="wpqss-component-meta">';
            html += `<span>${component.total_vulnerabilities} vulnerabilities</span>`;
            
            // Severity badges
            Object.keys(component.severity_counts).forEach(severity => {
                const count = component.severity_counts[severity];
                if (count > 0) {
                    html += `<span class="wpqss-severity-badge ${severity}">${count} ${severity}</span>`;
                }
            });
            
            html += '<span class="wpqss-toggle-icon dashicons dashicons-arrow-down"></span>';
            html += '</div>';
            html += '</div>';
            
            // Component content
            html += '<div class="wpqss-component-content">';
            component.files.forEach(file => {
                html += this.buildFileHTML(file);
            });
            html += '</div>';
            
            html += '</div>';
            return html;
        },
        
        // Build file HTML
        buildFileHTML: function(file) {
            let html = '<div class="wpqss-file">';
            html += `<div class="wpqss-file-header">${this.escapeHtml(file.file)}</div>`;
            
            file.vulnerabilities.forEach(vuln => {
                html += this.buildVulnerabilityHTML(vuln);
            });
            
            html += '</div>';
            return html;
        },
        
        // Build vulnerability HTML
        buildVulnerabilityHTML: function(vuln) {
            let html = `<div class="wpqss-vulnerability ${vuln.severity}">`;
            
            // Header
            html += '<div class="wpqss-vulnerability-header">';
            html += `<h4 class="wpqss-vulnerability-title">${this.escapeHtml(vuln.type)}</h4>`;
            html += `<span class="wpqss-vulnerability-line">Line ${vuln.line}</span>`;
            html += '</div>';
            
            // Description
            html += `<div class="wpqss-vulnerability-description">${this.escapeHtml(vuln.description)}</div>`;
            
            // Code
            html += '<div class="wpqss-code-block">';
            html += this.escapeHtml(vuln.code);
            html += '</div>';
            
            // Context
            if (vuln.context && vuln.context.length > 0) {
                html += '<div class="wpqss-code-context">';
                vuln.context.forEach(line => {
                    const className = line.is_vulnerable ? 'vulnerable' : '';
                    html += `<div class="wpqss-context-line ${className}">`;
                    html += `<span class="wpqss-context-line-number">${line.line_number}</span>`;
                    html += this.escapeHtml(line.code);
                    html += '</div>';
                });
                html += '</div>';
            }
            
            // Remediation
            html += '<div class="wpqss-remediation">';
            html += '<div class="wpqss-remediation-title">Remediation:</div>';
            html += `<div class="wpqss-remediation-text">${this.escapeHtml(vuln.remediation)}</div>`;
            html += '</div>';
            
            html += '</div>';
            return html;
        },
        
        // Toggle component visibility
        toggleComponent: function(e) {
            const $component = $(e.currentTarget).closest('.wpqss-component');
            $component.toggleClass('collapsed expanded');
        },
        
        // Export report
        exportReport: function(e) {
            e.preventDefault();
            
            if (!this.state.currentScanResults) {
                this.showErrorMessage('No scan results to export');
                return;
            }
            
            const format = $('#wpqss-export-format').val() || 'json';
            
            $.ajax({
                url: wpqss_ajax.url,
                type: 'POST',
                data: {
                    action: 'wpqss_export_report',
                    nonce: wpqss_ajax.nonce,
                    format: format,
                    scan_results: JSON.stringify(this.state.currentScanResults)
                },
                success: (response) => {
                    if (response.success && response.data.download_url) {
                        window.location.href = response.data.download_url;
                        this.showSuccessMessage(wpqss_ajax.strings.export_success);
                    } else {
                        this.showErrorMessage(response.data || wpqss_ajax.strings.export_error);
                    }
                },
                error: () => {
                    this.showErrorMessage(wpqss_ajax.strings.export_error);
                }
            });
        },
        
        // UI Helper Methods
        showProgress: function() {
            $('#wpqss-progress').show();
            $('.wpqss-progress-fill').css('width', '0%');
            $('.wpqss-progress-text').text(wpqss_ajax.strings.scanning + '...');
        },
        
        hideProgress: function() {
            $('#wpqss-progress').hide();
        },
        
        disableScanButtons: function() {
            $('#wpqss-scan-plugins, #wpqss-scan-themes, #wpqss-scan-specific').prop('disabled', true);
        },

        enableScanButtons: function() {
            $('#wpqss-scan-plugins, #wpqss-scan-themes').prop('disabled', false);
            this.updateScanSpecificButton(); // Re-enable specific scan if component is selected
        },
        
        enableExportButton: function() {
            $('#wpqss-export-report').prop('disabled', false);
        },
        
        clearResults: function() {
            $('#wpqss-results').empty();
            $('#wpqss-export-report').prop('disabled', true);
        },
        
        showEmptyState: function($container) {
            $container.html(`
                <div class="wpqss-empty-state">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h3>No Vulnerabilities Found</h3>
                    <p>Great! No security issues were detected in the scanned files.</p>
                </div>
            `);
        },
        
        showSuccessMessage: function(message) {
            this.showNotice(message, 'success');
        },
        
        showErrorMessage: function(message) {
            this.showNotice(message, 'error');
        },
        
        showNotice: function(message, type) {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${this.escapeHtml(message)}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);
            
            $('.wpqss-admin').prepend($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);
        },
        
        updateExportButton: function() {
            const format = $('#wpqss-export-format').val();
            $('#wpqss-export-report').text(`Export ${format.toUpperCase()}`);
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        WPQSS.init();
    });
    
})(jQuery);
