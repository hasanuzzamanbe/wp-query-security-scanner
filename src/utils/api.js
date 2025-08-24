/**
 * WordPress AJAX API utilities
 */

// Get WordPress AJAX configuration
const getWpAjaxConfig = () => {
  return window.wpqss_ajax || {
    url: '/wp-admin/admin-ajax.php',
    nonce: 'dev-nonce-123',
    strings: {
      scanning: 'Scanning...',
      scan_complete: 'Scan Complete',
      scan_error: 'Scan Error',
      export_success: 'Report exported successfully',
      export_error: 'Export failed'
    }
  };
};

// Make AJAX request to WordPress
export const makeAjaxRequest = async (action, data = {}) => {
  const config = getWpAjaxConfig();
  const formData = new FormData();
  
  formData.append('action', action);
  formData.append('nonce', config.nonce);
  
  // Append data to form
  Object.keys(data).forEach(key => {
    const value = data[key];
    if (value !== null && value !== undefined) {
      formData.append(key, value);
    }
  });

  try {
    const response = await fetch(config.url, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin' // Include cookies for WordPress auth
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();
    
    // Log for debugging in development
    if (process.env.NODE_ENV === 'development') {
      console.log(`AJAX ${action}:`, { data, result });
    }
    
    return result;
  } catch (error) {
    console.error('AJAX request failed:', error);
    
    // Return mock data in development if WordPress is not available
    if (process.env.NODE_ENV === 'development') {
      return getMockResponse(action, data);
    }
    
    throw error;
  }
};

// Mock responses for development
const getMockResponse = (action, data) => {
  console.warn(`Using mock response for ${action}`);
  
  switch (action) {
    case 'wpqss_get_available_components':
      return {
        success: true,
        data: {
          'hello-dolly/hello.php': {
            name: 'Hello Dolly',
            version: '1.7.2'
          },
          'akismet/akismet.php': {
            name: 'Akismet Anti-Spam',
            version: '4.2.2'
          },
          'wp-query-security-scanner/wp-query-security-scanner.php': {
            name: 'WP Query Security Scanner',
            version: '2.0.0'
          }
        }
      };
      
    case 'wpqss_scan_plugins':
    case 'wpqss_scan_themes':
    case 'wpqss_scan_specific_plugin':
    case 'wpqss_scan_specific_theme':
      return {
        success: true,
        data: [
          {
            name: 'Test Plugin',
            type: 'plugin',
            total_vulnerabilities: 3,
            severity_counts: {
              critical: 1,
              high: 1,
              medium: 1,
              low: 0
            },
            files: [
              {
                file: 'test-plugin/test.php',
                vulnerabilities: [
                  {
                    type: 'SQL Injection',
                    severity: 'critical',
                    line: 42,
                    description: 'Unsafe database query detected',
                    code: '$wpdb->query("SELECT * FROM table WHERE id = " . $_GET["id"]);',
                    remediation: 'Use prepared statements or $wpdb->prepare()',
                    context: [
                      { line_number: 40, code: '// Get user input', is_vulnerable: false },
                      { line_number: 41, code: '$id = $_GET["id"];', is_vulnerable: false },
                      { line_number: 42, code: '$wpdb->query("SELECT * FROM table WHERE id = " . $_GET["id"]);', is_vulnerable: true },
                      { line_number: 43, code: '// Process results', is_vulnerable: false }
                    ]
                  },
                  {
                    type: 'XSS',
                    severity: 'high',
                    line: 58,
                    description: 'Unescaped output detected',
                    code: 'echo $_POST["message"];',
                    remediation: 'Use esc_html() or wp_kses()',
                    context: []
                  },
                  {
                    type: 'CSRF',
                    severity: 'medium',
                    line: 75,
                    description: 'Missing nonce verification',
                    code: 'if ($_POST["action"] == "save") {',
                    remediation: 'Add wp_verify_nonce() check',
                    context: []
                  }
                ]
              }
            ]
          }
        ]
      };
      
    case 'wpqss_get_scan_progress':
      return {
        success: true,
        data: {
          progress: Math.min(100, Math.floor(Math.random() * 100) + 10),
          message: 'Scanning files...',
          status: 'running'
        }
      };
      
    case 'wpqss_export_report':
      return {
        success: true,
        data: {
          download_url: '#mock-download-url',
          filename: `security-report-${Date.now()}.${data.format || 'json'}`
        }
      };
      
    default:
      return {
        success: false,
        data: `Unknown action: ${action}`
      };
  }
};

// Utility functions for API responses
export const isSuccessResponse = (response) => {
  return response && response.success === true;
};

export const getResponseData = (response) => {
  return response && response.data ? response.data : null;
};

export const getResponseError = (response) => {
  if (!response) return 'Unknown error';
  if (response.success === false) return response.data || 'Request failed';
  return null;
};

// WordPress-specific API helpers
export const wpAjax = {
  // Scan operations
  scanPlugins: () => makeAjaxRequest('wpqss_scan_plugins'),
  scanThemes: () => makeAjaxRequest('wpqss_scan_themes'),
  scanSpecificPlugin: (plugin) => makeAjaxRequest('wpqss_scan_specific_plugin', { plugin_folder: plugin }),
  scanSpecificTheme: (theme) => makeAjaxRequest('wpqss_scan_specific_theme', { theme_folder: theme }),
  
  // Component operations
  getAvailableComponents: (type) => makeAjaxRequest('wpqss_get_available_components', { type }),
  
  // Progress operations
  getScanProgress: () => makeAjaxRequest('wpqss_get_scan_progress'),
  
  // Export operations
  exportReport: (format, results, filterType = 'all') => makeAjaxRequest('wpqss_export_report', {
    format,
    scan_results: JSON.stringify(results),
    filter_type: filterType,
    filename_suffix: filterType === 'all' ? '' : `_${filterType}_severity`
  })
};

// Request interceptor for debugging
export const enableRequestLogging = () => {
  const originalFetch = window.fetch;
  window.fetch = async (...args) => {
    console.log('Fetch request:', args);
    const response = await originalFetch(...args);
    console.log('Fetch response:', response);
    return response;
  };
};

// Disable request logging
export const disableRequestLogging = () => {
  if (window.fetch.toString().includes('originalFetch')) {
    window.fetch = window.fetch.originalFetch || window.fetch;
  }
};
