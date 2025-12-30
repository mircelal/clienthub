<template>
	<div>
		<ul>
			<NcAppNavigationItem
				v-for="item in menuItems"
				:key="item.id"
				:name="translate('domaincontrol', item.label)"
				:to="`#${item.id}`"
				:active="$props.currentTab === item.id"
				@click.prevent="handleTabClick(item.id)"
			>
				<template #icon>
					<MaterialIcon :name="item.icon" :size="20" />
				</template>
			</NcAppNavigationItem>
		</ul>
		<NcAppNavigationSettings>
			<NcAppNavigationItem
				:name="translate('domaincontrol', 'Settings')"
				:to="'#settings'"
				:active="$props.currentTab === 'settings'"
				@click.prevent="handleTabClick('settings')"
			>
				<template #icon>
					<MaterialIcon name="settings" :size="20" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigationSettings>
	</div>
</template>

<script>
import { NcAppNavigationItem, NcAppNavigationSettings } from '@nextcloud/vue'
import MaterialIcon from './MaterialIcon.vue'
import api from '../services/api'

export default {
	name: 'Navigation',
	components: {
		NcAppNavigationItem,
		NcAppNavigationSettings,
		MaterialIcon,
	},
	props: {
		currentTab: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			settingsOpen: false,
			activeModules: [],
			allMenuItems: [
				{
					id: 'dashboard',
					label: 'Dashboard',
					icon: 'home',
				},
				{
					id: 'clients',
					label: 'Clients',
					icon: 'contacts',
				},
				{
					id: 'domains',
					label: 'Domains',
					icon: 'public',
				},
				{
					id: 'hostings',
					label: 'Hosting',
					icon: 'category-office',
				},
				{
					id: 'inventory',
					label: 'Inventory',
					icon: 'package-variant',
				},
				{
					id: 'websites',
					label: 'Websites',
					icon: 'link',
				},
				{
					id: 'services',
					label: 'Services',
					icon: 'settings',
				},
				{
					id: 'invoices',
					label: 'Invoices',
					icon: 'files',
				},
				{
					id: 'projects',
					label: 'Projects',
					icon: 'folder',
				},
				{
					id: 'tasks',
					label: 'Tasks',
					icon: 'checkmark',
				},
				{
					id: 'transactions',
					label: 'Income/Expense',
					icon: 'accounting',
				},
				{
					id: 'debts',
					label: 'Debts/Receivables',
					icon: 'files',
				},
				{
					id: 'reports',
					label: 'Reports',
					icon: 'monitoring',
				},
			],
		}
	},
	computed: {
		menuItems() {
			if (this.activeModules.length === 0) {
				// Default: show all modules
				return this.allMenuItems
			}
			return this.allMenuItems.filter(item => this.activeModules.includes(item.id))
		},
	},
	mounted() {
		this.loadActiveModules()
		// Listen for settings updates
		window.addEventListener('settings-updated', this.handleSettingsUpdate)
		// Listen for tab changes from external code
		if (typeof window.DomainControl !== 'undefined') {
			// Store original switchTab method
			const originalSwitchTab = window.DomainControl.switchTab
			// Override to update Vue component
			window.DomainControl.switchTab = (tabName) => {
				this.$emit('switch-tab', tabName)
				if (originalSwitchTab) {
					originalSwitchTab.call(window.DomainControl, tabName)
				}
			}
		}
	},
	beforeUnmount() {
		window.removeEventListener('settings-updated', this.handleSettingsUpdate)
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
					this.activeModules = this.allMenuItems.map(m => m.id)
				}
			} catch (error) {
				console.error('Error loading active modules:', error)
				// Default: all modules active
				this.activeModules = this.allMenuItems.map(m => m.id)
			}
		},
		handleSettingsUpdate(event) {
			if (event.detail && event.detail.activeModules) {
				this.activeModules = event.detail.activeModules
			}
		},
		handleTabClick(tabId) {
			// Emit event to parent App component
			this.$emit('switch-tab', tabId)
			// Call existing DomainControl.switchTab if available
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab(tabId)
			}
		},
		toggleSettings() {
			this.settingsOpen = !this.settingsOpen
		},
	},
}
</script>

<style scoped>
	.app-navigation-entry.active {
    margin: 8px 0 0 8px;
    border-radius: 14px 0 0 14px;
}


.app-navigation__body {
    padding: 7px;
}
/* Nextcloud Files app navigation structure */
/* NcAppNavigationItem and NcAppNavigationSettings handle all styling automatically */
</style>

