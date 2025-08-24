const { defineConfig } = require('@vue/cli-service');
const path = require('path');

module.exports = defineConfig({
  transpileDependencies: true,
  
  // Output configuration for WordPress plugin
  outputDir: 'assets/dist',
  assetsDir: '',
  
  // Configure webpack
  configureWebpack: {
    entry: {
      'wpqss-app': './src/main.js'
    },
    output: {
      filename: '[name].js',
      chunkFilename: '[name].js'
    },
    optimization: {
      splitChunks: false // Don't split chunks for WordPress
    },
    externals: {
      // Don't bundle these - they'll be provided by WordPress/CDN
      'vue': 'Vue'
    }
  },
  
  // CSS configuration
  css: {
    extract: {
      filename: '[name].css',
      chunkFilename: '[name].css'
    }
  },
  
  // Development server configuration
  devServer: {
    port: 8080,
    hot: true,
    open: false
  },
  
  // Disable source maps in production
  productionSourceMap: false,
  
  // Public path for assets
  publicPath: process.env.NODE_ENV === 'production' ? './' : '/',
  
  // Webpack chain modifications
  chainWebpack: config => {
    // Remove prefetch and preload plugins
    config.plugins.delete('prefetch');
    config.plugins.delete('preload');
    
    // Configure HTML plugin for development
    if (process.env.NODE_ENV === 'development') {
      config.plugin('html').tap(args => {
        args[0].template = './public/index.html';
        return args;
      });
    }
    
    // Optimize for WordPress environment
    config.optimization.delete('splitChunks');
    
    // Configure file loader for assets
    config.module
      .rule('images')
      .test(/\.(png|jpe?g|gif|svg)(\?.*)?$/)
      .use('file-loader')
      .loader('file-loader')
      .options({
        name: 'img/[name].[hash:8].[ext]'
      });
  }
});
