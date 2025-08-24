<template>
  <div class="wpqss-help-section">
    <h2>Important Notes</h2>
    <div class="wpqss-help-content">
      <div class="wpqss-help-item">
        <h3>Manual Review Required</h3>
        <p>
          This scanner identifies potential security issues, but manual review is always required.
          Some findings may be false positives, and the scanner may not catch all vulnerabilities.
        </p>
      </div>

      <div class="wpqss-help-item">
        <h3>Test in Staging</h3>
        <p>
          Always test any security fixes in a staging environment before applying them to your live site.
        </p>
      </div>

      <div class="wpqss-help-item">
        <h3>Keep Updated</h3>
        <p>
          Regularly update WordPress, plugins, and themes to ensure you have the latest security patches.
        </p>
      </div>

      <div class="wpqss-help-item">
        <h3>Severity Levels</h3>
        <ul>
          <li>
            <strong class="severity-critical">Critical</strong>:
            Immediate attention required - high risk of exploitation
          </li>
          <li>
            <strong class="severity-high">High</strong>:
            Should be addressed soon - significant security risk
          </li>
          <li>
            <strong class="severity-medium">Medium</strong>:
            Moderate risk - address when possible
          </li>
          <li>
            <strong class="severity-low">Low</strong>:
            Low risk - consider addressing for best practices
          </li>
        </ul>
      </div>
    </div>
    
    <!-- Cleanup Section -->
    <div class="wpqss-cleanup-section">
      <h2>Report Cleanup</h2>
      <div class="wpqss-cleanup-content">
        <div class="wpqss-cleanup-info">
          <p>Clean up old security scan reports to free up disk space. Reports older than 24 hours will be automatically removed.</p>
          <div class="wpqss-cleanup-stats" v-if="cleanupStats">
            <span class="wpqss-stat-item">
              📁 {{ cleanupStats.total_files }} files
            </span>
            <span class="wpqss-stat-item">
              💾 {{ cleanupStats.total_size }}
            </span>
          </div>
        </div>

        <div class="wpqss-cleanup-actions">
          <button
            @click="cleanupOldReports"
            :disabled="isCleaningUp"
            class="wpqss-btn wpqss-btn-outline"
          >
            <span v-if="isCleaningUp">🔄 Cleaning...</span>
            <span v-else>🧹 Clean Old Reports (24h+)</span>
          </button>

          <button
            @click="cleanupAllReports"
            :disabled="isCleaningUp"
            class="wpqss-btn wpqss-btn-outline"
          >
            <span v-if="isCleaningUp">🔄 Cleaning...</span>
            <span v-else>🗑️ Delete All Reports</span>
          </button>

          <button
            @click="refreshStats"
            :disabled="isLoadingStats"
            class="wpqss-btn wpqss-btn-outline"
          >
            <span v-if="isLoadingStats">🔄 Loading...</span>
            <span v-else>📊 Refresh</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="wpqss-footer">
      <p>
        WP Query Security Scanner v2.0 -
        <a href="https://github.com/hasanuzzamanbe/wp-query-security-scanner" target="_blank">
          GitHub Repository
        </a>
      </p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { makeAjaxRequest } from '../utils/api';

export default {
  name: 'HelpSection',
  setup() {
    const cleanupStats = ref(null);
    const isLoadingStats = ref(false);
    const isCleaningUp = ref(false);

    const refreshStats = async () => {
      isLoadingStats.value = true;
      try {
        const response = await makeAjaxRequest('wpqss_get_cleanup_stats');
        if (response.success) {
          cleanupStats.value = response.data;
        } else {
          console.error('Failed to load cleanup stats:', response.data);
        }
      } catch (error) {
        console.error('Error loading cleanup stats:', error);
      } finally {
        isLoadingStats.value = false;
      }
    };

    const cleanupOldReports = async () => {
      if (!confirm('Clean up reports older than 24 hours? This action cannot be undone.')) {
        return;
      }

      isCleaningUp.value = true;
      try {
        const response = await makeAjaxRequest('wpqss_cleanup_reports', {
          cleanup_type: 'old'
        });

        if (response.success) {
          alert(response.data.message);
          await refreshStats();
        } else {
          alert('Cleanup failed: ' + response.data);
        }
      } catch (error) {
        console.error('Cleanup error:', error);
        alert('Cleanup failed: ' + error.message);
      } finally {
        isCleaningUp.value = false;
      }
    };

    const cleanupAllReports = async () => {
      if (!confirm('Delete ALL report files? This will remove all saved security scan reports and cannot be undone.')) {
        return;
      }

      isCleaningUp.value = true;
      try {
        const response = await makeAjaxRequest('wpqss_cleanup_reports', {
          cleanup_type: 'all'
        });

        if (response.success) {
          alert(response.data.message);
          await refreshStats();
        } else {
          alert('Cleanup failed: ' + response.data);
        }
      } catch (error) {
        console.error('Cleanup error:', error);
        alert('Cleanup failed: ' + error.message);
      } finally {
        isCleaningUp.value = false;
      }
    };

    // Load stats on component mount
    onMounted(() => {
      refreshStats();
    });

    return {
      cleanupStats,
      isLoadingStats,
      isCleaningUp,
      refreshStats,
      cleanupOldReports,
      cleanupAllReports
    };
  }
};
</script>

<style scoped>
.wpqss-help-section {
  margin-top: 40px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.wpqss-help-section h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 20px;
  font-weight: 600;
}

.wpqss-help-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.wpqss-help-item h3 {
  margin-top: 0;
  margin-bottom: 10px;
  color: #2c3e50;
  font-size: 16px;
  font-weight: 600;
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

.severity-critical { 
  color: #dc3545; 
}

.severity-high { 
  color: #fd7e14; 
}

.severity-medium { 
  color: #ffc107; 
}

.severity-low { 
  color: #28a745; 
}

.wpqss-footer {
  padding-top: 20px;
  border-top: 1px solid #ddd;
  text-align: center;
  color: #666;
  font-size: 14px;
}

.wpqss-footer a {
  color: #667eea;
  text-decoration: none;
}

.wpqss-footer a:hover {
  text-decoration: underline;
}

/* Cleanup Section Styles */
.wpqss-cleanup-section {
  margin-top: 40px;
  padding: 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 12px;
  border: 1px solid #0ea5e9;
}

.wpqss-cleanup-section h2 {
  margin-top: 0;
  margin-bottom: 16px;
  color: #0c4a6e;
  font-size: 18px;
  font-weight: 600;
}

.wpqss-cleanup-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.wpqss-cleanup-info{
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.wpqss-cleanup-info p {
  margin: 0 0 12px 0;
  color: #0c4a6e;
  font-size: 14px;
  line-height: 1.5;
}

.wpqss-cleanup-stats {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.wpqss-stat-item {
  background: rgba(255, 255, 255, 0.8);
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid rgba(14, 165, 233, 0.2);
  font-size: 13px;
  font-weight: 500;
  color: #0c4a6e;
}

.wpqss-cleanup-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.wpqss-cleanup-actions .wpqss-btn {
  flex: 1;
  min-width: 140px;
  justify-content: center;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-help-content {
    grid-template-columns: 1fr;
  }

  .wpqss-cleanup-actions {
    flex-direction: column;
  }

  .wpqss-cleanup-actions .wpqss-btn {
    flex: none;
    min-width: auto;
  }

  .wpqss-cleanup-stats {
    justify-content: center;
  }
}
</style>
