<template>
	<NcContent app-name="domaincontrol">
		<NcAppNavigation>
			<NcAppNavigationItem
				v-for="item in navigationItems"
				:key="item.id"
				:name="getNavigationLabel(item.label)"
				href="#"
				:active="currentTab === item.id"
				@click.prevent="handleTabSwitch(item.id)"
			>
				<template #icon>
					<component :is="item.icon" :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationSettings>
				<NcAppNavigationItem
					:name="getNavigationLabel('Settings')"
					href="#"
					:active="currentTab === 'settings'"
					@click.prevent="handleTabSwitch('settings')"
				>
					<template #icon>
						<Cog :size="20" />
					</template>
				</NcAppNavigationItem>
			</NcAppNavigationSettings>
		</NcAppNavigation>
		<NcAppContent>
		<Dashboard v-if="currentTab === 'dashboard'" />
		<Clients ref="clientsComponent" v-if="currentTab === 'clients'" />
		<Domains v-if="currentTab === 'domains'" />
		<Hostings v-if="currentTab === 'hostings'" />
		<Websites v-if="currentTab === 'websites'" />
		<Services v-if="currentTab === 'services'" />
		<Invoices ref="invoicesComponent" v-if="currentTab === 'invoices'" />
		<Projects ref="projectsComponent" v-if="currentTab === 'projects'" />
		<Tasks ref="tasksComponent" v-if="currentTab === 'tasks'" />
			<Transactions v-if="currentTab === 'transactions'" />
			<Debts v-if="currentTab === 'debts'" />
			<Reports v-if="currentTab === 'reports'" />
			<Settings v-if="currentTab === 'settings'" />
		</NcAppContent>
	</NcContent>
</template>

<script>
import { NcContent, NcAppNavigation, NcAppContent, NcAppNavigationItem, NcAppNavigationSettings } from '@nextcloud/vue'
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
import api from './services/api'
// vue-material-design-icons
import ViewDashboard from 'vue-material-design-icons/ViewDashboard.vue'
import AccountMultiple from 'vue-material-design-icons/AccountMultiple.vue'
import Web from 'vue-material-design-icons/Web.vue'
import Server from 'vue-material-design-icons/Server.vue'
import Laptop from 'vue-material-design-icons/Laptop.vue'
import Cog from 'vue-material-design-icons/Cog.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import ClipboardCheck from 'vue-material-design-icons/ClipboardCheck.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Wallet from 'vue-material-design-icons/Wallet.vue'
import ChartPie from 'vue-material-design-icons/ChartPie.vue'

export default {
	name: 'App',
	components: {
		NcContent,
		NcAppNavigation,
		NcAppContent,
		NcAppNavigationItem,
		NcAppNavigationSettings,
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
		ViewDashboard,
		AccountMultiple,
		Web,
		Server,
		Laptop,
		Cog,
		FileDocumentOutline,
		FolderOutline,
		ClipboardCheck,
		CurrencyUsd,
		Wallet,
		ChartPie,
	},
	data() {
		return {
			currentTab: 'dashboard',
			activeModules: [],
			allNavigationItems: [
				{ id: 'dashboard', label: 'Dashboard', icon: 'ViewDashboard' },
				{ id: 'clients', label: 'Clients', icon: 'AccountMultiple' },
				{ id: 'domains', label: 'Domains', icon: 'Web' },
				{ id: 'hostings', label: 'Hosting', icon: 'Server' },
				{ id: 'websites', label: 'Websites', icon: 'Laptop' },
				{ id: 'services', label: 'Services', icon: 'Cog' },
				{ id: 'invoices', label: 'Invoices', icon: 'FileDocumentOutline' },
				{ id: 'projects', label: 'Projects', icon: 'FolderOutline' },
				{ id: 'tasks', label: 'Tasks', icon: 'ClipboardCheck' },
				{ id: 'transactions', label: 'Income/Expense', icon: 'CurrencyUsd' },
				{ id: 'debts', label: 'Debts/Receivables', icon: 'Wallet' },
				{ id: 'reports', label: 'Reports', icon: 'ChartPie' },
			],
		}
	},
	computed: {
		navigationItems() {
			if (this.activeModules.length === 0) {
				return this.allNavigationItems
			}
			return this.allNavigationItems.filter(item => this.activeModules.includes(item.id))
		},
	},
	mounted() {
		// Load active modules
		this.loadActiveModules()
		
		// Listen for settings updates
		window.addEventListener('settings-updated', this.handleSettingsUpdate)

		// Get initial tab from URL or default
		const urlParams = new URLSearchParams(window.location.search)
		const tabFromUrl = urlParams.get('tab')
		if (tabFromUrl) {
			this.currentTab = tabFromUrl
		}

		// Initialize window.DomainControl if not exists
		if (typeof window.DomainControl === 'undefined') {
			window.DomainControl = {}
		}

		// Integrate with existing DomainControl.switchTab
		const originalSwitchTab = window.DomainControl.switchTab
		window.DomainControl.switchTab = (tabName) => {
			this.currentTab = tabName
			if (originalSwitchTab && typeof originalSwitchTab === 'function') {
				originalSwitchTab.call(window.DomainControl, tabName)
			}
		}

		// Listen for navigate-to-invoice event from Projects component
		window.addEventListener('navigate-to-invoice', this.handleNavigateToInvoice)

		// Define window.DomainControl methods for Dashboard quick actions
		window.DomainControl.showClientModal = () => {
			this.currentTab = 'clients'
			this.$nextTick(() => {
				if (this.$refs.clientsComponent && typeof this.$refs.clientsComponent.showAddModal === 'function') {
					this.$refs.clientsComponent.showAddModal()
				}
			})
		}

		window.DomainControl.showProjectModal = () => {
			this.currentTab = 'projects'
			this.$nextTick(() => {
				if (this.$refs.projectsComponent && typeof this.$refs.projectsComponent.showAddModal === 'function') {
					this.$refs.projectsComponent.showAddModal()
				}
			})
		}

		window.DomainControl.showTaskModal = () => {
			this.currentTab = 'tasks'
			this.$nextTick(() => {
				if (this.$refs.tasksComponent && typeof this.$refs.tasksComponent.showAddModal === 'function') {
					this.$refs.tasksComponent.showAddModal()
				}
			})
		}

		window.DomainControl.showInvoiceModal = () => {
			this.currentTab = 'invoices'
			this.$nextTick(() => {
				if (this.$refs.invoicesComponent && typeof this.$refs.invoicesComponent.showAddModal === 'function') {
					this.$refs.invoicesComponent.showAddModal()
				}
			})
		}

		window.DomainControl.selectClient = (clientId) => {
			this.currentTab = 'clients'
			this.$nextTick(() => {
				if (this.$refs.clientsComponent && typeof this.$refs.clientsComponent.selectClient === 'function') {
					// selectClient can now handle both ID and client object
					this.$refs.clientsComponent.selectClient(clientId)
				}
			})
		}

		window.DomainControl.selectInvoice = (invoiceId) => {
			this.currentTab = 'invoices'
			this.$nextTick(() => {
				if (this.$refs.invoicesComponent) {
					if (typeof this.$refs.invoicesComponent.selectInvoice === 'function') {
						this.$refs.invoicesComponent.selectInvoice(invoiceId)
					} else if (typeof this.$refs.invoicesComponent.viewInvoice === 'function') {
						// Fallback: try to find invoice and view it
						const invoice = this.$refs.invoicesComponent.invoices.find(inv => inv.id == invoiceId)
						if (invoice) {
							this.$refs.invoicesComponent.viewInvoice(invoice)
						}
					}
				}
			})
		}
	},
	beforeUnmount() {
		window.removeEventListener('settings-updated', this.handleSettingsUpdate)
		window.removeEventListener('navigate-to-invoice', this.handleNavigateToInvoice)
	},
	methods: {
		async loadActiveModules() {
			try {
				const response = await api.settings.get()
				const settings = response.data || {}
				
				if (settings.active_modules) {
					try {
						this.activeModules = JSON.parse(settings.active_modules)
					} catch (e) {
						this.activeModules = settings.active_modules.split(',').filter(Boolean)
					}
				} else {
					// Default: all modules active
					this.activeModules = this.allNavigationItems.map(m => m.id)
				}
			} catch (error) {
				console.error('Error loading active modules:', error)
				// Default: all modules active
				this.activeModules = this.allNavigationItems.map(m => m.id)
			}
		},
		handleSettingsUpdate(event) {
			if (event.detail && event.detail.activeModules) {
				this.activeModules = event.detail.activeModules
				// If current tab is not active anymore, switch to dashboard
				if (!this.activeModules.includes(this.currentTab)) {
					this.currentTab = 'dashboard'
				}
			}
		},
		handleTabSwitch(tabName) {
			this.currentTab = tabName
		},
		handleNavigateToInvoice(event) {
			const invoiceId = event.detail || event
			// Switch to invoices tab
			this.currentTab = 'invoices'
			// Wait for Invoices component to mount, then select the invoice
			this.$nextTick(() => {
				if (this.$refs.invoicesComponent && typeof this.$refs.invoicesComponent.selectInvoice === 'function') {
					this.$refs.invoicesComponent.selectInvoice(invoiceId)
				}
			})
		},
		getNavigationLabel(label) {
			try {
				if (typeof window !== 'undefined') {
					// Try OC.L10n.translate first (Nextcloud's standard method)
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate('domaincontrol', label, {})
						if (translated && translated !== label) {
							return translated
						}
					}
					// Fallback to window.t
					if (typeof window.t === 'function') {
						const translated = window.t('domaincontrol', label, {})
						if (translated && translated !== label) {
							return translated
						}
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}
			// Fallback: return label if translation not found
			return label
		},
	},
}
</script>

<style>
/* Hide old PHP-based tab system */
#app-content .tab-content {
	display: none !important;
}

/* Navigation spacing from top */
.app-navigation__content {
	padding-top: 20px;
}

/* Ensure settings are at the bottom */
.app-navigation__body {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.app-navigation-entry__settings {
	margin-top: auto;
	flex-shrink: 0;
}

/* Content spacing from navigation toggle button */
.app-content-wrapper {
	padding-left: 0;
}

/* Action buttons spacing from navigation toggle */
.domaincontrol-actions {
	margin-left: 44px; /* Space for navigation toggle button */
}

/* App content header spacing */
.app-content-header {
	margin-left: 44px; /* Space for navigation toggle button */
}
</style>

