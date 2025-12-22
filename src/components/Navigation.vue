<template>
	<div id="app-navigation">
		<ul class="with-icon">
			<li
				v-for="item in menuItems"
				:key="item.id"
				:data-id="item.id"
				:class="{ active: $props.currentTab === item.id }"
			>
				<a
					href="#"
					:class="['tab-button', item.icon]"
					:data-tab="item.id"
					@click.prevent="handleTabClick(item.id)"
				>
					{{ translate('domaincontrol', item.label) }}
				</a>
			</li>
		</ul>

		<div id="app-settings" class="app-navigation-entry">
			<div id="app-settings-header">
				<button
					class="settings-button"
					@click="toggleSettings"
					:aria-expanded="settingsOpen"
					:aria-controls="'app-settings-content'"
				>
					<span class="icon-settings"></span>
					<span>{{ translate('domaincontrol', 'Settings') }}</span>
				</button>
			</div>
			<div
				id="app-settings-content"
				:class="{ hidden: !settingsOpen }"
			>
				<ul>
					<li>
						<a
							href="#"
							class="icon-settings tab-button"
							data-tab="settings"
							@click.prevent="handleTabClick('settings')"
						>
							{{ translate('domaincontrol', 'General Settings') }}
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Navigation',
	data() {
		return {
			currentTab: 'dashboard',
			settingsOpen: false,
			menuItems: [
				{
					id: 'dashboard',
					label: 'Dashboard',
					icon: 'icon-home',
				},
				{
					id: 'clients',
					label: 'Clients',
					icon: 'icon-contacts',
				},
				{
					id: 'domains',
					label: 'Domains',
					icon: 'icon-public',
				},
				{
					id: 'hostings',
					label: 'Hosting',
					icon: 'icon-category-office',
				},
				{
					id: 'websites',
					label: 'Websites',
					icon: 'icon-link',
				},
				{
					id: 'services',
					label: 'Services',
					icon: 'icon-settings',
				},
				{
					id: 'invoices',
					label: 'Invoices',
					icon: 'icon-files',
				},
				{
					id: 'projects',
					label: 'Projects',
					icon: 'icon-folder',
				},
				{
					id: 'tasks',
					label: 'Tasks',
					icon: 'icon-checkmark',
				},
				{
					id: 'transactions',
					label: 'Income/Expense',
					icon: 'icon-category-office',
				},
				{
					id: 'debts',
					label: 'Debts/Receivables',
					icon: 'icon-files',
				},
				{
					id: 'reports',
					label: 'Reports',
					icon: 'icon-category-monitoring',
				},
			],
		}
	},
	mounted() {
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
	methods: {
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
#app-navigation {
	width: 300px;
	flex-shrink: 0;
	background-color: var(--color-main-background);
	border-right: 1px solid var(--color-border);
	display: flex;
	flex-direction: column;
	height: 100vh;
}

#app-navigation ul {
	padding: 10px;
	flex: 1;
	overflow-y: auto;
	list-style: none;
	margin: 0;
}

#app-navigation ul li {
	position: relative;
	margin-bottom: 2px;
}

#app-navigation ul li a {
	display: block;
	height: 44px;
	line-height: 44px;
	padding: 0 12px 0 44px;
	border-radius: var(--border-radius-large);
	color: var(--color-main-text);
	background-position: 14px center;
	background-repeat: no-repeat;
	background-size: 20px;
	transition: background-color 0.2s, color 0.2s;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-decoration: none;
	cursor: pointer;
}

#app-navigation ul li a:hover,
#app-navigation ul li a:focus {
	background-color: var(--color-background-hover);
}

#app-navigation ul li.active a {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

#app-navigation ul li.active a [class^='icon-'],
#app-navigation ul li.active a [class*=' icon-'] {
	filter: brightness(0) invert(1);
}

.app-navigation-entry {
	border-top: 1px solid var(--color-border);
	padding: 10px;
}

.settings-button {
	display: flex;
	align-items: center;
	width: 100%;
	padding: 10px 12px;
	background: transparent;
	border: none;
	border-radius: var(--border-radius-large);
	color: var(--color-main-text);
	cursor: pointer;
	transition: background-color 0.2s;
	font-size: 14px;
}

.settings-button:hover {
	background-color: var(--color-background-hover);
}

.settings-button .icon-settings {
	margin-right: 8px;
	width: 20px;
	height: 20px;
	display: inline-block;
}

#app-settings-content {
	transition: max-height 0.3s ease;
	overflow: hidden;
}

#app-settings-content.hidden {
	max-height: 0;
	display: none;
}

#app-settings-content:not(.hidden) {
	max-height: 500px;
	display: block;
}

#app-settings-content ul {
	list-style: none;
	padding: 0;
	margin: 0;
}

#app-settings-content ul li {
	margin-bottom: 2px;
}

#app-settings-content ul li a {
	display: block;
	height: 44px;
	line-height: 44px;
	padding: 0 12px 0 44px;
	border-radius: var(--border-radius-large);
	color: var(--color-main-text);
	background-position: 14px center;
	background-repeat: no-repeat;
	background-size: 20px;
	transition: background-color 0.2s, color 0.2s;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-decoration: none;
	cursor: pointer;
}

#app-settings-content ul li a:hover {
	background-color: var(--color-background-hover);
}
</style>

