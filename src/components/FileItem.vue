<template>
  <div class="wpqss-file">
    <div class="wpqss-file-header">{{ file.file }}</div>
    <VulnerabilityItem
      v-for="(vulnerability, index) in file.vulnerabilities"
      :key="index"
      :vulnerability="vulnerability"
    />
  </div>
</template>

<script>
import VulnerabilityItem from './VulnerabilityItem.vue';

export default {
  name: 'FileItem',
  components: {
    VulnerabilityItem
  },
  props: {
    file: {
      type: Object,
      required: true
    }
  }
};
</script>

<style scoped>
.wpqss-file {
  border-bottom: 1px solid #f1f1f1;
}

.wpqss-file:last-child {
  border-bottom: none;
}

.wpqss-file-header {
  background: #fafafa;
  padding: 10px 16px;
  border-bottom: 1px solid #f1f1f1;
  font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
  font-size: 13px;
  color: #666;
  font-weight: 500;
}
</style>
