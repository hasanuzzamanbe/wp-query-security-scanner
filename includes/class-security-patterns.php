<?php
/**
 * Security Patterns Class
 * 
 * Defines comprehensive security vulnerability patterns for detection
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

class WPQSS_Security_Patterns {
    
    /**
     * Get all security patterns organized by vulnerability type
     * 
     * @return array
     */
    public static function get_patterns() {
        return [
            'sql_injection' => self::get_sql_injection_patterns(),
            'xss' => self::get_xss_patterns(),
            'csrf' => self::get_csrf_patterns(),
            'file_inclusion' => self::get_file_inclusion_patterns(),
            'privilege_escalation' => self::get_privilege_escalation_patterns(),
            'information_disclosure' => self::get_information_disclosure_patterns(),
            'unsafe_deserialization' => self::get_unsafe_deserialization_patterns(),
            'command_injection' => self::get_command_injection_patterns(),
        ];
    }
    
    /**
     * SQL Injection patterns
     */
    private static function get_sql_injection_patterns() {
        return [
            [
                'pattern' => '/\$_(?:GET|POST|REQUEST|COOKIE)\s*\[\s*[\'"][^\'"]*[\'"]\s*\]\s*(?!.*(?:esc_sql|sanitize_|wp_kses|intval|absint|floatval))/',
                'severity' => 'critical',
                'description' => 'Unsanitized user input used directly in database queries',
                'remediation' => 'Use $wpdb->prepare() or proper sanitization functions like sanitize_text_field(), esc_sql(), etc.'
            ],
            [
                'pattern' => '/\$wpdb->query\s*\(\s*[\'"].*\$(?:_(?:GET|POST|REQUEST|COOKIE)|\w+).*[\'"]\s*\)/',
                'severity' => 'critical',
                'description' => 'Direct variable interpolation in $wpdb->query() without preparation',
                'remediation' => 'Use $wpdb->prepare() to safely include variables in SQL queries'
            ],
            [
                'pattern' => '/(?:SELECT|INSERT|UPDATE|DELETE).*FROM.*\$(?:_(?:GET|POST|REQUEST|COOKIE)|\w+).*(?!.*prepare)/',
                'severity' => 'high',
                'description' => 'Raw SQL query with variable interpolation',
                'remediation' => 'Use $wpdb->prepare() for all dynamic SQL queries'
            ],
            [
                'pattern' => '/\$wpdb->get_(?:var|row|col|results)\s*\(\s*[\'"].*\$.*[\'"]\s*\)/',
                'severity' => 'high',
                'description' => 'Unprepared query in $wpdb get methods',
                'remediation' => 'Use $wpdb->prepare() before calling get methods'
            ],
            [
                'pattern' => '/mysql_query\s*\(.*\$/',
                'severity' => 'critical',
                'description' => 'Deprecated mysql_query() function with variables',
                'remediation' => 'Use WordPress $wpdb methods with proper preparation'
            ],
            [
                'pattern' => '/mysqli_query\s*\(.*\$(?:_(?:GET|POST|REQUEST|COOKIE)|\w+)/',
                'severity' => 'critical',
                'description' => 'Direct mysqli_query() with user input',
                'remediation' => 'Use prepared statements or WordPress $wpdb methods'
            ]
        ];
    }
    
    /**
     * Cross-Site Scripting (XSS) patterns
     */
    private static function get_xss_patterns() {
        return [
            [
                'pattern' => '/echo\s+\$_(?:GET|POST|REQUEST|COOKIE)\s*\[.*\](?!.*(?:esc_html|esc_attr|esc_url|wp_kses))/',
                'severity' => 'high',
                'description' => 'Unescaped user input in echo statement',
                'remediation' => 'Use esc_html(), esc_attr(), or esc_url() to escape output'
            ],
            [
                'pattern' => '/print\s+\$_(?:GET|POST|REQUEST|COOKIE)\s*\[.*\](?!.*(?:esc_html|esc_attr|esc_url|wp_kses))/',
                'severity' => 'high',
                'description' => 'Unescaped user input in print statement',
                'remediation' => 'Use esc_html(), esc_attr(), or esc_url() to escape output'
            ],
            [
                'pattern' => '/\?\>\s*<.*\$_(?:GET|POST|REQUEST|COOKIE)\s*\[.*\](?!.*(?:esc_html|esc_attr|esc_url))/',
                'severity' => 'high',
                'description' => 'Unescaped user input directly in HTML',
                'remediation' => 'Use appropriate escaping functions before outputting user data'
            ],
            [
                'pattern' => '/value\s*=\s*[\'"]?\$_(?:GET|POST|REQUEST|COOKIE)\s*\[.*\][\'"]?(?!.*esc_attr)/',
                'severity' => 'medium',
                'description' => 'Unescaped user input in HTML attribute',
                'remediation' => 'Use esc_attr() for HTML attributes'
            ],
            [
                'pattern' => '/href\s*=\s*[\'"]?\$_(?:GET|POST|REQUEST|COOKIE)\s*\[.*\][\'"]?(?!.*esc_url)/',
                'severity' => 'medium',
                'description' => 'Unescaped user input in URL attribute',
                'remediation' => 'Use esc_url() for URL attributes'
            ]
        ];
    }
    
    /**
     * CSRF patterns
     */
    private static function get_csrf_patterns() {
        return [
            [
                'pattern' => '/\$_POST.*(?!.*(?:wp_verify_nonce|check_admin_referer|wp_nonce_field))/',
                'severity' => 'medium',
                'description' => 'POST request processing without nonce verification',
                'remediation' => 'Add wp_verify_nonce() or check_admin_referer() verification'
            ],
            [
                'pattern' => '/wp_ajax_(?!nopriv_).*(?!.*(?:wp_verify_nonce|check_ajax_referer))/',
                'severity' => 'medium',
                'description' => 'AJAX handler without nonce verification',
                'remediation' => 'Add check_ajax_referer() to AJAX handlers'
            ],
            [
                'pattern' => '/add_action\s*\(\s*[\'"]admin_post_.*(?!.*(?:wp_verify_nonce|check_admin_referer))/',
                'severity' => 'medium',
                'description' => 'Admin post handler without CSRF protection',
                'remediation' => 'Add nonce verification to admin post handlers'
            ]
        ];
    }
    
    /**
     * File inclusion patterns
     */
    private static function get_file_inclusion_patterns() {
        return [
            [
                'pattern' => '/(?:include|require)(?:_once)?\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'critical',
                'description' => 'Direct file inclusion with user input',
                'remediation' => 'Validate and sanitize file paths, use whitelist of allowed files'
            ],
            [
                'pattern' => '/file_get_contents\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'high',
                'description' => 'File reading with user-controlled path',
                'remediation' => 'Validate file paths and restrict to safe directories'
            ],
            [
                'pattern' => '/fopen\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'high',
                'description' => 'File opening with user-controlled path',
                'remediation' => 'Validate and sanitize file paths'
            ],
            [
                'pattern' => '/readfile\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'high',
                'description' => 'Direct file reading with user input',
                'remediation' => 'Validate file paths and use proper access controls'
            ]
        ];
    }
    
    /**
     * Privilege escalation patterns
     */
    private static function get_privilege_escalation_patterns() {
        return [
            [
                'pattern' => '/current_user_can\s*\(\s*[\'"](?:edit_posts|publish_posts)[\'"].*(?!.*(?:administrator|editor))/',
                'severity' => 'medium',
                'description' => 'Potentially insufficient capability check',
                'remediation' => 'Use more specific capabilities or role checks'
            ],
            [
                'pattern' => '/wp_set_current_user\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'critical',
                'description' => 'User switching with user-controlled input',
                'remediation' => 'Never allow user-controlled user switching'
            ],
            [
                'pattern' => '/update_user_meta.*role.*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'critical',
                'description' => 'User role modification with user input',
                'remediation' => 'Strictly validate and authorize role changes'
            ]
        ];
    }
    
    /**
     * Information disclosure patterns
     */
    private static function get_information_disclosure_patterns() {
        return [
            [
                'pattern' => '/var_dump\s*\(/',
                'severity' => 'low',
                'description' => 'Debug function that may expose sensitive information',
                'remediation' => 'Remove debug functions from production code'
            ],
            [
                'pattern' => '/print_r\s*\(.*\$_(?:SERVER|ENV)/',
                'severity' => 'medium',
                'description' => 'Printing server or environment variables',
                'remediation' => 'Remove or restrict access to debug output'
            ],
            [
                'pattern' => '/phpinfo\s*\(\s*\)/',
                'severity' => 'high',
                'description' => 'PHP information disclosure',
                'remediation' => 'Remove phpinfo() calls from production code'
            ]
        ];
    }
    
    /**
     * Unsafe deserialization patterns
     */
    private static function get_unsafe_deserialization_patterns() {
        return [
            [
                'pattern' => '/unserialize\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'critical',
                'description' => 'Unsafe deserialization of user input',
                'remediation' => 'Use safe alternatives like JSON or validate serialized data'
            ],
            [
                'pattern' => '/maybe_unserialize\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'high',
                'description' => 'Potentially unsafe deserialization',
                'remediation' => 'Validate input before deserialization'
            ]
        ];
    }
    
    /**
     * Command injection patterns
     */
    private static function get_command_injection_patterns() {
        return [
            [
                'pattern' => '/(?:exec|system|shell_exec|passthru|popen)\s*\(.*\$_(?:GET|POST|REQUEST|COOKIE)/',
                'severity' => 'critical',
                'description' => 'Command execution with user input',
                'remediation' => 'Avoid system commands or use escapeshellarg() and escapeshellcmd()'
            ],
            [
                'pattern' => '/`.*\$_(?:GET|POST|REQUEST|COOKIE).*`/',
                'severity' => 'critical',
                'description' => 'Backtick command execution with user input',
                'remediation' => 'Avoid backtick execution or properly escape input'
            ]
        ];
    }
    
    /**
     * Get severity color for display
     */
    public static function get_severity_color($severity) {
        $colors = [
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#ffc107',
            'low' => '#28a745'
        ];
        
        return $colors[$severity] ?? '#6c757d';
    }
    
    /**
     * Get severity priority for sorting
     */
    public static function get_severity_priority($severity) {
        $priorities = [
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1
        ];
        
        return $priorities[$severity] ?? 0;
    }
}
