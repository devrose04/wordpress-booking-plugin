const path = require('path');

module.exports = {
  entry: './assets/js/guesty-payment.js', // Entry point of your JavaScript
  output: {
    filename: '/assets/js/guesty-payment.bundle.js',
    path: path.resolve(__dirname, 'js'), // Output directory
  },
  mode: 'development', // Use 'production' for minified code
};
