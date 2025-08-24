<template>
  <div 
    class="wpqss-component"
    :class="{ 'expanded': isExpanded }"
  >
    <div class="wpqss-component-header" @click="toggleExpanded">
      <h3>{{ component.name }}</h3>
      <div class="wpqss-component-meta">
        <span>{{ component.total_vulnerabilities }} vulnerabilities</span>
        <span
          v-for="(count, severity) in visibleSeverityCounts"
          :key="severity"
          :class="['wpqss-severity-badge', severity]"
        >
          {{ count }} {{ severity }}
        </span>
        <span 
          :class="[
            'wpqss-toggle-icon', 
            'dashicons', 
            isExpanded ? 'dashicons-arrow-up' : 'dashicons-arrow-down'
          ]"
        ></span>
      </div>
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
    const isExpanded = ref(false);

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
  border: 1px solid #e9ecef;
  border-radius: 8px;
  margin-bottom: 16px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.wpqss-component:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.wpqss-component-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 12px 16px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e9ecef;
  transition: background 0.2s ease;
}

.wpqss-component-header:hover {
  background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
}

.wpqss-component-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 16px;
  font-weight: 600;
}

.wpqss-component-meta {
  display: flex;
  gap: 12px;
  align-items: center;
  color: #6c757d;
  font-size: 13px;
}

.wpqss-severity-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
  color: #fff;
}

.wpqss-severity-badge.critical { 
  background: #dc3545; 
}

.wpqss-severity-badge.high { 
  background: #fd7e14; 
}

.wpqss-severity-badge.medium { 
  background: #ffc107; 
  color: #212529; 
}

.wpqss-severity-badge.low { 
  background: #28a745; 
}

.wpqss-toggle-icon {
  transition: transform 0.2s ease;
  font-size: 16px;
}

.wpqss-component.expanded .wpqss-toggle-icon {
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
