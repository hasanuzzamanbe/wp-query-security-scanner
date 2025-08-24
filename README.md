# 🔒 WP Query Security Scanner

A comprehensive WordPress plugin for detecting security vulnerabilities in plugins and themes with a modern Vue.js interface.

## 🚀 Quick Start

### Installation
1. Clone or download this repository to your WordPress plugins directory
2. Install Node.js dependencies: `npm install`
3. Build the Vue.js assets: `npm run build`
4. Activate the plugin in WordPress admin

### Development
```bash
# Install dependencies
npm install

# Start development server
npm run serve

# Watch mode for WordPress integration
npm run watch

# Build for production
npm run build
```

## ✨ Features

- **🔍 Comprehensive Scanning**: Detects SQL injection, XSS, CSRF, file inclusion, and more
- **🎯 Specific Component Scanning**: Scan individual plugins or themes
- **📊 Real-time Filtering**: Filter vulnerabilities by severity level
- **📤 Multiple Export Formats**: JSON, CSV, HTML, XML
- **🎨 Modern Vue.js Interface**: Component-based architecture with hot reload
- **📱 Responsive Design**: Mobile-optimized interface
- **🔧 Developer Tools**: Debug panel and comprehensive logging

## 🏗️ Architecture

- **Frontend**: Vue.js 3 with Composition API
- **Build System**: Vue CLI with Webpack
- **Backend**: WordPress PHP with AJAX endpoints
- **Components**: Modular .vue single-file components
- **State Management**: Vue.js reactive state with composables

## 📚 Documentation

Comprehensive documentation is available in the [`docs/`](./docs/) directory:

### 🎯 Getting Started
- **[Vue CLI Setup](./docs/VUE_CLI_SETUP.md)** - Development environment setup
- **[Implementation Guide](./docs/VUE_CLI_IMPLEMENTATION_COMPLETE.md)** - Complete implementation overview

### 🔧 Development
- **[Vue.js Implementation](./docs/VUE_JS_ONLY_IMPLEMENTATION.md)** - Vue.js architecture details
- **[Migration Guide](./docs/JQUERY_TO_VUE_MIGRATION.md)** - jQuery to Vue.js migration
- **[Migration Summary](./docs/MIGRATION_SUMMARY.md)** - Migration overview

### 📈 Performance & Features
- **[Performance Improvements](./docs/PERFORMANCE_IMPROVEMENTS.md)** - Performance optimizations
- **[Filtering & UI](./docs/FILTERING_AND_UI_IMPROVEMENTS.md)** - UI enhancements

## 🛠️ Development Scripts

| Script | Description |
|--------|-------------|
| `npm run serve` | Development server with hot reload |
| `npm run build` | Production build |
| `npm run build:prod` | Optimized production build |
| `npm run build:dev` | Development build with source maps |
| `npm run watch` | Watch mode for WordPress integration |
| `npm run lint` | ESLint code quality checks |

## 📁 Project Structure

```
wp-query-security-scanner/
├── 📦 package.json              # Node.js dependencies
├── ⚙️ vue.config.js             # Vue CLI configuration
├── 🔧 babel.config.js           # Babel configuration
├── 📂 src/                      # Vue.js source files
│   ├── 📱 App.vue               # Root component
│   ├── 🎯 main.js               # Entry point
│   ├── 📂 components/           # Vue components
│   ├── 📂 composables/          # Reusable logic
│   ├── 📂 utils/                # Utilities
│   └── 📂 assets/               # Static assets
├── 📂 assets/dist/              # Built assets
├── 📂 includes/                 # PHP classes
├── 📂 templates/                # WordPress templates
├── 📂 docs/                     # Documentation
└── 🔒 wp-query-security-scanner.php # Main plugin file
```

## 🔍 Vulnerability Detection

The scanner detects various security vulnerabilities:

- **SQL Injection**: Unsafe database queries
- **Cross-Site Scripting (XSS)**: Unescaped output
- **Cross-Site Request Forgery (CSRF)**: Missing nonce verification
- **File Inclusion**: Unsafe file includes
- **Privilege Escalation**: Insufficient capability checks
- **Information Disclosure**: Debug functions and exposed data

## 🎨 Modern UI Features

- **Component-Based Architecture**: Modular Vue.js components
- **Real-time Updates**: No page refreshes needed
- **Smooth Animations**: Enhanced visual feedback
- **Responsive Design**: Mobile-optimized interface
- **Debug Tools**: Development panel with state inspection
- **Accessibility**: Keyboard navigation and screen reader support

## 🚀 WordPress Integration

- **Automatic Asset Detection**: Uses built assets when available
- **AJAX Integration**: Seamless WordPress communication
- **Admin Integration**: Native WordPress admin experience
- **Localization Support**: WordPress translation ready
- **Nonce Security**: Proper WordPress security implementation

## 🔧 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Node.js**: 16.x or higher (for development)
- **Modern Browser**: Chrome 60+, Firefox 60+, Safari 12+, Edge 79+

## 📄 License

This project is licensed under the GPL v2 or later - see the WordPress plugin standards for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Install dependencies: `npm install`
4. Make your changes
5. Run tests: `npm run lint`
6. Build assets: `npm run build`
7. Commit changes: `git commit -m 'Add amazing feature'`
8. Push to branch: `git push origin feature/amazing-feature`
9. Open a Pull Request

## 📞 Support

For support and questions:
- Check the [documentation](./docs/)
- Open an issue on GitHub
- Review the debug panel in development mode

## 🎯 Roadmap

- [ ] TypeScript migration
- [ ] Unit testing with Vue Test Utils
- [ ] E2E testing with Cypress
- [ ] PWA features
- [ ] Advanced state management with Pinia
- [ ] Real-time scanning with WebSockets

---

**Built with ❤️ using Vue.js 3, WordPress, and modern web technologies.**
