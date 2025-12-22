<template>
	<div class="app-navigation domaincontrol-navigation">
		<Navigation :current-tab="currentTab" @switch-tab="handleTabSwitch" />
	</div>
	<main id="app-content-vue" class="app-content no-snapper">
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
	</main>
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
/* Nextcloud native app layout */
/* Vue 3 allows multiple root elements, but we need to ensure proper layout */
/* Reference: https://docs.nextcloud.com/server/latest/developer_manual/html_css_design/navigation.html */
.app-navigation.domaincontrol-navigation {
	width: 300px;
	flex-shrink: 0;
	background-color: var(--color-main-background);
	border-right: 1px solid var(--color-border);
	display: flex;
	flex-direction: column;
	height: calc(100vh - var(--header-height, 50px));
	overflow: hidden;
	position: fixed;
	left: 0;
	top: var(--header-height, 50px);
	z-index: 100;
}

#app-content-vue {
	flex: 1;
	min-height: 100vh;
	overflow-y: auto;
	background-color: var(--color-main-background);
	position: relative;
	width: 100%;
	box-sizing: border-box;
	margin-left: 300px;
	padding-bottom: 40px;
}

/* Hide old tab-content system - but only for old PHP-based tabs */
/* Vue project components use .tab-content class, so we must be specific */
/* Old system is in #app-content, Vue is in #app-content-vue */
#app-content .tab-content {
	display: none !important;
}

/* Ensure Vue component tab-content is visible */
#app-content-vue .tab-content {
	display: block !important;
}

/* Ensure vue-app-root is a flex container */
#vue-app-root {
	display: flex;
	width: 100%;
	min-height: 100vh;
}
</style>

