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
    
    <!-- Footer -->
    <div class="wpqss-footer">
      <p>
        WP Query Security Scanner v2.0 - 
        <a href="https://github.com/your-repo/wp-query-security-scanner" target="_blank">
          GitHub Repository
        </a>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpSection'
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

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-help-content {
    grid-template-columns: 1fr;
  }
}
</style>
