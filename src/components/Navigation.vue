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
		translate(appId, text, vars) {
			// Use Nextcloud's translation system
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}

			// Fallback: Manual translation
			const translations = {
				'Dashboard': 'Dashboard',
				'Clients': 'Müşteriler',
				'Domains': 'Domainler',
				'Hosting': 'Hosting',
				'Websites': 'Websiteler',
				'Services': 'Hizmetler',
				'Invoices': 'Faturalar',
				'Projects': 'Projeler',
				'Tasks': 'Görevler',
				'Income/Expense': 'Gelir/Gider',
				'Debts/Receivables': 'Borç/Alacak',
				'Reports': 'Raporlar',
				'Settings': 'Ayarlar',
				'General Settings': 'Genel Ayarlar',
			}

			return translations[text] || text
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
/* Nextcloud Files app navigation structure */
/* NcAppNavigationItem and NcAppNavigationSettings handle all styling automatically */
</style>

