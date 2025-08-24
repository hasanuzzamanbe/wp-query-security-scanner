<?php
/**
 * Plugin Name: WP Query Security Scanner
 * Description: Advanced security scanner for detecting SQL injection, XSS, CSRF and other vulnerabilities in WordPress plugins with precise line number tracking
 * Version: 2.0.0
 * Author: Hasanuzzaman Shamim
 * Author URI: https://hasanuzzaman.com
 * Text Domain: wp-query-security-scanner
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Define plugin constants
define('WPQSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPQSS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPQSS_VERSION', '2.0.0');

// Autoload classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'WPQSS_') === 0) {
        $file = WPQSS_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', substr($class, 6))) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

/**
 * Main plugin class
 */
class WPQuerySecurityScanner {

    private static $instance = null;
    private $vulnerability_detector;
    private $report_generator;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        // Initialize components
        $this->vulnerability_detector = new WPQSS_Vulnerability_Detector();
        $this->report_generator = new WPQSS_Report_Generator();

        // Hook into WordPress
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_wpqss_scan_plugins', [$this, 'ajax_scan_plugins']);
        add_action('wp_ajax_wpqss_scan_themes', [$this, 'ajax_scan_themes']);
        add_action('wp_ajax_wpqss_scan_specific_plugin', [$this, 'ajax_scan_specific_plugin']);
        add_action('wp_ajax_wpqss_scan_specific_theme', [$this, 'ajax_scan_specific_theme']);
        add_action('wp_ajax_wpqss_get_available_components', [$this, 'ajax_get_available_components']);
        add_action('wp_ajax_wpqss_export_report', [$this, 'ajax_export_report']);
        add_action('wp_ajax_wpqss_get_scan_progress', [$this, 'ajax_get_scan_progress']);
        add_action('wp_ajax_wpqss_download_report', [$this, 'ajax_download_report']);

        // Add admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    public function add_admin_menu() {
        $hook = add_management_page(
            __('WP Query Security Scanner', 'wp-query-security-scanner'),
            __('Security Scanner', 'wp-query-security-scanner'),
            'manage_options',
            'wp-query-security-scanner',
            [$this, 'render_admin_page']
        );

        add_action("load-$hook", [$this, 'admin_page_load']);
    }

    public function admin_page_load() {
        // Add help tabs
        $screen = get_current_screen();
        $screen->add_help_tab([
            'id' => 'wpqss-overview',
            'title' => __('Overview', 'wp-query-security-scanner'),
            'content' => $this->get_help_content('overview')
        ]);

        $screen->add_help_tab([
            'id' => 'wpqss-vulnerabilities',
            'title' => __('Vulnerability Types', 'wp-query-security-scanner'),
            'content' => $this->get_help_content('vulnerabilities')
        ]);
    }

    public function enqueue_admin_assets($hook) {
        if ('tools_page_wp-query-security-scanner' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wpqss-admin-styles',
            WPQSS_PLUGIN_URL . 'assets/admin-styles.css',
            [],
            WPQSS_VERSION
        );

        wp_enqueue_script(
            'wpqss-admin-scripts',
            WPQSS_PLUGIN_URL . 'assets/admin-scripts.js',
            ['jquery'],
            WPQSS_VERSION,
            true
        );

        wp_localize_script('wpqss-admin-scripts', 'wpqss_ajax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpqss_nonce'),
            'strings' => [
                'scanning' => __('Scanning...', 'wp-query-security-scanner'),
                'scan_complete' => __('Scan Complete', 'wp-query-security-scanner'),
                'scan_error' => __('Scan Error', 'wp-query-security-scanner'),
                'export_success' => __('Report exported successfully', 'wp-query-security-scanner'),
                'export_error' => __('Export failed', 'wp-query-security-scanner'),
            ]
        ]);
    }

    public function ajax_scan_plugins() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $scan_type = sanitize_text_field($_POST['scan_type'] ?? 'plugins');
        $specific_component = sanitize_text_field($_POST['specific_component'] ?? '');

        $results = $this->vulnerability_detector->scan_directory(
            $scan_type === 'themes' ? WP_CONTENT_DIR . '/themes' : WP_PLUGIN_DIR,
            $scan_type,
            $specific_component ?: null
        );

        // Save scan statistics
        $this->save_scan_statistics($results);

        wp_send_json_success($results);
    }

    public function ajax_scan_themes() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $results = $this->vulnerability_detector->scan_directory(WP_CONTENT_DIR . '/themes', 'themes');

        // Save scan statistics
        $this->save_scan_statistics($results);

        wp_send_json_success($results);
    }

    public function ajax_scan_specific_plugin() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $plugin_folder = sanitize_text_field($_POST['plugin_folder'] ?? '');
        if (empty($plugin_folder)) {
            wp_send_json_error(__('Plugin folder not specified', 'wp-query-security-scanner'));
        }

        $results = $this->vulnerability_detector->scan_directory(WP_PLUGIN_DIR, 'plugins', $plugin_folder);

        // Save scan statistics
        $this->save_scan_statistics($results);

        wp_send_json_success($results);
    }

    public function ajax_scan_specific_theme() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $theme_folder = sanitize_text_field($_POST['theme_folder'] ?? '');
        if (empty($theme_folder)) {
            wp_send_json_error(__('Theme folder not specified', 'wp-query-security-scanner'));
        }

        $results = $this->vulnerability_detector->scan_directory(WP_CONTENT_DIR . '/themes', 'themes', $theme_folder);

        // Save scan statistics
        $this->save_scan_statistics($results);

        wp_send_json_success($results);
    }

    public function ajax_get_available_components() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $type = sanitize_text_field($_POST['type'] ?? 'plugins');

        if ($type === 'themes') {
            $components = $this->vulnerability_detector->get_available_themes();
        } else {
            $components = $this->vulnerability_detector->get_available_plugins();
        }

        wp_send_json_success($components);
    }

    public function ajax_export_report() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $format = sanitize_text_field($_POST['format'] ?? 'json');
        $scan_results = json_decode(stripslashes($_POST['scan_results'] ?? '[]'), true);

        $report = $this->report_generator->generate_report($scan_results, $format);

        wp_send_json_success([
            'download_url' => $report['url'],
            'filename' => $report['filename']
        ]);
    }

    public function ajax_get_scan_progress() {
        check_ajax_referer('wpqss_nonce', 'nonce');

        $progress = get_transient('wpqss_scan_progress_' . get_current_user_id());
        wp_send_json_success($progress ?: ['progress' => 0, 'status' => 'idle']);
    }

    public function ajax_download_report() {
        $filename = sanitize_file_name($_GET['file'] ?? '');
        $nonce = $_GET['nonce'] ?? '';

        if (!wp_verify_nonce($nonce, 'wpqss_download_' . $filename)) {
            wp_die(__('Invalid download link', 'wp-query-security-scanner'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-query-security-scanner'));
        }

        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/wpqss-reports/' . $filename;

        if (!file_exists($file_path)) {
            wp_die(__('Report file not found', 'wp-query-security-scanner'));
        }

        // Determine content type
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $content_types = [
            'json' => 'application/json',
            'csv' => 'text/csv',
            'html' => 'text/html',
            'xml' => 'application/xml'
        ];
        $content_type = $content_types[$extension] ?? 'application/octet-stream';

        // Send file
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        readfile($file_path);

        // Clean up old files (optional)
        $this->cleanup_old_reports();

        exit;
    }

    private function cleanup_old_reports() {
        $upload_dir = wp_upload_dir();
        $reports_dir = $upload_dir['basedir'] . '/wpqss-reports';

        if (!is_dir($reports_dir)) {
            return;
        }

        $files = glob($reports_dir . '/security-report_*.{json,csv,html,xml}', GLOB_BRACE);
        $cutoff_time = time() - (7 * 24 * 60 * 60); // 7 days ago

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }

    private function save_scan_statistics($results) {
        $total_vulnerabilities = 0;
        $severity_counts = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];

        foreach ($results as $component) {
            $total_vulnerabilities += $component['total_vulnerabilities'];
            foreach ($severity_counts as $severity => $count) {
                $severity_counts[$severity] += $component['severity_counts'][$severity] ?? 0;
            }
        }

        $stats = [
            'timestamp' => time(),
            'total_components' => count($results),
            'total_vulnerabilities' => $total_vulnerabilities,
            'severity_counts' => $severity_counts
        ];

        update_option('wpqss_last_scan_stats', $stats);
    }

    public function render_admin_page() {
        $template_file = WPQSS_PLUGIN_DIR . 'templates/admin-page.php';
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            $this->render_fallback_admin_page();
        }
    }

    private function render_fallback_admin_page() {
        ?>
        <div class="wrap wpqss-admin">
            <h1><?php _e('WP Query Security Scanner', 'wp-query-security-scanner'); ?></h1>

            <div class="wpqss-scan-controls">
                <button id="wpqss-scan-plugins" class="button button-primary">
                    <?php _e('Scan Plugins', 'wp-query-security-scanner'); ?>
                </button>
                <button id="wpqss-scan-themes" class="button button-secondary">
                    <?php _e('Scan Themes', 'wp-query-security-scanner'); ?>
                </button>
                <button id="wpqss-export-report" class="button" disabled>
                    <?php _e('Export Report', 'wp-query-security-scanner'); ?>
                </button>
            </div>

            <div id="wpqss-progress" style="display: none;">
                <div class="wpqss-progress-bar">
                    <div class="wpqss-progress-fill"></div>
                </div>
                <p class="wpqss-progress-text"></p>
            </div>

            <div id="wpqss-results"></div>
        </div>
        <?php
    }

    public function admin_notices() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'tools_page_wp-query-security-scanner') {
            ?>
            <div class="notice notice-info">
                <p>
                    <?php _e('This scanner detects potential security vulnerabilities. Always review results manually and test fixes in a staging environment.', 'wp-query-security-scanner'); ?>
                </p>
            </div>
            <?php
        }
    }

    private function get_help_content($tab) {
        switch ($tab) {
            case 'overview':
                return '<p>' . __('The WP Query Security Scanner helps identify potential security vulnerabilities in your WordPress plugins and themes. It scans for SQL injection, XSS, CSRF, and other common security issues.', 'wp-query-security-scanner') . '</p>';

            case 'vulnerabilities':
                return '<ul>
                    <li><strong>' . __('SQL Injection', 'wp-query-security-scanner') . '</strong>: ' . __('Unsafe database queries that could allow attackers to manipulate your database.', 'wp-query-security-scanner') . '</li>
                    <li><strong>' . __('Cross-Site Scripting (XSS)', 'wp-query-security-scanner') . '</strong>: ' . __('Unescaped output that could allow script injection.', 'wp-query-security-scanner') . '</li>
                    <li><strong>' . __('CSRF', 'wp-query-security-scanner') . '</strong>: ' . __('Missing nonce verification for sensitive operations.', 'wp-query-security-scanner') . '</li>
                    <li><strong>' . __('File Inclusion', 'wp-query-security-scanner') . '</strong>: ' . __('Unsafe file includes that could lead to code execution.', 'wp-query-security-scanner') . '</li>
                </ul>';

            default:
                return '';
        }
    }
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    WPQuerySecurityScanner::get_instance();
});

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create necessary database tables or options
    add_option('wpqss_version', WPQSS_VERSION);

    // Create uploads directory for reports
    $upload_dir = wp_upload_dir();
    $wpqss_dir = $upload_dir['basedir'] . '/wpqss-reports';
    if (!file_exists($wpqss_dir)) {
        wp_mkdir_p($wpqss_dir);
        // Add .htaccess to protect reports
        file_put_contents($wpqss_dir . '/.htaccess', "deny from all\n");
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpqss_%'");
});