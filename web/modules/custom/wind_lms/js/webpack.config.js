var webpack = require("webpack");
const path = require('path');

module.exports = {
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
};
