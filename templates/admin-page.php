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
        <!-- Vue.js app will be mounted here -->
    </div>

    <!-- Fallback content if Vue.js fails to load -->
    <noscript>
        <div class="notice notice-error">
            <p><strong>JavaScript Required:</strong> This security scanner requires JavaScript to function properly.</p>
        </div>
    </noscript>


</div>


</div>

<style>
/* Vue.js specific styles */
[v-cloak] {
    display: none;
}

#wpfooter {
    position: relative !important;
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
</div>
