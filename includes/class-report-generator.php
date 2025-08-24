<?php
/**
 * Report Generator Class
 * 
 * Generates detailed security reports in various formats
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

class WPQSS_Report_Generator {
    
    private $upload_dir;
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/wpqss-reports';
        
        // Ensure reports directory exists
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            file_put_contents($this->upload_dir . '/.htaccess', "deny from all\n");
        }
    }
    
    /**
     * Generate security report
     * 
     * @param array $scan_results
     * @param string $format
     * @return array
     */
    public function generate_report($scan_results, $format = 'json') {
        $timestamp = current_time('Y-m-d_H-i-s');
        $filename = "security-report_{$timestamp}.{$format}";
        $file_path = $this->upload_dir . '/' . $filename;
        
        switch ($format) {
            case 'json':
                $content = $this->generate_json_report($scan_results);
                break;
            case 'csv':
                $content = $this->generate_csv_report($scan_results);
                break;
            case 'html':
                $content = $this->generate_html_report($scan_results);
                break;
            case 'xml':
                $content = $this->generate_xml_report($scan_results);
                break;
            default:
                $content = $this->generate_json_report($scan_results);
                $format = 'json';
        }
        
        file_put_contents($file_path, $content);
        
        return [
            'filename' => $filename,
            'path' => $file_path,
            'url' => $this->get_secure_download_url($filename),
            'size' => filesize($file_path),
            'format' => $format
        ];
    }
    
    /**
     * Generate JSON report
     * 
     * @param array $scan_results
     * @return string
     */
    private function generate_json_report($scan_results) {
        $report = [
            'scan_info' => [
                'timestamp' => current_time('c'),
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'scanner_version' => WPQSS_VERSION,
                'site_url' => get_site_url(),
                'total_components' => count($scan_results),
                'total_vulnerabilities' => $this->count_total_vulnerabilities($scan_results)
            ],
            'summary' => $this->generate_summary($scan_results),
            'components' => $scan_results
        ];
        
        return wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Generate CSV report
     * 
     * @param array $scan_results
     * @return string
     */
    private function generate_csv_report($scan_results) {
        $csv_data = [];
        
        // CSV Headers
        $csv_data[] = [
            'Component',
            'Type',
            'File',
            'Line',
            'Vulnerability Type',
            'Severity',
            'Description',
            'Code',
            'Remediation'
        ];
        
        foreach ($scan_results as $component) {
            foreach ($component['files'] as $file) {
                foreach ($file['vulnerabilities'] as $vuln) {
                    $csv_data[] = [
                        $component['name'],
                        $component['type'],
                        $file['file'],
                        $vuln['line'],
                        $vuln['type'],
                        $vuln['severity'],
                        $vuln['description'],
                        $this->clean_code_for_csv($vuln['code']),
                        $vuln['remediation']
                    ];
                }
            }
        }
        
        return $this->array_to_csv($csv_data);
    }
    
    /**
     * Generate HTML report
     * 
     * @param array $scan_results
     * @return string
     */
    private function generate_html_report($scan_results) {
        $summary = $this->generate_summary($scan_results);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>WordPress Security Scan Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
                .summary-card { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; }
                .severity-critical { color: #dc3545; font-weight: bold; }
                .severity-high { color: #fd7e14; font-weight: bold; }
                .severity-medium { color: #ffc107; font-weight: bold; }
                .severity-low { color: #28a745; font-weight: bold; }
                .component { margin-bottom: 30px; border: 1px solid #ddd; border-radius: 5px; }
                .component-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd; }
                .vulnerability { margin: 10px; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
                .vulnerability.critical { border-left-color: #dc3545; }
                .vulnerability.high { border-left-color: #fd7e14; }
                .vulnerability.medium { border-left-color: #ffc107; }
                .vulnerability.low { border-left-color: #28a745; }
                .code { background: #f1f1f1; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
                .context { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; }
                .context-line { margin: 2px 0; }
                .context-line.vulnerable { background: #ffebee; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>WordPress Security Scan Report</h1>
                <p><strong>Generated:</strong> <?php echo current_time('F j, Y g:i A'); ?></p>
                <p><strong>Site:</strong> <?php echo esc_html(get_site_url()); ?></p>
                <p><strong>WordPress Version:</strong> <?php echo esc_html(get_bloginfo('version')); ?></p>
            </div>
            
            <div class="summary">
                <div class="summary-card">
                    <h3>Total Components</h3>
                    <p style="font-size: 24px; margin: 0;"><?php echo count($scan_results); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total Vulnerabilities</h3>
                    <p style="font-size: 24px; margin: 0;"><?php echo $this->count_total_vulnerabilities($scan_results); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Critical</h3>
                    <p style="font-size: 24px; margin: 0;" class="severity-critical"><?php echo $summary['severity_counts']['critical']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>High</h3>
                    <p style="font-size: 24px; margin: 0;" class="severity-high"><?php echo $summary['severity_counts']['high']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Medium</h3>
                    <p style="font-size: 24px; margin: 0;" class="severity-medium"><?php echo $summary['severity_counts']['medium']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Low</h3>
                    <p style="font-size: 24px; margin: 0;" class="severity-low"><?php echo $summary['severity_counts']['low']; ?></p>
                </div>
            </div>
            
            <?php foreach ($scan_results as $component): ?>
            <div class="component">
                <div class="component-header">
                    <h2><?php echo esc_html($component['name']); ?> (<?php echo esc_html(ucfirst($component['type'])); ?>)</h2>
                    <p>Total Vulnerabilities: <?php echo $component['total_vulnerabilities']; ?></p>
                </div>
                
                <?php foreach ($component['files'] as $file): ?>
                    <h3 style="margin: 15px; color: #666;">File: <?php echo esc_html($file['file']); ?></h3>
                    
                    <?php foreach ($file['vulnerabilities'] as $vuln): ?>
                    <div class="vulnerability <?php echo esc_attr($vuln['severity']); ?>">
                        <h4 class="severity-<?php echo esc_attr($vuln['severity']); ?>">
                            <?php echo esc_html($vuln['type']); ?> (<?php echo esc_html(ucfirst($vuln['severity'])); ?>)
                        </h4>
                        <p><strong>Line <?php echo $vuln['line']; ?>:</strong> <?php echo esc_html($vuln['description']); ?></p>
                        
                        <div class="code">
                            <strong>Vulnerable Code:</strong><br>
                            <?php echo esc_html($vuln['code']); ?>
                        </div>
                        
                        <?php if (!empty($vuln['context'])): ?>
                        <div class="context">
                            <strong>Context:</strong><br>
                            <?php foreach ($vuln['context'] as $context_line): ?>
                            <div class="context-line <?php echo $context_line['is_vulnerable'] ? 'vulnerable' : ''; ?>">
                                <?php echo sprintf('%3d: %s', $context_line['line_number'], esc_html($context_line['code'])); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p><strong>Remediation:</strong> <?php echo esc_html($vuln['remediation']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center;">
                <p><small>Generated by WP Query Security Scanner v<?php echo WPQSS_VERSION; ?></small></p>
            </div>
        </body>
        </html>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Generate XML report
     * 
     * @param array $scan_results
     * @return string
     */
    private function generate_xml_report($scan_results) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><security_report></security_report>');
        
        // Add scan info
        $scan_info = $xml->addChild('scan_info');
        $scan_info->addChild('timestamp', current_time('c'));
        $scan_info->addChild('wordpress_version', get_bloginfo('version'));
        $scan_info->addChild('php_version', PHP_VERSION);
        $scan_info->addChild('scanner_version', WPQSS_VERSION);
        $scan_info->addChild('site_url', get_site_url());
        
        // Add summary
        $summary = $this->generate_summary($scan_results);
        $summary_xml = $xml->addChild('summary');
        $summary_xml->addChild('total_components', count($scan_results));
        $summary_xml->addChild('total_vulnerabilities', $this->count_total_vulnerabilities($scan_results));
        
        $severity_counts = $summary_xml->addChild('severity_counts');
        foreach ($summary['severity_counts'] as $severity => $count) {
            $severity_counts->addChild($severity, $count);
        }
        
        // Add components
        $components_xml = $xml->addChild('components');
        foreach ($scan_results as $component) {
            $component_xml = $components_xml->addChild('component');
            $component_xml->addChild('name', htmlspecialchars($component['name']));
            $component_xml->addChild('type', $component['type']);
            $component_xml->addChild('total_vulnerabilities', $component['total_vulnerabilities']);
            
            $files_xml = $component_xml->addChild('files');
            foreach ($component['files'] as $file) {
                $file_xml = $files_xml->addChild('file');
                $file_xml->addChild('path', htmlspecialchars($file['file']));
                
                $vulnerabilities_xml = $file_xml->addChild('vulnerabilities');
                foreach ($file['vulnerabilities'] as $vuln) {
                    $vuln_xml = $vulnerabilities_xml->addChild('vulnerability');
                    $vuln_xml->addChild('type', htmlspecialchars($vuln['type']));
                    $vuln_xml->addChild('severity', $vuln['severity']);
                    $vuln_xml->addChild('line', $vuln['line']);
                    $vuln_xml->addChild('description', htmlspecialchars($vuln['description']));
                    $vuln_xml->addChild('code', htmlspecialchars($vuln['code']));
                    $vuln_xml->addChild('remediation', htmlspecialchars($vuln['remediation']));
                }
            }
        }
        
        return $xml->asXML();
    }
    
    /**
     * Generate summary statistics
     * 
     * @param array $scan_results
     * @return array
     */
    private function generate_summary($scan_results) {
        $summary = [
            'total_components' => count($scan_results),
            'total_vulnerabilities' => 0,
            'severity_counts' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ],
            'vulnerability_types' => []
        ];
        
        foreach ($scan_results as $component) {
            $summary['total_vulnerabilities'] += $component['total_vulnerabilities'];
            
            foreach ($component['severity_counts'] as $severity => $count) {
                $summary['severity_counts'][$severity] += $count;
            }
            
            foreach ($component['files'] as $file) {
                foreach ($file['vulnerabilities'] as $vuln) {
                    $type = $vuln['category'];
                    if (!isset($summary['vulnerability_types'][$type])) {
                        $summary['vulnerability_types'][$type] = 0;
                    }
                    $summary['vulnerability_types'][$type]++;
                }
            }
        }
        
        return $summary;
    }
    
    /**
     * Count total vulnerabilities across all components
     * 
     * @param array $scan_results
     * @return int
     */
    private function count_total_vulnerabilities($scan_results) {
        $total = 0;
        foreach ($scan_results as $component) {
            $total += $component['total_vulnerabilities'];
        }
        return $total;
    }
    
    /**
     * Convert array to CSV string
     * 
     * @param array $data
     * @return string
     */
    private function array_to_csv($data) {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Clean code for CSV output
     * 
     * @param string $code
     * @return string
     */
    private function clean_code_for_csv($code) {
        return str_replace(["\n", "\r", "\t"], [' ', ' ', ' '], $code);
    }
    
    /**
     * Get secure download URL for report
     * 
     * @param string $filename
     * @return string
     */
    private function get_secure_download_url($filename) {
        return add_query_arg([
            'action' => 'wpqss_download_report',
            'file' => $filename,
            'nonce' => wp_create_nonce('wpqss_download_' . $filename)
        ], admin_url('admin-ajax.php'));
    }
}
