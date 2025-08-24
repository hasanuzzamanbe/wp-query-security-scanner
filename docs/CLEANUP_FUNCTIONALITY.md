# 🧹 Report Cleanup Functionality

## 📋 **Overview**

The WordPress Query Security Scanner now includes comprehensive cleanup functionality for the `wpqss-reports` uploads directory. This prevents the accumulation of old report files that can consume disk space and ensures optimal performance.

## ✨ **Key Features**

### **🔄 Automatic Cleanup**
- **Daily scheduled cleanup** runs automatically
- **Configurable retention policies** (age and count limits)
- **Background processing** with minimal performance impact
- **Error logging** for debugging and monitoring

### **🎛️ Manual Cleanup Controls**
- **Interactive cleanup interface** in the admin panel
- **Real-time statistics** showing current storage usage
- **Customizable cleanup parameters** for immediate cleanup
- **Progress feedback** with detailed results

### **🛡️ Security Features**
- **Path validation** prevents directory traversal attacks
- **File type restrictions** only processes report files
- **Admin capability checks** for all cleanup operations
- **Nonce verification** for AJAX requests

## 🏗️ **Implementation Details**

### **1. Report Generator Class Enhancements**

#### **Automatic Cleanup on Initialization**
```php
public function __construct() {
    // ... existing code ...
    
    // Clean up old reports on initialization
    $this->cleanup_old_reports();
}
```

#### **Core Cleanup Method**
```php
public function cleanup_old_reports($max_age_hours = 24, $max_files = 50) {
    // Configurable cleanup with age and count limits
    // Returns detailed results including deleted count and freed space
}
```

#### **Security-First File Operations**
```php
private function delete_report_file($file_path) {
    // Security check: ensure file is in our reports directory
    $real_path = realpath($file_path);
    $real_upload_dir = realpath($this->upload_dir);
    
    if (!$real_path || !$real_upload_dir || strpos($real_path, $real_upload_dir) !== 0) {
        return false; // Security violation
    }
    
    return unlink($file_path);
}
```

### **2. WordPress Integration**

#### **Scheduled Cleanup Hook**
```php
// Activation: Schedule daily cleanup
if (!wp_next_scheduled('wpqss_cleanup_reports')) {
    wp_schedule_event(time(), 'daily', 'wpqss_cleanup_reports');
}

// Cleanup handler
add_action('wpqss_cleanup_reports', function() {
    $report_generator = new WPQSS_Report_Generator();
    $results = $report_generator->cleanup_old_reports(24, 50);
    
    // Log results for monitoring
    if ($results['deleted_count'] > 0) {
        error_log(sprintf(
            'WPQSS: Automatic cleanup completed - %d files deleted (%s freed)',
            $results['deleted_count'],
            $report_generator->format_file_size($results['deleted_size'])
        ));
    }
});
```

#### **AJAX Endpoints**
- **`wpqss_cleanup_reports`**: Manual cleanup with custom parameters
- **`wpqss_get_cleanup_stats`**: Real-time statistics and file information

### **3. Vue.js Frontend Integration**

#### **Cleanup Interface Component**
```vue
<template>
  <div class="wpqss-cleanup-section">
    <!-- Real-time statistics display -->
    <div class="wpqss-cleanup-stats">
      <div class="wpqss-stats-grid">
        <div class="wpqss-stat-item">
          <span class="wpqss-stat-label">Total Files:</span>
          <span class="wpqss-stat-value">{{ cleanupStats.total_files }}</span>
        </div>
        <!-- More stats... -->
      </div>
    </div>
    
    <!-- Interactive cleanup controls -->
    <div class="wpqss-cleanup-controls">
      <select v-model="cleanupOptions.maxAge">
        <option value="24">24 hours (default)</option>
        <!-- More options... -->
      </select>
      
      <button @click="performCleanup">🧹 Clean Up Reports</button>
    </div>
  </div>
</template>
```

## 🎯 **Cleanup Policies**

### **Default Settings**
- **Age Limit**: 24 hours (files older than this are deleted)
- **Count Limit**: 50 files maximum (oldest files deleted first)
- **Schedule**: Daily automatic cleanup
- **File Types**: Only `.json`, `.csv`, `.html`, `.xml` report files

### **Configurable Options**

#### **Age-Based Cleanup**
- **1 hour**: For testing environments
- **6 hours**: For development environments
- **24 hours**: Default for production
- **3 days**: For environments with infrequent scans
- **1 week**: For archival purposes

#### **Count-Based Cleanup**
- **10 files**: Minimal storage
- **25 files**: Light usage
- **50 files**: Default balanced approach
- **100 files**: Heavy usage environments
- **No limit**: Age-only cleanup

### **Cleanup Priority**
1. **Age-based deletion**: Files older than specified age
2. **Count-based deletion**: Excess files beyond the limit
3. **Newest files preserved**: Most recent files are always kept

## 📊 **Statistics and Monitoring**

### **Real-Time Statistics**
```javascript
{
  total_files: 15,
  total_size: "2.4 MB",
  total_size_bytes: 2516582,
  files_by_type: {
    json: 8,
    csv: 4,
    html: 2,
    xml: 1
  },
  oldest_file: {
    name: "security-report-20240820-143022.json",
    age_days: 4.2
  },
  newest_file: {
    name: "security-report-20240824-095245.html",
    age_hours: 0.5
  }
}
```

### **Cleanup Results**
```javascript
{
  deleted_count: 8,
  deleted_size: 1048576, // bytes
  kept_count: 7,
  errors: []
}
```

## 🔧 **Configuration Options**

### **WordPress Hooks**
```php
// Customize cleanup schedule
add_filter('wpqss_cleanup_schedule', function($schedule) {
    return 'hourly'; // or 'twicedaily', 'weekly'
});

// Customize default cleanup parameters
add_filter('wpqss_default_cleanup_age', function($hours) {
    return 48; // Keep files for 48 hours
});

add_filter('wpqss_default_cleanup_count', function($count) {
    return 100; // Keep maximum 100 files
});
```

### **Environment Variables**
```php
// In wp-config.php
define('WPQSS_CLEANUP_AGE_HOURS', 72);
define('WPQSS_CLEANUP_MAX_FILES', 25);
define('WPQSS_CLEANUP_DISABLED', false);
```

## 🛡️ **Security Considerations**

### **Path Validation**
- **Realpath verification**: Prevents directory traversal
- **Directory containment**: Files must be within reports directory
- **File type validation**: Only processes known report file types

### **Access Control**
- **Admin capability required**: `manage_options` capability check
- **Nonce verification**: All AJAX requests verified
- **Error handling**: Graceful failure with logging

### **File System Safety**
- **Atomic operations**: Individual file deletions
- **Error recovery**: Continues on individual file failures
- **Logging**: Comprehensive error and success logging

## 📈 **Performance Impact**

### **Automatic Cleanup**
- **Scheduled execution**: Runs during low-traffic periods
- **Minimal resource usage**: Processes files individually
- **Non-blocking**: Doesn't affect user experience
- **Error resilience**: Continues operation on individual failures

### **Manual Cleanup**
- **AJAX-based**: Non-blocking user interface
- **Progress feedback**: Real-time status updates
- **Cancellable**: User can navigate away safely
- **Resource efficient**: Optimized file operations

## 🔮 **Future Enhancements**

### **Planned Features**
- **Compression before deletion**: Archive old reports
- **Cloud storage integration**: Move old reports to cloud storage
- **Advanced filtering**: Cleanup by scan type or severity
- **Cleanup scheduling**: Custom cleanup schedules per user

### **Monitoring Improvements**
- **Dashboard widgets**: Cleanup statistics in WordPress dashboard
- **Email notifications**: Alerts for cleanup failures or large deletions
- **Cleanup history**: Track cleanup operations over time
- **Storage analytics**: Detailed storage usage trends

## 📋 **Usage Examples**

### **Manual Cleanup via Interface**
1. Navigate to **Tools > Security Scanner**
2. Scroll to **Report Cleanup** section
3. Review current statistics
4. Adjust cleanup parameters if needed
5. Click **🧹 Clean Up Reports**
6. Confirm the action
7. Review cleanup results

### **Programmatic Cleanup**
```php
// Get report generator instance
$report_generator = new WPQSS_Report_Generator();

// Perform cleanup with custom parameters
$results = $report_generator->cleanup_old_reports(
    48,  // Keep files for 48 hours
    25   // Keep maximum 25 files
);

// Check results
if ($results['deleted_count'] > 0) {
    echo sprintf(
        'Cleaned up %d files, freed %s',
        $results['deleted_count'],
        $report_generator->format_file_size($results['deleted_size'])
    );
}
```

### **Force Complete Cleanup**
```php
// Delete all report files
$results = $report_generator->cleanup_all_reports();
```

## ✅ **Benefits**

### **🗄️ Storage Management**
- **Prevents disk space exhaustion** from accumulated reports
- **Configurable retention policies** for different environments
- **Automatic maintenance** without manual intervention

### **⚡ Performance Optimization**
- **Faster directory operations** with fewer files
- **Reduced backup sizes** by excluding old reports
- **Improved file system performance**

### **🛡️ Security & Compliance**
- **Data retention compliance** with configurable policies
- **Secure file operations** with path validation
- **Audit trail** with comprehensive logging

### **👥 User Experience**
- **Visual feedback** with real-time statistics
- **Easy configuration** through admin interface
- **Non-disruptive operation** with background processing

---

The cleanup functionality ensures that the WP Query Security Scanner maintains optimal performance while providing flexible storage management options for different deployment scenarios.
