export function useNotifications() {
  
  // Show notification to user
  const showNotice = (message, type = 'success') => {
    // Create WordPress-style notice
    const notice = document.createElement('div');
    notice.className = `notice notice-${type} is-dismissible wpqss-notice`;
    notice.innerHTML = `
      <p>${escapeHtml(message)}</p>
      <button type="button" class="notice-dismiss">
        <span class="screen-reader-text">Dismiss this notice.</span>
      </button>
    `;
    
    // Add styles for the notice
    notice.style.cssText = `
      position: fixed;
      top: 32px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
      margin: 0;
      padding: 12px 16px;
      border-left: 4px solid;
      background: white;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      border-radius: 4px;
      animation: slideInRight 0.3s ease;
    `;
    
    // Set border color based on type
    const borderColors = {
      success: '#46b450',
      error: '#dc3232',
      warning: '#ffb900',
      info: '#00a0d2'
    };
    notice.style.borderLeftColor = borderColors[type] || borderColors.info;
    
    // Add to page
    const container = document.querySelector('.wpqss-admin') || document.body;
    container.appendChild(notice);
    
    // Add dismiss functionality
    const dismissBtn = notice.querySelector('.notice-dismiss');
    if (dismissBtn) {
      dismissBtn.addEventListener('click', () => {
        notice.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notice.remove(), 300);
      });
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (notice.parentNode) {
        notice.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notice.remove(), 300);
      }
    }, 5000);
  };

  // Show success notification
  const showSuccess = (message) => {
    showNotice(message, 'success');
  };

  // Show error notification
  const showError = (message) => {
    showNotice(message, 'error');
  };

  // Show warning notification
  const showWarning = (message) => {
    showNotice(message, 'warning');
  };

  // Show info notification
  const showInfo = (message) => {
    showNotice(message, 'info');
  };

  // Escape HTML to prevent XSS
  const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  };

  // Show loading notification
  const showLoading = (message = 'Loading...') => {
    const notice = document.createElement('div');
    notice.className = 'notice notice-info wpqss-loading-notice';
    notice.innerHTML = `
      <p>
        <span class="spinner is-active" style="float: left; margin: 0 8px 0 0;"></span>
        ${escapeHtml(message)}
      </p>
    `;
    
    notice.style.cssText = `
      position: fixed;
      top: 32px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
      margin: 0;
      padding: 12px 16px;
      border-left: 4px solid #00a0d2;
      background: white;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      border-radius: 4px;
      animation: slideInRight 0.3s ease;
    `;
    
    const container = document.querySelector('.wpqss-admin') || document.body;
    container.appendChild(notice);
    
    return {
      remove: () => {
        if (notice.parentNode) {
          notice.style.animation = 'slideOutRight 0.3s ease';
          setTimeout(() => notice.remove(), 300);
        }
      },
      update: (newMessage) => {
        const p = notice.querySelector('p');
        if (p) {
          p.innerHTML = `
            <span class="spinner is-active" style="float: left; margin: 0 8px 0 0;"></span>
            ${escapeHtml(newMessage)}
          `;
        }
      }
    };
  };

  // Clear all notifications
  const clearNotifications = () => {
    const notices = document.querySelectorAll('.wpqss-notice, .wpqss-loading-notice');
    notices.forEach(notice => {
      notice.style.animation = 'slideOutRight 0.3s ease';
      setTimeout(() => notice.remove(), 300);
    });
  };

  // Add CSS animations if not already present
  const addNotificationStyles = () => {
    if (document.getElementById('wpqss-notification-styles')) {
      return;
    }
    
    const style = document.createElement('style');
    style.id = 'wpqss-notification-styles';
    style.textContent = `
      @keyframes slideInRight {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      
      @keyframes slideOutRight {
        from {
          transform: translateX(0);
          opacity: 1;
        }
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
      
      .wpqss-notice .notice-dismiss,
      .wpqss-loading-notice .notice-dismiss {
        position: absolute;
        top: 8px;
        right: 8px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
      }
      
      .wpqss-notice .notice-dismiss:before,
      .wpqss-loading-notice .notice-dismiss:before {
        content: "\\f153";
        font-family: dashicons;
        font-size: 16px;
        color: #666;
      }
      
      .wpqss-notice .notice-dismiss:hover:before,
      .wpqss-loading-notice .notice-dismiss:hover:before {
        color: #333;
      }
    `;
    
    document.head.appendChild(style);
  };

  // Initialize notification styles
  addNotificationStyles();

  return {
    showNotice,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showLoading,
    clearNotifications
  };
}
