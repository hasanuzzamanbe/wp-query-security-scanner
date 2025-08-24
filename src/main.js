import { createApp } from 'vue';
import App from './App.vue';
import './assets/styles/main.css';

// WordPress integration
const initializeApp = () => {
  console.log('Initializing WP Query Security Scanner Vue App...');
  
  // Check if Vue is available (from CDN in WordPress)
  const VueLib = window.Vue;
  
  if (!VueLib) {
    console.error('Vue.js is not available!');
    return;
  }
  
  // Create and mount the app
  const app = createApp(App);
  
  // Global error handler
  app.config.errorHandler = (err, instance, info) => {
    console.error('Vue Error:', err);
    console.error('Component:', instance);
    console.error('Info:', info);
  };
  
  // Global properties for WordPress integration
  app.config.globalProperties.$wpAjax = window.wpqss_ajax || {};
  
  // Mount the app
  const mountElement = document.getElementById('wpqss-vue-app');
  if (mountElement) {
    app.mount('#wpqss-vue-app');
    console.log('Vue app mounted successfully');
  } else {
    console.error('Mount element #wpqss-vue-app not found');
  }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeApp);
} else {
  initializeApp();
}

// Export for WordPress integration
window.WPQSSApp = {
  init: initializeApp
};
