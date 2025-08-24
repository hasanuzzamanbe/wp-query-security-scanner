<?php
/**
 * Admin Page Template
 * 
 * Enhanced admin interface for the security scanner
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
        <span style="font-size: 14px; color: #666; font-weight: normal;">v<?php echo WPQSS_VERSION; ?></span>
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
    
    <!-- Scan Controls -->
    <div class="wpqss-scan-controls">
        <h2><?php _e('Security Scan', 'wp-query-security-scanner'); ?></h2>
        <p class="description">
            <?php _e('Scan your WordPress plugins and themes for potential security vulnerabilities including SQL injection, XSS, CSRF, and other common security issues.', 'wp-query-security-scanner'); ?>
        </p>
        
        <div class="wpqss-scan-buttons">
            <div class="wpqss-scan-row">
                <button id="wpqss-scan-plugins" class="button button-primary button-large">
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <?php _e('Scan All Plugins', 'wp-query-security-scanner'); ?>
                </button>

                <button id="wpqss-scan-themes" class="button button-secondary button-large">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php _e('Scan All Themes', 'wp-query-security-scanner'); ?>
                </button>

                <button id="wpqss-export-report" class="button button-large" disabled>
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export Report', 'wp-query-security-scanner'); ?>
                </button>
            </div>

            <div class="wpqss-specific-scan">
                <h3><?php _e('Scan Specific Component', 'wp-query-security-scanner'); ?></h3>
                <p class="description">
                    <?php _e('Scan individual plugins or themes for faster, targeted analysis.', 'wp-query-security-scanner'); ?>
                </p>

                <div class="wpqss-specific-controls">
                    <div class="wpqss-control-group">
                        <label for="wpqss-component-type"><?php _e('Type:', 'wp-query-security-scanner'); ?></label>
                        <select id="wpqss-component-type">
                            <option value="plugins"><?php _e('Plugin', 'wp-query-security-scanner'); ?></option>
                            <option value="themes"><?php _e('Theme', 'wp-query-security-scanner'); ?></option>
                        </select>
                    </div>

                    <div class="wpqss-control-group">
                        <label for="wpqss-component-select"><?php _e('Component:', 'wp-query-security-scanner'); ?></label>
                        <select id="wpqss-component-select" disabled>
                            <option value=""><?php _e('Loading...', 'wp-query-security-scanner'); ?></option>
                        </select>
                    </div>

                    <button id="wpqss-scan-specific" class="button button-primary" disabled>
                        <span class="dashicons dashicons-search"></span>
                        <?php _e('Scan Selected', 'wp-query-security-scanner'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="wpqss-scan-info">
            <h3><?php _e('What does this scanner detect?', 'wp-query-security-scanner'); ?></h3>
            <div class="wpqss-vulnerability-types">
                <div class="wpqss-vuln-type">
                    <strong><?php _e('SQL Injection', 'wp-query-security-scanner'); ?></strong>
                    <p><?php _e('Unsafe database queries that could allow attackers to manipulate your database.', 'wp-query-security-scanner'); ?></p>
                </div>
                <div class="wpqss-vuln-type">
                    <strong><?php _e('Cross-Site Scripting (XSS)', 'wp-query-security-scanner'); ?></strong>
                    <p><?php _e('Unescaped output that could allow malicious script injection.', 'wp-query-security-scanner'); ?></p>
                </div>
                <div class="wpqss-vuln-type">
                    <strong><?php _e('Cross-Site Request Forgery (CSRF)', 'wp-query-security-scanner'); ?></strong>
                    <p><?php _e('Missing nonce verification for sensitive operations.', 'wp-query-security-scanner'); ?></p>
                </div>
                <div class="wpqss-vuln-type">
                    <strong><?php _e('File Inclusion', 'wp-query-security-scanner'); ?></strong>
                    <p><?php _e('Unsafe file includes that could lead to remote code execution.', 'wp-query-security-scanner'); ?></p>
                </div>
                <div class="wpqss-vuln-type">
                    <strong><?php _e('Privilege Escalation', 'wp-query-security-scanner'); ?></strong>
                    <p><?php _e('Insufficient capability checks that could allow unauthorized access.', 'wp-query-security-scanner'); ?></p>
                </div>
                <div class="wpqss-vuln-type">
                    <strong><?php _e('Information Disclosure', 'wp-query-security-scanner'); ?></strong>
                    <p><?php _e('Debug functions and exposed sensitive information.', 'wp-query-security-scanner'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Progress Indicator -->
    <div id="wpqss-progress" class="wpqss-progress" style="display: none;">
        <h3><?php _e('Scanning in Progress', 'wp-query-security-scanner'); ?></h3>
        <div class="wpqss-progress-bar">
            <div class="wpqss-progress-fill"></div>
        </div>
        <p class="wpqss-progress-text"><?php _e('Initializing scan...', 'wp-query-security-scanner'); ?></p>
        <p class="description">
            <?php _e('This may take a few minutes depending on the number of files to scan. Please do not close this page.', 'wp-query-security-scanner'); ?>
        </p>
    </div>
    
    <!-- Results Container -->
    <div id="wpqss-results" class="wpqss-results"></div>
    
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
/* Additional template-specific styles */
.wpqss-scan-buttons {
    margin: 20px 0;
}

.wpqss-scan-row {
    margin-bottom: 30px;
}

.wpqss-scan-buttons .button {
    margin-right: 15px;
    margin-bottom: 10px;
}

.wpqss-scan-buttons .dashicons {
    margin-right: 5px;
}

.wpqss-specific-scan {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.wpqss-specific-scan h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #23282d;
}

.wpqss-specific-controls {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.wpqss-control-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
}

.wpqss-control-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #23282d;
}

.wpqss-control-group select {
    padding: 6px 8px;
    border: 1px solid #ccd0d4;
    border-radius: 3px;
    background: #fff;
}

.wpqss-control-group select:disabled {
    background: #f1f1f1;
    color: #666;
}

.wpqss-scan-info {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.wpqss-vulnerability-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.wpqss-vuln-type {
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
    border-left: 4px solid #0073aa;
}

.wpqss-vuln-type strong {
    display: block;
    margin-bottom: 5px;
    color: #23282d;
}

.wpqss-vuln-type p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

.wpqss-help-section {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
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
    color: #23282d;
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
    color: #0073aa;
    text-decoration: none;
}

.wpqss-footer a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .wpqss-vulnerability-types,
    .wpqss-help-content {
        grid-template-columns: 1fr;
    }

    .wpqss-scan-buttons .button {
        width: 100%;
        margin-right: 0;
    }

    .wpqss-specific-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .wpqss-control-group {
        min-width: auto;
    }

    .wpqss-control-group select {
        width: 100%;
    }
}
</style>
