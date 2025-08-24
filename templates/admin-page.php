<?php
/**
 * Vue.js Admin Page Template
 *
 * Modern Vue.js interface for the security scanner
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Get current scan statistics if available
$last_scan_option = get_option('wpqss_last_scan_stats', []);
$has_previous_scan = !empty($last_scan_option);
?>

<div class="wrap wpqss-admin">
    <h1>
        <?php _e('WP Query Security Scanner', 'wp-query-security-scanner'); ?>
        <span style="font-size: 14px; color: #667eea; font-weight: normal;">v<?php echo WPQSS_VERSION; ?></span>
    </h1>

    <?php if ($has_previous_scan): ?>
    <div class="notice notice-info">
        <p>
            <?php
            printf(
                __('Last scan: %s - Found %d vulnerabilities across %d components', 'wp-query-security-scanner'),
                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_scan_option['timestamp']),
                $last_scan_option['total_vulnerabilities'],
                $last_scan_option['total_components']
            );
            ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Vue.js Application Container -->
    <div id="wpqss-vue-app" v-cloak>
        <!-- Debug Info -->
        <div v-if="state" style="background: #f0f0f0; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <strong>Debug Info:</strong><br>
            Selected Type: {{ state.selectedComponentType }}<br>
            Available Components: {{ Object.keys(state.availableComponents).length }}<br>
            Selected Component: {{ state.selectedComponent }}<br>
            Can Scan Specific: {{ canScanSpecific }}<br>
            <button @click="loadAvailableComponents" class="button button-small">Reload Components</button>
        </div>

        <!-- Scan Controls (Direct Implementation) -->
        <div class="wpqss-scan-controls-compact">
            <div class="wpqss-scan-header">
                <div class="wpqss-scan-title">
                    <h2>Security Scanner</h2>
                    <p>Detect vulnerabilities in plugins and themes</p>
                </div>
                <div class="wpqss-scan-actions">
                    <button
                        @click="startScan('plugins')"
                        :disabled="state.isScanning"
                        class="wpqss-btn wpqss-btn-primary"
                    >
                        <span class="dashicons dashicons-admin-plugins"></span>
                        Scan Plugins
                    </button>
                    <button
                        @click="startScan('themes')"
                        :disabled="state.isScanning"
                        class="wpqss-btn wpqss-btn-secondary"
                    >
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Scan Themes
                    </button>
                </div>
            </div>

            <div class="wpqss-scan-options">
                <div class="wpqss-option-group">
                    <label>Specific Component:</label>
                    <div class="wpqss-inline-controls">
                        <select
                            v-model="state.selectedComponentType"
                            @change="loadAvailableComponents"
                            class="wpqss-select-compact"
                        >
                            <option value="plugins">Plugin</option>
                            <option value="themes">Theme</option>
                        </select>
                        <select
                            v-model="state.selectedComponent"
                            :disabled="Object.keys(state.availableComponents).length === 0"
                            class="wpqss-select-compact"
                        >
                            <option value="">Select component...</option>
                            <option
                                v-for="(component, key) in state.availableComponents"
                                :key="key"
                                :value="key"
                            >
                                {{ component.name }} (v{{ component.version }})
                            </option>
                        </select>
                        <button
                            @click="startSpecificScan(state.selectedComponentType, state.selectedComponent)"
                            :disabled="!canScanSpecific || state.isScanning"
                            class="wpqss-btn wpqss-btn-outline"
                        >
                            <span class="dashicons dashicons-search"></span>
                            Scan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div v-if="state.progress.visible" class="wpqss-progress">
            <h3>Scanning in Progress</h3>
            <div class="wpqss-progress-bar">
                <div
                    class="wpqss-progress-fill"
                    :style="{ width: state.progress.percentage + '%' }"
                ></div>
            </div>
            <p class="wpqss-progress-text">{{ state.progress.message }} ({{ state.progress.percentage }}%)</p>
            <p class="description">
                This may take a few minutes depending on the number of files to scan. Please do not close this page.
            </p>
        </div>

        <!-- Results Container -->
        <div v-if="hasResults || state.filteredResults" class="wpqss-results-container">
            <!-- Filter Controls -->
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
                                @click="applyFilter(severity)"
                                :class="[
                                    'wpqss-filter-btn',
                                    { 'active': state.currentFilter === severity },
                                    severity !== 'all' ? 'wpqss-filter-' + severity : ''
                                ]"
                            >
                                {{ severity.charAt(0).toUpperCase() + severity.slice(1) }}
                                <span class="wpqss-filter-count">{{ count }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="wpqss-export-group">
                        <label>Export:</label>
                        <div class="wpqss-export-controls">
                            <select
                                v-model="state.exportFormat"
                                class="wpqss-select-compact"
                            >
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                                <option value="html">HTML</option>
                                <option value="xml">XML</option>
                            </select>
                            <button
                                @click="exportResults(state.currentScanResults, 'all')"
                                :disabled="!hasResults"
                                class="wpqss-btn wpqss-btn-outline"
                            >
                                <span class="dashicons dashicons-download"></span>
                                All Results
                            </button>
                            <button
                                @click="exportResults(state.filteredResults, state.currentFilter)"
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

            <!-- Results Display -->
            <div class="wpqss-results">
                <div v-if="!hasResults || !state.filteredResults || state.filteredResults.length === 0" class="wpqss-empty-state">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h3>No Vulnerabilities Found</h3>
                    <p>Great! No security issues were detected in the scanned files.</p>
                </div>

                <div v-else>
                    <div
                        v-for="(component, index) in state.filteredResults"
                        :key="index"
                        class="wpqss-component"
                        :class="{ 'expanded': component.expanded }"
                    >
                        <div class="wpqss-component-header" @click="component.expanded = !component.expanded">
                            <h3>{{ component.name }}</h3>
                            <div class="wpqss-component-meta">
                                <span>{{ component.total_vulnerabilities }} vulnerabilities</span>
                                <span
                                    v-for="(count, severity) in component.severity_counts"
                                    :key="severity"
                                    v-if="count > 0"
                                    :class="['wpqss-severity-badge', severity]"
                                >
                                    {{ count }} {{ severity }}
                                </span>
                                <span :class="['wpqss-toggle-icon', 'dashicons', component.expanded ? 'dashicons-arrow-up' : 'dashicons-arrow-down']"></span>
                            </div>
                        </div>

                        <div v-if="component.expanded" class="wpqss-component-content">
                            <div
                                v-for="(file, fileIndex) in component.files"
                                :key="fileIndex"
                                class="wpqss-file"
                            >
                                <div class="wpqss-file-header">{{ file.file }}</div>
                                <div
                                    v-for="(vulnerability, vulnIndex) in file.vulnerabilities"
                                    :key="vulnIndex"
                                    :class="['wpqss-vulnerability', vulnerability.severity]"
                                >
                                    <div class="wpqss-vulnerability-header">
                                        <h4 class="wpqss-vulnerability-title">{{ vulnerability.type }}</h4>
                                        <span class="wpqss-vulnerability-line">Line {{ vulnerability.line }}</span>
                                    </div>

                                    <div class="wpqss-vulnerability-description">{{ vulnerability.description }}</div>

                                    <div class="wpqss-code-block">{{ vulnerability.code }}</div>

                                    <div v-if="vulnerability.context && vulnerability.context.length > 0" class="wpqss-code-context">
                                        <div
                                            v-for="line in vulnerability.context"
                                            :key="line.line_number"
                                            :class="['wpqss-context-line', { 'vulnerable': line.is_vulnerable }]"
                                        >
                                            <span class="wpqss-context-line-number">{{ line.line_number }}</span>{{ line.code }}
                                        </div>
                                    </div>

                                    <div class="wpqss-remediation">
                                        <div class="wpqss-remediation-title">Remediation:</div>
                                        <div class="wpqss-remediation-text">{{ vulnerability.remediation }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="wpqss-help-section">
            <h2><?php _e('Important Notes', 'wp-query-security-scanner'); ?></h2>
            <div class="wpqss-help-content">
                <div class="wpqss-help-item">
                    <h3><?php _e('Manual Review Required', 'wp-query-security-scanner'); ?></h3>
                    <p><?php _e('This scanner identifies potential security issues, but manual review is always required. Some findings may be false positives, and the scanner may not catch all vulnerabilities.', 'wp-query-security-scanner'); ?></p>
                </div>

                <div class="wpqss-help-item">
                    <h3><?php _e('Test in Staging', 'wp-query-security-scanner'); ?></h3>
                    <p><?php _e('Always test any security fixes in a staging environment before applying them to your live site.', 'wp-query-security-scanner'); ?></p>
                </div>

                <div class="wpqss-help-item">
                    <h3><?php _e('Keep Updated', 'wp-query-security-scanner'); ?></h3>
                    <p><?php _e('Regularly update WordPress, plugins, and themes to ensure you have the latest security patches.', 'wp-query-security-scanner'); ?></p>
                </div>

                <div class="wpqss-help-item">
                    <h3><?php _e('Severity Levels', 'wp-query-security-scanner'); ?></h3>
                    <ul>
                        <li><strong class="severity-critical"><?php _e('Critical', 'wp-query-security-scanner'); ?></strong>: <?php _e('Immediate attention required - high risk of exploitation', 'wp-query-security-scanner'); ?></li>
                        <li><strong class="severity-high"><?php _e('High', 'wp-query-security-scanner'); ?></strong>: <?php _e('Should be addressed soon - significant security risk', 'wp-query-security-scanner'); ?></li>
                        <li><strong class="severity-medium"><?php _e('Medium', 'wp-query-security-scanner'); ?></strong>: <?php _e('Moderate risk - address when possible', 'wp-query-security-scanner'); ?></li>
                        <li><strong class="severity-low"><?php _e('Low', 'wp-query-security-scanner'); ?></strong>: <?php _e('Low risk - consider addressing for best practices', 'wp-query-security-scanner'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="wpqss-footer">
            <p>
                <?php
                printf(
                    __('WP Query Security Scanner v%s - For support and updates, visit %s', 'wp-query-security-scanner'),
                    WPQSS_VERSION,
                    '<a href="https://github.com/your-repo/wp-query-security-scanner" target="_blank">GitHub</a>'
                );
                ?>
            </p>
        </div>
</div>

<style>
/* Vue.js specific styles */
[v-cloak] {
    display: none;
}

.wpqss-admin {
    max-width: 1200px;
}

/* Compact Modern UI Styles */
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
    opacity: 0.6;
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

/* Results Container */
.wpqss-results-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 24px;
}

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

/* Progress styles */
.wpqss-progress {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.wpqss-progress h3 {
    margin-top: 0;
    color: #2c3e50;
}

.wpqss-progress-bar {
    width: 100%;
    height: 20px;
    background: #f1f1f1;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.wpqss-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
    border-radius: 10px;
}

.wpqss-progress-text {
    margin: 0;
    color: #666;
    font-size: 14px;
}

/* Component styles */
.wpqss-component {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.wpqss-component:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.wpqss-component-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
}

.wpqss-component-header:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
}

.wpqss-component-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
    font-weight: 600;
}

.wpqss-component-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    color: #6c757d;
    font-size: 13px;
}

.wpqss-severity-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    color: #fff;
}

.wpqss-severity-badge.critical { background: #dc3545; }
.wpqss-severity-badge.high { background: #fd7e14; }
.wpqss-severity-badge.medium { background: #ffc107; color: #212529; }
.wpqss-severity-badge.low { background: #28a745; }

.wpqss-toggle-icon {
    transition: transform 0.2s ease;
}

.wpqss-component.collapsed .wpqss-toggle-icon {
    transform: rotate(-90deg);
}

.wpqss-component-content {
    display: none;
}

.wpqss-component.expanded .wpqss-component-content {
    display: block;
}

.wpqss-file {
    border-bottom: 1px solid #f1f1f1;
}

.wpqss-file:last-child {
    border-bottom: none;
}

.wpqss-file-header {
    background: #fafafa;
    padding: 10px 16px;
    border-bottom: 1px solid #f1f1f1;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-size: 13px;
    color: #666;
}

.wpqss-vulnerability {
    padding: 12px 16px;
    border-left: 3px solid #ddd;
    margin: 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.wpqss-vulnerability:last-child {
    border-bottom: none;
}

.wpqss-vulnerability.critical {
    border-left-color: #dc3545;
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
}
.wpqss-vulnerability.high {
    border-left-color: #fd7e14;
    background: linear-gradient(90deg, rgba(253, 126, 20, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
}
.wpqss-vulnerability.medium {
    border-left-color: #ffc107;
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
}
.wpqss-vulnerability.low {
    border-left-color: #28a745;
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
}

.wpqss-vulnerability-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.wpqss-vulnerability-title {
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    font-size: 14px;
}

.wpqss-vulnerability-line {
    background: #6c757d;
    color: #fff;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-weight: 500;
}

.wpqss-vulnerability-description {
    margin: 8px 0;
    color: #555;
    line-height: 1.5;
    font-size: 14px;
}

.wpqss-code-block {
    background: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 12px;
    margin: 10px 0;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-size: 13px;
    overflow-x: auto;
    line-height: 1.4;
}

.wpqss-code-context {
    background: #f1f1f1;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin: 10px 0;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-size: 12px;
    max-height: 200px;
    overflow-y: auto;
}

.wpqss-context-line {
    padding: 2px 10px;
    border-bottom: 1px solid #eee;
    white-space: pre;
}

.wpqss-context-line:last-child {
    border-bottom: none;
}

.wpqss-context-line.vulnerable {
    background: #ffebee;
    font-weight: bold;
}

.wpqss-context-line-number {
    display: inline-block;
    width: 40px;
    color: #999;
    text-align: right;
    margin-right: 10px;
    user-select: none;
}

.wpqss-remediation {
    background: #e8f4fd;
    border: 1px solid #b8daff;
    border-radius: 6px;
    padding: 12px;
    margin: 10px 0 0 0;
}

.wpqss-remediation-title {
    font-weight: bold;
    color: #004085;
    margin: 0 0 5px 0;
    font-size: 14px;
}

.wpqss-remediation-text {
    margin: 0;
    color: #004085;
    line-height: 1.4;
    font-size: 14px;
}

.wpqss-empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}

.wpqss-empty-state .dashicons {
    font-size: 48px;
    color: #28a745;
    margin-bottom: 15px;
}

.wpqss-empty-state h3 {
    color: #28a745;
    margin-bottom: 10px;
}

.wpqss-help-section {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.wpqss-help-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.wpqss-help-item h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #2c3e50;
}

.wpqss-help-item p,
.wpqss-help-item ul {
    margin: 0;
    color: #555;
    font-size: 14px;
    line-height: 1.5;
}

.wpqss-help-item ul {
    padding-left: 20px;
}

.wpqss-help-item li {
    margin-bottom: 5px;
}

.severity-critical { color: #dc3545; }
.severity-high { color: #fd7e14; }
.severity-medium { color: #ffc107; }
.severity-low { color: #28a745; }

.wpqss-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    text-align: center;
    color: #666;
    font-size: 14px;
}

.wpqss-footer a {
    color: #667eea;
    text-decoration: none;
}

.wpqss-footer a:hover {
    text-decoration: underline;
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

    .wpqss-component-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .wpqss-vulnerability-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .wpqss-help-content {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Ensure Vue.js is loaded
if (typeof Vue === 'undefined') {
    console.error('Vue.js is not loaded. Please ensure Vue.js is included before this script.');
}
</script>

    <!-- Help Section -->
    <div class="wpqss-help-section">
        <h2><?php _e('Important Notes', 'wp-query-security-scanner'); ?></h2>
        <div class="wpqss-help-content">
            <div class="wpqss-help-item">
                <h3><?php _e('Manual Review Required', 'wp-query-security-scanner'); ?></h3>
                <p><?php _e('This scanner identifies potential security issues, but manual review is always required. Some findings may be false positives, and the scanner may not catch all vulnerabilities.', 'wp-query-security-scanner'); ?></p>
            </div>
            
            <div class="wpqss-help-item">
                <h3><?php _e('Test in Staging', 'wp-query-security-scanner'); ?></h3>
                <p><?php _e('Always test any security fixes in a staging environment before applying them to your live site.', 'wp-query-security-scanner'); ?></p>
            </div>
            
            <div class="wpqss-help-item">
                <h3><?php _e('Keep Updated', 'wp-query-security-scanner'); ?></h3>
                <p><?php _e('Regularly update WordPress, plugins, and themes to ensure you have the latest security patches.', 'wp-query-security-scanner'); ?></p>
            </div>
            
            <div class="wpqss-help-item">
                <h3><?php _e('Severity Levels', 'wp-query-security-scanner'); ?></h3>
                <ul>
                    <li><strong class="severity-critical"><?php _e('Critical', 'wp-query-security-scanner'); ?></strong>: <?php _e('Immediate attention required - high risk of exploitation', 'wp-query-security-scanner'); ?></li>
                    <li><strong class="severity-high"><?php _e('High', 'wp-query-security-scanner'); ?></strong>: <?php _e('Should be addressed soon - significant security risk', 'wp-query-security-scanner'); ?></li>
                    <li><strong class="severity-medium"><?php _e('Medium', 'wp-query-security-scanner'); ?></strong>: <?php _e('Moderate risk - address when possible', 'wp-query-security-scanner'); ?></li>
                    <li><strong class="severity-low"><?php _e('Low', 'wp-query-security-scanner'); ?></strong>: <?php _e('Low risk - consider addressing for best practices', 'wp-query-security-scanner'); ?></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="wpqss-footer">
        <p>
            <?php 
            printf(
                __('WP Query Security Scanner v%s - For support and updates, visit %s', 'wp-query-security-scanner'),
                WPQSS_VERSION,
                '<a href="https://github.com/your-repo/wp-query-security-scanner" target="_blank">GitHub</a>'
            ); 
            ?>
        </p>
    </div>
</div>
