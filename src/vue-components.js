/**
 * Vue.js Components Entry Point
 * Full Vue.js migration - All components are now Vue.js
 */

import { createApp } from 'vue'
import App from './App.vue'

// Set Nextcloud Vue app name and version (REQUIRED)
// These are set via webpack DefinePlugin as global variables
// Also set via NcContent component's app-name prop
if (typeof window !== 'undefined') {
	// Set global variables for Nextcloud Vue library
	if (typeof appName !== 'undefined') {
		window.appName = appName
	}
	if (typeof appVersion !== 'undefined') {
		window.appVersion = appVersion
	}
	// Also set on OC object if available
	if (window.OC) {
		window.OC.appName = typeof appName !== 'undefined' ? appName : 'domaincontrol'
		window.OC.appVersion = typeof appVersion !== 'undefined' ? appVersion : '3.7.8528'
	}
}

console.log('Vue Components: Loading...')

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
	console.log('Vue Components: DOM ready, initializing Vue.js App...')
	
	const appRoot = document.getElementById('vue-app-root')
	if (appRoot) {
		try {
			const app = createApp(App)
			app.mount(appRoot)
			console.log('Vue Components: App mounted successfully!')
		} catch (error) {
			console.error('Vue Components: Error mounting App:', error)
		}
	} else {
		console.error('Vue Components: App root container (#vue-app-root) not found')
	}
})

