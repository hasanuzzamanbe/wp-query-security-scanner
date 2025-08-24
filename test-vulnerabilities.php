<?php
/**
 * Test File with Intentional Vulnerabilities
 * 
 * This file contains examples of security vulnerabilities that the scanner should detect.
 * DO NOT USE THIS CODE IN PRODUCTION!
 */

// SQL Injection vulnerabilities
function vulnerable_sql_query() {
    global $wpdb;
    
    // Critical: Direct user input in query
    $user_id = $_GET['user_id'];
    $wpdb->query("SELECT * FROM {$wpdb->users} WHERE ID = $user_id");
    
    // Critical: Unprepared query with POST data
    $search = $_POST['search'];
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%$search%'");
    
    // High: Variable interpolation without prepare
    $table_name = $_REQUEST['table'];
    $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
}

// XSS vulnerabilities
function vulnerable_output() {
    // High: Unescaped GET parameter
    echo $_GET['message'];
    
    // High: Unescaped POST data
    print $_POST['content'];
    
    // Medium: Unescaped in HTML attribute
    $value = $_GET['value'];
    echo '<input type="text" value="' . $value . '">';
    
    // Medium: Unescaped URL
    $redirect = $_GET['redirect'];
    echo '<a href="' . $redirect . '">Click here</a>';
}

// CSRF vulnerabilities
function vulnerable_admin_action() {
    // Medium: No nonce verification
    if (isset($_POST['delete_user'])) {
        wp_delete_user($_POST['user_id']);
    }
    
    // Medium: Admin post without CSRF protection
    add_action('admin_post_delete_data', function() {
        // Delete data without nonce check
        delete_option($_POST['option_name']);
    });
}

// File inclusion vulnerabilities
function vulnerable_file_operations() {
    // Critical: Direct file inclusion
    $page = $_GET['page'];
    include($page . '.php');
    
    // High: File reading with user input
    $filename = $_POST['filename'];
    $content = file_get_contents($filename);
    
    // High: File opening with user path
    $file = $_REQUEST['file'];
    $handle = fopen($file, 'r');
}

// Privilege escalation
function vulnerable_capability_check() {
    // Medium: Insufficient capability check
    if (current_user_can('edit_posts')) {
        // Allow sensitive operation with basic capability
        update_option('admin_email', $_POST['email']);
    }
    
    // Critical: User switching with user input
    $user_id = $_GET['switch_user'];
    wp_set_current_user($user_id);
}

// Information disclosure
function vulnerable_debug_info() {
    // Low: Debug function
    var_dump($_SERVER);
    
    // Medium: Printing environment variables
    print_r($_ENV);
    
    // High: PHP info disclosure
    phpinfo();
}

// Unsafe deserialization
function vulnerable_deserialization() {
    // Critical: Unserialize user input
    $data = unserialize($_POST['data']);
    
    // High: Maybe unserialize user input
    $config = maybe_unserialize($_GET['config']);
}

// Command injection
function vulnerable_command_execution() {
    // Critical: System command with user input
    $command = $_GET['cmd'];
    system($command);
    
    // Critical: Exec with user input
    exec('ls ' . $_POST['directory']);
    
    // Critical: Backtick execution
    $output = `ping $_GET[host]`;
}

// AJAX handler without nonce
add_action('wp_ajax_vulnerable_action', function() {
    // Medium: No nonce verification in AJAX
    update_user_meta(get_current_user_id(), 'preference', $_POST['value']);
});

// Unsafe WordPress functions
function more_vulnerabilities() {
    // Various unsafe patterns
    $wpdb->prepare("SELECT * FROM table WHERE id = %s", $_GET['id']); // This is actually safe
    
    // But this is not:
    $query = "SELECT * FROM {$wpdb->posts} WHERE ID = " . $_GET['post_id'];
    $wpdb->query($query);
    
    // Unsafe file operations
    readfile($_GET['download']);
    
    // Missing sanitization
    update_option('setting', $_POST['value']); // Should use sanitize_text_field()
}
?>
