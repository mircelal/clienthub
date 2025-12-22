/**
 * Vue.js Components Entry Point
 * Step-by-step migration: Dashboard converted to Vue.js
 */

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'

console.log('Vue Components: Loading...')

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
	console.log('Vue Components: DOM ready, initializing...')
	
	// Mount Dashboard component
	const dashboardContainer = document.getElementById('vue-dashboard-container')
	if (dashboardContainer) {
		console.log('Vue Components: Mounting Dashboard...')
		try {
			const app = createApp(Dashboard)
			app.mount(dashboardContainer)
			console.log('Vue Components: Dashboard mounted successfully!')
		} catch (error) {
			console.error('Vue Components: Error mounting Dashboard:', error)
		}
	} else {
		console.warn('Vue Components: Dashboard container not found')
	}
})

