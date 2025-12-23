const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
const webpack = require('webpack')

module.exports = {
	// Use 'source-map' to avoid CSP violations in Nextcloud (NO eval!)
	devtool: 'source-map',
	
	entry: './src/vue-components.js',
	output: {
		path: path.resolve(__dirname, './js'),
		filename: 'vue-components.js',
		publicPath: '/apps/domaincontrol/js/',
		clean: false, // Don't clean, keep existing files
		// CRITICAL FIXES FOR NEXTCLOUD:
		uniqueName: 'domaincontrol',
		chunkLoadingGlobal: 'webpackChunkDomainControl',
		// Force IIFE to ensure immediate execution
		iife: true,
		scriptType: 'text/javascript'
	},
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
				options: {
					presets: [['@babel/preset-env', { targets: 'defaults' }]]
				}
			},
			{
				test: /\.css$/,
				use: [
					'vue-style-loader',
					'css-loader'
				]
			}
		],
	},
	plugins: [
		new VueLoaderPlugin(),
		// REQUIRED for Vue 3
		new webpack.DefinePlugin({
			__VUE_OPTIONS_API__: true,
			__VUE_PROD_DEVTOOLS__: false,
			__VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
			// Nextcloud Vue appName and appVersion (required by @nextcloud/vue)
			appName: JSON.stringify('domaincontrol'),
			appVersion: JSON.stringify('3.7.8533'),
		}),
	],
	resolve: {
		extensions: ['.js', '.vue', '.json'],
		alias: {
			// This is correct for bundling
			'vue$': 'vue/dist/vue.esm-bundler.js',
			'@': path.resolve(__dirname, 'src'),
		},
	},
	externals: {
		// Nextcloud globals - these are available at runtime
		OC: 'OC',
	},
}
