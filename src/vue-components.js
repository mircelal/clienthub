/**
 * Vue.js Components Entry Point
 * Full Vue.js migration - All components are now Vue.js
 */

import { createApp } from 'vue'
import App from './App.vue'

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

