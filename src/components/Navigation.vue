<template>
	<nav id="app-navigation-vue" class="app-navigation__content" aria-label="ClientHub">
		<div class="app-navigation__body">
			<ul class="app-navigation-list">
				<li
					v-for="item in menuItems"
					:key="item.id"
					class="app-navigation-entry-wrapper"
					:class="{ 'app-navigation-entry--opened': $props.currentTab === item.id }"
				>
					<div class="app-navigation-entry" :class="{ active: $props.currentTab === item.id }">
						<a
							href="#"
							class="app-navigation-entry-link"
							:aria-current="$props.currentTab === item.id ? 'page' : undefined"
							@click.prevent="handleTabClick(item.id)"
						>
							<div class="app-navigation-entry-icon">
								<MaterialIcon :name="item.icon" :size="20" />
							</div>
							<span class="app-navigation-entry__name">
								{{ translate('domaincontrol', item.label) }}
							</span>
						</a>
					</div>
				</li>
			</ul>
		</div>
		<ul class="app-navigation-entry__settings">
			<li class="app-navigation-entry-wrapper">
				<div class="app-navigation-entry" :class="{ active: $props.currentTab === 'settings' }">
					<a
						href="#"
						class="app-navigation-entry-link"
						:aria-current="$props.currentTab === 'settings' ? 'page' : undefined"
						@click.prevent="handleTabClick('settings')"
					>
						<div class="app-navigation-entry-icon">
							<MaterialIcon name="settings" :size="20" />
						</div>
						<span class="app-navigation-entry__name">
							{{ translate('domaincontrol', 'Settings') }}
						</span>
					</a>
				</div>
			</li>
		</ul>
	</nav>
</template>

<script>
import MaterialIcon from './MaterialIcon.vue'

export default {
	name: 'Navigation',
	components: {
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
			menuItems: [
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
/* Nextcloud native navigation structure - Following official guidelines */
/* Reference: https://docs.nextcloud.com/server/latest/developer_manual/html_css_design/index.html */

.app-navigation__content {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.app-navigation__body {
	flex: 1;
	overflow-y: auto;
	overflow-x: hidden;
}

.app-navigation-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.app-navigation-entry-wrapper {
	margin: 0;
}

.app-navigation-entry {
	position: relative;
}

.app-navigation-entry-link {
	display: flex;
	align-items: center;
	padding: 0 12px;
	color: var(--color-main-text);
	text-decoration: none;
	border-radius: var(--border-radius-large);
	transition: background-color 0.2s, color 0.2s;
	cursor: pointer;
	min-height: 44px;
	width: 100%;
	box-sizing: border-box;
	gap: 12px;
}

.app-navigation-entry-link:hover,
.app-navigation-entry-link:focus {
	background-color: var(--color-background-hover);
}

.app-navigation-entry.active .app-navigation-entry-link,
.app-navigation-entry-link[aria-current='page'] {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

/* Icon styling - Nextcloud standard with Material Design icons */
/* Following Nextcloud's official navigation structure */
/* Reference: https://docs.nextcloud.com/server/latest/developer_manual/html_css_design/navigation.html */
.app-navigation-entry-icon {
	width: 20px;
	height: 20px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
	margin-right: 0;
}

.app-navigation-entry-icon .material-icon {
	width: 20px;
	height: 20px;
	color: var(--color-main-text);
	transition: color 0.2s;
	opacity: 0.7;
}

.app-navigation-entry-link:hover .app-navigation-entry-icon .material-icon {
	opacity: 1;
}

.app-navigation-entry.active .app-navigation-entry-icon .material-icon,
.app-navigation-entry-link[aria-current='page'] .app-navigation-entry-icon .material-icon {
	color: var(--color-primary-element-text);
	opacity: 1;
}

.app-navigation-entry__name {
	flex: 1;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	font-size: var(--default-font-size);
	line-height: var(--default-line-height);
}

.app-navigation-entry__settings {
	list-style: none;
	padding: 0;
	margin: 0;
	border-top: 1px solid var(--color-border);
}
</style>

