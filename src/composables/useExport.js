import { makeAjaxRequest } from '../utils/api';
import { useNotifications } from './useNotifications';

export function useExport(state) {
  const { showNotice } = useNotifications();

  // Export results with specified filter type
  const exportResults = async (results, filterType) => {
    if (!results || results.length === 0) {
      showNotice('No results to export', 'error');
      return;
    }

    const filename_suffix = filterType === 'all' ? '' : `_${filterType}_severity`;

    try {
      const response = await makeAjaxRequest('wpqss_export_report', {
        format: state.exportFormat,
        scan_results: JSON.stringify(results),
        filter_type: filterType,
        filename_suffix: filename_suffix
      });

      if (response.success && response.data.download_url) {
        // Trigger download
        window.location.href = response.data.download_url;
        
        const message = filterType === 'all' 
          ? 'All results exported successfully'
          : `${filterType} severity results exported successfully`;
        showNotice(message, 'success');
      } else {
        showNotice(response.data || 'Export failed', 'error');
      }
    } catch (error) {
      showNotice('Export error: ' + error.message, 'error');
    }
  };

  // Export all results
  const exportAllResults = () => {
    exportResults(state.currentScanResults, 'all');
  };

  // Export filtered results
  const exportFilteredResults = () => {
    exportResults(state.filteredResults, state.currentFilter);
  };

  // Export specific severity levels
  const exportBySeverity = (severity) => {
    if (!state.currentScanResults) {
      showNotice('No results to export', 'error');
      return;
    }

    // Filter results by severity
    const filteredResults = state.currentScanResults.map(component => {
      const filteredComponent = { ...component };
      
      filteredComponent.files = component.files.map(file => {
        const filteredFile = { ...file };
        filteredFile.vulnerabilities = file.vulnerabilities.filter(vuln => 
          vuln.severity === severity
        );
        return filteredFile;
      }).filter(file => file.vulnerabilities.length > 0);

      // Recalculate counts
      filteredComponent.total_vulnerabilities = 0;
      filteredComponent.severity_counts = {
        critical: 0, high: 0, medium: 0, low: 0
      };

      filteredComponent.files.forEach(file => {
        file.vulnerabilities.forEach(vuln => {
          filteredComponent.total_vulnerabilities++;
          if (filteredComponent.severity_counts[vuln.severity] !== undefined) {
            filteredComponent.severity_counts[vuln.severity]++;
          }
        });
      });

      return filteredComponent;
    }).filter(component => component.total_vulnerabilities > 0);

    exportResults(filteredResults, severity);
  };

  // Export multiple severity levels
  const exportBySeverities = (severities) => {
    if (!state.currentScanResults || !Array.isArray(severities)) {
      showNotice('No results to export', 'error');
      return;
    }

    const filteredResults = state.currentScanResults.map(component => {
      const filteredComponent = { ...component };
      
      filteredComponent.files = component.files.map(file => {
        const filteredFile = { ...file };
        filteredFile.vulnerabilities = file.vulnerabilities.filter(vuln => 
          severities.includes(vuln.severity)
        );
        return filteredFile;
      }).filter(file => file.vulnerabilities.length > 0);

      // Recalculate counts
      filteredComponent.total_vulnerabilities = 0;
      filteredComponent.severity_counts = {
        critical: 0, high: 0, medium: 0, low: 0
      };

      filteredComponent.files.forEach(file => {
        file.vulnerabilities.forEach(vuln => {
          filteredComponent.total_vulnerabilities++;
          if (filteredComponent.severity_counts[vuln.severity] !== undefined) {
            filteredComponent.severity_counts[vuln.severity]++;
          }
        });
      });

      return filteredComponent;
    }).filter(component => component.total_vulnerabilities > 0);

    const filterName = severities.join('_');
    exportResults(filteredResults, filterName);
  };

  // Get available export formats
  const getAvailableFormats = () => {
    return [
      { value: 'json', label: 'JSON', description: 'Machine-readable format' },
      { value: 'csv', label: 'CSV', description: 'Spreadsheet format' },
      { value: 'html', label: 'HTML', description: 'Web page format' },
      { value: 'xml', label: 'XML', description: 'Structured data format' }
    ];
  };

  // Validate export format
  const isValidFormat = (format) => {
    const validFormats = ['json', 'csv', 'html', 'xml'];
    return validFormats.includes(format);
  };

  // Set export format
  const setExportFormat = (format) => {
    if (isValidFormat(format)) {
      state.exportFormat = format;
    } else {
      console.warn('Invalid export format:', format);
    }
  };

  return {
    exportResults,
    exportAllResults,
    exportFilteredResults,
    exportBySeverity,
    exportBySeverities,
    getAvailableFormats,
    isValidFormat,
    setExportFormat
  };
}
