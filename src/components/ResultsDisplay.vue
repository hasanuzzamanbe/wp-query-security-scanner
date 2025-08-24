<template>
  <div class="wpqss-results">
    <!-- Empty State -->
    <div v-if="!hasResults" class="wpqss-empty-state">
      <span class="dashicons dashicons-yes-alt"></span>
      <h3>No Vulnerabilities Found</h3>
      <p>Great! No security issues were detected in the scanned files.</p>
    </div>
    
    <!-- Results List -->
    <div v-else>
      <ComponentItem
        v-for="(component, index) in results"
        :key="index"
        :component="component"
      />
    </div>
  </div>
</template>

<script>
import ComponentItem from './ComponentItem.vue';

export default {
  name: 'ResultsDisplay',
  components: {
    ComponentItem
  },
  props: {
    results: {
      type: Array,
      default: () => []
    },
    hasResults: {
      type: Boolean,
      default: false
    }
  }
};
</script>

<style scoped>
.wpqss-results {
  padding: 20px;
}

.wpqss-empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}

.wpqss-empty-state .dashicons {
  font-size: 48px;
  color: #28a745;
  margin-bottom: 15px;
  display: block;
}

.wpqss-empty-state h3 {
  color: #28a745;
  margin-bottom: 10px;
  font-size: 20px;
  font-weight: 600;
}

.wpqss-empty-state p {
  margin: 0;
  font-size: 16px;
  line-height: 1.5;
}
</style>
