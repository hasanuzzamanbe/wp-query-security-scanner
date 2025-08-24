import { computed } from 'vue';

export function useFiltering(state) {
  
  // Calculate summary from scan results
  const calculateSummary = (results) => {
    if (!results || !Array.isArray(results)) {
      return {
        totalComponents: 0,
        totalVulnerabilities: 0,
        severityCounts: {
          critical: 0,
          high: 0,
          medium: 0,
          low: 0
        }
      };
    }

    const summary = {
      totalComponents: results.length,
      totalVulnerabilities: 0,
      severityCounts: {
        critical: 0,
        high: 0,
        medium: 0,
        low: 0
      }
    };

    results.forEach(component => {
      summary.totalVulnerabilities += component.total_vulnerabilities || 0;
      Object.keys(summary.severityCounts).forEach(severity => {
        summary.severityCounts[severity] += (component.severity_counts && component.severity_counts[severity]) || 0;
      });
    });

    return summary;
  };

  // Computed summary for current results
  const summary = computed(() => {
    return calculateSummary(state.currentScanResults);
  });

  // Computed summary for filtered results
  const filteredSummary = computed(() => {
    return calculateSummary(state.filteredResults);
  });

  // Computed severity counts
  const severityCounts = computed(() => {
    if (!summary.value) {
      return { all: 0, critical: 0, high: 0, medium: 0, low: 0 };
    }
    
    return {
      all: summary.value.totalVulnerabilities,
      critical: summary.value.severityCounts.critical,
      high: summary.value.severityCounts.high,
      medium: summary.value.severityCounts.medium,
      low: summary.value.severityCounts.low
    };
  });

  // Computed results count text
  const resultsCountText = computed(() => {
    const count = filteredSummary.value.totalVulnerabilities;
    if (state.currentFilter === 'all') {
      return `${count} vulnerabilities found`;
    }
    return `${count} ${state.currentFilter} severity vulnerabilities`;
  });

  // Apply severity filter
  const applyFilter = (severity) => {
    state.currentFilter = severity;

    if (!state.currentScanResults) {
      return;
    }

    if (severity === 'all') {
      state.filteredResults = state.currentScanResults;
      return;
    }

    // Filter results by severity
    state.filteredResults = state.currentScanResults.map(component => {
      const filteredComponent = { ...component };
      
      // Filter files and vulnerabilities
      filteredComponent.files = component.files.map(file => {
        const filteredFile = { ...file };
        filteredFile.vulnerabilities = file.vulnerabilities.filter(vuln => 
          vuln.severity === severity
        );
        return filteredFile;
      }).filter(file => file.vulnerabilities.length > 0);

      // Recalculate counts for filtered component
      filteredComponent.total_vulnerabilities = 0;
      filteredComponent.severity_counts = {
        critical: 0, 
        high: 0, 
        medium: 0, 
        low: 0
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
  };

  // Reset filter to show all results
  const resetFilter = () => {
    applyFilter('all');
  };

  // Get filtered results by multiple severities
  const getFilteredBySeverities = (severities) => {
    if (!state.currentScanResults || !Array.isArray(severities)) {
      return [];
    }

    return state.currentScanResults.map(component => {
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
  };

  return {
    summary,
    filteredSummary,
    severityCounts,
    resultsCountText,
    applyFilter,
    resetFilter,
    getFilteredBySeverities,
    calculateSummary
  };
}
