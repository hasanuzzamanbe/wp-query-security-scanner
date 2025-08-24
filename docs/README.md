# WP Query Security Scanner

A comprehensive WordPress plugin for detecting security vulnerabilities in plugins and themes with precise line number tracking and detailed reporting.

## Features

### 🔍 **Comprehensive Vulnerability Detection**
- **SQL Injection**: Detects unsafe database queries, unprepared statements, and direct variable interpolation
- **Cross-Site Scripting (XSS)**: Identifies unescaped output in HTML, attributes, and URLs
- **Cross-Site Request Forgery (CSRF)**: Finds missing nonce verification in forms and AJAX handlers
- **File Inclusion**: Detects unsafe file includes and path traversal vulnerabilities
- **Privilege Escalation**: Identifies insufficient capability checks and unsafe user switching
- **Information Disclosure**: Finds debug functions and exposed sensitive information
- **Unsafe Deserialization**: Detects unserialize() calls with user input
- **Command Injection**: Identifies system command execution with user-controlled data

### 📊 **Advanced Reporting**
- **Precise Line Numbers**: Exact location of each vulnerability
- **Code Context**: Shows surrounding code for better understanding
- **Severity Levels**: Critical, High, Medium, Low classifications
- **Multiple Export Formats**: JSON, CSV, HTML, XML reports
- **Detailed Remediation**: Specific fix recommendations for each issue

### 🎯 **Smart Detection Engine**
- **Pattern-based Analysis**: Uses comprehensive regex patterns for accurate detection
- **False Positive Reduction**: Intelligent filtering to minimize false alarms
- **WordPress-specific**: Tailored for WordPress coding standards and functions
- **Real-time Progress**: Live progress tracking during scans

### 💻 **User-Friendly Interface**
- **Modern Admin UI**: Clean, responsive design
- **Interactive Results**: Expandable components and detailed views
- **Progress Indicators**: Real-time scan progress with status updates
- **Help Documentation**: Built-in help and vulnerability explanations

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-query-security-scanner/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Tools > Security Scanner** to start scanning

## Usage

### Basic Scanning

1. **Scan Plugins**: Click "Scan All Plugins" to analyze all installed plugins
2. **Scan Themes**: Click "Scan All Themes" to analyze all installed themes
3. **View Results**: Expand components to see detailed vulnerability information
4. **Export Reports**: Generate reports in various formats for documentation

### Understanding Results

#### Severity Levels
- **🔴 Critical**: Immediate attention required - high risk of exploitation
- **🟠 High**: Should be addressed soon - significant security risk  
- **🟡 Medium**: Moderate risk - address when possible
- **🟢 Low**: Low risk - consider addressing for best practices

#### Vulnerability Information
Each detected issue includes:
- **Type**: Category of vulnerability (SQL Injection, XSS, etc.)
- **Location**: Exact file path and line number
- **Code**: The vulnerable code snippet
- **Context**: Surrounding code for better understanding
- **Description**: Explanation of the security risk
- **Remediation**: Specific steps to fix the issue

### Export Options

Generate detailed reports in multiple formats:
- **JSON**: Machine-readable format for integration
- **CSV**: Spreadsheet-compatible for analysis
- **HTML**: Formatted report for sharing
- **XML**: Structured data format

## Detected Vulnerability Types

### SQL Injection
```php
// ❌ Vulnerable
$wpdb->query("SELECT * FROM table WHERE id = " . $_GET['id']);

// ✅ Secure
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $_GET['id']);
```

### Cross-Site Scripting (XSS)
```php
// ❌ Vulnerable
echo $_GET['message'];

// ✅ Secure
echo esc_html($_GET['message']);
```

### CSRF Protection
```php
// ❌ Vulnerable
if ($_POST['action'] === 'delete') {
    wp_delete_user($_POST['user_id']);
}

// ✅ Secure
if (wp_verify_nonce($_POST['nonce'], 'delete_user') && $_POST['action'] === 'delete') {
    wp_delete_user($_POST['user_id']);
}
```

## Configuration

The plugin works out of the box with no configuration required. Advanced users can modify detection patterns in:
- `includes/class-security-patterns.php`

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Permissions**: Administrator access required

## File Structure

```
wp-query-security-scanner/
├── wp-query-security-scanner.php    # Main plugin file
├── includes/
│   ├── class-security-patterns.php   # Vulnerability patterns
│   ├── class-vulnerability-detector.php # Detection engine
│   └── class-report-generator.php    # Report generation
├── assets/
│   ├── admin-styles.css              # Admin interface styles
│   └── admin-scripts.js              # JavaScript functionality
├── templates/
│   └── admin-page.php                # Admin page template
└── README.md                         # Documentation
```

## Security Patterns

The scanner uses sophisticated regex patterns to detect:

- Unsanitized `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` usage
- Unprepared database queries
- Missing output escaping
- Absent nonce verification
- Unsafe file operations
- Insufficient capability checks
- Debug information exposure
- Command injection vectors

## Limitations

⚠️ **Important Notes:**

1. **Manual Review Required**: This scanner identifies potential issues but manual review is always necessary
2. **False Positives**: Some findings may be false positives requiring developer judgment
3. **Not Exhaustive**: The scanner may not catch all vulnerabilities
4. **Static Analysis**: Only analyzes code patterns, not runtime behavior

## Best Practices

1. **Test in Staging**: Always test fixes in a staging environment first
2. **Regular Scans**: Run scans after plugin/theme updates
3. **Keep Updated**: Maintain current WordPress, plugin, and theme versions
4. **Code Review**: Combine automated scanning with manual code review
5. **Security Training**: Educate developers on secure coding practices

## Troubleshooting

### Common Issues

**Scan Takes Too Long**
- Large codebases may take several minutes
- Ensure adequate server resources
- Consider scanning individual components

**Memory Issues**
- Increase PHP memory limit if needed
- Scan smaller batches of files

**Permission Errors**
- Ensure proper file permissions
- Verify administrator access

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Support

For support and bug reports:
- Create an issue on GitHub
- Include WordPress and PHP versions
- Provide scan results if relevant

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 2.0.0
- Complete rewrite with enhanced detection engine
- Added multiple vulnerability types
- Improved user interface
- Added export functionality
- Enhanced reporting with code context
- Added progress tracking

### Version 1.0.0
- Initial release
- Basic SQL injection detection
- Simple admin interface

---

**⚠️ Disclaimer**: This tool is for security assessment purposes. Always verify findings manually and test fixes thoroughly before deploying to production environments.
