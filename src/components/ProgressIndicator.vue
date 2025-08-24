<template>
  <Transition name="fade">
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
  </Transition>
</template>

<script>
export default {
  name: 'ProgressIndicator',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    percentage: {
      type: Number,
      default: 0
    },
    message: {
      type: String,
      default: 'Scanning...'
    }
  }
};
</script>

<style scoped>
.wpqss-progress {
  background: #fff;
  border: 1px solid #ccd0d4;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.wpqss-progress h3 {
  margin-top: 0;
  color: #2c3e50;
  font-size: 18px;
  font-weight: 600;
}

.wpqss-progress-bar {
  width: 100%;
  height: 20px;
  background: #f1f1f1;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 10px;
  position: relative;
}

.wpqss-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  transition: width 0.3s ease;
  border-radius: 10px;
  position: relative;
}

.wpqss-progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.wpqss-progress-text {
  margin: 0;
  color: #666;
  font-size: 14px;
  font-weight: 500;
}

.description {
  margin: 10px 0 0 0;
  color: #888;
  font-size: 13px;
  font-style: italic;
}

/* Transition animations */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
