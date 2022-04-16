var webpack = require("webpack");
const path = require('path');
var config = {
  mode: 'development',
  devtool: 'inline-source-map',
  watch : true,
  output: {
    // path: path.resolve(__dirname, 'dist'),
    // [name] is based on on the entry point names
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'dist'),
    chunkFilename: '[name].bundle.js',
    publicPath: 'js/dist/',
  },
  resolve: {
    extensions: ['.js', '.jsx']
  },
  module: {
    rules: [
      {
        // test: /\.js?$/,
        test: /\.jsx?$/,
        // test: /\.(js|jsx)$/,
        // test: /\.(js|jsx)$/,
        // test: /\.m?js$/,
        // test: /\.txt$/,
        // use: 'raw-loader'
        // exclude: /node_modules/,
        exclude: /(node_modules|bower_components)/,
        loader: 'babel-loader',
        options: {
          presets: ['@babel/preset-env']
        }
      }
    ]
  }
};

var dashboard = Object.assign({}, config, {
  mode: 'development',
  devtool: 'inline-source-map',
  // the key 'wind_lms.dashboard' will be used in '[name]' in output
  entry: { 'wind_lms.dashboard' : './src/pages/wind_lms.dashboard.js'},
  name: 'test',
  output: {
    // path: path.resolve(__dirname, 'dist'),
    // [name] is based on on the entry point names
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'dist'),
    chunkFilename: '[name].bundle.js',
    publicPath: 'js/dist/',
  },
  resolve: {
    extensions: ['.js', '.jsx']
  },
  module: {
    rules: [
      {
        // test: /\.js?$/,
        test: /\.jsx?$/,
        // test: /\.(js|jsx)$/,
        // test: /\.(js|jsx)$/,
        // test: /\.m?js$/,
        // test: /\.txt$/,
        // use: 'raw-loader'
        // exclude: /node_modules/,
        exclude: /(node_modules|bower_components)/,
        loader: 'babel-loader',
        options: {
          presets: ['@babel/preset-env']
        }
      }
    ]
  }
});

var importUser = Object.assign({}, config, {
  mode: 'development',
  devtool: 'inline-source-map',
  // the key 'wind_lms.import_user' will be used in '[name]' in output
  entry: { 'wind_lms.import_user' : './src/pages/wind_lms.import_user.js'},
  name: 'test',
  output: {
    // path: path.resolve(__dirname, 'dist'),
    // [name] is based on on the entry point names
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'dist'),
    chunkFilename: '[name].bundle.js',
    publicPath: 'js/dist/',
  },
  resolve: {
    extensions: ['.js', '.jsx']
  },
  module: {
    rules: [
      {
        // test: /\.js?$/,
        test: /\.jsx?$/,
        // test: /\.(js|jsx)$/,
        // test: /\.(js|jsx)$/,
        // test: /\.m?js$/,
        // test: /\.txt$/,
        // use: 'raw-loader'
        // exclude: /node_modules/,
        exclude: /(node_modules|bower_components)/,
        loader: 'babel-loader',
        options: {
          presets: ['@babel/preset-env']
        }
      }
    ]
  }
});

let courseNode = Object.assign({}, config, {
  entry: { 'wind_lms.courseNode' : './src/pages/wind_lms.courseNode.js'},
  name: 'courseNode',
});

let team = Object.assign({}, config, {
  entry: { 'wind_lms.team' : './src/pages/wind_lms.team.js'},
  name: 'courseNode',
});

// Return Array of Configurations
module.exports = [ dashboard, courseNode, team, importUser];
