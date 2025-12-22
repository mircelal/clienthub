<template>
	<div id="app">
		<Navigation @switch-tab="handleTabSwitch" />
		<div id="app-content">
			<div id="app-content-wrapper">
				<Dashboard v-if="currentTab === 'dashboard'" />
				<Clients v-if="currentTab === 'clients'" />
				<Domains v-if="currentTab === 'domains'" />
				<Hostings v-if="currentTab === 'hostings'" />
				<Websites v-if="currentTab === 'websites'" />
				<Services v-if="currentTab === 'services'" />
				<Invoices v-if="currentTab === 'invoices'" />
				<Projects v-if="currentTab === 'projects'" />
				<Tasks v-if="currentTab === 'tasks'" />
				<Transactions v-if="currentTab === 'transactions'" />
				<Debts v-if="currentTab === 'debts'" />
				<Reports v-if="currentTab === 'reports'" />
				<Settings v-if="currentTab === 'settings'" />
			</div>
		</div>
	</div>
</template>

<script>
import Navigation from './components/Navigation.vue'
import Dashboard from './components/Dashboard.vue'
import Clients from './components/Clients.vue'
import Domains from './components/Domains.vue'
import Hostings from './components/Hostings.vue'
import Websites from './components/Websites.vue'
import Services from './components/Services.vue'
import Invoices from './components/Invoices.vue'
import Projects from './components/Projects.vue'
import Tasks from './components/Tasks.vue'
import Transactions from './components/Transactions.vue'
import Debts from './components/Debts.vue'
import Reports from './components/Reports.vue'
import Settings from './components/Settings.vue'

export default {
	name: 'App',
	components: {
		Navigation,
		Dashboard,
		Clients,
		Domains,
		Hostings,
		Websites,
		Services,
		Invoices,
		Projects,
		Tasks,
		Transactions,
		Debts,
		Reports,
		Settings,
	},
	data() {
		return {
			currentTab: 'dashboard',
		}
	},
	mounted() {
		// Get initial tab from URL or default
		const urlParams = new URLSearchParams(window.location.search)
		const tabFromUrl = urlParams.get('tab')
		if (tabFromUrl) {
			this.currentTab = tabFromUrl
		}

		// Integrate with existing DomainControl.switchTab
		if (typeof window.DomainControl !== 'undefined') {
			const originalSwitchTab = window.DomainControl.switchTab
			window.DomainControl.switchTab = (tabName) => {
				this.currentTab = tabName
				if (originalSwitchTab) {
					originalSwitchTab.call(window.DomainControl, tabName)
				}
			}
		}
	},
	methods: {
		handleTabSwitch(tabName) {
			this.currentTab = tabName
		},
	},
}
</script>

<style>
/* Global styles will be here if needed */
</style>

