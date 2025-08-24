<template>
  <div 
    class="wpqss-component"
    :class="{ 'expanded': isExpanded }"
  >
    <div class="wpqss-component-header" @click="toggleExpanded">
      <div class="wpqss-component-title">
        <h3>{{ component.name }}</h3>
        <div class="wpqss-component-stats">
          <span class="wpqss-vuln-count">{{ component.total_vulnerabilities }} issues</span>
          <div class="wpqss-severity-badges">
            <span
              v-for="(count, severity) in visibleSeverityCounts"
              :key="severity"
              :class="['wpqss-severity-badge', 'wpqss-severity-' + severity]"
              :title="`${count} ${severity} severity issues`"
            >
              {{ count }}
            </span>
          </div>
        </div>
      </div>
      <button
        class="wpqss-toggle-btn"
        :class="{ 'expanded': isExpanded }"
        :aria-label="isExpanded ? 'Collapse' : 'Expand'"
      >
        <span class="wpqss-toggle-icon"></span>
      </button>
    </div>
    
    <Transition name="expand">
      <div v-if="isExpanded" class="wpqss-component-content">
        <FileItem
          v-for="(file, fileIndex) in component.files"
          :key="fileIndex"
          :file="file"
        />
      </div>
    </Transition>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import FileItem from './FileItem.vue';

export default {
  name: 'ComponentItem',
  components: {
    FileItem
  },
  props: {
    component: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const isExpanded = ref(true); // Auto-expand components by default

    const toggleExpanded = () => {
      isExpanded.value = !isExpanded.value;
    };

    // Computed property to filter severity counts with count > 0
    const visibleSeverityCounts = computed(() => {
      if (!props.component.severity_counts) return {};

      const filtered = {};
      Object.entries(props.component.severity_counts).forEach(([severity, count]) => {
        if (count > 0) {
          filtered[severity] = count;
        }
      });
      return filtered;
    });

    return {
      isExpanded,
      toggleExpanded,
      visibleSeverityCounts
    };
  }
};
</script>

<style scoped>
.wpqss-component {
  border: 1px solid #e1e5e9;
  border-radius: 12px;
  margin-bottom: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.wpqss-component:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  //border-color: #667eea;
}

.wpqss-component-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 16px 20px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e1e5e9;
  transition: all 0.2s ease;
  min-height: 60px;
}

.wpqss-component-header:hover {
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

.wpqss-component-title {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.wpqss-component-title h3 {
  margin: 0;
  color: #1e293b;
  font-size: 16px;
  font-weight: 600;
  line-height: 1.2;
}

.wpqss-component-stats {
  display: flex;
  align-items: center;
  gap: 12px;
}

.wpqss-vuln-count {
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
}

.wpqss-severity-badges {
  display: flex;
  gap: 6px;
  align-items: center;
}

.wpqss-severity-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  color: #fff !important;
  line-height: 1;
}

.wpqss-severity-badge.wpqss-severity-critical {
  background: #dc2626;
  box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
}

.wpqss-severity-badge.wpqss-severity-high {
  background: #ea580c;
  box-shadow: 0 1px 3px rgba(234, 88, 12, 0.3);
}

.wpqss-severity-badge.wpqss-severity-medium {
  background: #d97706;
  color: #fff;
  box-shadow: 0 1px 3px rgba(217, 119, 6, 0.3);
}

.wpqss-severity-badge.wpqss-severity-low {
  background: #16a34a;
  box-shadow: 0 1px 3px rgba(22, 163, 74, 0.3);
}

.wpqss-toggle-btn {
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
}

.wpqss-toggle-btn:hover {
  background: rgba(102, 126, 234, 0.1);
}

.wpqss-toggle-icon {
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 8px solid #64748b;
  transition: transform 0.3s ease;
}

.wpqss-toggle-btn.expanded .wpqss-toggle-icon {
  transform: rotate(180deg);
}

.wpqss-component-content {
  background: #fff;
}

/* Expand transition */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}

.expand-enter-to,
.expand-leave-from {
  max-height: 1000px;
  opacity: 1;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .wpqss-component-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .wpqss-component-meta {
    flex-wrap: wrap;
  }
}
</style>
