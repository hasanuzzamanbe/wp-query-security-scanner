/**
 * Vue.js Components for WP Query Security Scanner
 */

// Scan Controls Component
const ScanControls = {
    props: {
        isScanning: Boolean,
        selectedComponentType: String,
        selectedComponent: String,
        availableComponents: Object,
        canScanSpecific: Boolean
    },
    emits: ['scan-plugins', 'scan-themes', 'scan-specific', 'update-component-type', 'update-component'],
    template: `
        <div class="wpqss-scan-controls-compact">
            <div class="wpqss-scan-header">
                <div class="wpqss-scan-title">
                    <h2>Security Scanner</h2>
                    <p>Detect vulnerabilities in plugins and themes</p>
                </div>
                <div class="wpqss-scan-actions">
                    <button 
                        @click="$emit('scan-plugins')"
                        :disabled="isScanning"
                        class="wpqss-btn wpqss-btn-primary"
                    >
                        <span class="dashicons dashicons-admin-plugins"></span>
                        Scan Plugins
                    </button>
                    <button 
                        @click="$emit('scan-themes')"
                        :disabled="isScanning"
                        class="wpqss-btn wpqss-btn-secondary"
                    >
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Scan Themes
                    </button>
                </div>
            </div>
            
            <div class="wpqss-scan-options">
                <div class="wpqss-option-group">
                    <label>Specific Component:</label>
                    <div class="wpqss-inline-controls">
                        <select 
                            :value="selectedComponentType"
                            @change="$emit('update-component-type', $event.target.value)"
                            class="wpqss-select-compact"
                        >
                            <option value="plugins">Plugin</option>
                            <option value="themes">Theme</option>
                        </select>
                        <select 
                            :value="selectedComponent"
                            @change="$emit('update-component', $event.target.value)"
                            :disabled="Object.keys(availableComponents).length === 0"
                            class="wpqss-select-compact"
                        >
                            <option value="">Select component...</option>
                            <option 
                                v-for="(component, key) in availableComponents" 
                                :key="key" 
                                :value="key"
                            >
                                {{ component.name }} (v{{ component.version }})
                            </option>
                        </select>
                        <button 
                            @click="$emit('scan-specific')"
                            :disabled="!canScanSpecific || isScanning"
                            class="wpqss-btn wpqss-btn-outline"
                        >
                            <span class="dashicons dashicons-search"></span>
                            Scan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `
};

// Progress Component
const ProgressIndicator = {
    props: {
        visible: Boolean,
        percentage: Number,
        message: String
    },
    template: `
        <div v-if="visible" class="wpqss-progress">
            <h3>Scanning in Progress</h3>
            <div class="wpqss-progress-bar">
                <div 
                    class="wpqss-progress-fill" 
                    :style="{ width: percentage + '%' }"
                ></div>
            </div>
            <p class="wpqss-progress-text">{{ message }} ({{ percentage }}%)</p>
            <p class="description">
                This may take a few minutes depending on the number of files to scan. Please do not close this page.
            </p>
        </div>
    `
};

// Filter Controls Component
const FilterControls = {
    props: {
        currentFilter: String,
        severityCounts: Object,
        resultsCountText: String,
        exportFormat: String,
        hasResults: Boolean
    },
    emits: ['apply-filter', 'update-export-format', 'export-all', 'export-filtered'],
    template: `
        <div class="wpqss-results-header">
            <div class="wpqss-results-title">
                <h3>Scan Results</h3>
                <span class="wpqss-results-count">{{ resultsCountText }}</span>
            </div>
            
            <div class="wpqss-results-controls">
                <div class="wpqss-filter-group">
                    <label>Filter by Severity:</label>
                    <div class="wpqss-severity-filters">
                        <button 
                            v-for="(count, severity) in severityCounts" 
                            :key="severity"
                            @click="$emit('apply-filter', severity)"
                            :class="[
                                'wpqss-filter-btn',
                                { 'active': currentFilter === severity },
                                severity !== 'all' ? 'wpqss-filter-' + severity : ''
                            ]"
                        >
                            {{ severity.charAt(0).toUpperCase() + severity.slice(1) }}
                            <span class="wpqss-filter-count">{{ count }}</span>
                        </button>
                    </div>
                </div>
                
                <div class="wpqss-export-group">
                    <label>Export:</label>
                    <div class="wpqss-export-controls">
                        <select 
                            :value="exportFormat"
                            @change="$emit('update-export-format', $event.target.value)"
                            class="wpqss-select-compact"
                        >
                            <option value="json">JSON</option>
                            <option value="csv">CSV</option>
                            <option value="html">HTML</option>
                            <option value="xml">XML</option>
                        </select>
                        <button 
                            @click="$emit('export-all')"
                            :disabled="!hasResults"
                            class="wpqss-btn wpqss-btn-outline"
                        >
                            <span class="dashicons dashicons-download"></span>
                            All Results
                        </button>
                        <button 
                            @click="$emit('export-filtered')"
                            :disabled="!hasResults"
                            class="wpqss-btn wpqss-btn-outline"
                        >
                            <span class="dashicons dashicons-filter"></span>
                            Filtered Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `
};

// Vulnerability Component
const VulnerabilityItem = {
    props: {
        vulnerability: Object
    },
    template: `
        <div :class="['wpqss-vulnerability', vulnerability.severity]">
            <div class="wpqss-vulnerability-header">
                <h4 class="wpqss-vulnerability-title">{{ vulnerability.type }}</h4>
                <span class="wpqss-vulnerability-line">Line {{ vulnerability.line }}</span>
            </div>
            
            <div class="wpqss-vulnerability-description">{{ vulnerability.description }}</div>
            
            <div class="wpqss-code-block">{{ vulnerability.code }}</div>
            
            <div v-if="vulnerability.context && vulnerability.context.length > 0" class="wpqss-code-context">
                <div 
                    v-for="line in vulnerability.context" 
                    :key="line.line_number"
                    :class="['wpqss-context-line', { 'vulnerable': line.is_vulnerable }]"
                >
                    <span class="wpqss-context-line-number">{{ line.line_number }}</span>{{ line.code }}
                </div>
            </div>
            
            <div class="wpqss-remediation">
                <div class="wpqss-remediation-title">Remediation:</div>
                <div class="wpqss-remediation-text">{{ vulnerability.remediation }}</div>
            </div>
        </div>
    `
};

// File Component
const FileItem = {
    props: {
        file: Object
    },
    components: {
        VulnerabilityItem
    },
    template: `
        <div class="wpqss-file">
            <div class="wpqss-file-header">{{ file.file }}</div>
            <VulnerabilityItem 
                v-for="(vulnerability, index) in file.vulnerabilities" 
                :key="index"
                :vulnerability="vulnerability"
            />
        </div>
    `
};

// Component Item
const ComponentItem = {
    props: {
        component: Object
    },
    components: {
        FileItem
    },
    data() {
        return {
            expanded: false
        };
    },
    computed: {
        severityBadges() {
            const badges = [];
            Object.keys(this.component.severity_counts).forEach(severity => {
                const count = this.component.severity_counts[severity];
                if (count > 0) {
                    badges.push({ severity, count });
                }
            });
            return badges;
        }
    },
    template: `
        <div :class="['wpqss-component', expanded ? 'expanded' : 'collapsed']">
            <div class="wpqss-component-header" @click="expanded = !expanded">
                <h3>{{ component.name }}</h3>
                <div class="wpqss-component-meta">
                    <span>{{ component.total_vulnerabilities }} vulnerabilities</span>
                    <span 
                        v-for="badge in severityBadges" 
                        :key="badge.severity"
                        :class="['wpqss-severity-badge', badge.severity]"
                    >
                        {{ badge.count }} {{ badge.severity }}
                    </span>
                    <span :class="['wpqss-toggle-icon', 'dashicons', expanded ? 'dashicons-arrow-up' : 'dashicons-arrow-down']"></span>
                </div>
            </div>
            
            <div v-if="expanded" class="wpqss-component-content">
                <FileItem 
                    v-for="(file, index) in component.files" 
                    :key="index"
                    :file="file"
                />
            </div>
        </div>
    `
};

// Results Display Component
const ResultsDisplay = {
    props: {
        results: Array,
        hasResults: Boolean
    },
    components: {
        ComponentItem
    },
    template: `
        <div class="wpqss-results">
            <div v-if="!hasResults" class="wpqss-empty-state">
                <span class="dashicons dashicons-yes-alt"></span>
                <h3>No Vulnerabilities Found</h3>
                <p>Great! No security issues were detected in the scanned files.</p>
            </div>
            
            <ComponentItem 
                v-else
                v-for="(component, index) in results" 
                :key="index"
                :component="component"
            />
        </div>
    `
};

// Register components globally
const app = {
    components: {
        ScanControls,
        ProgressIndicator,
        FilterControls,
        ResultsDisplay,
        ComponentItem,
        FileItem,
        VulnerabilityItem
    }
};

// Make components available globally
window.ScanControls = ScanControls;
window.ProgressIndicator = ProgressIndicator;
window.FilterControls = FilterControls;
window.ResultsDisplay = ResultsDisplay;
window.ComponentItem = ComponentItem;
window.FileItem = FileItem;
window.VulnerabilityItem = VulnerabilityItem;

// Export for use in main app
if (typeof module !== 'undefined' && module.exports) {
    module.exports = app;
} else {
    window.WPQSSComponents = app;
}
