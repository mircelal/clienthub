/**
 * Vue.js Components Entry Point
 * Step-by-step migration: Only small components here
 */

import { createApp } from 'vue'
import StatsCard from './components/StatsCard.vue'

console.log('Vue Components: Loading...')

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
	console.log('Vue Components: DOM ready, initializing...')
	
	// Mount StatsCard component to test container
	const statsCardContainer = document.getElementById('vue-stats-card-container')
	if (statsCardContainer) {
		console.log('Vue Components: Mounting StatsCard...')
		try {
			const app = createApp(StatsCard, {
				title: 'Toplam Müşteri',
				value: 0,
				icon: 'icon-contacts',
				color: 'primary'
			})
			app.mount(statsCardContainer)
			console.log('Vue Components: StatsCard mounted successfully!')
		} catch (error) {
			console.error('Vue Components: Error mounting StatsCard:', error)
		}
	} else {
		console.warn('Vue Components: StatsCard container not found')
	}
})

