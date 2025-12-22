/**
 * Vue.js Components Entry Point
 * Step-by-step migration: Dashboard, Navigation, and Clients converted to Vue.js
 */

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'
import Navigation from './components/Navigation.vue'
import Clients from './components/Clients.vue'

console.log('Vue Components: Loading...')

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
	console.log('Vue Components: DOM ready, initializing...')
	
	// Mount Navigation component
	const navigationContainer = document.getElementById('vue-navigation-container')
	if (navigationContainer) {
		console.log('Vue Components: Mounting Navigation...')
		try {
			const navApp = createApp(Navigation)
			navApp.mount(navigationContainer)
			console.log('Vue Components: Navigation mounted successfully!')
		} catch (error) {
			console.error('Vue Components: Error mounting Navigation:', error)
		}
	} else {
		console.warn('Vue Components: Navigation container not found')
	}
	
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

	// Mount Clients component
	const clientsContainer = document.getElementById('vue-clients-container')
	if (clientsContainer) {
		console.log('Vue Components: Mounting Clients...')
		try {
			const clientsApp = createApp(Clients)
			clientsApp.mount(clientsContainer)
			console.log('Vue Components: Clients mounted successfully!')
		} catch (error) {
			console.error('Vue Components: Error mounting Clients:', error)
		}
	} else {
		console.warn('Vue Components: Clients container not found')
	}
})

